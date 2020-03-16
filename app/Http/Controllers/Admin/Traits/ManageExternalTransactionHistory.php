<?php

namespace Buzzex\Http\Controllers\Admin\Traits;

use Buzzex\Models\ExternalDepositHistory;
use Carbon\Carbon;

trait ManageExternalTransactionHistory
{
    /**
     *
     * @param string $ticker
     * @param string $type 'deposits'|'withdrawals'
     * @param int $limit
     * @param int $skip
     * @return mixed|array
     */
    public function getItemHistories($ticker = 'all', $type = 'deposits', $limit = 100, $skip = 0)
    {
        if ($type == 'deposits') {
            return $this->getItemDepositsHistory($ticker, $limit, $skip);
        }
        return $this->getItemWithdrawalsHistory($ticker, $limit, $skip);
    }

    /**
     *
     * @param string|int $ticker
     * @param int $limit
     * @param int $skip
     * @return array
     */
    public function getItemWithdrawalsHistory($ticker = 'all', $limit = 100, $skip = 0)
    {
        $count = 0;
        $history = [];
        return array('count' => $count, 'data' => $history);
    }

    /**
     *
     * @param string|int $ticker
     * @param int $limit
     * @param int $skip
     * @return array
     */
    public function getItemDepositsHistory($ticker = 'all', $limit = 100, $skip = 0)
    {
        $count = 0;
        $history  = ExternalDepositHistory::join('exchange_items', 'exchange_items.symbol', '=', 'external_deposit_history.asset')
                    ->where('exchange_items.type', '<>', 4);

        if ($ticker != 'all') {
            $history = $history->where('exchange_items.symbol', '=', $ticker);
        }
                    
        $count = $history->count();

        $history = $history->skip($skip)->take($limit)->orderBy('id', 'desc')->get();

        if ($history) {
            $history = $history->mapWithKeys(function ($history, $key) {
                return [
                    $key => [
                        'id' => $history->id,
                        'txid' => $history->txid,
                        'timestamp' => $history->timestamp,
                        'address' => $history->address,
                        'amount' => abs($history->amount) - $history->fee,
                        'fee' => $history->fee,
                        'item' => $history->symbol,
                        'status' => $history->status,
                        'source' => $history->source,
                        'raw_data' => json_decode($history->raw_data, true)
                    ]
                ];
            });
        }

        return array('count' => $count, 'data' => $history);
    }
}
