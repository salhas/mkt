<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BmkgWeatherService
{
    /**
     * Preset locations for MKT Disaster Posko & Regional Offices
     */
    public static array $locations = [
        '73.71.01.1001' => [
            'name' => 'Kota Makassar',
            'label' => 'Markas Pusat MKT (Makassar)',
            'province' => 'Sulawesi Selatan',
            'code' => '73.71.01.1001',
        ],
        '31.74.01.1001' => [
            'name' => 'Jakarta Selatan',
            'label' => 'Posko DKI Jakarta',
            'province' => 'DKI Jakarta',
            'code' => '31.74.01.1001',
        ],
        '32.71.01.1001' => [
            'name' => 'Kota Bogor',
            'label' => 'Posko Utama Rescue (Bogor)',
            'province' => 'Jawa Barat',
            'code' => '32.71.01.1001',
        ],
        '32.73.01.1001' => [
            'name' => 'Kota Bandung',
            'label' => 'Posko Jabar (Bandung)',
            'province' => 'Jawa Barat',
            'code' => '32.73.01.1001',
        ],
        '33.74.01.1001' => [
            'name' => 'Kota Semarang',
            'label' => 'Posko Jateng (Semarang)',
            'province' => 'Jawa Tengah',
            'code' => '33.74.01.1001',
        ],
        '35.78.01.1001' => [
            'name' => 'Kota Surabaya',
            'label' => 'Posko Jatim (Surabaya)',
            'province' => 'Jawa Timur',
            'code' => '35.78.01.1001',
        ],
        '12.71.01.1001' => [
            'name' => 'Kota Medan',
            'label' => 'Posko Sumut (Medan)',
            'province' => 'Sumatera Utara',
            'code' => '12.71.01.1001',
        ],
    ];

    /**
     * Fetch BMKG weather forecast for specific location code (adm4)
     */
    public function getWeather(string $adm4 = '73.71.01.1001'): array
    {
        $cacheKey = "bmkg_weather_v3_{$adm4}";

        return Cache::remember($cacheKey, 1800, function () use ($adm4) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(6)
                    ->get("https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4={$adm4}");

                if ($response->successful()) {
                    $json = $response->json();
                    return $this->formatBmkgData($json, $adm4);
                }
            } catch (\Exception $e) {
                Log::warning("BMKG Weather API Error: " . $e->getMessage());
            }

            return $this->getFallbackData($adm4);
        });
    }

    /**
     * Format raw BMKG JSON into clean structured array for Vue dashboard
     */
    private function formatBmkgData(array $json, string $adm4): array
    {
        $locationMeta = self::$locations[$adm4] ?? [
            'name' => $json['lokasi']['kotkab'] ?? 'Makassar',
            'label' => $json['lokasi']['kecamatan'] ?? 'Markas Pusat MKT',
            'province' => $json['lokasi']['provinsi'] ?? 'Sulawesi Selatan',
            'code' => $adm4,
        ];

        $cuacaList = [];
        if (isset($json['data']) && is_array($json['data'])) {
            foreach ($json['data'] as $dayGroup) {
                if (isset($dayGroup['cuaca']) && is_array($dayGroup['cuaca'])) {
                    foreach ($dayGroup['cuaca'] as $item) {
                        if (isset($item['weather_desc'])) {
                            $cuacaList[] = $item;
                        } elseif (is_array($item)) {
                            foreach ($item as $subItem) {
                                if (isset($subItem['weather_desc'])) {
                                    $cuacaList[] = $subItem;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (empty($cuacaList)) {
            return $this->getFallbackData($adm4);
        }

        // Current forecast slot
        $current = $cuacaList[0];

        // Format hourly list (next 6 slots)
        $hourly = array_map(function ($slot) {
            return [
                'time' => date('H:i', strtotime($slot['local_datetime'] ?? $slot['datetime'])),
                'temp' => (int)($slot['t'] ?? 30),
                'desc' => $slot['weather_desc'] ?? 'Cerah',
                'icon' => $slot['image'] ?? 'https://api-apps.bmkg.go.id/storage/icon/cuaca/cerah-am.svg',
                'humidity' => (int)($slot['hu'] ?? 70),
                'wind' => (float)($slot['ws'] ?? 10),
            ];
        }, array_slice($cuacaList, 0, 6));

        // Format 3-day summary outlook
        $daily = [];
        $groupedByDate = [];
        foreach ($cuacaList as $slot) {
            $date = date('Y-m-d', strtotime($slot['local_datetime'] ?? $slot['datetime']));
            $groupedByDate[$date][] = $slot;
        }

        $dayIndex = 0;
        foreach ($groupedByDate as $date => $slots) {
            if ($dayIndex >= 3) break;
            $temps = array_column($slots, 't');
            $minTemp = !empty($temps) ? min($temps) : 24;
            $maxTemp = !empty($temps) ? max($temps) : 33;
            $midSlot = $slots[floor(count($slots) / 2)] ?? $slots[0];

            $dayLabel = match ($dayIndex) {
                0 => 'Hari Ini',
                1 => 'Besok',
                2 => date('D, d M', strtotime($date)),
                default => date('d M', strtotime($date)),
            };

            $daily[] = [
                'day' => $dayLabel,
                'date' => date('d M Y', strtotime($date)),
                'min_temp' => (int)$minTemp,
                'max_temp' => (int)$maxTemp,
                'desc' => $midSlot['weather_desc'] ?? 'Cerah Berawan',
                'icon' => $midSlot['image'] ?? 'https://api-apps.bmkg.go.id/storage/icon/cuaca/cerah berawan-am.svg',
            ];
            $dayIndex++;
        }

        return [
            'status' => 'online',
            'source' => 'BMKG (Badan Meteorologi, Klimatologi, dan Geofisika)',
            'location' => [
                'code' => $adm4,
                'name' => $locationMeta['name'],
                'label' => $locationMeta['label'],
                'province' => $locationMeta['province'],
                'full' => ($json['lokasi']['kotkab'] ?? 'Kota Makassar') . ', ' . ($json['lokasi']['provinsi'] ?? 'Sulawesi Selatan'),
            ],
            'current' => [
                'temp' => (int)($current['t'] ?? 31),
                'humidity' => (int)($current['hu'] ?? 65),
                'wind' => (float)($current['ws'] ?? 12),
                'wind_direction' => $current['wd'] ?? 'SE',
                'weather_code' => $current['weather'] ?? 1,
                'desc' => $current['weather_desc'] ?? 'Cerah Berawan',
                'desc_en' => $current['weather_desc_en'] ?? 'Partly Cloudy',
                'icon' => $current['image'] ?? 'https://api-apps.bmkg.go.id/storage/icon/cuaca/cerah berawan-am.svg',
                'local_time' => date('H:i', strtotime($current['local_datetime'] ?? 'now')),
                'date_formatted' => date('l, d F Y', strtotime($current['local_datetime'] ?? 'now')),
            ],
            'hourly' => $hourly,
            'daily' => $daily,
            'available_locations' => array_values(self::$locations),
            'updated_at' => now()->format('H:i WITA'),
        ];
    }

    /**
     * Fallback data in case of BMKG connection timeout or offline status
     */
    private function getFallbackData(string $adm4): array
    {
        $locationMeta = self::$locations[$adm4] ?? self::$locations['73.71.01.1001'];

        return [
            'status' => 'fallback',
            'source' => 'BMKG (Mode Cadangan Siaga MKT)',
            'location' => [
                'code' => $adm4,
                'name' => $locationMeta['name'],
                'label' => $locationMeta['label'],
                'province' => $locationMeta['province'],
                'full' => $locationMeta['name'] . ', ' . $locationMeta['province'],
            ],
            'current' => [
                'temp' => 31,
                'humidity' => 68,
                'wind' => 11.5,
                'wind_direction' => 'SE',
                'weather_code' => 2,
                'desc' => 'Cerah Berawan',
                'desc_en' => 'Partly Cloudy',
                'icon' => 'https://api-apps.bmkg.go.id/storage/icon/cuaca/cerah berawan-am.svg',
                'local_time' => now()->format('H:i'),
                'date_formatted' => now()->translatedFormat('l, d F Y'),
            ],
            'hourly' => [
                ['time' => '12:00', 'temp' => 32, 'desc' => 'Cerah Berawan', 'icon' => 'https://api-apps.bmkg.go.id/storage/icon/cuaca/cerah berawan-am.svg', 'humidity' => 60, 'wind' => 12],
                ['time' => '15:00', 'temp' => 31, 'desc' => 'Hujan Ringan', 'icon' => 'https://api-apps.bmkg.go.id/storage/icon/cuaca/hujan ringan-pm.svg', 'humidity' => 75, 'wind' => 15],
                ['time' => '18:00', 'temp' => 28, 'desc' => 'Berawan', 'icon' => 'https://api-apps.bmkg.go.id/storage/icon/cuaca/berawan-pm.svg', 'humidity' => 80, 'wind' => 8],
                ['time' => '21:00', 'temp' => 26, 'desc' => 'Berawan', 'icon' => 'https://api-apps.bmkg.go.id/storage/icon/cuaca/berawan-pm.svg', 'humidity' => 85, 'wind' => 6],
            ],
            'daily' => [
                ['day' => 'Hari Ini', 'date' => now()->format('d M Y'), 'min_temp' => 25, 'max_temp' => 33, 'desc' => 'Cerah Berawan', 'icon' => 'https://api-apps.bmkg.go.id/storage/icon/cuaca/cerah berawan-am.svg'],
                ['day' => 'Besok', 'date' => now()->addDay()->format('d M Y'), 'min_temp' => 24, 'max_temp' => 32, 'desc' => 'Hujan Ringan', 'icon' => 'https://api-apps.bmkg.go.id/storage/icon/cuaca/hujan ringan-am.svg'],
                ['day' => now()->addDays(2)->format('D, d M'), 'date' => now()->addDays(2)->format('d M Y'), 'min_temp' => 24, 'max_temp' => 31, 'desc' => 'Berawan', 'icon' => 'https://api-apps.bmkg.go.id/storage/icon/cuaca/berawan-am.svg'],
            ],
            'available_locations' => array_values(self::$locations),
            'updated_at' => now()->format('H:i WITA'),
        ];
    }
}
