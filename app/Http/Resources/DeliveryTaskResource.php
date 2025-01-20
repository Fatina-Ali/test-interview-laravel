<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryTaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'latitude_start'=>$this->latitude_start,
            'longitude_start'=>$this->longitude_start,
            'latitude_end'=>$this->latitude_end,
            'longitude_end'=>$this->longitude_end,
            'shipments'=>$this->shipment_ids
        ];
    }
}
