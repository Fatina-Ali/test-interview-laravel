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
    protected $user_id;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user_id = auth('sanctum')->user()->id ?? null;
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $addresses = Address::where('user_id', $this->user_id)->get();
        return apiResponse(AddressResource::collection($addresses));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $client = auth()->user()->client;

            // Check if the client has reached the address limit
            $addressCount = Address::where('client_id', $client->id)->count();
            if ($addressCount >= 100) {
                throw new Exception("You've reached the maximum count of addresses!");
            }

            // Get the formatted address using the latitude and longitude
            $formattedAddress = $this->getFormattedAddress($request->latitude, $request->longitude);

            $address = Address::create([
                'name' => $request->name,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_description' => $formattedAddress,
                'country_id' => $request->country_id,
                'client_id' => $client->id,
            ]);

            $address->indexByLocation();
            $address->save();

            // Attach the client to the address
            $this->manageAddressClients($address, $client->id, 'attach');

            return apiResponse(['address' => new AddressResource($address)]);
        } catch (Exception $e) {
            return apiResponse(null, $e->getMessage(), 400);
        }
    }

    /**
     * Add address to user's list.
     */
    public function addAddressToMyList(Request $request)
    {
        try {
            $address = Address::findOrFail($request->id);
            $this->manageAddressClients($address, auth()->user()->client_id, 'attach');
            return apiResponse(['address' => new AddressResource($address)]);
        } catch (Exception $e) {
            return apiResponse(null, $e->getMessage(), 400);
        }
    }

    /**
     * Move address to another client's account.
     */
    public function moveAddress(Request $request)
    {
        try {
            $address = Address::findOrFail($request->address_id);
            $address->client_id = $request->client_id;
            $address->save();
            return apiResponse(['message' => 'Moved successfully!']);
        } catch (Exception $e) {
            return apiResponse(null, $e->getMessage(), 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        // Detach the current client from the address and delete it if no clients are attached
        $this->manageAddressClients($address, auth()->user()->client_id, 'detach');
        return apiResponse(null, __('Deleted Successfully'));
    }

    /**
     * Manage the clients associated with the address.
     */
    private function manageAddressClients(Address $address, $clientId, $action = 'attach')
    {
        if ($action === 'attach') {
            $address->clients()->attach($clientId);
        } elseif ($action === 'detach') {
            $address->clients()->detach($clientId);
            if ($address->clients()->count() == 0) {
                $address->delete();
            }
        }
    }

    /**
     * Get the formatted address based on latitude and longitude.
     */
    private function getFormattedAddress($latitude, $longitude)
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
            'latlng' => "{$latitude},{$longitude}",
            'key' => $apiKey
        ]);

        if ($response->failed()) {
            throw new Exception("Google Maps API request failed.");
        }

        $data = $response->json();
        return $data['results'][0]['formatted_address'] ?? null;
    }
}
