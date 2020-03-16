<?php

namespace Buzzex\Services;

use Buzzex\Models\ExchangeApi;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeItemWithdrawalsFee;
use Buzzex\Models\ExchangeOrder;
use Buzzex\Models\ExchangeTransaction;
use Buzzex\Models\ExchangeUserDepositRequest;
use Buzzex\Models\ExternalDepositHistory;
use Buzzex\Models\ExternalWithdrawalHistory;
use Carbon\Carbon;
use Curl\Curl;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tolawho\Loggy\Facades\Loggy;
use ccxt\binance;

class BinanceService extends ExchangeService
{
    /**
     * Determines if debugging is on or off
     * @var bool
     */
    protected $debug = false;

    /**
     * @var string
     */
    protected $service = 'binance';

    /**
     * @return \ccxt\binance;
     */
    public function ccxtservice()
    {
        return new binance([
                'apiKey' => config('external_exchanges.' . $this->service . '.api_key'),
                'secret' => config('external_exchanges.' . $this->service . '.secret_key'),
                'options' => [
                  'timeDifference' => 1000
                ]
        ]);
    }

    /**
     * @param $code
     * @param $amount
     * @param $address
     * @param null $tag
     * @param array $params
     * @return array
     */
    public function withdraw($code, $amount, $address, $tag=null, $params=array())
    {
        $service = new binance([
            'apiKey' => config('external_exchanges.' . $this->service . '.api_key_two'),
            'secret' => config('external_exchanges.' . $this->service . '.secret_key_two'),
            'options' => [
                'timeDifference' => 1000
            ]
        ]);

        try {
            return $service->withdraw($code, $amount, $address, $tag, $params);
        } catch (\Exception $e) {
            return str_replace($this->service, "", $e->getMessage());
        }
    }

    /**
     * @return array;
     */
    public function markets()
    {
        if (Cache::has('binance_markets')) {
            return json_decode(Cache::get('binance_markets', '{}'), true);
        }

        $markets = $this->ccxtservice()->fetch_markets();

        $expiresAt = now()->addMinutes(5);
        Cache::put('binance_markets', json_encode($markets), $expiresAt);

        return $markets;
    }

    /**
     * @return array;
     */
    public function fetch_deposit_address($code, $params = [])
    {
        return $this->ccxtservice()->fetch_deposit_address($code, $params = []);
    }

    /**
     * @return array;
     */
    public function checkBalance()
    {
        return $this->ccxtservice()->fetch_balance();
    }
    
    /**
     * @return array;
     */
    public function getLimits($symbol)
    {
        $market = $this->checkMarket($symbol);
        return array_key_exists('limits', $market) ? $market['limits'] : false;
    }

    /**
     * @param array $params
     *
     * @return bool
     * @throws \ErrorException
     */
    public function trade(array $params)
    {
        $curl = new Curl();
        $recvWindow = 5000;

        $params = array_merge($params, [
            'type'        => 'LIMIT',
            'timeInForce' => 'GTC',
            'recvWindow'  => $recvWindow,
            'timestamp'   => $this->getServerTime(),
        ]);

        $params['signature'] = hmac256(config('external_exchanges.'.$this->service.'.secret_key'), $params);

        $curl->setHeader('X-MBX-APIKEY', config('external_exchanges.'.$this->service.'.api_key'));
        $curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        $curl->setHeader('User-Agent', 'Mozilla/4.0 (compatible; PHP Binance API)');
        $curl->post($this->getTradeEndpoint(), $params);

        $data = @json_decode($curl->response);

        return $this->isTradingSuccessful($data);
    }
    /**
     * @param $data
     *
     * @return bool|mixed
     */
    protected function isTradingSuccessful($data)
    {
        $data = (object) $data;

        if (isset($data->code)) {
            Loggy::info('exchange', 'External trade failed :'.json_encode($data));
            return false;
        }

        if (!property_exists($data, 'status') || empty($data)) {
            return false;
        }

        if ($data->executedQty < $data->origQty) {
            $cancelled = $this->ccxtservice()->cancel_order($data->orderId, $this->getPairString(false));
            Loggy::info('exchange', 'External order cancelled :'.json_encode($cancelled));
        }

        return $data;
    }

