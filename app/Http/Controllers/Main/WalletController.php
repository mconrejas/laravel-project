<?php

namespace Buzzex\Http\Controllers\Main;

use Buzzex\Console\Commands\ValidateWithdrawals;
use Buzzex\Crypto\Currency\CoinFactory;
use Buzzex\Crypto\Currency\Coins\Exceptions\CoinNotFoundException;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Http\Requests\WithdrawalRequest;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangePair;
use Buzzex\Models\ExchangePairStat;
use Buzzex\Models\ExchangeTransaction;
use Buzzex\Models\User;
use Buzzex\Repositories\ExchangeRepository;
use Buzzex\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery\CountValidator\Exception;
use Session;

class WalletController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ExchangeRepository
     */
    private $exchangeRepository;

    /**
     *  Determines if manager checking is skipped or not
     */
    private $skipManagerCheck = false;

    /**
     * WalletController constructor.
     *
     * @param UserRepository $userRepository
     * @param ExchangeRepository $exchangeRepository
     */
    public function __construct(UserRepository $userRepository, ExchangeRepository $exchangeRepository)
    {
        $this->userRepository = $userRepository;
        $this->exchangeRepository = $exchangeRepository;
    }

    /**
     * Show wallets
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->requestWalletsCheck();
        $option = personalWalletOption('', false);

        return view('main.wallet.index', ['value' => $option ? true : false]);
    }

    /**
     * Show records
     *
     * @return \Illuminate\Http\Response
     */
    public function record($lang, $type = "deposit")
    {
        $data = ['type' => $type];

        return view('main.wallet.record', $data);
    }

    /**
     * Show wallets
     *
     * @return \Illuminate\Http\Response
     */
    public function getWallets(Request $request)
    {
        $data = [];
        /**
         * Request being passed
         * coin -- string 'all' or coin symbol (if just need detail for that particular coin wallet)
         * size -- integer, this is the limit
         * page -- integer, the page number being requested
         */
        //todo validate first

        $items = ExchangeItem::whereNotIn('type', [4])->where('deleted', 0);

        if ($request->coin != 'all') {
            $items = $items->where('symbol', 'LIKE', $request->coin.'%');
        }
        $count = $items->count();

        if (isset($request->value)) {
            $items = $items->orderBy('symbol', 'asc')->get();
        } else {
            $items = $items->skip($request->size * ($request->page - 1))
                    ->take($request->size)
                    ->orderBy('symbol', 'asc')
                    ->get();
        }

        $ordersWOFrozen = $this->userRepository->getFunds(false, auth()->user());
        $ordersWFrozen = $this->userRepository->getFunds(true, auth()->user());

        if ($items) {
            $option = personalWalletOption($request->value);

            foreach ($items as $key => $item) {
                $amount = isset($ordersWOFrozen[$item->symbol]) ? $ordersWOFrozen[$item->symbol] : 0;
                $frozen = isset($ordersWOFrozen[$item->symbol]) ? $ordersWFrozen[$item->symbol] - $ordersWOFrozen[$item->symbol] : 0;
                $available = isset($ordersWOFrozen[$item->symbol]) ? $ordersWOFrozen[$item->symbol] : 0;
                $marketValue = isset($ordersWOFrozen[$item->symbol]) ? $ordersWOFrozen[$item->symbol] * $item->index_price_usd : 0;

                if ($option && $marketValue < $request->value) {
                    continue;
                }

                $data[] = [
                    'id'          => $item->item_id,
                    'coin'        => $item->symbol,
                    'coinName'    => $item->name,
                    'amount'      => currency($amount, 2),
                    'frozen'      => currency($frozen, 4),
                    'available'   => currency($available, 4),
                    'marketValue' => currency($marketValue, 0),
                ];
            }
        }

        if (isset($request->value)) {
            $count = count($data);
        }

        $response = [
            //the total number of available pages (this could be record_counts for particular query divided by size ), must greater than zero
            'last_page' => ceil($count / $request->size),
            'data'      => $data,
        ];

        return response()->json($response, 200);
    }

    /**
     * Show wallets
     *
     * @return \Illuminate\Http\Response
     */
    public function getPendingDeposit(Request $request)
    {
        $data = [];
        $auth_user = Auth::user();
        $user_id = $auth_user->id;
        $text = ($request->text == 'all') ? '' : trim($request->text);

        /*
        * Request being passed
        * type -- string 'deposit' or 'withdrawal'
        * coin -- string 'all' or coin shortname like 'BTC'
        * limit -- integer
        */

        // begin query
        $records = ExchangeTransaction::where('exchange_transactions.user_id', '=', $user_id)
            ->join('exchange_items', 'exchange_transactions.item_id', 'exchange_items.item_id')
            ->where('exchange_items.symbol', 'like', '%' . $text . '%')
            ->where('exchange_transactions.type', 'like', '%deposit%')
            ->where('exchange_transactions.cancelled', '=', 0)
            ->where('exchange_transactions.amount', '>', 0)
            ->where(function ($query) {
                $query->where('exchange_transactions.released', '>', time())
                    ->orWhere('exchange_transactions.released', '=', 0);
            });

        // count
        $count = $auth_user->countPendingDepositTransactions($text);

        // pagination
        $records = $records->skip($request->size * ($request->page - 1))
            ->take($request->size)
            ->get();

        if ($records) {
            foreach ($records as $record) {
                $data[] = [
                    'time'     => date('Y-m-d H:i:s', $record->created),
                    'coin'     => $record->exchangeItem->symbol, // in short name 'BTC'
                    'type'     => $request->type, // or 'withdrawal'
                    'coinName' => $record->exchangeItem->name,
                    'amount'   => $record->amount,
                    'status'   => 'pending',
                    'details'  => $record->remarks ? $record->remarks : 'N/A',
                    'txid'     => $record->txid,
                ];
            }
        }

        $response = [
            'last_page' => ceil($count / $request->size), // allrecord count divide by request->size
            'data'      => $data,
        ];

        return response()->json($response);
    }

    /**
     * Show wallets
     *
     * @return \Illuminate\Http\Response
     */
    public function getRecords(Request $request)
    {
        abort_unless(Auth::check(), 419);

        $data = [];
        $auth_user = Auth::user();
        $text = ($request->has('text') && $request->text == 'all') ? '' : trim($request->text);

        // begin query
        $records = ExchangeTransaction::select('exchange_transactions.*')
            ->where('exchange_transactions.user_id', '=', $auth_user->id)
            ->join('exchange_items', 'exchange_transactions.item_id', 'exchange_items.item_id')
            ->where('exchange_items.type', '<>', 4);

        if ($request->has('text')) {
            $records = $records->where('exchange_items.symbol', 'like', "%" . $text . "%");
        }

        if ($request->has('coin') && $request->coin != 'all') {
            $records = $records->where('exchange_items.symbol', '=', $request->coin);
        }

        // switch type
        switch ($request->type) {
            case 'deposit':
                // $count = $auth_user->countDepositTransactions($text); // count all data
                $records = $records->where('exchange_transactions.amount', '>', 0)
                    ->where(
                        'exchange_transactions.type',
                        'like',
                        "%deposit%"
                    ); // type -- 'deposit-request' or 'deposit'

                // filters
                if ($request->approved == 1) {
                    $records = $records->where('exchange_transactions.cancelled', '=', 0);
                }
                $count = $records->count();
                break;

            case 'withdrawal':
                // $count = $auth_user->countWithdrawalTransactions($text); // count all data
                $records = $records->where('exchange_transactions.cancelled', '=', 0)
                    ->where('exchange_transactions.type', '=', "withdrawal-request"); // type -- 'withdrawal-request'
                $count = $records->count();
                break;
        }

        // pagination
        $records = $records->skip($request->size * ($request->page - 1))
            ->take($request->size)
            ->orderBy('exchange_transactions.created', 'DESC')
            ->get();

        if ($records) {
            $index = 0;
            foreach ($records as $record) {
                $data[$index] = [
                    'time'     => date('Y-m-d H:i:s', $record->created),
                    'coin'     => $record->exchangeItem->symbol, // in short name 'BTC'
                    'type'     => $request->type, // or 'withdrawal'
                    'coinName' => $record->exchangeItem->name,
                    'amount'   => currency(abs($record->amount)),
                    'net_amount' => currency(abs($record->amount+$record->fee)),
                    'address'  => !empty($record->address) ? $record->address : $record->remarks,
                    "status" => strtolower($record->getStatus())
                ];
                if ($request->type == 'withdrawal') {
                    $data[$index]['txid'] = ($record->getStatus() == 'Released') ? $record->remarks2 : '-';
                }

                if ($request->type == 'deposit') {
                    $data[$index]['txid'] = !empty($record->txid) ? $record->txid : '-';
                }

                $index++;
            }
        }

        $response = [
            'last_page' => ceil($count / $request->size) <= 0 ? 1 : ceil($count / $request->size),
            'data'      => $data,
        ];

        return response()->json($response);
    }

    /**
     * Show deposit form
     *
     * @return \Illuminate\Http\Response
     */
    public function showDepositForm(Request $request)
    {
        $coin = ExchangeItem::where('symbol', strtoupper($request->coin))->where('deleted', 0)->first();

        if ($coin->deposits_off > 0) {
            toast("Deposits for $coin->symbol is currently disabled!", 'error', 'top-right');
   
            return redirect(route('my.wallet'));
        }

        abort_unless($coin, 404, __('Invalid coin ').strtoupper($request->coin));

        $currency = strtolower($coin->symbol);
        $deposit_address = Auth::user()->getDepositAddress($coin);
        $is_available = isDepositAddressAvailable($currency);
        $history_address = Auth::user()->getHistoryDepositAddress($currency);

        $exact_amount = 0;
        $amount = 0;
        $exchange_api = $coin->getExchangeApi();
        if ($exchange_api) {
            $exchange_api_id = $exchange_api->id;
        } else {
            $exchange_api_id = parameter("exchange.alt_deposit_default_api_id", 1);
        }
        $exchange_api_name = ucfirst(($exchange_api && !empty($exchange_api->name))?$exchange_api->name:parameter("exchange.alt_deposit_default_api_name", "binance"));
        $exchange_api_name_service = "Buzzex\\Services\\".$exchange_api_name."Service";
        $api_coin = $service = false;
        if ($coin->getAltDepositStatus()) {
            if (isset($request->amount) && $request->amount > 0) {
                $amount = $request->amount;
                $exact_amount=$this->generateUniqueDepositAmount($request->coin, $request->amount, $exchange_api_id);
                if (!is_array($exact_amount)) {
                    $pair = ExchangePair::query()->active()->first();
                    if (!$pair) {
                        $request->session()->flash('error', __('Invalid Coin!'));
                    }
                    if (class_exists($exchange_api_name_service)) {
                        $service = $exchange_api_name_service::create(['pair_stat' => $pair->exchangePairStat]);
                    }
                    if (!$service) {
                        $request->session()->flash('error', __('Service Not Found! Please contact support.'));
                    }
                    $api_coin = @$service->fetch_deposit_address($request->coin) ?? ''; //@TODO: cache the result for $x minutes to avoid reaching exchange API's ratelimits
                    if (!$api_coin) {
                        $request->session()->flash('error', __('Invalid Coin!'));
                    }
                } else {
                    $request->session()->flash('error', __($exact_amount["message"]));
                    $exact_amount = 0;
                    //return error message here saying - "n.
                }
            }
        }

        //echo "Exact Amount: $exact_amount";

        // dd($api_coin);
        return view(
            'main.wallet.deposit',
            compact(
                'coin',
                'deposit_address',
                'is_available',
                'history_address',
                'exact_amount',
                'api_coin',
                'amount'
            )
        );
    }

    /**
     * Process request for new deposit address
     *
     * @return \Illuminate\Http\Response
     */
    public function newDepositAddress(Request $request)
    {
        $coin = ExchangeItem::where('symbol', $request->coin)->first();
        $currency = strtolower($coin->symbol);
        abort_unless($coin, 404);

        return Auth::user()->getNewDepositAddress($currency);
    }

    /**
     * Show withdrawal form
     *
     * @return \Illuminate\Http\Response
     */
    public function showWithdrawalForm(Request $request)
    {
        $coin = ExchangeItem::where('symbol', $request->coin)->first();
        
        if ($coin->withdrawals_off > 0) {
            toast("Withdrawals for $coin->symbol is currently disabled!", 'error', 'top-right');
   
            return redirect(route('my.wallet'));
        }

        $fund = $this->userRepository->getFundsByTickers(false, auth()->user(), [$coin->symbol]);

        abort_unless($coin, 404);

        $balance = currency($fund[$coin->symbol] ?? 0, 8);
        $addressHistory = Auth::user()->getHistoryDepositAddress($coin->symbol);

        $addresses = $addressHistory ? $addressHistory->pluck('address')->toArray() : [];
        $coins_config = config("coins");
        $coins_with_tag = (isset($coins_config["coins_with_tag"]))?$coins_config["coins_with_tag"]:array();
        return view('main.wallet.withdraw', compact('coin', 'addresses', 'balance', 'coins_with_tag'));
    }

    /**
     * Process withdrawal
     *
     * @return \Illuminate\Http\Response
     */
    public function withdraw(WithdrawalRequest $request)
    {
        $exchangeitem = ExchangeItem::where('deleted', 0)->where('symbol', '=', $request->coin)->first();
        abort_unless($exchangeitem, 404, __('Invalid coin ').strtoupper($request->coin));

        $funds = Auth::user()->getFundsByCoin($request->coin);
        //$balance = isset($funds[strtoupper($request->coin)]) ? $funds[strtoupper($request->coin)] : 0;

        if ($funds  < $request->amount) {
            return redirect()->route('my.withdrawalForm', [$request->coin])->with(
                'error',
                __('Insufficient balance on ').strtoupper($request->coin)
            );
        }
        $withdrawal_tag = isset($request->tag)?$request->tag:"";
        $success = $this->exchangeRepository->withdraw(
            Auth()->user(),
            $request->coin,
            $request->address,
            $request->amount,
            $withdrawal_tag
        );

        if ($success) {
            return redirect()->route('my.withdrawalForm', [$request->coin])->with(
                'success',
                __('Withdrawal request complete!')
            );
        } else {
            return redirect()->route('my.withdrawalForm', [$request->coin])->with(
                'error',
                __('Unable to complete the request! Please try again later.')
            );
        }
    }

    /**
     * get trading pair for item
     *
     * @return \Illuminate\Http\Response
     */
    public function getTradeLinks(Request $request)
    {
        $request->validate(['item_id' => 'required|numeric']);
        $data = array();

        $pairs = ExchangePair::where('exchange_pairs.item1', '=', $request->item_id)
                ->active()
                ->orderBy('exchange_pairs.pair_id', 'asc')
                ->get();
        if ($pairs) {
            foreach ($pairs as $key => $pair) {
                if (!$pair->isBaseActive() || $pair->hasInactiveItem() || $pair->hasActTokenItem()) {
                    continue;
                }
                $data[] = [
                        'id' => $pair->pair_id,
                        'link' => route('exchange', ['locale'=> app()->getLocale(),'base'=> $pair->exchangeItemTwo->symbol, 'target' =>  $pair->exchangeItemOne->symbol]),
                        'label' => $pair->exchangeItemOne->symbol.'/'. $pair->exchangeItemTwo->symbol
                    ];
            }
        }

        return response()->json($data, 200);
    }

    public function requestWalletsCheck($user_id=0, $debug=false)
    {
        $wallet_managers = explode(",", parameter("wallet_managers"));
        $auth_user = Auth::user();
        $auth_user_id = $auth_user?$auth_user->id:0;
        $is_manager = in_array($auth_user_id, $wallet_managers);
        if (!$this->skipManagerCheck && (!$user_id || $user_id < 0 || !$is_manager)) {
            $auth_user = Auth::user();
            $user_id = $auth_user->id;
        }
        if (empty($user_id) || $user_id < 0) {
            return false;
        }
        #1. Check all active exchange items
        #2. Check users assigned addresses on every exchange item
        #3. Insert row to wallet check requests table
        $exchangeItems = (new ExchangeItem())->newQuery()
            ->active()
            ->where('type', '<>', 4)
            ->get();

        if (!$exchangeItems) {
            return false;
        }
        foreach ($exchangeItems as $exchangeItem) {
            if ($debug) {
                echo $exchangeItem->symbol."; ";
            }
            $coin = CoinFactory::create($exchangeItem->symbol);
            if (!$coin) {
                if ($debug) {
                    echo "Invalid coin.<hr/>";
                }
                continue;
            }
            $addresses_table = $exchangeItem->addresses_table;//$coin->getTable();
            $addresses_assigned_table = $exchangeItem->addresses_assigned_table;//$addresses_table."_assigned";
            if (in_array($exchangeItem->symbol, array("BNB"))) {
                if ($debug) {
                    echo "--Skip for now, no tables created yet<hr/>";
                }
                continue;
            }
            if (!Schema::hasTable($addresses_assigned_table)) {
                continue;
            }

            $assigned_addresses = DB::table($addresses_assigned_table)
                ->leftJoin($addresses_table, "$addresses_table.address_id", "=", "$addresses_assigned_table.address_id")
                ->where([["status_id","<",4],["type","=","3"],["type_id","=",$user_id]])
                ->orderBy("$addresses_assigned_table.created", "desc")
                ->get();

            if (!$assigned_addresses) {
                continue;
            }
            if ($debug) {
                echo "<br/> -- Assigned Addresses:<br/>";
            }
            foreach ($assigned_addresses as $assigned_address) {
                if ($debug) {
                    echo "----".$assigned_address->address." (".$assigned_address->address_id.")<br/>";
                }
                if ($assigned_address->type == 1) {
                    $check_type = 0;
                } //Web Wallets
                elseif ($assigned_address->type == 2) {
                    $check_type = 2;
                } //Gateway Wallets
                elseif ($assigned_address->type == 3) {
                    $check_type = 1;
                } //Exchange Wallets
                else {
                    continue;
                } //unknown wallet type or category
                $request = DB::table("wallet_addresses_check_requests")
                    ->where([
                        ["type","=",$exchangeItem->item_id],
                        ["wallet_type","=",$check_type],
                        ["user_id","=",$user_id],
                        ["address_id","=",$assigned_address->address_id],
                        ["status_id","=",0]
                    ])
                    ->first();
                if (!$request) {
                    DB::table("wallet_addresses_check_requests")->insert([
                        "type" => $exchangeItem->item_id,
                        "wallet_type" => $check_type,
                        "user_id"    => $user_id,
                        "address_id" => $assigned_address->address_id,
                        "time"    => time(),
                        "ip_address" => request()->ip()
                    ]);
                } else {
                    if ($debug) {
                        echo "--request already exists:";
                    }
                    if ($debug) {
                        print_r($request);
                    }
                }
            }
            if ($debug) {
                echo "<hr/>";
            }
        }
    }

    public function setSkipManagerCheck($bool)
    {
        $this->skipManagerCheck = ($bool === true)?true:false;
    }

    /**
     * Generates unique amount for deposit
     * @param $symbol
     * @param $amount
     * @param bool $debug
     * @return bool|double
     */
    public function generateUniqueDepositAmount($symbol, $amount, $exchange_api_id, $debug=false)
    {
        $coin = ExchangeItem::where('symbol', strtoupper($symbol))->where('deleted', 0)->first();
        abort_unless($coin, 404, __('Invalid coin ').strtoupper($symbol));
        $auth_user = Auth::user();
        $user_id = $auth_user->id;
        if (!$user_id) {
            $message = "User ID, $user_id, does not exist or invalid.";
            if ($debug) {
                echo $message;
            }
            return array("status"=>0,"message"=>$message);
        }
        $decimals = parameter("exchange.deposit_unique_amount_decimals", 6);
        $tolerance_usd_worth_max = parameter("exchange.deposit_amount_request_tolerance_max", 2);
        $tolerance_usd_worth_min = $tolerance_usd_worth_max/100;
        $deposit_amount_min_usd = parameter("exchange.deposit_amount_usd_worth_min", 5);
        global $tries;

        $item_usd_price = $coin->index_price_usd;
        $request_amount_usd_worth = $item_usd_price * $amount;
        if ($request_amount_usd_worth < $deposit_amount_min_usd) {
            $message = "Requested deposit amount is lower than the minimum deposit amount worth $deposit_amount_min_usd USD. $amount $symbol is only around ".round($request_amount_usd_worth, 8)." USD.";
            if ($debug) {
                echo $message;
            }
            return array("status"=>0,"message"=>$message);
        }
        $random_min = $tolerance_usd_worth_min * pow(10, $decimals);
        $random_max = $tolerance_usd_worth_max * pow(10, $decimals);
        $random_number = mt_rand($random_min, $random_max);
        $random_number_usd = $random_number/pow(10, $decimals);

        $random_number_item = $random_number_usd / $item_usd_price;
        $random_number_item_formatted = number_format($random_number_item, $decimals, ".", "");
        $exact_deposit_amount = $amount - $random_number_item;
        $exact_deposit_amount_formatted = number_format($exact_deposit_amount, $decimals, ".", "");
        $exact_deposit_amount_rounded = round($exact_deposit_amount, $decimals);

        if ($debug) {
            echo "<hr/>Try #$tries...<br/>
              Item ID: ".$coin->item_id."<br/>
              Amount: $amount<br/>
              Random Min: $random_min<br/>
              Random Max: $random_max<br/>
              Random Number: $random_number<br/>
              Random Number USD: $random_number_usd<br/>
              Random Number Item: $random_number_item<br/>
              Random Number Item Formatted: $random_number_item_formatted<br/>
              Exact Amount to Deposit: $exact_deposit_amount<br/>
              Exact Amount to Deposit Formatted: $exact_deposit_amount_formatted<br/>
              Exact Amount to Deposit Rounded: $exact_deposit_amount_rounded<br/>";
        }

        $amount_exists = DB::table("exchange_user_deposit_requests")->where("exchange_api_id", $exchange_api_id)->where("item_id", $coin->item_id)->where("amount", $exact_deposit_amount_rounded)->exists();
        if (!$amount_exists) {
            $request_id = DB::table("exchange_user_deposit_requests")->insertGetId([
                "user_id"   => $user_id,
                "exchange_api_id" => $exchange_api_id,
                "item_id"   => $coin->item_id,
                "amount"    => $exact_deposit_amount_rounded,
                "created"   => time(),
                "expired"   => time() + (30*86400)
            ]);

            if ($request_id > 0) {
                if ($debug) {
                    echo "Deposit request saved with request ID#".$request_id."<br/>";
                }
                return $exact_deposit_amount_rounded;
            } elseif ($debug) {
                $message = "Deposit request was NOT saved. Please try again.<br/>";
                if ($debug) {
                    echo $message;
                }
                return array("status"=>0,"message"=>$message);
            }
        } else {
            if ($debug) {
                echo "Amount, $exact_deposit_amount_rounded, already exists for item ID #".$coin->item_id." Trying again...";
            }
            $tries++;
            if ($tries > 100) {
                if ($debug) {
                    echo "---We have reached the maximum tries of 100. Exiting.";
                }
                $message = "We cannot generate an exact deposit amount for you, please try again";
                return array("status"=>0,"message"=>$message);
            } else {
                return $this->generateUniqueDepositAmount($symbol, $amount, $exchange_api_id);
            }
        }
        return array("status"=>0,"message"=>"Something went wrong. Please try again.");
    }

    /**
     * Show offline wallet links
     *
     * @return \Illuminate\Http\Response
     */
    public function offlineWallet()
    {
        return view('main.wallet.offline');
    }
}
