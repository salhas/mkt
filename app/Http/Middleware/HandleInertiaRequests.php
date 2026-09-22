<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\MktProfile;
use App\Models\OrganizationMember;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'mktProfile' => function () {
                return MktProfile::first();
            },
            'organizationLeaders' => function () {
                // Must explicitly select from Tier Pengurus (NOT Dewan Pembina / NOT Dewan Pengawas)
                $chairman = OrganizationMember::where('tier', 'Pengurus')
                    ->where(function($q) {
                        $q->where('position', 'like', '%Ketua%');
                    })
                    ->orderBy('order_index')
                    ->first();

                if (!$chairman) {
                    $chairman = OrganizationMember::where('name', 'like', '%Salman Hasmin%')->first();
                }

                $treasurer = OrganizationMember::where('tier', 'Pengurus')
                    ->where('position', 'like', '%Bendahara%')
                    ->orderBy('order_index')
                    ->first();

                if (!$treasurer) {
                    $treasurer = OrganizationMember::where('position', 'like', '%Bendahara%')
                        ->where('tier', '!=', 'Dewan Pembina')
                        ->first();
                }

                return [
                    'chairman' => [
                        'name' => $chairman ? $chairman->name : 'Salman Hasmin, ST',
                        'title' => 'Ketua Pengurus Yayasan MKT',
                        'nip' => $chairman ? 'NIP/KTA: ' . $chairman->member_number : 'NIP/KTA: MKT-PG-001',
                    ],
                    'treasurer' => [
                        'name' => $treasurer ? $treasurer->name : 'Sarif, ST',
                        'title' => $treasurer ? $treasurer->position : 'Bendahara Umum',
                        'nip' => $treasurer ? 'NIP/KTA: ' . $treasurer->member_number : 'NIP/KTA: MKT-PG-003',
                    ],
                ];
            },
        ];
    }
}
