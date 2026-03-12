<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Http\Requests\StorePromoRequest;
use App\Http\Requests\UpdatePromoRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;

class PromoController extends Controller
{
    public function index()
    {
        try {
            $promos = Promo::latest()->paginate(10);

            $data = [
                'promos' => $promos->map(function ($promo) {
                    return [
                        'id' => $promo->id,
                        'name' => $promo->name,
                        'image_url' => $promo->image ? asset('storage/promos/' . $promo->image) : null,
                        'detail' => $promo->detail,
                        'original_price' => (float) $promo->original_price,
                        'promo_price' => (float) $promo->promo_price,
                        'created_at' => $promo->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'pagination' => [
                    'current_page' => $promos->currentPage(),
                    'last_page' => $promos->lastPage(),
                    'per_page' => $promos->perPage(),
                    'total' => $promos->total(),
                ],
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data promo berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StorePromoRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $imageName = FileHelper::uploadImage($request->file('image'), 'promos');

            if (!$imageName) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Gagal upload gambar'),
                    500
                );
            }

            $promo = Promo::create([
                'name' => $request->name,
                'image' => $imageName,
                'detail' => $request->detail,
                'original_price' => $request->original_price,
                'promo_price' => $request->promo_price,
            ]);

            DB::commit();

            $data = [
                'id' => $promo->id,
                'name' => $promo->name,
                'image_url' => asset('storage/promos/' . $promo->image),
                'detail' => $promo->detail,
                'original_price' => (float) $promo->original_price,
                'promo_price' => (float) $promo->promo_price,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Promo berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($imageName)) {
                FileHelper::deleteImage('promos/' . $imageName);
            }

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan promo: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $promo = Promo::find($id);

            if (!$promo) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Promo tidak ditemukan'),
                    404
                );
            }

            $data = [
                'id' => $promo->id,
                'name' => $promo->name,
                'image_url' => $promo->image ? asset('storage/promos/' . $promo->image) : null,
                'detail' => $promo->detail,
                'original_price' => (float) $promo->original_price,
                'promo_price' => (float) $promo->promo_price,
                'created_at' => $promo->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $promo->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail promo berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdatePromoRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $promo = Promo::find($id);

            if (!$promo) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Promo tidak ditemukan'),
                    404
                );
            }

            $oldImage = $promo->image;

            if ($request->hasFile('image')) {
                $imageName = FileHelper::uploadImage($request->file('image'), 'promos');
                
                if (!$imageName) {
                    return response()->json(
                        FileHelper::formatResponse(false, null, 'Gagal upload gambar'),
                        500
                    );
                }

                $promo->image = $imageName;
            }

            if ($request->has('name')) $promo->name = $request->name;
            if ($request->has('detail')) $promo->detail = $request->detail;
            if ($request->has('original_price')) $promo->original_price = $request->original_price;
            if ($request->has('promo_price')) $promo->promo_price = $request->promo_price;

            $promo->save();

            if ($request->hasFile('image') && $oldImage) {
                FileHelper::deleteImage('promos/' . $oldImage);
            }

            DB::commit();

            $data = [
                'id' => $promo->id,
                'name' => $promo->name,
                'image_url' => asset('storage/promos/' . $promo->image),
                'detail' => $promo->detail,
                'original_price' => (float) $promo->original_price,
                'promo_price' => (float) $promo->promo_price,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Promo berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($imageName)) {
                FileHelper::deleteImage('promos/' . $imageName);
            }

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate promo: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $promo = Promo::find($id);

            if (!$promo) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Promo tidak ditemukan'),
                    404
                );
            }

            $oldImage = $promo->image;

            $promo->delete();

            if ($oldImage) {
                FileHelper::deleteImage('promos/' . $oldImage);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Promo berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus promo: ' . $e->getMessage()),
                500
            );
        }
    }
}
