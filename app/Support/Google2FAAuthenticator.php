<?php

namespace Buzzex\Support;

use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FALaravel\Exceptions\InvalidSecretKey;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class Google2FAAuthenticator extends Authenticator
{
    /**
     * check if user can pass without checking for 2fa
     *
     * @return boolean
     */
    protected function canPassWithoutCheckingOTP()
    {
        $user = $this->getUser();

        if (!$user && !Auth::check()) {
            return true;
        }

        if ($user->passwordSecurity === null) {
            return true;
        }

        if ($user->passwordSecurity()->count() === 0) {
            return true;
        }

        return
            !$user->passwordSecurity->google2fa_enable ||
            !$this->isEnabled() ||
            $this->noUserIsAuthenticated() ||
            $this->twoFactorAuthStillValid();
    }

    /**
     * Get the Google 2FA secret key for user
     *
     * @return string
     * @throws InvalidSecretKey
     */
    protected function getGoogle2FASecretKey()
    {
        $secret = $this->getUser()->passwordSecurity->{$this->config('otp_secret_column')};

        if (is_null($secret) || empty($secret)) {
            throw new InvalidSecretKey(__('Secret key cannot be empty.'));
        }

        return $secret;
    }
}
