<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Http\Requests\UpdateReservationStatusRequest;
use App\Helpers\FileHelper;

class ReservationController extends Controller
{
    public function index()
    {
        try {
            $reservations = Reservation::with(['patient', 'services', 'doctor'])
                ->latest()
                ->paginate(10);

            $data = [
                'reservations' => $reservations->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'patient' => [
                            'id' => $reservation->patient->id,
                            'name' => $reservation->patient->name,
                            'phone' => $reservation->patient->phone,
                        ],
                        'services' => $reservation->services->map(function ($service) {
                            return [
                                'id' => $service->id,
                                'name' => $service->name,
                            ];
                        }),
                        'doctor' => [
                            'id' => $reservation->doctor->id,
                            'name' => $reservation->doctor->name,
                        ],
                        'reservation_date' => $reservation->reservation_date,
                        'appointment_time' => substr($reservation->appointment_time, 0, 5),
                        'status' => $reservation->status,
                        'created_at' => $reservation->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'pagination' => [
                    'current_page' => $reservations->currentPage(),
                    'last_page' => $reservations->lastPage(),
                    'per_page' => $reservations->perPage(),
                    'total' => $reservations->total(),
                ],
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data reservasi berhasil diambil'),
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
            $reservation = Reservation::with(['patient.medicalHistory', 'patient.dentalHistory', 'services', 'doctor'])
                ->find($id);

            if (!$reservation) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Reservasi tidak ditemukan'),
                    404
                );
            }

            $data = [
                'id' => $reservation->id,
                'patient' => [
                    'id' => $reservation->patient->id,
                    'name' => $reservation->patient->name,
                    'phone' => $reservation->patient->phone,
                    'birth_date' => $reservation->patient->birth_date,
                    'gender' => $reservation->patient->gender,
                    'address' => $reservation->patient->address,
                    'medical_history' => $reservation->patient->medicalHistory ? [
                        'has_allergy' => $reservation->patient->medicalHistory->has_allergy,
                        'allergy_detail' => $reservation->patient->medicalHistory->allergy_detail,
                        'has_systemic_disease' => $reservation->patient->medicalHistory->has_systemic_disease,
                        'systemic_disease_detail' => $reservation->patient->medicalHistory->systemic_disease_detail,
                        'undergoing_treatment' => $reservation->patient->medicalHistory->undergoing_treatment,
                        'treatment_detail' => $reservation->patient->medicalHistory->treatment_detail,
                        'ever_hospitalized' => $reservation->patient->medicalHistory->ever_hospitalized,
                        'hospitalized_reason' => $reservation->patient->medicalHistory->hospitalized_reason,
                        'smoking_or_alcohol' => $reservation->patient->medicalHistory->smoking_or_alcohol,
                    ] : null,
                    'dental_history' => $reservation->patient->dentalHistory ? [
                        'frequent_tooth_pain' => $reservation->patient->dentalHistory->frequent_tooth_pain,
                        'tooth_pain_detail' => $reservation->patient->dentalHistory->tooth_pain_detail,
                        'bleeding_gums' => $reservation->patient->dentalHistory->bleeding_gums,
                        'ever_dental_treatment' => $reservation->patient->dentalHistory->ever_dental_treatment,
                        'dental_treatment_detail' => $reservation->patient->dentalHistory->dental_treatment_detail,
                        'brushing_frequency' => $reservation->patient->dentalHistory->brushing_frequency,
                        'use_floss_or_mouthwash' => $reservation->patient->dentalHistory->use_floss_or_mouthwash,
                        'bad_habits' => $reservation->patient->dentalHistory->bad_habits,
                        'bad_habits_detail' => $reservation->patient->dentalHistory->bad_habits_detail,
                        'ever_braces' => $reservation->patient->dentalHistory->ever_braces,
                        'braces_years' => $reservation->patient->dentalHistory->braces_years,
                        'root_canal_treatment' => $reservation->patient->dentalHistory->root_canal_treatment,
                        'root_canal_detail' => $reservation->patient->dentalHistory->root_canal_detail,
                        'dentures' => $reservation->patient->dentalHistory->dentures,
                        'routine_checkup' => $reservation->patient->dentalHistory->routine_checkup,
                        'dental_checkup_frequency' => $reservation->patient->dentalHistory->dental_checkup_frequency,
                    ] : null,
                ],
                'services' => $reservation->services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'detail' => $service->detail,
                    ];
                }),
                'doctor' => [
                    'id' => $reservation->doctor->id,
                    'name' => $reservation->doctor->name,
                    'specialization' => $reservation->doctor->specialization,
                ],
                'complain' => $reservation->complain,
                'reservation_date' => $reservation->reservation_date,
                'appointment_time' => substr($reservation->appointment_time, 0, 5),
                'status' => $reservation->status,
                'created_at' => $reservation->created_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail reservasi berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateReservationStatusRequest $request, $id)
    {
        try {
            $reservation = Reservation::find($id);

            if (!$reservation) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Reservasi tidak ditemukan'),
                    404
                );
            }

            if ($request->has('status')) {
                $reservation->status = $request->status;
            }

            $reservation->save();

            $data = [
                'id' => $reservation->id,
                'status' => $reservation->status,
                'created_at' => $reservation->created_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Status reservasi berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate reservasi: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        try {
            $reservation = Reservation::find($id);

            if (!$reservation) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Reservasi tidak ditemukan'),
                    404
                );
            }

            $reservation->delete();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Reservasi berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus reservasi: ' . $e->getMessage()),
                500
            );
        }
    }
}
