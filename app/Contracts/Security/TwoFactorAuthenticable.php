<?php

namespace Buzzex\Contracts\Security;

use Buzzex\Models\User;

interface TwoFactorAuthenticable
{
    /**
     * @param User $user
     *
     * @return string
     */
    public function getQRCodeUrl(User $user);

    /**
     * @param User $user
     *
     * @return bool
     */
    public function generateSecretKey(User $user);

    /**
     * @param User $user
     * @param $secret
     *
     * @return bool
     */
    public function enable(User $user, $secret);

    /**
     * @param User $user
     *
     * @return bool
     */
    public function disable(User $user);
}
