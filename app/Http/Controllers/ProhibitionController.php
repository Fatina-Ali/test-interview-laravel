<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProhibitionRequest;
use App\Http\Requests\RestrictionRequest;
use App\Models\Prohibition;
use App\Models\Restriction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProhibitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Prohibition::all();
        return view('backend.prohibitions.index', compact( 'data'));
    }

    public function create()
    {
        return view('backend.prohibitions.create');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(ProhibitionRequest $request)
    {
        // validate
        $data = $request->validated();
        $pro = new Prohibition();
        $pro->name = $data['name'];

        // insert
        if ($pro->save())
            return response(['msg' => 'Prohibition is added successfully.'], 200);
        else
            return redirect('prohibitions')->with('error', 'Failed to add this Prohibition, try again.');
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
            $pro = Prohibition::findOrFail($id);
            if ($pro->delete()) {
                return redirect()->route('prohibitions.index')->with('success', 'Successfully removed.');
            } else {
                return redirect()->route('prohibitions.index')->with('error', 'Failed to remove this Prohibition.');
            }
        } catch (ModelNotFoundException $exception) {
            return redirect()->route('prohibitions.index')->with('error', 'Prohibition not found.');
        }
    }

}
