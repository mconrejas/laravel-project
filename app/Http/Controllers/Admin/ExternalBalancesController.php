<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Http\Controllers\Admin\Traits\ManageExternalTransactionHistory;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Services\BinanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ExternalBalancesController extends Controller
{
    use ManageExternalTransactionHistory;

    /**
     * Show external balance page
     *
     * @return \Illuminate\Support\Facades\View
     */
    public function externalBalances()
    {
        return view('admin.balances.external');
    }

    /**
     * get external balance
     *
     * @return Response
     */
    public function getExternalBalances(Request $request)
    {
        if (!Cache::has('binance_external_balances')) {
            $service = BinanceService::create(['pair_stat' => new ExchangePairStat()]);
            $from_external = $service->ccxtservice()->fetch_balance();
            Cache::put('binance_external_balances', json_encode($from_external), Carbon::now()->addMinutes(1));
        }

        $items = array();
        $response = json_decode(Cache::get('binance_external_balances'));

        if ($response && isset($response->info->balances)) {
            foreach ($response->info->balances as $key => $coinObj) {
                $items[] = array(
                    'asset' => $coinObj->asset,
                    'free'  => $coinObj->free,
                    'locked' => $coinObj->locked,
                    'total' => $coinObj->free + $coinObj->locked
                );
            }
        }

        return response()->json($items, 200);
    }
    /**
     *
     *
     */
    public function itemHistory()
    {
        $type = isset($request->type) ? $request->type : 'deposits';

        return view('admin.balances.item-external-history', compact('type'));
    }

    /**
     * get item history records
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function getItemHistory(Request $request)
    {
        $data = array();
        $size = isset($request->size) ? $request->size: 100;
        $page = isset($request->page) ? $request->page: 1;
        $type = isset($request->type) ? $request->type: 'deposits';
        $ticker = isset($request->ticker) ? $request->ticker: 'all';

        $history = $this->getItemHistories($ticker, $type, $size, $page - 1);

        $response =  array(
            'last_page' => ceil($history['count'] / $size),
            'data' => $history['data'],
            'counts' => $history['count']
        );

        return response()->json($response, 200);
    }
    /**
     *
     *
     */
    public function resyncDeposits(Request $request)
    {
        if (Cache::has('external_deposit_history_request')) {
            return response()->json(['status' => true , 'message' => 'To avoid violating rate limit, this feature is temporarily unavailable. Please try again in 1 minute'], 500);
        }

        Cache::put('external_deposit_history_request', 'pending', now()->addMinutes(1));

        $service = BinanceService::create(['pair_stat' => new ExchangePairStat()]);
        $service->downloadExternalDeposits();

        return response()->json(['status' => true ], 200);
    }

    /**
     *
     *
     */
    public function resyncWithdrawals(Request $request)
    {
        if (Cache::has('external_withdrawal_history_request')) {
            return response()->json(['status' => true , 'message' => 'To avoid violating rate limit, this feature is temporarily unavailable. Please try again in 1 minute'], 500);
        }

        Cache::put('external_withdrawal_history_request', 'pending', now()->addMinutes(1));

        $service = BinanceService::create(['pair_stat' => new ExchangePairStat()]);
        $service->downloadExternalWithdrawals();

        return response()->json(['status' => true ], 200);
    }
}
