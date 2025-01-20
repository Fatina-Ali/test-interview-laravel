<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryTask extends Model
{
    use HasFactory;

    protected $guarded=['id'];
    protected $casts=[
        'shipment_ids'=>'array'
    ];

    public function findNearbyShipments()
    {

        $radius = 0.000454;

        return Shipment::whereHas('senderAddress', function ($query) use ($radius) {
            $query->whereBetween('latitude', [$this->latitude_start - $radius, $this->latitude_start + $radius])
                ->whereBetween('longitude', [$this->longitude_start - $radius, $this->longitude_start + $radius]);
        })->orWhereHas('receiverAddress', function ($query) use ($radius) {
            $query->whereBetween('latitude', [$this->latitude_end - $radius, $this->latitude_end + $radius])
                ->whereBetween('longitude', [$this->longitude_end - $radius, $this->longitude_end + $radius]);
        })->where('status', 0)->get();
    }

}
