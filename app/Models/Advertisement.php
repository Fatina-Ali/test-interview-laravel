<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{

    protected $table = 'advertisements';
    protected $fillable = [
        'name',
        'image_url',
        'link',

    ];
    use HasFactory;
}
