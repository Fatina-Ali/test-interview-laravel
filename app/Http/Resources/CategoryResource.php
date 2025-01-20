<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name' => $this->category_name,
            'image' => $this->category_image,
            'slug' => $this->category_slug,
            'sub-categories' => SubcategoryResource::collection($this->subcategories)
        ];
    }
}
