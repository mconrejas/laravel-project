<?php

namespace Buzzex\Repositories;

use Buzzex\Contracts\User\CanManageOwnFund;
use Buzzex\Contracts\User\CanManageUser;
use Buzzex\Contracts\User\Tradable;
use Buzzex\Models\User;

class UserRepository
{
    /**
     * @var CanManageUser
     */
    private $userManager;

    /**
     * @var Tradable
     */
    private $trading;

    /**
     * @var CanManageOwnFund
     */
    private $funds;

    /**
     * UserRepository constructor.
     *
     * @param CanManageUser $userManager
     * @param Tradable $trading
     * @param CanManageOwnFund $funds
     */
    public function __construct(CanManageUser $userManager, Tradable $trading, CanManageOwnFund $funds)
    {
        $this->userManager = $userManager;
        $this->trading = $trading;
        $this->funds = $funds;
    }

    /**
     * @param array $data
     *
     * @return \Buzzex\Models\User
     */
    public function create(array $data)
    {
        return $this->userManager->create($data);
    }

    /**
     * @param $affiliateId
     *
     * @return \Buzzex\Models\User
     */
    public function getUserByAffiliateId($affiliateId)
    {
        return $this->userManager->read(['affiliate_id' => $affiliateId], true)->first();
    }

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function getCurrentOrders(User $user, $filters = [], $orderBy = 'order_id', $orderDirection = 'desc')
    {
        return $this->trading->getCurrentOrders($user, $filters, $orderBy, $orderDirection);
    }

    /**
     * @param User $user
     * @param array $filters
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection\
     */
    public function getOrderHistory(User $user, $filters = [])
    {
        return $this->trading->getOrderHistory($user, $filters);
    }

    /**
     * Get Funds
     *
     * @param boolean $includeOrders
     * @param User $user
     *
     * @return array
     */
    public function getFunds($includeOrders = false, User $user)
    {
        return $this->funds->getFunds($includeOrders, $user);
    }

    /**
     * Get Funds by tickers
     *
     * @param boolean $includeOrders
     * @param array $tickers
     * @param User $user
     *
     * @return array
     */
    public function getFundsByTickers($includeOrders = false, User $user, $tickers = [])
    {
        return $this->funds->getFundsByTickers($includeOrders, $user, $tickers);
    }

    /**
     * @param \Buzzex\Models\User $user
     * @param array $params
     * @return \Buzzex\Contracts\User\ExchangeOrder
     */
    public function trade(User $user, array $params)
    {
        return $this->trading->trade($user, $params);
    }
}
