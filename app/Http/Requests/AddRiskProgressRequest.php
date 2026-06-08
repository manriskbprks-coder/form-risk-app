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
            'note' => ['required', 'string', 'min:5'],
            'new_status' => ['nullable', 'in:approved_in_progress,closed'],
        ];
    }
}

