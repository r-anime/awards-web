<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Storage;

class DownloadImage implements ShouldQueue
{
    use Queueable;
    /**
     * Create a new job instance.
     */
    public function __construct(public string $filename, public string $url)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $imageContent = file_get_contents($this->url);
            
            if ($imageContent === false) {
                \Log::error("Failed to download image from URL: {$this->url}");
                throw new \Exception("Failed to download image from URL");
            }
            
            // Ensure directory exists before writing
            $directory = dirname($this->filename);
            $fullPath = storage_path('app/public/' . $directory);
            
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0775, true);
            }
            
            Storage::disk('public')->put($this->filename, $imageContent);
        } catch (\Exception $e) {
            \Log::error("DownloadImage job failed", [
                'filename' => $this->filename,
                'url' => $this->url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
