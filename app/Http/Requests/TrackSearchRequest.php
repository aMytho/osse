<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackSearchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'track_offset' => 'nullable|int|min:0',
            'track' => 'nullable|string'
        ];
    }
}