    /**
     * @param $limit
     *
     * @return array
     */
    public function getOrderbook($limit)
    {
        $url = $this->getOrderbookEndpoint();
        $orderbook_user_id = parameter('external_exchange_order_user_id');
        $pairstat = $this->getExchangePairStat();
        $orderBook = [];

        $results = null;
        try {
            $results = $this->getData($url, $limit);
        } catch (GuzzleException $exception) {
        }

        if (!$results) {
            return [];
        }
        
        $data = [
            'BUY'  => $results->bids ?: [],
            'SELL' => $results->asks ?: [],
        ];

        $profitMargin = $this->getProfitMargin();

        foreach ($data as $type => $orders) {
            foreach ($orders as $order) {
                $price = ($type === 'BUY')
                    ? $order[0] - ($order[0] * $profitMargin)
                    : $order[0] + ($order[0] * $profitMargin);

                $orderBook[] = [
                    'action'     => $type,
                    'price'      => $price,
                    'amount'     => $order[1],
                    'pair_id'    => $pairstat->pair_id,
                    'module_id'  => $this->exchange->id,
                    'module'     => $this->service,
                    'user_id'    => $orderbook_user_id,
                    'created'    => Carbon::now()->timestamp,
                    'ip_address' => request()->ip(),
                ];
            }
        }

        return $orderBook;
    }

    /**
     * @param $url
     * @param $limit
     *
     * @return mixed
     * @throws GuzzleException
     */
    protected function getData($url, $limit)
    {
        $client = new Client;
        $pair_string = $this->getPairString();

        $response = $client->request(
            'GET',
            $url,
            [
                'query' => [
                    'symbol' => $pair_string,
                    'limit'  => $limit,
                ],
            ]
        );

        return json_decode((string)$response->getBody());
    }

    /**
     * @return string
     */
    public function getPairString($noSlash=true)
    {
        $pairstat = $this->getExchangePairStat();

        return $pairstat->exchangePair->getNameAttribute($noSlash);
    }

    /**
     *
     * @return timestamp
     */
    public function getServerTime()
    {
        // NOTE: your computer time must be sync with 'time-a.nist.gov' time
        $response = "";
        $curl = new Curl();
        $curl->get($this->getServerTimeEndpoint());

        if ($response = json_decode($curl->response)) {
            //print_r($response);
            $response = $response->serverTime;
        } else {
            $response = (int)(microtime(true) * 1000);
        }

        return $response;
    }

    public function deleteOldEntries()
    {
        $currentTimestamp = now()->subMinutes(1)->timestamp;

        ExchangeOrder::where('module_id', '=', $this->getExchangeApi()->id)
            ->where('fulfilled_amount', '=', 0)
            ->where('pair_id', $this->getExchangePairStat()->pair_id)
            ->where('created', '<=', $currentTimestamp)
            ->delete();
    }

    /**
     * @param string $symbol
     *
     * @return \ccxt\binance;
     */
    public function checkMarket($symbol)
    {
        $market = array_keys(array_column($this->markets(), 'id'), $symbol);

        if (!$market) {
            $market = array_keys(array_column($this->markets(), 'symbol'), $symbol);
        }

        if ($market) {
            return $this->markets()[$market[0]];
        }

        return false;
    }

    /**
     *
     *
     */
    public function downloadExternalWithdrawals($ticker = null, $params = [])
    {
        $range_days = parameter('external_withdrawal_history.fetch_range_in_days', null);

        $timestamp_since = null;

        if (!is_null($range_days)) {
            $firstrecord = ExternalWithdrawalHistory::whereDate('created_at', '>=', Carbon::now()->subDays($range_days))->orderBy('id', 'asc')->first();
            $timestamp_since = $firstrecord ? $firstrecord->timestamp : null;
        }

        $records = $this->ccxtservice()->fetch_withdrawals($ticker, $timestamp_since, null, $params);
        if (!empty($records)) {
            foreach ($records as $key => $record) {
                if ($record['type'] == 'withdrawal') {
                    $on_database = ExternalWithdrawalHistory::where('external_id', '=', $record['id'])
                        ->where('amount', '=', currency(((float) $record['amount']), 8))
                        ->where('asset', '=', strtoupper($record['currency']))
                        ->first();

                    if ($on_database) {
                        if ((int) $on_database->status != (int) $record['info']['status'] || $on_database->txid != $record['txid']) { //0:pending,1:success,6:Completed
                            $this->updateExternalWithdrawals($on_database, $record);
                        }
                    } elseif (is_null($on_database)) {
                        $this->saveExternalWithdrawals($record);
                    }
                }
            }
        }
    }

