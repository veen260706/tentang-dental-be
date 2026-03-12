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
            $reservations = Reservation::with(['patient', 'service', 'doctor'])
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
                            'email' => $reservation->patient->email,
                        ],
                        'service' => [
                            'id' => $reservation->service->id,
                            'name' => $reservation->service->name,
                        ],
                        'doctor' => [
                            'id' => $reservation->doctor->id,
                            'name' => $reservation->doctor->name,
                        ],
                        'reservation_date' => $reservation->reservation_date,
                        'reservation_time' => substr($reservation->reservation_time, 0, 5),
                        'status' => $reservation->status,
                        'notes' => $reservation->notes,
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
            $reservation = Reservation::with(['patient.medicalHistory', 'patient.dentalHistory', 'service', 'doctor'])
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
                    'email' => $reservation->patient->email,
                    'date_of_birth' => $reservation->patient->date_of_birth,
                    'gender' => $reservation->patient->gender,
                    'address' => $reservation->patient->address,
                    'medical_history' => $reservation->patient->medicalHistory ? [
                        'blood_type' => $reservation->patient->medicalHistory->blood_type,
                        'allergies' => $reservation->patient->medicalHistory->allergies,
                        'current_medications' => $reservation->patient->medicalHistory->current_medications,
                        'medical_conditions' => $reservation->patient->medicalHistory->medical_conditions,
                    ] : null,
                    'dental_history' => $reservation->patient->dentalHistory ? [
                        'last_dental_visit' => $reservation->patient->dentalHistory->last_dental_visit,
                        'dental_problems' => $reservation->patient->dentalHistory->dental_problems,
                        'previous_treatments' => $reservation->patient->dentalHistory->previous_treatments,
                    ] : null,
                ],
                'service' => [
                    'id' => $reservation->service->id,
                    'name' => $reservation->service->name,
                    'price' => $reservation->service->price,
                ],
                'doctor' => [
                    'id' => $reservation->doctor->id,
                    'name' => $reservation->doctor->name,
                    'specialization' => $reservation->doctor->specialization,
                ],
                'reservation_date' => $reservation->reservation_date,
                'reservation_time' => substr($reservation->reservation_time, 0, 5),
                'status' => $reservation->status,
                'notes' => $reservation->notes,
                'created_at' => $reservation->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $reservation->updated_at->format('Y-m-d H:i:s'),
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

            if ($request->has('notes')) {
                $reservation->notes = $request->notes;
            }

            $reservation->save();

            $data = [
                'id' => $reservation->id,
                'status' => $reservation->status,
                'notes' => $reservation->notes,
                'updated_at' => $reservation->updated_at->format('Y-m-d H:i:s'),
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
