<?php

namespace Buzzex\Models\Traits;

use Buzzex\Contracts\User\CanManageOwnFund;

trait HasFunds
{
    /**
     * @return array
     */
    public function getAllFunds()
    {
        $app = app(CanManageOwnFund::class);
        return (array) $app->getFunds(false, $this);
    }

    /**
     * @param string $pair_text
     * @return array
     */
    public function getFundsByPairText($pair_text)
    {
        $app = app(CanManageOwnFund::class);
        $pair = explode("_", $pair_text);
        $base =  $pair[0];
        $target =  $pair[1];
        $funds = $app->getFundsByTickers(false, $this, [$base, $target]);
        return [
            'base' => isset($funds[$base]) ? round($funds[$base], 8) : 0,
            'target' => isset($funds[$target]) ? round($funds[$target], 8) : 0
        ];
    }

    /**
     * @param string $ticker
     * @return int
     */
    public function getFundsByCoin($ticker)
    {
        $app = app(CanManageOwnFund::class);
        $funds = $app->getFundsByTickers(false, $this, [ strtoupper($ticker) ]);
        return isset($funds[strtoupper($ticker)]) ? round($funds[strtoupper($ticker)], 8) : 0;
    }
}
