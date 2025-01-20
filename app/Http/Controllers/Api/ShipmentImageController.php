<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShipmentImageResource;
use App\Models\Shipment;
use App\Models\ShipmentImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShipmentImageController extends Controller
{

    protected $user_id;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user_id = auth('sanctum')->user()->id ?? null;
            // Proceed with the request
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $shipment = Shipment::find($request->shipment_id);
        if ($this->user_id !== $shipment->sender_id) {
            return apiResponse(null, __('You can only edit your own shipment.'), 403);
        }
        $request->validate([
            'shipment_id'   => 'required',
            'image'    => 'required|mimes:pdf,jpeg,gif,png,jpg,PNG|max:2048'
        ]);
        $fileName = '';
        if ($file = $request->file('image')) {
            $attachment = $request->file('image');
            //$name = $attachment->getClientOriginalName();
            $fileName = time().'_'.rand(0,29999).'.'.$attachment->getClientOriginalExtension();
            $destinationPath = 'shipments';
            $filePath1 = $attachment->move($destinationPath, $fileName);
        }
        $shipmentImage = ShipmentImage::create([
            'shipment_id' => $request->shipment_id,
            'image' => $fileName
        ]);
        return apiResponse(new ShipmentImageResource($shipmentImage));

    }

    /**
     * Display the specified resource.
     */
    public function show(ShipmentImage $shipments_image)
    {
        return apiResponse(ShipmentImageResource::make($shipments_image));
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
    public function destroy(ShipmentImage $shipments_image)
    {
       // Log::info($shipments_image->id);
        $shipments_image->delete();
        return  apiResponse(null,__('Deleted Successfully'));
    }
}
