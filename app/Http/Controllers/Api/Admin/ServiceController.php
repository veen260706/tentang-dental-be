<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        try {
            $services = Service::latest()->paginate(10);

            $data = [
                'services' => $services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'detail' => $service->detail,
                        'icon_url' => $service->icon ? asset('storage/services/' . $service->icon) : null,
                        'support_image_url' => $service->support_image ? asset('storage/services/' . $service->support_image):null,
                        'created_at' => $service->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                ],
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data layanan berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StoreServiceRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $iconName = FileHelper::uploadImage($request->file('icon'), 'services');
            
            $supportImageName = FileHelper::uploadImage($request->file('support_image'), 'services');

            if (!$iconName || !$supportImageName) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Gagal upload gambar'),
                    500
                );
            }

            $service = Service::create([
                'name' => $request->name,
                'detail' => $request->detail,
                'icon' => $iconName,
                'article_content' => $request->article_content,
                'support_image' => $supportImageName,
            ]);

            DB::commit();

            $data = [
                'id' => $service->id,
                'name' => $service->name,
                'detail' => $service->detail,
                'icon_url' => asset('storage/services/' . $service->icon),
                'article_content' => $service->article_content,
                'support_image_url' => asset('storage/services/' . $service->support_image),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Layanan berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($iconName)) FileHelper::deleteImage('services/' . $iconName);
            if (isset($supportImageName)) FileHelper::deleteImage('services/' . $supportImageName);

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan layanan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $service = Service::find($id);

            if (!$service) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Layanan tidak ditemukan'),
                    404
                );
            }

            $data = [
                'id' => $service->id,
                'name' => $service->name,
                'detail' => $service->detail,
                'icon_url' => $service->icon ? asset('storage/services/' . $service->icon) : null,
                'article_content' => $service->article_content,
                'support_image_url' => $service->support_image ? asset('storage/services/' . $service->support_image) : null,
                'created_at' => $service->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $service->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail layanan berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateServiceRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $service = Service::find($id);

            if (!$service) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Layanan tidak ditemukan'),
                    404
                );
            }

            $oldIcon = $service->icon;
            $oldSupportImage = $service->support_image;

            if ($request->hasFile('icon')) {
                $iconName = FileHelper::uploadImage($request->file('icon'), 'services');
                if ($iconName) {
                    $service->icon = $iconName;
                }
            }

            if ($request->hasFile('support_image')) {
                $supportImageName = FileHelper::uploadImage($request->file('support_image'), 'services');
                if ($supportImageName) {
                    $service->support_image = $supportImageName;
                }
            }

            if ($request->has('name')) $service->name = $request->name;
            if ($request->has('detail')) $service->detail = $request->detail;
            if ($request->has('article_content')) $service->article_content = $request->article_content;

            $service->save();

            if ($request->hasFile('icon') && $oldIcon) {
                FileHelper::deleteImage('services/' . $oldIcon);
            }
            if ($request->hasFile('support_image') && $oldSupportImage) {
                FileHelper::deleteImage('services/' . $oldSupportImage);
            }

            DB::commit();

            $data = [
                'id' => $service->id,
                'name' => $service->name,
                'detail' => $service->detail,
                'icon_url' => asset('storage/services/' . $service->icon),
                'article_content' => $service->article_content,
                'support_image_url' => asset('storage/services/' . $service->support_image),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Layanan berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate layanan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $service = Service::find($id);

            if (!$service) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Layanan tidak ditemukan'),
                    404
                );
            }

            $oldIcon = $service->icon;
            $oldSupportImage = $service->support_image;

            $service->delete();

            if ($oldIcon) FileHelper::deleteImage('services/' . $oldIcon);
            if ($oldSupportImage) FileHelper::deleteImage('services/' . $oldSupportImage);

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Layanan berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus layanan: ' . $e->getMessage()),
                500
            );
        }
    }
}
