<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QueueRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ids' => 'nullable|array',
            'ids.*' => 'int',
            // Index of the active track
            'active_track' => [
                'nullable',
                'int',
                'gte:0',
                'lt:'.count($this->input('ids', 0)),
            ],
        ];
    }
}
