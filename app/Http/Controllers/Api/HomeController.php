<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Http\Resources\AdvertisementResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\ShipmentResource;
use App\Models\Address;
use App\Models\Advertisement;
use App\Models\CategoryModel;
use App\Models\Country;
use App\Models\Shipment;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shipments = ShipmentResource::collection(Shipment::where('sender_id',\auth()->user()->client->id)
                            ->orderBy('created_at','DESC')->take(20)->get());
        $countries = CountryResource::collection(Country::all());
        $advertisements = AdvertisementResource::collection(Advertisement::all());
        $addresses = AddressResource::collection(Address::where('client_id',auth()->user()->client->id)->get());
        return apiResponse([
            'shipments'    => $shipments,
            'countries'     => $countries,
            'advertisements'    => $advertisements,
            'addresses'     => $addresses
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
