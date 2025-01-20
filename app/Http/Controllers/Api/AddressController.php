<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Client;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $user_id;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user_id = auth('sanctum')->user()->id ?? null;
            // Proceed with the request
            return $next($request);
        });
    }


    public function index()
    {
        $addresses = Address::where('user_id',$this->user_id)->get();
        return apiResponse(AddressResource::collection($addresses));
    }

    /**
     * Store a newly created resource in storage.
     */


    public function store(Request $request)
    {
        try {
            $latitude = $request->latitude;
            $longitude = $request->longitude;

            $client = auth()->user()->client;

            $addressCount = Address::where('client_id', $client->id)->count();
            if ($addressCount >= 100) {
                throw new Exception("You've reached the maximum count of addresses!");
            }

            $apiKey = env('GOOGLE_MAPS_API_KEY');
            $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
                'latlng' => "{$latitude},{$longitude}",
                'key' => $apiKey
            ]);

            if ($response->failed()) {
                throw new Exception("Google Maps API request failed.");
            }

            $data = $response->json();
            $formatted_address = $data['results'][0]['formatted_address'] ?? null;

            if (!$formatted_address) {
                throw new Exception("Formatted address not found.");
            }

            $address = new Address([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'location_description' => $formatted_address,
                'country_id' => $request->country_id,
                'client_id' => $client->id
            ]);

            $address->indexByLocation();

            $address->save();

            return apiResponse(['address' => new AddressResource($address)]);
        } catch (Exception $e) {
            return apiResponse(null, $e->getMessage(), 400);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show( $address)
    {

        //return apiResponse(AddressResource::make($address));
        try {
            // Find the address by ID, or throw an exception if not found
            $address = Address::findOrFail($address);
            // Return the response if the address is found
            return apiResponse(AddressResource::make($address));
        } catch (ModelNotFoundException $e) {
            // Return a response if the address is not found
            return apiResponse(null, 'Address not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Address $address)
    {
        $address->update($request->only(['name', 'latitude','longitude']));
        $address->indexByLocation();
        return  apiResponse(new AddressResource($address));
    }



    /**
     * Move address to another client's account
     */
    function moveAddress(Request $request) {
        try{
            $address = Address::find($request->address_id);
            $address->client_id = $request->client_id;
            $address->save();
            return \apiResponse(['message'  => 'Moved successfully!']);
        }catch(Exception $e){
            return \apiResponse(null,$e->getMessage(),400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        $address->delete();
        return  apiResponse(null,__('Deleted Successfully'));
    }
}
