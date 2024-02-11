<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function rules()
    {
        return [
            'code' => 'required|string|max:255|unique:products,code',
            'slug' => 'required|unique:products,slug',
            'product_category_id' => 'required',
            'product_brand_id' => 'required',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ];
    }
}
