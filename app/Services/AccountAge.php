<?php

namespace App\Services;

use App\Models\Option;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;

class AccountAge
{
    /**
     * Whether the Reddit or AniList account is younger than the account_age_requirement option.
     * Accounts without a creation date are let through.
     */
    public static function isTooYoung(string $provider, SocialiteUserContract $oauthUser): bool
    {
        $createdAt = match ($provider) {
            'reddit' => $oauthUser->user['created_utc'] ?? null,
            'anilist' => $oauthUser->user['createdAt'] ?? null,
            default => null,
        };

        if ($createdAt === null) {
            return false;
        }

        $accountAgeInDays = (now()->timestamp - (int) $createdAt) / 86400;

        return $accountAgeInDays < Option::get('account_age_requirement', 30);
    }
}
