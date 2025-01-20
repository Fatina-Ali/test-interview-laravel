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

    ];
    use HasFactory;

    public function country() {
        return $this->belongsTo(Country::class);
    }
}
