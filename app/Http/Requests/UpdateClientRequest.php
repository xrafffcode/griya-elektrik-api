<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:clients,name,'.$this->route('id').',id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'delete_logo' => 'nullable|boolean',
            'url' => 'nullable|url',
        ];
    }

    public function prepareForValidation()
    {
        if (! $this->hasFile('logo')) {
            $this->merge(['logo' => null]);
        }

        if (! $this->has('delete_logo')) {
            $this->merge(['delete_logo' => false]);
        }

        if (! $this->has('url')) {
            $this->merge(['url' => null]);
        }
    }
}
