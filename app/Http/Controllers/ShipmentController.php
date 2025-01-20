<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;


class ShipmentController extends Controller
{
    public function index(){
        $data = Shipment::all();
        return view('backend.shipments.index', compact( 'data'));
    }

    public function edit(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'id' => 'required|exists:shipments,id',
        ]);
        // Retrieve shipment by ID
        $shipment = Shipment::find($request->id);

        return response()->json([
            'id' => $shipment->id,
            'serial_num' => $shipment->serial_num,
            'status' => $shipment->status // Include status
        ]);

    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'serial_num' => 'required|string|max:255',
            'status' => 'required|in:1,2,3,4,5' // Validate status
        ]);

        $shipment = Shipment::findOrFail($id);
        $shipment->status = $request->status; // Update status
        $shipment->save();

        //return response()->json(['message' => 'Shipment updated successfully']);
        return redirect()->route('shipment.index')->with('success', 'Successfully Updated.');
    }

    public function destroy($id){

        try {
            $sh = Shipment::findOrFail($id);
            if($sh->images)
                $sh->images()->delete();
            if ($sh->delete())
                return redirect()->route('shipment.index')->with('success', 'Successfully removed.');
            else
                return redirect('shipment.index')->with('error', 'Failed to remove this Shipment.');
        }catch (ModelNotFoundException $exception){
            return redirect('shipment')->with('error', 'Failed to remove this Prohibition.');
        }
    }
}
