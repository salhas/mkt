<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('website')->nullable()->after('email');
            $table->string('instagram')->nullable()->after('website');
            $table->string('facebook')->nullable()->after('instagram');
            $table->string('banner_path')->nullable()->after('logo_path');
            $table->text('vision')->nullable()->after('description');
            $table->text('mission')->nullable()->after('vision');
        });

        // Generate slugs for existing partners
        $partners = DB::table('partners')->get();
        $usedSlugs = [];

        foreach ($partners as $partner) {
            $baseSlug = Str::slug($partner->name);
            if (empty($baseSlug)) {
                $baseSlug = 'mitra-' . $partner->id;
            }

            $slug = $baseSlug;
            $counter = 1;
            while (in_array($slug, $usedSlugs)) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $usedSlugs[] = $slug;

            DB::table('partners')->where('id', $partner->id)->update([
                'slug' => $slug
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'website',
                'instagram',
                'facebook',
                'banner_path',
                'vision',
                'mission',
            ]);
        });
    }
};
