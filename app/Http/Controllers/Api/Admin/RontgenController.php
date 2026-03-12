<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rontgen;
use App\Models\Patient;
use App\Http\Requests\StoreRontgenRequest;
use App\Http\Requests\UpdateRontgenRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RontgenController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Rontgen::with('patient');

            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->patient_id);
            }

            $rontgens = $query->latest()->paginate(10);

            $data = [
                'rontgens' => $rontgens->map(function ($rontgen) {
                    return [
                        'id' => $rontgen->id,
                        'patient' => [
                            'id' => $rontgen->patient->id,
                            'name' => $rontgen->patient->name,
                            'phone' => $rontgen->patient->phone,
                        ],
                        'xray_image_url' => asset('storage/rontgens/' . $rontgen->xray_image),
                        'detail' => $rontgen->detail,
                        'created_at' => $rontgen->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'pagination' => [
                    'current_page' => $rontgens->currentPage(),
                    'last_page' => $rontgens->lastPage(),
                    'per_page' => $rontgens->perPage(),
                    'total' => $rontgens->total(),
                ],
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data rontgen berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StoreRontgenRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $patient = Patient::find($request->patient_id);
            
            if (!$patient) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Pasien tidak ditemukan'),
                    404
                );
            }

            $imageName = FileHelper::uploadImage($request->file('xray_image'), 'rontgens');

            if (!$imageName) {
                throw new \Exception('Gagal mengupload gambar rontgen');
            }

            $rontgen = Rontgen::create([
                'patient_id' => $request->patient_id,
                'xray_image' => $imageName,
                'detail' => $request->detail ?? null,
            ]);

            DB::commit();

            $data = [
                'id' => $rontgen->id,
                'patient' => [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                ],
                'xray_image_url' => asset('storage/rontgens/' . $rontgen->xray_image),
                'detail' => $rontgen->detail,
                'created_at' => $rontgen->created_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data rontgen berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($imageName)) {
                FileHelper::deleteImage('rontgens/' . $imageName);
            }

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan rontgen: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $rontgen = Rontgen::with('patient.medicalHistory', 'patient.dentalHistory')->find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            $data = [
                'id' => $rontgen->id,
                'patient' => [
                    'id' => $rontgen->patient->id,
                    'name' => $rontgen->patient->name,
                    'phone' => $rontgen->patient->phone,
                    'email' => $rontgen->patient->email,
                    'date_of_birth' => $rontgen->patient->date_of_birth,
                    'gender' => $rontgen->patient->gender,
                    'medical_history' => $rontgen->patient->medicalHistory ? [
                        'blood_type' => $rontgen->patient->medicalHistory->blood_type,
                        'allergies' => $rontgen->patient->medicalHistory->allergies,
                        'medical_conditions' => $rontgen->patient->medicalHistory->medical_conditions,
                    ] : null,
                    'dental_history' => $rontgen->patient->dentalHistory ? [
                        'last_dental_visit' => $rontgen->patient->dentalHistory->last_dental_visit,
                        'dental_problems' => $rontgen->patient->dentalHistory->dental_problems,
                    ] : null,
                ],
                'xray_image_url' => asset('storage/rontgens/' . $rontgen->xray_image),
                'detail' => $rontgen->detail,
                'created_at' => $rontgen->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $rontgen->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail rontgen berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateRontgenRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $rontgen = Rontgen::find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            $oldImage = $rontgen->xray_image;

            if ($request->hasFile('xray_image')) {
                $imageName = FileHelper::uploadImage($request->file('xray_image'), 'rontgens');
                if ($imageName) {
                    $rontgen->xray_image = $imageName;
                }
            }

            if ($request->has('detail')) {
                $rontgen->detail = $request->detail;
            }

            $rontgen->save();

            if ($request->hasFile('xray_image') && $oldImage) {
                FileHelper::deleteImage('rontgens/' . $oldImage);
            }

            DB::commit();

            $rontgen->load('patient');

            $data = [
                'id' => $rontgen->id,
                'patient' => [
                    'id' => $rontgen->patient->id,
                    'name' => $rontgen->patient->name,
                ],
                'xray_image_url' => asset('storage/rontgens/' . $rontgen->xray_image),
                'detail' => $rontgen->detail,
                'updated_at' => $rontgen->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data rontgen berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate rontgen: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $rontgen = Rontgen::find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            $oldImage = $rontgen->xray_image;

            $rontgen->delete();

            if ($oldImage) {
                FileHelper::deleteImage('rontgens/' . $oldImage);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Data rontgen berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus rontgen: ' . $e->getMessage()),
                500
            );
        }
    }
}
