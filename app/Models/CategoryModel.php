<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_name',
        'category_image',
        'category_slug',

    ];
    protected $guarded = [];

    protected $table = 'category';
    public $timestamps = false;
    protected $primaryKey = 'category_id';

    public function subcategories(){
        return $this->hasMany(SubCategoryModel::class, 'category_id');
    }
}
