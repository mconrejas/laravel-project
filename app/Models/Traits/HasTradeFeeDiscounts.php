<?php

namespace Buzzex\Models\Traits;

use Buzzex\Contracts\User\CanManageOwnFund;

trait HasTradeFeeDiscounts
{
    /**
     * Get user BZX balance
     *
     * @return float
     */
    public function getBZXBalance()
    {
        $user = app()->make(CanManageOwnFund::class);
        $balance = $user->getFundsByTickers(true, $this, ['BZX']);
        if ($balance && array_key_exists('BZX', $balance)) {
            return $balance['BZX'];
        }
        return 0;
    }

    public function getAllBZXBalance(){
        $app = app()->make(CanManageOwnFund::class);
        $balance = $app->getAllFunds(true, $this, ['BZX']);
        if ($balance && array_key_exists('BZX', $balance)) {
            return $balance['BZX'];
        }
        return 0;
    }

    /**
     * Check if user has discount based on BZX balance
     * @param $balance float
     * @return boolean
     */
    public function hasTradeFeeDiscounts($balance)
    {
        if ((int) parameter('trade_fee_discount_disabled', 1) == 1) {
            return false;
        }

        return $balance >= parameter('trade_fee_discount_min_bzx_balance', 1000);
    }

    /**
     * Get percentage discount map
     *
     * @return array
     */
    public function getPercentageFeeDiscountsMap()
    {
        $discountMap = array();

        $percentage = 5;
        $balance = 1000;
        while ($balance <= 20000) {
            $discountMap["$percentage"] = $balance;
            $percentage += 5;
            $balance += 1000;
        }

        return $discountMap;
    }

    /**
     * Get percentage discount in percentage
     * @param $balance float
     * @return array
     */
    public function getPercentageFeeDiscounts($balance = null)
    {
        if ((int) parameter('trade_fee_discount_disabled', 1) == 1) {
            return 0;
        }

        $percentage_map = $this->getPercentageFeeDiscountsMap();

        if (is_null($balance)) {
            $balance = $this->getBZXBalance();
        }
        
        $percentage = 0;
        foreach ($percentage_map as $percent => $min_balance) {
            if ($balance < $min_balance) {
                break;
            }
            $percentage = $percent;
        }

        return $percentage;
    }
}
