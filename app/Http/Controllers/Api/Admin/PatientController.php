<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Http\Requests\UpdatePatientRequest;
use App\Helpers\FileHelper;

class PatientController extends Controller
{
    public function index()
    {
        try {
            $patients = Patient::with(['medicalHistory', 'dentalHistory'])
                ->latest()
                ->paginate(10);

            $data = [
                'patients' => $patients->map(function ($patient) {
                    return [
                        'id' => $patient->id,
                        'name' => $patient->name,
                        'phone' => $patient->phone,
                        'email' => $patient->email,
                        'date_of_birth' => $patient->date_of_birth,
                        'gender' => $patient->gender,
                        'created_at' => $patient->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'pagination' => [
                    'current_page' => $patients->currentPage(),
                    'last_page' => $patients->lastPage(),
                    'per_page' => $patients->perPage(),
                    'total' => $patients->total(),
                ],
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data pasien berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $patient = Patient::with(['medicalHistory', 'dentalHistory', 'reservations.service', 'reservations.doctor', 'rontgens'])
                ->find($id);

            if (!$patient) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Pasien tidak ditemukan'),
                    404
                );
            }

            $data = [
                'id' => $patient->id,
                'name' => $patient->name,
                'phone' => $patient->phone,
                'email' => $patient->email,
                'date_of_birth' => $patient->date_of_birth,
                'gender' => $patient->gender,
                'address' => $patient->address,
                'medical_history' => $patient->medicalHistory ? [
                    'id' => $patient->medicalHistory->id,
                    'blood_type' => $patient->medicalHistory->blood_type,
                    'allergies' => $patient->medicalHistory->allergies,
                    'current_medications' => $patient->medicalHistory->current_medications,
                    'medical_conditions' => $patient->medicalHistory->medical_conditions,
                ] : null,
                'dental_history' => $patient->dentalHistory ? [
                    'id' => $patient->dentalHistory->id,
                    'last_dental_visit' => $patient->dentalHistory->last_dental_visit,
                    'dental_problems' => $patient->dentalHistory->dental_problems,
                    'previous_treatments' => $patient->dentalHistory->previous_treatments,
                ] : null,
                'reservations' => $patient->reservations->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'service_name' => $reservation->service->name,
                        'doctor_name' => $reservation->doctor->name,
                        'reservation_date' => $reservation->reservation_date,
                        'reservation_time' => substr($reservation->reservation_time, 0, 5),
                        'status' => $reservation->status,
                    ];
                }),
                'rontgens' => $patient->rontgens->map(function ($rontgen) {
                    return [
                        'id' => $rontgen->id,
                        'xray_image_url' => asset('storage/rontgens/' . $rontgen->xray_image),
                        'detail' => $rontgen->detail,
                        'created_at' => $rontgen->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'created_at' => $patient->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $patient->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail pasien berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdatePatientRequest $request, $id)
    {
        try {
            $patient = Patient::with(['medicalHistory', 'dentalHistory'])->find($id);

            if (!$patient) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Pasien tidak ditemukan'),
                    404
                );
            }

            if ($request->has('name')) $patient->name = $request->name;
            if ($request->has('phone')) $patient->phone = $request->phone;
            if ($request->has('email')) $patient->email = $request->email;
            if ($request->has('date_of_birth')) $patient->date_of_birth = $request->date_of_birth;
            if ($request->has('gender')) $patient->gender = $request->gender;
            if ($request->has('address')) $patient->address = $request->address;

            $patient->save();

            if ($request->has('medical_history')) {
                $medicalData = $request->medical_history;
                
                if ($patient->medicalHistory) {
                    $patient->medicalHistory->update($medicalData);
                } else {
                    $patient->medicalHistory()->create($medicalData);
                }
            }

            if ($request->has('dental_history')) {
                $dentalData = $request->dental_history;
                
                if ($patient->dentalHistory) {
                    $patient->dentalHistory->update($dentalData);
                } else {
                    $patient->dentalHistory()->create($dentalData);
                }
            }

            $patient->load(['medicalHistory', 'dentalHistory']);

            $data = [
                'id' => $patient->id,
                'name' => $patient->name,
                'phone' => $patient->phone,
                'email' => $patient->email,
                'date_of_birth' => $patient->date_of_birth,
                'gender' => $patient->gender,
                'address' => $patient->address,
                'medical_history' => $patient->medicalHistory ? [
                    'blood_type' => $patient->medicalHistory->blood_type,
                    'allergies' => $patient->medicalHistory->allergies,
                    'current_medications' => $patient->medicalHistory->current_medications,
                    'medical_conditions' => $patient->medicalHistory->medical_conditions,
                ] : null,
                'dental_history' => $patient->dentalHistory ? [
                    'last_dental_visit' => $patient->dentalHistory->last_dental_visit,
                    'dental_problems' => $patient->dentalHistory->dental_problems,
                    'previous_treatments' => $patient->dentalHistory->previous_treatments,
                ] : null,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data pasien berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate data pasien: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        try {
            $patient = Patient::with(['reservations', 'rontgens'])->find($id);

            if (!$patient) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Pasien tidak ditemukan'),
                    404
                );
            }

            $activeReservations = $patient->reservations()
                ->whereIn('status', ['pending', 'validated'])
                ->count();

            if ($activeReservations > 0) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Tidak dapat menghapus pasien dengan reservasi aktif'),
                    400
                );
            }

            foreach ($patient->rontgens as $rontgen) {
                FileHelper::deleteImage('rontgens/' . $rontgen->xray_image);
            }

            $patient->delete();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Pasien berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus pasien: ' . $e->getMessage()),
                500
            );
        }
    }
}
