<?php

namespace Buzzex\Contracts\User;

use Buzzex\Models\User;

interface CanManageOwnFund
{
    
    /**
     * Get Funds by tickers
     *
     * @param boolean $includeOrders
     * @param User $user
     * @param array $tickers
     * @return array
     */
    public function getFundsByTickers($includeOrders = false, User $user, $tickers = []);

    /**
     * Get Funds
     *
     * @param boolean $includeOrders
     * @param User $user
     * @return array
     */
    public function getFunds($includeOrders = false, User $user);

    /**
     * Get Funds
     *
     * @param boolean $includeOrders
     * @return array
     */
    public function getAllFunds($includeOrders = false);

    /**
     * @param User $user
     * @param array $tickers
     * @return array
     */
    public function getOpenOrders($user, $tickers = []);
}
