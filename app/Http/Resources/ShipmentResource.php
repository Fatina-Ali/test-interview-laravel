<?php

namespace App\Http\Resources;

use App\Models\Shipment;
use App\Models\ShipmentImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use function Psy\Shell;

class ShipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'serial_num'    => $this->serial_num,
            'receiver' => new ClientResource($this->receiver),
            'receiver_address'  => new AddressResource($this->receiverAddress),
            'sender_address'  => new AddressResource($this->senderAddress),
            'description' => $this->description ?? '',
            'created_at' => $this->created_at,
            'status'  => Shipment::STATUS[\strval($this->status)],
            'images' =>  (ShipmentImageResource::collection($this->images)) ,
            'created_at_unix'   => $this->created_at_unix,
            'on_the_way_at_unix'   => $this->on_the_way_at_unix,
            'expected_arrival_at_unix'   => $this->expected_arrival_at_unix,
        ];
    }
}
