<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

        // Sanitasi input teks — strip tags untuk cegah XSS
        if ($this->has('kronologis_kejadian')) {
            $this->merge([
                'kronologis_kejadian' => strip_tags($this->input('kronologis_kejadian')),
            ]);
        }
        if ($this->has('mitigasi_tambahan')) {
            $this->merge([
                'mitigasi_tambahan' => strip_tags($this->input('mitigasi_tambahan')),
            ]);
        }
        if ($this->has('tindakan_awal')) {
            $this->merge([
                'tindakan_awal' => strip_tags($this->input('tindakan_awal')),
            ]);
        }
        if ($this->has('other_item_description')) {
            $this->merge([
                'other_item_description' => strip_tags($this->input('other_item_description')),
            ]);
        }
        if ($this->has('other_cause_description')) {
            $this->merge([
                'other_cause_description' => strip_tags($this->input('other_cause_description')),
            ]);
        }
        if ($this->has('dampak_non_finansial')) {
            $this->merge([
                'dampak_non_finansial' => strip_tags($this->input('dampak_non_finansial')),
            ]);
        }
    }

    public function rules(): array
    {
        $kategori = (string) $this->input('kategori');

        $rules = [
            'kategori' => ['required', 'in:finansial,non-finansial'],
            'tanggal_kejadian' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'tanggal_diketahui' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today', 'after_or_equal:tanggal_kejadian'],

            'risk_item_id' => ['required', 'integer', 'min:1', 'exists:risk_items,id'],
            'other_item_description' => ['nullable', 'string', 'max:255'],

            'risk_cause_id' => ['nullable', 'integer', 'min:1', 'exists:risk_causes,id', 'required_without:other_cause_description'],
            'other_cause_description' => ['nullable', 'string', 'max:255', 'required_without:risk_cause_id'],
            'kronologis_kejadian' => ['required', 'string', 'min_words:20', 'max:5000'],

            'mitigasi_tambahan' => ['nullable', 'string', 'max:2000'],

            'tindakan_awal' => ['nullable', 'string', 'max:2000'],
            'status_awal' => ['required', 'in:open,in_progress'],

            // Validasi durasi
            'durasi_penyelesaian' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'durasi_satuan' => ['nullable', 'in:menit,jam,hari,minggu,bulan'],
        ];

        if ($kategori === 'finansial') {
            $rules['dampak_finansial'] = ['required', 'numeric', 'min:0', 'max:999999999999.99'];
        } else {
            $rules['skala_dampak'] = ['required', 'string', 'max:50'];
            $rules['dampak_non_finansial'] = ['required', 'string', 'max:2000'];
        }

        return $rules;
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'tanggal_kejadian.before_or_equal' => 'Tanggal kejadian tidak boleh melebihi hari ini.',
            'tanggal_diketahui.before_or_equal' => 'Tanggal diketahui tidak boleh melebihi hari ini.',
            'tanggal_diketahui.after_or_equal' => 'Tanggal diketahui harus setelah atau sama dengan tanggal kejadian.',
            'kronologis_kejadian.min_words' => 'Kronologis kejadian minimal 20 kata.',
            'kronologis_kejadian.max' => 'Kronologis kejadian maksimal 5000 karakter.',
            'dampak_finansial.max' => 'Nilai dampak finansial terlalu besar.',
            'durasi_penyelesaian.max' => 'Durasi penyelesaian maksimal 9999.',
        ];
    }
}
