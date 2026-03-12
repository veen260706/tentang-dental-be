<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientDentalHistoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'frequent_tooth_pain' => 'required|boolean',
            'tooth_pain_detail' => 'required_if:frequent_tooth_pain,true|nullable|string',
            'bleeding_gums' => 'required|boolean',
            'ever_dental_treatment' => 'required|boolean',
            'dental_treatment_detail' => 'required_if:ever_dental_treatment,true|nullable|string',
            'brushing_frequency' => 'required|in:1,2,more_than_2',
            'use_floss_or_mouthwash' => 'required|boolean',
            'bad_habits' => 'required|boolean',
            'bad_habits_detail' => 'required_if:bad_habits,true|nullable|string',
            'ever_braces' => 'required|boolean',
            'braces_years' => 'required_if:ever_braces,true|nullable|integer|min:0',
            'root_canal_treatment' => 'required|boolean',
            'root_canal_detail' => 'required_if:root_canal_treatment,true|nullable|string',
            'dentures' => 'required|boolean',
            'routine_checkup' => 'required|boolean',
            'dental_checkup_frequency' => 'required|in:6_months,1_year,more_than_1_year,never',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'ID pasien wajib diisi',
            'patient_id.exists' => 'Data pasien tidak ditemukan',
            'frequent_tooth_pain.required' => 'Status sakit gigi wajib dipilih',
            'tooth_pain_detail.required_if' => 'Detail sakit gigi wajib diisi',
            'bleeding_gums.required' => 'Status gusi berdarah wajib dipilih',
            'ever_dental_treatment.required' => 'Status perawatan gigi wajib dipilih',
            'dental_treatment_detail.required_if' => 'Detail perawatan gigi wajib diisi',
            'brushing_frequency.required' => 'Frekuensi sikat gigi wajib dipilih',
            'brushing_frequency.in' => 'Frekuensi sikat gigi tidak valid',
            'use_floss_or_mouthwash.required' => 'Penggunaan floss/mouthwash wajib dipilih',
            'bad_habits.required' => 'Status kebiasaan buruk wajib dipilih',
            'bad_habits_detail.required_if' => 'Detail kebiasaan buruk wajib diisi',
            'ever_braces.required' => 'Status behel wajib dipilih',
            'braces_years.required_if' => 'Lama penggunaan behel wajib diisi',
            'braces_years.integer' => 'Lama behel harus berupa angka',
            'root_canal_treatment.required' => 'Status PSA wajib dipilih',
            'root_canal_detail.required_if' => 'Detail PSA wajib diisi',
            'dentures.required' => 'Status gigi palsu wajib dipilih',
            'routine_checkup.required' => 'Status checkup rutin wajib dipilih',
            'dental_checkup_frequency.required' => 'Frekuensi checkup wajib dipilih',
            'dental_checkup_frequency.in' => 'Frekuensi checkup tidak valid',
        ];
    }
}
