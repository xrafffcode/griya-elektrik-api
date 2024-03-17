<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductBrandRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:product_brands,code,'.$this->route('id'),
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'delete_logo' => 'nullable|boolean',
            'slug' => 'nullable|string|max:255|unique:product_brands,slug,'.$this->route('id'),
        ];
    }

    public function prepareForValidation()
    {
        if (! $this->has('slug')) {
            $this->merge(['slug' => null]);
        }

        if (! $this->hasFile('logo')) {
            $this->merge(['logo' => null]);
        }

        if (! $this->has('delete_logo')) {
            $this->merge(['delete_logo' => false]);
        }
    }
}