    /**
     *
     *
     */
    public function updateExternalWithdrawals(ExternalWithdrawalHistory $withdrawal, $record)
    {
        $withdrawal->status = $record['info']['status'];
        $withdrawal->txid = $record['txid'];
        if (!$withdrawal->save()) {
            if ($this->debug) {
                echo "Updating status FAILED!";
            }
            return false;
        }
        $exchangeTransaction = ExchangeTransaction::where('transaction_id',$withdrawal->has_match)->where('released',0)->first();
        if(!$exchangeTransaction){
            if($this->debug){
                echo "No matching unreleased user withdrawal transaction.";
            }
            return false;
        }

        if(!$withdrawal->txid){
            $exchangeTransaction->remarks2 = $withdrawal->txid;
            $exchangeTransaction->released = Carbon::now()->timestamp;
            //<!-- log notes
            #$data = array();
            $current_logs = $exchangeTransaction->logs;
            $current_logs[] = array(
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'notes' => "Alt withdrawal automatically marked as released via External withdrawals download script.",
                'updated_by' => 0
            );
            #$data['logs'] = $current_logs;
            $exchangeTransaction->logs = $current_logs;
            //-->

            if($exchangeTransaction->save()){
                if($this->debug){
                    echo "Matching user withdrawal transactions successfully marked as released.";
                }
            }else{
                if($this->debug){
                    echo "Failed releasing matching user withdrawal transaction!";
                }
            }
        }


    }

    /**
     *
     *
     */
    public function saveExternalWithdrawals($record = array())
    {
        if (!empty($record)) {
            $model = ExternalWithdrawalHistory::create([
                'external_id' => $record['id'],
                'txid' => $record['txid'],
                'timestamp' => (int) $record['timestamp'],
                'status' => $record['info']['status'],
                'address' => $record['address'],
                'amount' => currency(((float) $record['amount']), 8),
                'source' => 'binance',
                'fee' => is_null($record['fee']['cost']) ? 0 : $record['fee']['cost'],
                'asset' => strtoupper($record['currency']),
                'raw_data' => json_encode($record)
            ]);
            if (!$model) {
                if ($this->debug) {
                    echo "--Inserting row FAILED!";
                }
                return false;
            }
            if ($this->debug) {
                print_r($model);
                echo "Insert ID: ".$model->id;
            }

            return $this->checkExternalWithdrawalMatch($model);
        }
    }

    /**
     *
     *
     */
    public function downloadExternalDeposits($ticker = null, $params = [])
    {
        $range_days = parameter('external_deposit_history.fetch_range_in_days', null);

        $timestamp_since = null;

        if (!is_null($range_days)) {
            $firstrecord = ExternalDepositHistory::whereDate('created_at', '>=', Carbon::now()->subDays($range_days))->orderBy('id', 'asc')->first();
            $timestamp_since = $firstrecord ? $firstrecord->timestamp : null;
        }

        $records = $this->ccxtservice()->fetch_deposits($ticker, $timestamp_since, null, $params);
        if (!empty($records)) {
            foreach ($records as $key => $record) {
                if ($record['type'] == 'deposit') {
                    $on_database = ExternalDepositHistory::where('txid', '=', $record['txid'])
                        ->where('amount', '=', currency(((float) $record['amount']), 8))
                        ->where('asset', '=', strtoupper($record['currency']))
                        ->first();

                    if ($on_database) {
                        if ($on_database->has_match === null) {
                            $this->checkExternalDepositMatch($on_database);
                        }
                        if ((int) $on_database->status == 0 && (int) $record['info']['status'] == 1) {
                            $this->updateExternalDeposits($on_database, $record['info']['status']);
                        }
                    } elseif (is_null($on_database)) {
                        $this->saveExternalDeposits($record);
                    }
                }
            }
        }
    }

    /**
     *
     *
     */
    public function saveExternalDeposits($record = array())
    {
        if (!empty($record)) {
            $model = ExternalDepositHistory::create([
                'txid' => $record['txid'],
                'timestamp' => (int) $record['timestamp'],
                'status' => $record['info']['status'],
                'address' => $record['address'],
                'amount' => currency(((float) $record['amount']), 8),
                'source' => 'binance',
                'fee' => is_null($record['fee']['cost']) ? 0 : $record['fee']['cost'],
                'asset' => strtoupper($record['currency']),
                'raw_data' => json_encode($record)
            ]);
            if (!$model) {
                if ($this->debug) {
                    echo "--Inserting row FAILED!";
                }
                return false;
            }
            if ($this->debug) {
                print_r($model);
                echo "Insert ID: ".$model->id;
            }
            return $this->checkExternalDepositMatch($model);
        }
    }

