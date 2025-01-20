<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RestrictionRequest extends FormRequest
{
    //private const ALLOWED_EXTENSION = 'jpg,jpeg,png,webp,gif';
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return 1;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // get the brand id ( for updating only )
        $currentBrandId = 0;
        if ($this->has('brand_id')){
            $currentBrandId = $this->get('brand_id');
        }

        return [
            'name' =>  ['required', 'string', 'max:150',
                Rule::unique('restrictions')->ignore($currentBrandId, 'id')]
        ];
    }
}
