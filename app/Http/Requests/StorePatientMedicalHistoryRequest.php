<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientMedicalHistoryRequest extends FormRequest
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
            'has_allergy' => 'required|boolean',
            'allergy_detail' => 'required_if:has_allergy,true|nullable|string',
            'has_systemic_disease' => 'required|boolean',
            'systemic_disease_detail' => 'required_if:has_systemic_disease,true|nullable|string',
            'undergoing_treatment' => 'required|boolean',
            'treatment_detail' => 'required_if:undergoing_treatment,true|nullable|string',
            'ever_hospitalized' => 'required|boolean',
            'hospitalized_reason' => 'required_if:ever_hospitalized,true|nullable|string',
            'smoking_or_alcohol' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'ID pasien wajib diisi',
            'patient_id.exists' => 'Data pasien tidak ditemukan',
            'has_allergy.required' => 'Status alergi wajib dipilih',
            'allergy_detail.required_if' => 'Detail alergi wajib diisi jika memiliki alergi',
            'has_systemic_disease.required' => 'Status penyakit sistemik wajib dipilih',
            'systemic_disease_detail.required_if' => 'Detail penyakit wajib diisi jika ada penyakit sistemik',
            'undergoing_treatment.required' => 'Status pengobatan wajib dipilih',
            'treatment_detail.required_if' => 'Detail pengobatan wajib diisi jika sedang menjalani treatment',
            'ever_hospitalized.required' => 'Status rawat inap wajib dipilih',
            'hospitalized_reason.required_if' => 'Alasan rawat inap wajib diisi',
            'smoking_or_alcohol.required' => 'Status merokok/alkohol wajib dipilih',
        ];
    }
}
