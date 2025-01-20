<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProhibitionResource;
use App\Models\Prohibition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProhibitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $countryId = $request->country_id;
        //Log::info($countryId);
        $prohibitions = Prohibition::query()
            ->with('countries') // Eager load the countries relationship
            ->when($countryId, function ($query, $countryId) {
                $query->whereHas('countries', function ($query) use ($countryId) {
                    $query->where('countries.id', $countryId);
                });
            })
            ->get();
        return apiResponse(ProhibitionResource::collection($prohibitions));
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