    /**
     * @param ExternalDepositHistory $deposit
     * @
     */
    public function updateExternalDeposits(ExternalDepositHistory $deposit, $status)
    {
        $deposit->status = $status;
        if (!$deposit->save()) {
            if ($this->debug) {
                echo "Updating status FAILED!";
            }
            return false;
        }
        if ($this->debug) {
            print_r($deposit);
        }
        $exchangeUserDepositRequest = ExchangeUserDepositRequest::where('request_id', $deposit->has_match)->first();
        if (!$exchangeUserDepositRequest) {
            if ($this->debug) {
                echo "User deposit request not found.";
            }
            $deposit->status = $status;
            $deposit->save();
            return false;
        }
        $exchangeTransaction = ExchangeTransaction::where('module', $exchangeUserDepositRequest->getTable())->where('module_id', $deposit->has_match)->first();
        if (!$exchangeTransaction) {
            if ($this->debug) {
                echo "Exchange transaction not found.";
            }
            $deposit->status = $status;
            $deposit->save();
            return false;
        }
        $exchangeTransaction->confirmations = 100000;
        $exchangeTransaction->released = time();
        if (!$exchangeTransaction->save()) {
            if ($this->debug) {
                echo "Exchange transaction update FAILED!";
            }
            $deposit->status = $status;
            $deposit->save();
            return false;
        }
    }

    /**
     * @param ExternalDepositHistory $deposit
     * @return bool
     */
    public function checkExternalDepositMatch(ExternalDepositHistory $deposit)
    {
        $exchangeItem = ExchangeItem::where('symbol', $deposit->asset)->where('deleted', 0)->first();
        if (!$exchangeItem) {
            if ($this->debug) {
                echo "Exchange Item Null";
            }
            return false;
        }
        if ($this->debug) {
            echo "Exchange Item ID: ".$exchangeItem->item_id;
        }
        $exchangeApi = ExchangeApi::where('name', $deposit->source)->first();
        if (!$exchangeApi) {
            if ($this->debug) {
                echo "Exchange API Null";
            }
            return false;
        }
        if ($this->debug) {
            echo "API ID: ".$exchangeApi->id;
        }
        #1. check if there's a match on user deposit requests
        $exchangeUserDepositRequest = ExchangeUserDepositRequest::where('exchange_api_id', $exchangeApi->id)->where('item_id', $exchangeItem->item_id)->where('amount', $deposit->amount)->first();
        if (!$exchangeUserDepositRequest) {
            if ($this->debug) {
                echo "Exchange User Deposit Request Null";
            }
            $has_match = 0;
        } else {
            $has_match = $exchangeUserDepositRequest->request_id;
        }

        if ($this->debug) {
            print_r($exchangeUserDepositRequest);
        }
        $deposit->has_match = $has_match;
        if (!$deposit->save()) {
            if ($this->debug) {
                echo "Updating match status FAILED.";
            }
            return false;
        }
        if (!$has_match) {
            if ($this->debug) {
                echo "No match found. End.";
            }
            return false;
        }
        #2. save to exchange_transactions table if match is found.
        $data = [
            'time' => $deposit->timestamp/1000,
            'user_id' => $exchangeUserDepositRequest->user_id,
            'amount' => $deposit->amount,
            'address' => $deposit->address,
            'txid' => $deposit->txid,
            'raw_data' => $deposit->raw_data,
            'confirmations' => $deposit->status?100000:0,
            'module_id' => $has_match,
            'module' => $exchangeUserDepositRequest->getTable(),
            'item_id' => $exchangeItem->item_id,
            'created' => time(),
            'released' => $deposit->status?time():0,
            'type' => 'deposit'
        ];
        $exchangeTransaction = ExchangeTransaction::create($data);
        if (!$exchangeTransaction) {
            if ($this->debug) {
                echo "Inserting exchange transaction FAILED!";
            }
            $deposit->has_match = null; //reset value
            $deposit->save();
            return false;
        }
        if ($this->debug) {
            echo "Exchange transaction inserted with ID ".$exchangeTransaction->transaction_id;
        }
        return $exchangeTransaction->transaction_id;
    }


