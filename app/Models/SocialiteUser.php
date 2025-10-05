<?php

namespace App\Models;

use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser as BaseSocialiteUser;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use App\Models\User;

class SocialiteUser extends BaseSocialiteUser
{
    public static function findForProvider(string $provider, SocialiteUserContract $oauthUser): ?self
    {
        /** @var self|null $model */
        $model = parent::findForProvider($provider, $oauthUser);
        if (!$model) {
            return null;
        }

        // If the related user no longer exists, remove the stale mapping and return null
        if (! User::query()->whereKey($model->user_id)->exists()) {
            try {
                $model->delete();
            } catch (\Throwable $e) {
                // ignore cleanup errors
            }
            return null;
        }

        return $model;
    }
}


