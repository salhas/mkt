<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Self-healing Storage Symlink for Shared Hosting / cPanel environments
        $this->ensureValidStorageLink();
    }

    /**
     * Self-healing Storage Symlink & Directory bootstrap for hosting environments.
     * Detects broken symlinks uploaded from local/dev machines and auto-repairs them.
     */
    protected function ensureValidStorageLink(): void
    {
        try {
            $publicStorage = public_path('storage');
            $targetStorage = storage_path('app/public');

            // 1. If public/storage is a broken symlink, unlink it
            if (is_link($publicStorage)) {
                $target = @readlink($publicStorage);
                if (!$target || !file_exists($publicStorage) || !file_exists($target)) {
                    @unlink($publicStorage);
                }
            }

            // 2. If public/storage does not exist, attempt to create valid relative or absolute symlink
            if (!file_exists($publicStorage) && !is_link($publicStorage)) {
                if (file_exists($targetStorage) && function_exists('symlink')) {
                    @symlink($targetStorage, $publicStorage);
                }
            }

            // 3. Ensure required upload directories exist
            $subDirs = ['meeting_attendance_images', 'meeting_attachments', 'news_images', 'organization_photos'];
            foreach ($subDirs as $dir) {
                $dirPath = $targetStorage . '/' . $dir;
                if (!file_exists($dirPath)) {
                    @mkdir($dirPath, 0755, true);
                }
            }
        } catch (\Throwable $e) {
            // Fail silently to never interrupt request lifecycle
        }
    }
}
