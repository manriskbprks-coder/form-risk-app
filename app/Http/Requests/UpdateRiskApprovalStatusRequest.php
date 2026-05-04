<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRiskApprovalStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:approved,rejected'],
            'alasan_reject' => ['required_if:status,rejected', 'string', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'alasan_reject.required_if' => 'Wajib mengisi alasan penolakan.',
            'alasan_reject.min' => 'Alasan penolakan minimal 10 karakter.',
        ];
    }
}
