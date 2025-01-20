<?php

namespace App\Http\Controllers;

use App\Http\Requests\CountryRequest;
use App\Models\Country;
use App\Models\Prohibition;
use App\Models\Restriction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use PHPUnit\Framework\Constraint\Count;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Country::all();
        return view('backend.countries.index', compact( 'data'));
    }

    public function create()
    {

        $restrictions = Restriction::all();
        $prohibitions = Prohibition::all();
        return view('backend.countries.create', compact( 'prohibitions','restrictions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CountryRequest $request)
    {
        // validate
        $data = $request->validated();
        $saved = false;
        $country = new Country();
        $country->name = $request->name;
        $country->code = '';

        if($country->save()){
            $saved = true;
        }
        if($request->restrictions) {
            $newResrictions = array_keys($request->restrictions);
            $country->restrictions()->attach($newResrictions);
        }
        if($request->prohibitions){
            $newProhibitions = array_keys($request->prohibitions);
            $country->prohibitions()->attach($newProhibitions);
        }

        if ($saved){
            return response(['msg' => 'Country is added successfully.'], 200);
        }
        else{
            return redirect('countries')->with('error', 'Failed to add this Country, try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    public function edit($country)
    {
        $data = Country::findOrFail($country);
        //var_dump($data->restrictions);exit();
        $countryResrictions = $data->restrictions->pluck('id')->toArray();
        $countryProhibitions = $data->prohibitions->pluck('id')->toArray();
        $restrictions = Restriction::all();
        $prohibitions = Prohibition::all();
        return view('backend.countries.edit', compact( 'data','restrictions','prohibitions','countryResrictions','countryProhibitions'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request , $id)
    {
        //var_dump($request->restrictions);exit();
        $country = Country::find($id);
        $newResrictions = array_keys($request->restrictions);
        $country->restrictions()->detach();
        $country->restrictions()->attach($newResrictions);

        $newProhibitions = array_keys($request->prohibitions);
        $country->prohibitions()->detach();
        $country->prohibitions()->attach($newProhibitions);

        return redirect()->route('countries.index')->with('success', 'Successfully Updated.');


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id){
        try {
            $country = Country::findOrFail($id);
            $country->restrictions()->detach();
            $country->prohibitions()->detach();
            if ($country->delete()) {
                return redirect()->route('countries.index')->with('success', 'Successfully removed.');
            } else {
                return redirect()->route('countries.index')->with('error', 'Failed to remove this Country.');
            }
        } catch (ModelNotFoundException $exception) {
            return redirect()->route('countries.index')->with('error', 'Country not found.');
        }
    }
}
