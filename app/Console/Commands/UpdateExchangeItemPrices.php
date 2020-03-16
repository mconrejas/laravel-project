<?php

namespace Buzzex\Console\Commands;

use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeItemPrice;
use Carbon\Carbon;
use Curl\Curl;
use Illuminate\Console\Command;

class UpdateExchangeItemPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange-item-prices:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update prices of all active exchange items.';

    protected $priceCurrencies = ['usd', 'btc'];

    protected $sudoPrices = [];

    protected $sources = [
        /*'CoinMarketCap' => [
            'url' => 'https://api.coinmarketcap.com/v1/ticker/?limit=0',
            'currencyPrefix' => 'price_',
            'resultsKey' => null,
            'coinNameKey' => 'name',
            'symbolKey' => 'x',
        ],*/
        'WorldCoinIndex' => [
            'url' => 'https://www.worldcoinindex.com/apiservice/json?key=Zm2rQiBzsCp5tSThZzjnZP54U',
            'currencyPrefix' => 'Price_',
            'resultsKey' => 'Markets',
            'coinNameKey' => 'Name',
            'symbolKey' => "Label",
            'altSymbols' => [
                "BSV" => "BCHSV",
                "BCH" => "BCHABC",
                "IOTA" => "IOT"
            ],
            'fixedPrices' => [ //USD & BTC ONLY
                "BZX" => [
                    "USD" => 0.13,
                    "BTC" => 0, //0 = auto
                ],
                "GX" => [
                    "USD" => 0.005,
                    "BTC" => 0, //0 = auto
                ]
            ],
        ],
    ];

    protected $btc_dollar_value = false;

    /**
     * Determines whether or not debugging is on
     * @var bool
     */
    protected $debug = true;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \ErrorException
     */
    public function handle()
    {
        $exchangeItems = (new ExchangeItem())->newQuery()
            ->active()
            ->get()
            ->mapWithKeys(function ($item) {
                return [strtolower($item->name) => $item->item_id];
            })
            ->toArray();

        if (empty($exchangeItems)) {
            return;
        }
        if($this->debug) print_r($exchangeItems);

        foreach ($this->sources as $sourceName => $sourceInfo) {
            if($this->debug) echo "Source: $sourceName...";
            $results = $this->getFromSource($sourceInfo['url']);
            $items = $exchangeItems;
            $sourceInfo['name'] = $sourceName;

            if ($sourceInfo['resultsKey'] !== null) {
                $results = $results[$sourceInfo['resultsKey']] ?: [];
            }

            if($this->debug) print_r($results);
            if($this->debug) echo "Processing Results...";

            $this->processResults($results, $items, $sourceInfo);
        }
    }

    /**
     * @param array $results
     * @param array $exchangeItems
     * @param array $source
     */
    protected function processResults(array $results, array &$exchangeItems, array $source)
    {
        if (count($results) === 0) {
            return;
        }
        $exchangeItem = null;
        $info = array();
        foreach ($results as $index => $info) {
            $coinName = strtolower($info[$source['coinNameKey']]);
            if($this->debug) echo "CoinName: $coinName...";

            if (!isset($exchangeItems[$coinName])) {
                if ($this->debug) echo " -- DOES NOT Exists!...";
                echo "Symbol: ".$info[$source['symbolKey']]."; Source: {$source["name"]}";
                if (isset($info[$source['symbolKey']])){
                    $coinSymbol = false;
                    if($source["name"] == "WorldCoinIndex"){
                        $coinSymbol = explode("/",$info[$source['symbolKey']]);
                        $coinSymbol = $coinSymbol[0];
                        $exchangeItem = (new ExchangeItem())->newQuery()
                            ->where('symbol', $coinSymbol)
                            ->first();
                        if(!$exchangeItem){
                            if(false !== $coinSymbol=array_search(strtoupper($coinSymbol),$source["altSymbols"])){
                                $exchangeItem = (new ExchangeItem())->newQuery()
                                    ->where('symbol', $coinSymbol)
                                    ->first();
                                if(!$exchangeItem){
                                    continue;
                                }
                            }else{
                                continue;
                            }
                        }
                    }
                    if(!$coinSymbol) continue;
                }else{
                    echo "Does not exist {$info[$source['symbolKey']]}";
                    continue;
                }
            }

            if($this->debug) echo "--$coinName Exists!!";

            $this->processInfo($exchangeItems, $info, $coinName, $source, $exchangeItem);
            #if($this->debug) break;
        }

        echo "\n\nProcessing Fixed Prices...\n";
        $exchangeItems = (new ExchangeItem())->newQuery()
            ->active()
            ->get()
            ->mapWithKeys(function ($item) {
                return [strtolower($item->symbol) => $item->item_id];
            })
            ->toArray();

        if (empty($exchangeItems)) {
            return;
        }

        foreach($exchangeItems as $exchangeItemSymbol => $exchangeItemID){
            if(false === array_key_exists(strtoupper($exchangeItemSymbol),$source["fixedPrices"])){
                continue;
            }
            $exchangeItem = (new ExchangeItem())->newQuery()
                ->where('symbol', $exchangeItemSymbol)
                ->first();
            if(!$exchangeItem){
                continue;
            }
            $this->processInfo($exchangeItems, $info, $exchangeItem->name, $source, $exchangeItem);
        }

    }

    /**
     * @param array $exchangeItems
     * @param array $info
     * @param $coinName
     * @param array $source
     */
    protected function processInfo(array &$exchangeItems, array $info, $coinName, array $source, ExchangeItem $exchangeItem = null)
    {
        if(!$exchangeItem){
            if($this->debug) echo "Exchange Item CoinName: ".$exchangeItems[$coinName];
            $item_id = $exchangeItems[$coinName];
        }else{
            if($this->debug) echo "Exchange Item Symbol: ".$exchangeItem->symbol;
            $coinName = $exchangeItem->name;
            $item_id = $exchangeItem->item_id;
        }

        foreach ($info as $key => $value) {
            if($this->debug) echo "$key => $value";
            if (strpos($key, $source['currencyPrefix']) === false) {
                continue;
            }

            $currency = strtoupper(str_replace($source['currencyPrefix'], '', $key));
            if($this->debug) echo "Currency: $currency, price: $value";
            if(isset($this->sudoPrices[$item_id][strtolower($currency)])){
                continue;
            }

            if(strtoupper($coinName) == "BITCOIN" && $currency == "USD" && !$this->btc_dollar_value){
                $this->btc_dollar_value = $value;
            }

            if($exchangeItem){
                if(false !== array_key_exists(strtoupper($exchangeItem->symbol),$source["fixedPrices"])){
                    $coinSymbol = $source["fixedPrices"][$exchangeItem->symbol];
                    if(isset($coinSymbol[$currency])){
                        if($coinSymbol[$currency] == 0){
                            if($currency == "USD" && isset($coinSymbol["BTC"]) && $coinSymbol["BTC"] > 0 && $this->btc_dollar_value > 0){
                                $value = $coinSymbol["BTC"] * $this->btc_dollar_value;
                            }elseif($currency == "BTC" && isset($coinSymbol["USD"]) && $coinSymbol["USD"] > 0 && $this->btc_dollar_value > 0){
                                $value = $coinSymbol["USD"] / $this->btc_dollar_value;
                            }
                        }else{
                            $value = $coinSymbol[$currency];
                        }
                    }else{
                        continue; // do not record other item prices for fixed-price items except the item-conversions specified
                    }
                }
            }

            $exchangeItemPrice = ExchangeItemPrice::create([
                'item_id' => $item_id,
                'currency' => $currency,
                'source' => $source['name'],
                'price' => $value,
                'created' => Carbon::now()->timestamp,
            ]);

            if (!$exchangeItemPrice) {
                if($this->debug) echo "xItem price Not inserted";
                continue;
            }

            if (!in_array(strtolower($currency), $this->priceCurrencies)) {
                if($this->debug) echo "price current not on list";
                continue;
            }

            if(!$exchangeItem){
                $exchangeItem = (new ExchangeItem())->newQuery()
                    ->where('item_id', $exchangeItems[$coinName])
                    ->first();
            }

            if (!$exchangeItem) {
                if($this->debug) echo "xItem not found!";
                continue;
            }

            $exchangeItemUpdateStatus = $exchangeItem->update([
                'index_price_' . strtolower($currency) => $value,
            ]);

            if (!$exchangeItemUpdateStatus) {
                if($this->debug) echo "updating sudo ".strtolower($currency)." price FAILED!";
                continue;
            }

            $this->sudoPrices[$item_id][strtolower($currency)] = 1;
        }

        if(!$exchangeItem) unset($exchangeItems[$coinName]);
    }

    /**
     * @param $url
     *
     * @return bool|mixed
     * @throws \ErrorException
     */
    protected function getFromSource($url)
    {
        $curl = new Curl();
        $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->get($url);

        if ($curl->error) {
            return false;
        }

        return json_decode($curl->response, true);
    }
}
