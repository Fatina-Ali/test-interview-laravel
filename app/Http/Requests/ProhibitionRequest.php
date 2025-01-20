<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProhibitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return 1;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // get the brand id ( for updating only )
        $currentProhibitionId = 0;
        if ($this->has('brand_id')){
            $currentProhibitionId = $this->get('id');
        }

        return [
            'name' =>  ['required', 'string', 'max:150',
                Rule::unique('prohibitions')->ignore($currentProhibitionId, 'id')]
        ];
    }
}
