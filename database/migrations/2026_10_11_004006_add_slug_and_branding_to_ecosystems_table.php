<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ecosystems', function (Blueprint $table) {
            if (!Schema::hasColumn('ecosystems', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('code');
            }
            if (!Schema::hasColumn('ecosystems', 'tagline')) {
                $table->string('tagline')->nullable()->after('lead_institution');
            }
            if (!Schema::hasColumn('ecosystems', 'address')) {
                $table->text('address')->nullable()->after('region');
            }
            if (!Schema::hasColumn('ecosystems', 'website')) {
                $table->string('website')->nullable()->after('pic_email');
            }
            if (!Schema::hasColumn('ecosystems', 'instagram')) {
                $table->string('instagram')->nullable()->after('website');
            }
            if (!Schema::hasColumn('ecosystems', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('instagram');
            }
            if (!Schema::hasColumn('ecosystems', 'banner_path')) {
                $table->string('banner_path')->nullable()->after('logo_path');
            }
            if (!Schema::hasColumn('ecosystems', 'vision')) {
                $table->text('vision')->nullable()->after('description');
            }
            if (!Schema::hasColumn('ecosystems', 'mission')) {
                $table->text('mission')->nullable()->after('vision');
            }
        });

        // Generate slug untuk ekosistem yang sudah ada jika belum memiliki slug
        $ecosystems = DB::table('ecosystems')->get();
        foreach ($ecosystems as $eco) {
            $base = \Illuminate\Support\Str::slug($eco->name) ?: 'ekosistem-' . $eco->id;
            $slug = $base;
            $c = 1;
            while (DB::table('ecosystems')->where('slug', $slug)->where('id', '!=', $eco->id)->exists()) {
                $slug = $base . '-' . $c;
                $c++;
            }
            DB::table('ecosystems')->where('id', $eco->id)->update([
                'slug' => $slug,
                'tagline' => $eco->tagline ?? 'Kolaborasi & Sinergi Kemanusiaan Terpadu',
                'vision' => $eco->vision ?? 'Menjadi jejaring ekosistem kemanusiaan yang solid, responsif, dan adaptif dalam menghadapi setiap bencana serta krisis kemanusiaan.',
                'mission' => $eco->mission ?? "1. Membangun koordinasi cepat antar lembaga dalam satu kawasan.\n2. Mengoptimalkan mobilisasi relawan dan peralatan evakuasi.\n3. Memperkuat kapasitas edukasi dan kesiapsiagaan masyarakat.",
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecosystems', function (Blueprint $table) {
            $columns = ['slug', 'tagline', 'address', 'website', 'instagram', 'logo_path', 'banner_path', 'vision', 'mission'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('ecosystems', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
