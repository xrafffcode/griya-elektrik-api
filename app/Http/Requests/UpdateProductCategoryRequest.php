<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductCategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|exists:product_categories,id',
            'code' => 'required|string|max:255|unique:product_categories,code,'.$this->route('id'),
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'slug' => 'nullable|string|max:255|unique:product_categories,slug,'.$this->route('id'),
        ];
    }

    public function prepareForValidation()
    {
        if (! $this->has('parent_id')) {
            $this->merge(['parent_id' => null]);
        }

        if (! $this->has('slug')) {
            $this->merge(['slug' => null]);
        }

        if (! $this->has('image')) {
            $this->merge(['image' => null]);
        }
    }
}
