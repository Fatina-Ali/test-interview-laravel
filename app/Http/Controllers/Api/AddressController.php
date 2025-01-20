<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Client;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
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
        try{
            $address = Address::create([
                'name'  => $request->name,
                'latitude'  => $request->latitude,
                'longitude' => $request->longitude,
                'country_id'  =>  $request->country_id,
            ]);
            $address->clients()->attach(auth()->user()->client_id);
            return \apiResponse(['address'  => new AddressResource($address)]);
        }catch(Exception $e){
            return \apiResponse(null,$e->getMessage(),400);
        }

    }


    function addAddressToMyList(Request $request) {
        try{
            $address = Address::find($request->id);
            $address->clients()->attach(auth()->user()->client_id);
            return \apiResponse(['address'  => new AddressResource($address)]);
        }catch(Exception $e){
            return \apiResponse(null,$e->getMessage(),400);
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

        return  apiResponse(new AddressResource($address));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        $address->clients()->detach(\auth()->user()->client_id);
        if($address->clients()->count() == 0){
            $address->delete();
        }
        return  apiResponse(null,__('Deleted Successfully'));
    }


}
