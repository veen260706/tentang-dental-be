<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRontgenRequest extends FormRequest
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
            'xray_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'detail' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'ID pasien wajib diisi',
            'patient_id.exists' => 'Data pasien tidak ditemukan',
            'xray_image.required' => 'Foto rontgen wajib diupload',
            'xray_image.image' => 'File harus berupa gambar',
            'xray_image.mimes' => 'Format gambar harus jpeg, jpg, png, atau webp',
            'xray_image.max' => 'Ukuran foto rontgen maksimal 5MB',
        ];
    }
}
