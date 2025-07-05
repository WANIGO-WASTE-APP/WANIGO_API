<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportProject extends Command
{
    protected $signature = 'project:export {--name=project-export}';
    protected $description = 'Export project by copying to temporary folder';

    public function handle()
    {
        $projectName = $this->option('name');
        $exportFolder = $projectName . '-' . date('Y-m-d-H-i-s');

        // Folder yang akan di-exclude
        $excludeFolders = [
            'node_modules',
            'vendor',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            '.git'
        ];

        // File yang akan di-exclude
        $excludeFiles = [
            '.env',
            '.DS_Store',
            'Thumbs.db'
        ];

        $this->info("Creating export folder: $exportFolder");

        // Buat folder export
        if (!File::exists($exportFolder)) {
            File::makeDirectory($exportFolder, 0755, true);
        }

        // Copy semua file kecuali yang di-exclude
        $this->copyDirectory(base_path(), $exportFolder, $excludeFolders, $excludeFiles);

        // Copy .env.example jika ada
        if (File::exists(base_path('.env.example'))) {
            File::copy(base_path('.env.example'), $exportFolder . '/.env.example');
        }

        $this->info("✅ Project exported to folder: $exportFolder");
        $this->info("📁 Folder size: " . $this->getFolderSize($exportFolder));
        $this->warn("💡 Use WinRAR or 7-Zip to create ZIP file from this folder");

        return 0;
    }

    private function copyDirectory($source, $destination, $excludeFolders, $excludeFiles)
    {
        $files = File::allFiles($source);
        $directories = File::directories($source);

        // Copy directories first
        foreach ($directories as $directory) {
            $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $directory);

            // Skip excluded folders
            $shouldExclude = false;
            foreach ($excludeFolders as $excludeFolder) {
                if (strpos($relativePath, $excludeFolder) === 0) {
                    $shouldExclude = true;
                    break;
                }
            }

            if (!$shouldExclude) {
                $newDirectory = $destination . DIRECTORY_SEPARATOR . $relativePath;
                if (!File::exists($newDirectory)) {
                    File::makeDirectory($newDirectory, 0755, true);
                }
            }
        }

        // Copy files
        foreach ($files as $file) {
            $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $file->getPathname());

            // Skip excluded folders
            $shouldExclude = false;
            foreach ($excludeFolders as $excludeFolder) {
                if (strpos($relativePath, $excludeFolder) === 0) {
                    $shouldExclude = true;
                    break;
                }
            }

            // Skip excluded files
            foreach ($excludeFiles as $excludeFile) {
                if (basename($relativePath) === $excludeFile) {
                    $shouldExclude = true;
                    break;
                }
            }

            if (!$shouldExclude) {
                $destinationFile = $destination . DIRECTORY_SEPARATOR . $relativePath;

                // Ensure directory exists
                $destinationDir = dirname($destinationFile);
                if (!File::exists($destinationDir)) {
                    File::makeDirectory($destinationDir, 0755, true);
                }

                File::copy($file->getPathname(), $destinationFile);
                $this->line("📄 Copied: $relativePath");
            }
        }
    }

    private function getFolderSize($folder)
    {
        $size = 0;
        $files = File::allFiles($folder);

        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $this->formatBytes($size);
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . ' ' . $units[$i];
    }
}