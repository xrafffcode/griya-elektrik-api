<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function rules()
    {
        return [
            'code' => 'required|string|max:255|unique:products,code,'.$this->route('id').',id',
            'product_category_id' => 'required|exists:product_categories,id',
            'product_brand_id' => 'required|exists:product_brands,id',
            'name' => 'required|string|max:255',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'slug' => 'nullable|string|max:255|unique:products,slug,'.$this->route('id').',id',
            'product_images' => 'nullable|array',
            'product_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'string|distinct|exists:product_images,id',
            'product_links' => 'nullable|array',
            'product_links.*.name' => 'required|string|max:255',
            'product_links.*.url' => 'required|url',
        ];
    }

    public function prepareForValidation()
    {
        if (! $this->has('slug')) {
            $this->merge(['slug' => null]);
        }

        if (! $this->has('deleted_images')) {
            $this->merge(['deleted_images' => []]);
        }
    }
}
