<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRiskApprovalStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    protected function prepareForValidation(): void
    {
        // Sanitasi input teks — strip tags untuk cegah XSS
        if ($this->has('alasan_reject')) {
            $this->merge([
                'alasan_reject' => strip_tags($this->input('alasan_reject')),
            ]);
        }
        if ($this->has('alasan_revisi')) {
            $this->merge([
                'alasan_revisi' => strip_tags($this->input('alasan_revisi')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:approved,rejected'],
            'alasan_reject' => ['required_if:status,rejected', 'string', 'min:10', 'max:2000'],
            'alasan_revisi' => ['nullable', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'alasan_reject.required_if' => 'Wajib mengisi alasan penolakan.',
            'alasan_reject.min' => 'Alasan penolakan minimal 10 karakter.',
            'alasan_reject.max' => 'Alasan penolakan maksimal 2000 karakter.',
            'alasan_revisi.min' => 'Alasan revisi minimal 10 karakter.',
            'alasan_revisi.max' => 'Alasan revisi maksimal 2000 karakter.',
        ];
    }
}
