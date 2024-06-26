<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function rules()
    {
        return [
            'code' => 'required|string|max:255|unique:products,code',
            'product_category_id' => 'required|exists:product_categories,id',
            'product_brand_id' => 'required|exists:product_brands,id',
            'name' => 'required|string|max:255',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:15360',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'slug' => 'nullable|string|max:255|unique:product_categories,slug',
            'product_images' => 'nullable|array',
            'product_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:15360',
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
    }
}
