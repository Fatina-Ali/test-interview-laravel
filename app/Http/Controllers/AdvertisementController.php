<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestrictionRequest;
use App\Models\Advertisement;
use App\Models\Restriction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdvertisementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Advertisement::all();
        return view('backend.advertisements.index', compact( 'data'));
    }

    public function create()
    {
        $data = Restriction::all();
        return view('backend.advertisements.create', compact( 'data'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(RestrictionRequest $request)
    {
        // validate
        $data = $request->validated();

        // insert
        if (Restriction::insert($data))
            return response(['msg' => 'Restriction is added successfully.'], 200);
        else
            return redirect('advertisements')->with('error', 'Failed to add this Restriction, try again.');
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

    public function destroy($id){
    //Log::info($id);
        try {
            $restriction = Restriction::findOrFail($id);
            if ($restriction->delete()) {
                return redirect()->route('advertisements.index')->with('success', 'Successfully removed.');
            } else {
                return redirect()->route('advertisements.index')->with('error', 'Failed to remove this Restriction.');
            }
        } catch (ModelNotFoundException $exception) {
            return redirect()->route('advertisements.index')->with('error', 'Restriction not found.');
        }
    }


}
