<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{

    protected $table = 'addresses';
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'country_id',
        'client_id',
        'location_description',
        'client_north_to_south_order'
    ];
    use HasFactory;

    public function country() {
        return $this->belongsTo(Country::class);
    }


    public function clients() {
        return $this->belongsToMany(Client::class);

    }
    // Order client's addresses from north to south
    public function indexByLocation() {

        self::where('client_id', $this->client_id)
            ->where('id', '<', $this->id)
            ->orderBy('client_north_to_south_order', 'DESC')
            ->chunk(100, function ($addresses) {
                foreach ($addresses as $address) {

                    if ($address->latitude > $this->latitude) {

                        $address->increment('client_north_to_south_order');
                    } else {

                        $this->client_north_to_south_order = $address->client_north_to_south_order + 1;
                        $this->save();
                    }
                }
            });
    }
}
