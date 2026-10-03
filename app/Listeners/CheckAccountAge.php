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
     * Staff and users a host has exempted are never restricted (any old flag is cleared).
     */
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->socialiteUser->getUser();

        $restricted = $user->isSubjectToAgeCheck()
            && AccountAge::isTooYoung($event->socialiteUser->provider, $event->oauthUser);

        if ($restricted !== $user->isAgeRestricted()) {
            $user->update(['flags' => $restricted
                ? $user->flags | User::FLAG_AGE_RESTRICTED
                : $user->flags & ~User::FLAG_AGE_RESTRICTED]);
        }
    }
}
