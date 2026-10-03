<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AccountAge;
use DutchCodingCompany\FilamentSocialite\Events\Login;

class CheckAccountAge
{
    /**
     * Runs on every OAuth login, unlike the panel's create/resolve callbacks, so the
     * restriction follows the account's current age and the current requirement.
     * Users a host has exempted are never restricted.
     */
    public function handle(Login $event): void
    {
        $user = $event->socialiteUser->getUser();

        $restricted = ! $user->isAgeExempt()
            && AccountAge::isTooYoung($event->socialiteUser->provider, $event->oauthUser);

        if ($restricted !== $user->isAgeRestricted()) {
            $user->update(['flags' => $restricted
                ? $user->flags | User::FLAG_AGE_RESTRICTED
                : $user->flags & ~User::FLAG_AGE_RESTRICTED]);
        }
    }
}
