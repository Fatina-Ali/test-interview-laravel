<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name'  => $this->name,
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,
            'country'   => new CountryResource($this->country),
            'location_description'  => $this->location_description,
        ];
    }
}
