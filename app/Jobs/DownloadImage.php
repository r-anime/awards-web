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
        //

        $imageContent = file_get_contents($this->url);
        Storage::disk('public')->put($this->filename, $imageContent);
    }
}
