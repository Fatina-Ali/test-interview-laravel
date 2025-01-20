<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restriction extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];

    public function countries()
    {
        return $this->belongsToMany(Country::class,'countries_restrictions','restriction_id', 'country_id');
    }
}
