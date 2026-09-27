<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class VerifyProductionConfiguration extends Command
{
    protected $signature = 'app:verify-production-config';

    protected $description = 'Verify security-sensitive configuration before deploying to production';

    public function handle(): int
    {
        if (! app()->environment('production')) {
            $this->error('Deployment aborted: APP_ENV must be production.');

            return self::FAILURE;
        }

        if (config('auth.local_login.enabled')) {
            $this->error('Deployment aborted: LOCAL_LOGIN_ENABLED must be false.');

            return self::FAILURE;
        }

        $localUserExists = User::query()
            ->where('uuid', config('auth.local_login.user_uuid'))
            ->exists();

        if ($localUserExists) {
            $this->error('Deployment aborted: the local development administrator exists in the production database.');

            return self::FAILURE;
        }

        $this->info('Production configuration is safe to deploy.');

        return self::SUCCESS;
    }
}
