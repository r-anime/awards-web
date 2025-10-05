<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendAuditWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $webhookUrl,
        private readonly array $payload,
    ) {
    }

    public function handle(): void
    {
        try {
            Http::timeout(5)->acceptJson()->asJson()->post($this->webhookUrl, $this->payload);
        } catch (\Throwable $e) {
            // swallow
        }
    }
}


