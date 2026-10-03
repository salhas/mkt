<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RepairStorageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:repair';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repair and verify public storage symlink and directory permissions for shared hosting/cPanel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Storage Diagnostics & Repair...');

        $publicStorage = public_path('storage');
        $targetStorage = storage_path('app/public');

        $this->line("Target Storage: {$targetStorage}");
        $this->line("Public Storage: {$publicStorage}");

        // 1. Ensure target storage directories exist
        if (!file_exists($targetStorage)) {
            mkdir($targetStorage, 0755, true);
            $this->info("Created {$targetStorage}");
        }

        $subDirs = [
            'meeting_attendance_images',
            'meeting_attachments',
            'news_images',
            'organization_photos',
        ];

        foreach ($subDirs as $dir) {
            $path = $targetStorage . '/' . $dir;
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
                $this->info("Created directory: {$dir}");
            } else {
                @chmod($path, 0755);
            }
        }

        // 2. Inspect public/storage
        if (is_link($publicStorage)) {
            $target = @readlink($publicStorage);
            $this->line("Existing symlink found pointing to: {$target}");
            
            if (!$target || !file_exists($publicStorage) || !file_exists($target)) {
                $this->warn("Broken symlink detected! Unlinking: {$publicStorage}");
                @unlink($publicStorage);
            } else {
                $this->info("Symlink is valid and functional!");
            }
        } elseif (is_dir($publicStorage)) {
            $this->warn("{$publicStorage} is a physical directory (not a symlink).");
        }

        // 3. Attempt symlink creation if not present
        if (!file_exists($publicStorage) && !is_link($publicStorage)) {
            if (function_exists('symlink')) {
                try {
                    $created = @symlink($targetStorage, $publicStorage);
                    if ($created) {
                        $this->info("Successfully created new storage symlink!");
                    } else {
                        $this->warn("Could not create symlink (hosting restrictions). Laravel web fallback route will handle /storage/* automatically.");
                    }
                } catch (\Throwable $e) {
                    $this->warn("Symlink exception: " . $e->getMessage() . ". Laravel web fallback route will handle /storage/*.");
                }
            } else {
                $this->warn("PHP symlink() is disabled on this server. Laravel web fallback route in routes/web.php will serve files seamlessly.");
            }
        }

        $this->info('Storage repair completed successfully.');
        return Command::SUCCESS;
    }
}
