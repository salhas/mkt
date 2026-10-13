<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\MktProfile;
use App\Models\SarOperation;
use App\Models\Partner;
use App\Models\Ecosystem;
use App\Models\PartnerEquipment;
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
        $activeOperations = SarOperation::whereIn('status', ['Siaga', 'Operasi SAR', 'Evaluasi', 'Operasi Aktif', 'Siaga SAR'])->count();
        $totalVolunteers = Volunteer::count() + 1250;
        $totalDonors = Donor::count() + 850;

        $partners = Partner::withCount(['volunteers', 'equipments'])
            ->orderBy('id', 'asc')
            ->get();

        $ecosystems = Ecosystem::withCount('partners')
            ->orderBy('id', 'asc')
            ->get();

        return Inertia::render('Public/Home', [
            'latestNews' => $latestNews,
            'partners' => $partners,
            'ecosystems' => $ecosystems,
            'stats' => [
                'partners' => Partner::count(),
                'ecosystems' => Ecosystem::count(),
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
        $partners = Partner::withCount(['volunteers', 'equipments'])->orderBy('id', 'asc')->get();
        $ecosystems = Ecosystem::withCount('partners')->orderBy('id', 'asc')->get();

        return Inertia::render('Public/About', [
            'mktProfile' => MktProfile::first(),
            'partners' => $partners,
            'ecosystems' => $ecosystems,
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
     * Mitra & Ekosistem Kolaborasi (Partners & Ecosystems Directory)
     */
    public function partners(Request $request)
    {
        $partners = Partner::withCount(['volunteers', 'equipments'])->orderBy('id', 'asc')->get();

        $ecosystems = Ecosystem::where('status', 'Aktif')
            ->with(['partners' => function ($pq) {
                $pq->where('status', 'Aktif')->select('id', 'ecosystem_id', 'name', 'slug', 'category', 'logo_path', 'pic_name');
            }])
            ->withCount('partners')
            ->orderBy('id', 'asc')
            ->get();

        return Inertia::render('Public/Partners', [
            'partners' => $partners,
            'ecosystems' => $ecosystems,
            'mktProfile' => MktProfile::first(),
            'defaultTab' => $request->query('tab', 'semua'),
        ]);
    }

    /**
     * Landing Page Khusus Mitra Resmi (Single Partner Landing Page via Slug / Code / ID)
     */
    public function partnerDetail($slug)
    {
        $partner = Partner::where('slug', $slug)
            ->orWhere('code', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        // Ambil data personel relawan yang terdaftar di bawah mitra ini
        $members = Volunteer::where('partner_id', $partner->id)
            ->where('status', 'Aktif')
            ->orderBy('id', 'desc')
            ->take(8)
            ->get(['id', 'name', 'role', 'blood_type', 'certifications']);

        $totalMembersCount = max(
            Volunteer::where('partner_id', $partner->id)->count(),
            (int) $partner->personnel_count
        );

        // Riwayat Operasi SAR / Misi Kemanusiaan
        $sarMissions = $partner->getSarParticipations();

        // Berita & Artikel terkait mitra (prioritas berita yang diterbitkan mitra ini)
        $partnerNews = News::where('partner_id', $partner->id)
            ->orderBy('id', 'desc')
            ->take(6)
            ->get();

        if ($partnerNews->count() < 3) {
            $needed = 3 - $partnerNews->count();
            $fallbackNews = News::where(function ($q) use ($partner) {
                $q->where('title', 'like', "%{$partner->name}%")
                  ->orWhere('content', 'like', "%{$partner->name}%")
                  ->orWhere('category', $partner->category);
            })
            ->whereNotIn('id', $partnerNews->pluck('id'))
            ->orderBy('id', 'desc')
            ->take($needed)
            ->get();

            $partnerNews = $partnerNews->concat($fallbackNews);
        }

        if ($partnerNews->isEmpty()) {
            $partnerNews = News::orderBy('id', 'desc')->take(3)->get();
        }

        // Mitra Terkait Lainnya untuk rekomendasi
        $otherPartners = Partner::where('id', '!=', $partner->id)
            ->where('status', 'Aktif')
            ->orderBy('id', 'asc')
            ->take(4)
            ->get();

        return Inertia::render('Public/PartnerDetail', [
            'partner' => $partner,
            'members' => $members,
            'totalMembersCount' => $totalMembersCount,
            'sarMissions' => $sarMissions,
            'partnerNews' => $partnerNews,
            'otherPartners' => $otherPartners,
            'mktProfile' => MktProfile::first(),
        ]);
    }

    /**
     * Direktori Ekosistem Kemanusiaan (Ecosystems Directory)
     */
    public function ecosystems(Request $request)
    {
        $query = Ecosystem::where('status', 'Aktif')
            ->with(['partners' => function ($pq) {
                $pq->where('status', 'Aktif')->select('id', 'ecosystem_id', 'name', 'slug', 'category', 'logo_path', 'pic_name');
            }])
            ->withCount('partners');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('region', 'like', "%{$s}%")
                  ->orWhere('lead_institution', 'like', "%{$s}%");
            });
        }

        $ecosystems = $query->orderBy('id', 'asc')->get();

        return Inertia::render('Public/Ecosystems', [
            'ecosystems' => $ecosystems,
            'filters' => $request->only(['search']),
            'mktProfile' => MktProfile::first(),
        ]);
    }

    /**
     * Landing Page Khusus Ekosistem Kemanusiaan (Single Ecosystem Public Landing Page)
     */
    public function ecosystemDetail($slug)
    {
        $ecosystem = Ecosystem::where('slug', $slug)
            ->orWhere('code', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        // Ambil semua mitra lembaga resmi yang bernaung dalam ekosistem ini
        $partners = Partner::where('ecosystem_id', $ecosystem->id)
            ->where('status', 'Aktif')
            ->withCount('volunteers')
            ->orderBy('id', 'asc')
            ->get();

        $partnerIds = $partners->pluck('id');

        // Agregasi relawan terdaftar
        $volunteers = Volunteer::whereIn('partner_id', $partnerIds)
            ->where('status', 'Aktif')
            ->orderBy('id', 'desc')
            ->take(12)
            ->get(['id', 'partner_id', 'name', 'role', 'blood_type', 'certifications']);

        $totalVolunteersCount = Volunteer::whereIn('partner_id', $partnerIds)->count();
        $totalPersonnelEstimated = max($totalVolunteersCount, (int) $partners->sum('personnel_count'));

        // Agregasi peralatan dan perlengkapan gabungan ekosistem
        $equipments = PartnerEquipment::whereIn('partner_id', $partnerIds)
            ->where('status', '!=', 'Tidak Aktif')
            ->with('partner:id,name,slug')
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        $totalEquipmentsCount = PartnerEquipment::whereIn('partner_id', $partnerIds)->count();

        // Berita & Publikasi terkait lembaga-lembaga dalam ekosistem
        $ecosystemNews = News::whereIn('partner_id', $partnerIds)
            ->orderBy('id', 'desc')
            ->take(6)
            ->get();

        if ($ecosystemNews->count() < 3) {
            $needed = 3 - $ecosystemNews->count();
            $fallbackNews = News::where(function ($q) use ($ecosystem) {
                $q->where('title', 'like', "%{$ecosystem->name}%")
                  ->orWhere('content', 'like', "%{$ecosystem->name}%")
                  ->orWhere('title', 'like', "%{$ecosystem->lead_institution}%");
            })
            ->whereNotIn('id', $ecosystemNews->pluck('id'))
            ->orderBy('id', 'desc')
            ->take($needed)
            ->get();

            $ecosystemNews = $ecosystemNews->concat($fallbackNews);
        }

        if ($ecosystemNews->isEmpty()) {
            $ecosystemNews = News::orderBy('id', 'desc')->take(3)->get();
        }

        // Ekosistem lainnya untuk navigasi eksplorasi
        $otherEcosystems = Ecosystem::where('id', '!=', $ecosystem->id)
            ->where('status', 'Aktif')
            ->withCount('partners')
            ->take(3)
            ->get(['id', 'name', 'code', 'slug', 'region', 'lead_institution', 'logo_path']);

        // Semua mitra aktif untuk opsi pilih mitra yang sudah terdaftar
        $allPartners = Partner::where('status', 'Aktif')
            ->select('id', 'name', 'code', 'category', 'ecosystem_id', 'pic_name', 'pic_phone', 'pic_email', 'phone', 'email', 'address', 'personnel_count', 'description')
            ->orderBy('name', 'asc')
            ->get();

        return Inertia::render('Public/EcosystemDetail', [
            'ecosystem' => $ecosystem,
            'partners' => $partners,
            'allPartners' => $allPartners,
            'volunteers' => $volunteers,
            'totalVolunteersCount' => $totalVolunteersCount,
            'totalPersonnelEstimated' => $totalPersonnelEstimated,
            'equipments' => $equipments,
            'totalEquipmentsCount' => $totalEquipmentsCount,
            'ecosystemNews' => $ecosystemNews,
            'otherEcosystems' => $otherEcosystems,
            'mktProfile' => MktProfile::first(),
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

    /**
     * Halaman Web Syarat & Ketentuan Kemitraan dan Kolaborasi MKT
     */
    public function partnerTerms(Request $request)
    {
        $filePath = public_path('docs/syarat-dan-ketentuan-kemitraan-mkt.pdf');

        if (!file_exists($filePath)) {
            abort(404, 'Berkas Syarat & Ketentuan Kemitraan tidak ditemukan.');
        }

        // Tampilkan halaman web interaktif jika diminta via Inertia atau query view=web
        if ($request->header('X-Inertia') || $request->query('view') === 'web' || $request->routeIs('public.partner-terms')) {
            return Inertia::render('Public/PartnerTerms', [
                'pdfUrl' => '/docs/syarat-dan-ketentuan-kemitraan-mkt.pdf',
                'downloadUrl' => route('partner.terms.download'),
                'mktProfile' => MktProfile::first(),
            ]);
        }

        // Default endpoint /syarat-ketentuan-kemitraan menyajikan berkas PDF resmi
        return response()->download($filePath, 'Syarat-dan-Ketentuan-Kemitraan-dan-Kolaborasi-MKT.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Unduh langsung berkas PDF resmi
     */
    public function downloadPartnerTerms()
    {
        $filePath = public_path('docs/syarat-dan-ketentuan-kemitraan-mkt.pdf');

        if (!file_exists($filePath)) {
            abort(404, 'Berkas Syarat & Ketentuan Kemitraan tidak ditemukan.');
        }

        return response()->download($filePath, 'Syarat-dan-Ketentuan-Kemitraan-dan-Kolaborasi-MKT.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
