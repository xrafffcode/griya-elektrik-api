<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'desktop_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ];
    }
}
