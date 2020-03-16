<?php

namespace Buzzex\Services;

use Buzzex\Contracts\Security\TwoFactorAuthenticable;
use Buzzex\Models\PasswordSecurity;
use Buzzex\Models\User;
use PragmaRX\Google2FA\Exceptions\InsecureCallException;
use PragmaRX\Google2FA\Google2FA;

class Google2FAService implements TwoFactorAuthenticable
{
    /**
     * @var Google2FA
     */
    private $google2FA;

    public function __construct(Google2FA $google2FA)
    {
        $this->google2FA = $google2FA;
    }

    /**
     * @param User $user
     *
     * @return string
     */
    public function getQRCodeUrl(User $user)
    {
        if (!$user->passwordSecurity()->exists()) {
            return '';
        }

        $this->google2FA->setAllowInsecureCallToGoogleApis(config('google2fa.allow_insecure_call'));

        try {
            return $this->google2FA->getQRCodeGoogleUrl(
                config('app.name'),
                $user->email,
                $user->passwordSecurity->google2fa_secret
            );
        } catch (InsecureCallException $exception) {
            return '';
        }
    }


    /**
     * @param User $user
     *
     * @return bool
     */
    public function generateSecretKey(User $user)
    {
        $passwordSecurity = PasswordSecurity::create([
            'user_id' => $user->id,
            'google2fa_enable' => 0,
            'google2fa_secret' => $this->google2FA->generateSecretKey(),
        ]);

        return $passwordSecurity ? true : false;
    }

    /**
     * @param User $user
     * @param $secret
     *
     * @return bool
     */
    public function enable(User $user, $secret)
    {
        if (!$user->passwordSecurity()->exists()) {
            return false;
        }

        if (!$this->google2FA->verifyKey($user->passwordSecurity->google2fa_secret, $secret)) {
            return false;
        }

        $user->passwordSecurity->google2fa_enable = 1;

        return $user->passwordSecurity->save();
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function disable(User $user)
    {
        if (!$user->passwordSecurity()->exists()) {
            return false;
        }

        $user->passwordSecurity->google2fa_enable = 0;

        return $user->passwordSecurity->save();
    }
}