    public function checkExternalWithdrawalMatch(ExternalWithdrawalHistory $withdrawal)
    {
        $exchangeItem = ExchangeItem::where('symbol', $withdrawal->asset)->where('deleted', 0)->first();
        if (!$exchangeItem) {
            if ($this->debug) {
                echo "Exchange Item Null";
            }
            return false;
        }
        if ($this->debug) {
            echo "Exchange Item ID: ".$exchangeItem->item_id;
        }
        $exchangeApi = ExchangeApi::where('name', $withdrawal->source)->first();
        if (!$exchangeApi) {
            if ($this->debug) {
                echo "Exchange API Null";
            }
            return false;
        }
        if ($this->debug) {
            echo "API ID: ".$exchangeApi->id;
        }
        #1. check if there's a match on user deposit requests
        $exchangeTransaction = ExchangeTransaction::where('exchange_api_id',$exchangeApi->id)->where('item_id',$exchangeItem->item_id)->where('remarks2',$withdrawal->external_id)->first();
        if (!$exchangeTransaction) {
            if ($this->debug) {
                echo "No matching user withdrawal request";
            }
            $has_match = 0;
        } else {
            $has_match = $exchangeTransaction->transaction_id;
        }

        if ($this->debug) {
            print_r($exchangeTransaction);
        }
        $withdrawal->has_match = $has_match;
        if (!$withdrawal->save()) {
            if ($this->debug) {
                echo "Updating match status FAILED.";
            }
            return false;
        }
        if (!$has_match) {
            if ($this->debug) {
                echo "No match found. End.";
            }
            return false;
        }
        #2. update exchange transactions table if match is found with enough parameters.
        if(!$withdrawal->txid){
            if($this->debug){
                echo "External withdrawal txid is null or empty. Nothing to update yet.";
            }
        }else{
            $exchangeTransaction->remarks2 = $withdrawal->txid;
            $exchangeTransaction->released = Carbon::now()->timestamp;
            //<!-- log notes
            #$data = array();
            $current_logs = $exchangeTransaction->logs;
            $current_logs[] = array(
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'notes' => "Alt withdrawal automatically marked as released via External withdrawals download script.",
                'updated_by' => 0
            );
            #$data['logs'] = $current_logs;
            $exchangeTransaction->logs = $current_logs;
            //-->
            if($exchangeTransaction->save()){
                if($this->debug){
                    echo "Matching user withdrawal transactions successfully marked as released.";
                }
            }else{
                if($this->debug){
                    echo "Failed releasing matching user withdrawal transaction!";
                }
            }
        }

        return $exchangeTransaction->transaction_id;
    }

    /**
     * download external withdrawals fees
     * @return void
     */
    public function downloadExternalWithdrawalFees()
    {
        $response =  $this->ccxtservice()->fetch_funding_fees();

        if ($response && isset($response['info']['assetDetail'])) {
            $assets = $response['info']['assetDetail'] ?? [];
            if (!empty($assets)) {
                foreach ($assets as $coinSymbol => $asset) {
                    $item = ExchangeItem::where('symbol', strtoupper($coinSymbol))->first();
                    if (!$item) {
                        continue;
                    }
                    $this->updateOrCreateWithdrawalFee($item, $asset) ;
                }
            }
        }
    }

    /**
     * update or save given withdrawals fees
     * @param Exchangeitem $item
     * @param array $asset
     * @return void
     */
    public function updateOrCreateWithdrawalFee(ExchangeItem $item, $asset = [])
    {
        $service = ExchangeApi::where('name', $this->service)->first();
        if (!$service || empty($asset)) {
            return;
        }
        $fee = ExchangeItemWithdrawalsFee::where('item_id', $item->item_id)
                ->where('exchange_api_id', $service->id)
                ->first();
  
        if (!$fee) {
            ExchangeItemWithdrawalsFee::create([
                'item_id' => $item->item_id,
                'exchange_api_id' => $service->id,
                'fee' => $asset['withdrawFee'],
                'minimum_amount' => $asset['minWithdrawAmount'],
                'raw_data' => $asset
            ]);
            return;
        }
        //update only if there is changes
        if ((float) $fee->minimum_amount != (float) $asset['minWithdrawAmount'] || (float) $fee->fee != (float) $asset['withdrawFee']) {
            $fee->update([
                'fee' => $asset['withdrawFee'],
                'minimum_amount' => $asset['minWithdrawAmount'],
                'raw_data' => $asset
            ]);
        }
        return;
    }
}
