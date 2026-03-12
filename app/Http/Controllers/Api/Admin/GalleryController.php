<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Http\Requests\StoreGalleryRequest;
use App\Http\Requests\UpdateGalleryRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;

class GalleryController extends Controller
{
    public function index()
    {
        try {
            $galleries = Gallery::latest()->paginate(10);

            $data = [
                'galleries' => $galleries->map(function ($gallery) {
                    return [
                        'id' => $gallery->id,
                        'image_url' => $gallery->image ? asset('storage/galleries/' . $gallery->image) : null,
                        'caption' => $gallery->caption,
                        'created_at' => $gallery->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'pagination' => [
                    'current_page' => $galleries->currentPage(),
                    'last_page' => $galleries->lastPage(),
                    'per_page' => $galleries->perPage(),
                    'total' => $galleries->total(),
                ],
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data galeri berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StoreGalleryRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $imageName = FileHelper::uploadImage($request->file('image'), 'galleries');

            if (!$imageName) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Gagal upload gambar'),
                    500
                );
            }

            $gallery = Gallery::create([
                'image' => $imageName,
                'caption' => $request->caption,
            ]);

            DB::commit();

            $data = [
                'id' => $gallery->id,
                'image_url' => asset('storage/galleries/' . $gallery->image),
                'caption' => $gallery->caption,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Galeri berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($imageName)) {
                FileHelper::deleteImage('galleries/' . $imageName);
            }

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan galeri: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $gallery = Gallery::find($id);

            if (!$gallery) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Galeri tidak ditemukan'),
                    404
                );
            }

            $data = [
                'id' => $gallery->id,
                'image_url' => $gallery->image ? asset('storage/galleries/' . $gallery->image) : null,
                'caption' => $gallery->caption,
                'created_at' => $gallery->created_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail galeri berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateGalleryRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $gallery = Gallery::find($id);

            if (!$gallery) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Galeri tidak ditemukan'),
                    404
                );
            }

            $oldImage = $gallery->image;

            if ($request->hasFile('image')) {
                $imageName = FileHelper::uploadImage($request->file('image'), 'galleries');
                if ($imageName) {
                    $gallery->image = $imageName;
                }
            }

            if ($request->has('caption')) {
                $gallery->caption = $request->caption;
            }

            $gallery->save();

            if ($request->hasFile('image') && $oldImage) {
                FileHelper::deleteImage('galleries/' . $oldImage);
            }

            DB::commit();

            $data = [
                'id' => $gallery->id,
                'image_url' => asset('storage/galleries/' . $gallery->image),
                'caption' => $gallery->caption,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Galeri berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate galeri: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $gallery = Gallery::find($id);

            if (!$gallery) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Galeri tidak ditemukan'),
                    404
                );
            }

            $oldImage = $gallery->image;

            $gallery->delete();

            if ($oldImage) {
                FileHelper::deleteImage('galleries/' . $oldImage);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Galeri berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus galeri: ' . $e->getMessage()),
                500
            );
        }
    }
}
