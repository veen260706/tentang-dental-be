<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Helpers\FileHelper;

class FaqController extends Controller
{
    public function index()
    {
        try {
            $faqs = Faq::latest()->paginate(10);

            $data = [
                'faqs' => $faqs->map(function ($faq) {
                    return [
                        'id' => $faq->id,
                        'question' => $faq->question,
                        'answer' => $faq->answer,
                        'created_at' => $faq->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'pagination' => [
                    'current_page' => $faqs->currentPage(),
                    'last_page' => $faqs->lastPage(),
                    'per_page' => $faqs->perPage(),
                    'total' => $faqs->total(),
                ],
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data FAQ berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StoreFaqRequest $request)
    {
        try {
            $faq = Faq::create([
                'question' => $request->question,
                'answer' => $request->answer,
            ]);

            $data = [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'created_at' => $faq->created_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'FAQ berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan FAQ: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'FAQ tidak ditemukan'),
                    404
                );
            }

            $data = [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'created_at' => $faq->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $faq->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail FAQ berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateFaqRequest $request, $id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'FAQ tidak ditemukan'),
                    404
                );
            }

            if ($request->has('question')) {
                $faq->question = $request->question;
            }

            if ($request->has('answer')) {
                $faq->answer = $request->answer;
            }

            $faq->save();

            $data = [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'updated_at' => $faq->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'FAQ berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate FAQ: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'FAQ tidak ditemukan'),
                    404
                );
            }

            $faq->delete();

            return response()->json(
                FileHelper::formatResponse(true, null, 'FAQ berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus FAQ: ' . $e->getMessage()),
                500
            );
        }
    }
}
