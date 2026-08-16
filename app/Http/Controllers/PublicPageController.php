<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\MktProfile;
use App\Models\SarOperation;
use App\Models\Partner;
use App\Models\Volunteer;
use App\Models\Donor;
use App\Models\Donation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicPageController extends Controller
{
    /**
     * Beranda (Home Page)
     */
    public function home()
    {
        $latestNews = News::orderBy('id', 'desc')->take(3)->get();
        $activeOperations = SarOperation::whereIn('status', ['Siaga', 'Operasi SAR', 'Evaluasi'])->count();
        $totalVolunteers = Volunteer::count() + 1250;
        $totalDonors = Donor::count() + 850;

        return Inertia::render('Public/Home', [
            'latestNews' => $latestNews,
            'stats' => [
                'volunteers' => $totalVolunteers,
                'donors' => $totalDonors,
                'operations' => $activeOperations + 42,
                'donations' => 'Rp 1.5M+'
            ]
        ]);
    }

    /**
     * Tentang Kami (About Us)
     */
    public function about()
    {
        return Inertia::render('Public/About', [
            'mktProfile' => MktProfile::first()
        ]);
    }

    /**
     * 3 Pilar Siklus Kebencanaan (Disaster Pillars)
     */
    public function pillars()
    {
        return Inertia::render('Public/Pillars');
    }

    /**
     * Layanan Kemanusiaan (Services)
     */
    public function services()
    {
        return Inertia::render('Public/Services');
    }

    /**
     * Mitra & Kolaborasi (Partners)
     */
    public function partners()
    {
        $partners = Partner::orderBy('id', 'asc')->get();
        return Inertia::render('Public/Partners', [
            'partners' => $partners
        ]);
    }

    /**
     * Berita & Artikel (News & Articles List)
     */
    public function news(Request $request)
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

        $news = $query->orderBy('id', 'desc')->paginate(9)->withQueryString();
        $categories = News::select('category')->distinct()->pluck('category');

        return Inertia::render('Public/News/Index', [
            'news' => $news,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category'])
        ]);
    }

    /**
     * Detail Berita & Artikel (Single News Article via Slug)
     */
    public function newsDetail($slug)
    {
        $article = News::where('slug', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        $relatedNews = News::where('id', '!=', $article->id)
            ->where('category', $article->category)
            ->orderBy('id', 'desc')
            ->take(3)
            ->get();

        if ($relatedNews->isEmpty()) {
            $relatedNews = News::where('id', '!=', $article->id)
                ->orderBy('id', 'desc')
                ->take(3)
                ->get();
        }

        return Inertia::render('Public/News/Show', [
            'article' => $article,
            'relatedNews' => $relatedNews
        ]);
    }

    /**
     * Kontak & Lokasi Kantor Posko (Contact & Location)
     */
    public function contact()
    {
        return Inertia::render('Public/Contact', [
            'mktProfile' => MktProfile::first()
        ]);
    }
}
