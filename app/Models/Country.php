<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
    ];

    public function prohibitions()
    {
        return $this->belongsToMany(Prohibition::class,'countries_prohibitions','country_id', 'prohibition_id');
    }

    public function restrictions()
    {
        return $this->belongsToMany(Restriction::class,'countries_restrictions','country_id', 'restriction_id');
    }
}
