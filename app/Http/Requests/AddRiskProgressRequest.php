<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddRiskProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'note' => ['required', 'string', 'min_words:10'],
            'new_status' => ['nullable', 'in:approved_in_progress,closed'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'note.required' => 'Catatan progress/penyelesaian wajib diisi.',
            'note.min_words' => 'Catatan progress/penyelesaian minimal terdiri dari 10 kata.',
        ];
    }
}

