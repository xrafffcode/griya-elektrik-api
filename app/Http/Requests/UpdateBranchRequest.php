<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:branches,code,'.$this->route('id'),
            'name' => 'required|string|max:255',
            'map_url' => 'required|string|max:255',
            'iframe_map' => 'required|string',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'sort' => 'required|integer',
            'is_main' => 'required|boolean',
            'is_active' => 'required|boolean',
            'branch_images' => 'nullable|array',
            'branch_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'string|exists:branch_images,id',
        ];
    }

    public function prepareForValidation()
    {
        if (! $this->has('email')) {
            $this->merge(['email' => null]);
        }

        if (! $this->has('phone')) {
            $this->merge(['phone' => null]);
        }

        if (! $this->has('facebook')) {
            $this->merge(['facebook' => null]);
        }

        if (! $this->has('instagram')) {
            $this->merge(['instagram' => null]);
        }

        if (! $this->has('youtube')) {
            $this->merge(['youtube' => null]);
        }

        if (! $this->has('branch_images')) {
            $this->merge(['branch_images' => null]);
        }

        if (! $this->has('deleted_images')) {
            $this->merge(['deleted_images' => []]);
        }
    }
}
