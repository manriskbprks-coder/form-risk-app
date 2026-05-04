<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRiskReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('risk_cause_id') === 'other') {
            $this->merge(['risk_cause_id' => null]);
        }
    }

    public function rules(): array
    {
        $kategori = (string) $this->input('kategori');

        $rules = [
            'kategori' => ['required', 'in:finansial,non-finansial'],
            'tanggal_kejadian' => ['required', 'date'],
            'tanggal_diketahui' => ['required', 'date'],

            'risk_item_id' => ['required', 'integer', 'exists:risk_items,id'],
            'other_item_description' => ['nullable', 'string', 'max:255'],

            'risk_cause_id' => ['nullable', 'integer', 'exists:risk_causes,id', 'required_without:other_cause_description'],
            'other_cause_description' => ['nullable', 'string', 'max:255', 'required_without:risk_cause_id'],
            'kronologis_kejadian' => ['required', 'string', 'min_words:20'],

            'mitigasi_tambahan' => ['nullable', 'string'],

            'tindakan_awal' => ['nullable', 'string'],
            'status_awal' => ['required', 'in:open,in_progress'],
        ];

        if ($kategori === 'finansial') {
            $rules['dampak_finansial'] = ['required', 'numeric', 'min:0'];
        } else {
            $rules['skala_dampak'] = ['required', 'string', 'max:50'];
            $rules['dampak_non_finansial'] = ['required', 'string'];
        }

        return $rules;
    }
}

