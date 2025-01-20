<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryTaskResource;
use Illuminate\Http\Request;
use App\Models\DeliveryTask;
class DeliveryTaskController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'latitude_start' => 'required|numeric',
            'longitude_start' => 'required|numeric',
            'latitude_end' => 'required|numeric',
            'longitude_end' => 'required|numeric',
        ]);


        $deliveryTask = DeliveryTask::create($request->all());


        $nearbyShipments = $deliveryTask->findNearbyShipments();
        $shipmentIds = $nearbyShipments->pluck('id')->toArray();
        $deliveryTask->shipment_ids = $shipmentIds;
        $deliveryTask->save();

        return apiResponse(['delivery_task' => new DeliveryTaskResource($deliveryTask)]);
    }


}
