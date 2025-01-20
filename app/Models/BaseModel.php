<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasFactory;

    /**
     * Resolve route binding using custom logic.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $model = $this->where($field ?? $this->getRouteKeyName(), $value)->first();

        if (!$model) {
            abort(apiResponse(null,
                __('Service Offer not found..')
                , 404));
        }

        return $model;
    }
}
