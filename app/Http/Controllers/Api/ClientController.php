<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $clients = Client::leftJoin('users','clients.user_id','=','users.id')
            ->where('phone','like', '%'.$request->q.'%')
            ->orWhere('name','like', '%'.$request->q.'%')
            ->get();
        return apiResponse(ClientResource::collection($clients));

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


    function filer(Request $request) {
        $linker = $request->route()->parameter('var');
        return \deStorager($linker);
    }
}
