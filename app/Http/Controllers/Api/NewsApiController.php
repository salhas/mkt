<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsApiController extends Controller
{
    /**
     * Memeriksa apakah user yang diautentikasi memiliki peranan Webmaster, Admin, atau Staff
     */
    private function checkNewsPermission(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['webmaster', 'administrator', 'admin', 'staff'])) {
            return false;
        }
        return true;
    }

    /**
     * GET /api/v1/news - Daftar Berita & Publikasi Artikel MKT
     */
    public function index(Request $request)
    {
        $query = News::query();

        if ($request->filled('category') && $request->input('category') !== 'Semua') {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $news = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'count' => $news->count(),
            'data' => $news
        ]);
    }

    /**
     * GET /api/v1/news/{id} - Detail Berita
     */
    public function show($id)
    {
        $news = News::find($id);

        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'Artikel berita tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $news
        ]);
    }

    /**
     * POST /api/v1/news - Tambah Berita Baru (Khusus Role Webmaster, Admin, Staff)
     */
    public function store(Request $request)
    {
        if (!$this->checkNewsPermission($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Fitur Kelola Berita khusus untuk peranan Webmaster, Admin, dan Staff.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'author' => 'nullable|string|max:100',
            'image_url' => 'nullable|string',
            'content' => 'required|string',
            'published_at' => 'nullable|string',
        ]);

        if (empty($validated['author'])) {
            $validated['author'] = $request->user()->name ?? 'Humas MKT';
        }

        if (empty($validated['published_at'])) {
            $validated['published_at'] = date('Y-m-d');
        }

        $news = News::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Artikel berita berhasil diterbitkan.',
            'data' => $news
        ], 201);
    }

    /**
     * PUT /api/v1/news/{id} - Edit Berita (Khusus Role Webmaster, Admin, Staff)
     */
    public function update(Request $request, $id)
    {
        if (!$this->checkNewsPermission($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Fitur Kelola Berita khusus untuk peranan Webmaster, Admin, dan Staff.'
            ], 403);
        }

        $news = News::find($id);

        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'Artikel berita tidak ditemukan.'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'author' => 'nullable|string|max:100',
            'image_url' => 'nullable|string',
            'content' => 'required|string',
            'published_at' => 'nullable|string',
        ]);

        $news->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Artikel berita berhasil diperbarui.',
            'data' => $news
        ]);
    }

    /**
     * DELETE /api/v1/news/{id} - Hapus Berita (Khusus Role Webmaster, Admin, Staff)
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->checkNewsPermission($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Fitur Kelola Berita khusus untuk peranan Webmaster, Admin, dan Staff.'
            ], 403);
        }

        $news = News::find($id);

        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'Artikel berita tidak ditemukan.'
            ], 404);
        }

        $news->delete();

        return response()->json([
            'success' => true,
            'message' => 'Artikel berita berhasil dihapus.'
        ]);
    }
}
