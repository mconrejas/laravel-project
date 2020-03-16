<?php

namespace Buzzex\Crypto\Currency\Coins;

use Buzzex\Crypto\Currency\CoinAddress;
use Buzzex\Models\ExchangeItem;

class Ethereum extends Coin
{
    /**
     * @var string
     */
    protected $shortName = 'ETH';

    /**
     * @var string
     */
    protected $name = 'ETHEREUM';

    /**
     * @var integer
     */
    protected $numberOfConfirmationsRequired = 24;

    /**
     * @var string
     */
    protected $apiDomain = 'https://api.ethplorer.io/';

    /**
     * @var string
     */
    protected $apiRawTx = 'getTxInfo/[[var1]]?apiKey=freekey';

    /**
     * @var string
     */
    protected $apiBlockHash = 'getTop?apiKey=freekey'; // to be added (dummy value only)

    /**
     * @var string
     */
    protected $apiBlockCount = 'getTop?apiKey=freekey'; // to be added (dummy value only)

    /**
     * @var string
     */
    protected $apiBlock = 'getTop?apiKey=freekey'; // to be added (dummy value only)

    /**
     * @var string
     */
    protected $apiAddress = 'getAddressTransactions/[[var1]]?apiKey=freekey&limit=50';

    /**
     * @var string
     */
    protected $apiAddressExplorer = 'address/';

    /**
     * @var string
     */
    protected $txExplorer = 'tx/';


    /**
     * Override Address Explorer link
     *
     * @return mixed
     */
    public function getApiAddressExplorer()
    {
        return "https://etherscan.io/" . $this->apiAddressExplorer;
    }

    /**
     * Override Tx Explorer link
     *
     * @return mixed
     */
    public function getTxExplorer()
    {
        return "https://etherscan.io/" . $this->txExplorer;
    }

    /**
     * Get transaction key
     *
     * @return string
     */
    public function getTransactionKey()
    {
        return 'txs';
    }

    /**
     * @param CoinAddress $coinAddress
     * @param $address
     *
     * @return array|mixed
     * @throws Exceptions\CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function getAddress(CoinAddress $coinAddress, $address)
    {
        $result = parent::getAddress($coinAddress, $address);

        return ["txs" => $result];
    }

    /**
     * Get transactions
     *
     * @override
     *
     * @param  array $results
     * @param  integer $type
     *
     * @return mixed
     */
    public function getTransactions(array $results, $type)
    {
        $transactionKey = $this->getTransactionKey();

        if (!array_key_exists($transactionKey, $results)) {
            return false;
        }

        if (empty($results[$transactionKey])) {
            return false;
        }

        return array_reverse($results[$transactionKey]);
    }

    /**
     * Get Tx ID
     *
     * @param  mixed $transaction
     *
     * @return mixed
     */
    public function getTxId($transaction)
    {
        return $transaction["hash"];
    }

    /**
     * Get output tx key
     *
     * @return string
     */
    public function getOutputTxKey()
    {
        return 'outputs';
    }

    /**
     * Build block chain data
     *
     * @param  CoinAddress $coinAddress
     * @param  array $rawTxInfo
     * @param  string $txId
     * @param  string $address
     * @param  boolean $debug
     *
     * @return array
     */
    public function buildBlockChainData(CoinAddress $coinAddress, $rawTxInfo, $txId, $address, $debug = false)
    {
        $output_tx_key = $this->getOutputTxKey();
        $data = [];

        $info = $rawTxInfo[$output_tx_key];

        if (empty($info)) {
            if($debug) echo "Info array is empty.";
            return $data;
        }

        if ($info["success"] != 1) {
            if($debug) echo "success key is not 1";
            return $data;
        }

        if (!isset($info["to"])) {
            if($debug) echo "receiver is not set.";
            return $data;
        }

        if (strtolower($address) != strtolower($info["to"])) {
            if($debug) echo "address is not the same as receiver";
            return $data;
        }

        if ($info["value"] <= 0.000000005) {
            if($debug) echo "value is too low";
            return $data;
        }

        if (false === $item_info = $this->getRowsBySymbol($this->shortName)) {
            if($debug) echo "Exchange item not found, ".$this->shortName;
            return $data;
        }

        if($debug){
            print_r($item_info);
        }

        if ($info["value"] < ($item_info["withdrawal_fee"] * 10)) {
            if($debug) echo "Value is below the withdrawal fee x 10";
            return $data;
        }

        //Ethereum's transactions only has one from and one to
        $data[] = [
            'category' => "receive",
            'time' => $info['timestamp'],
            'amount' => $info['value'],
            'created' => date('Y-m-d H:i:s'),
            'address' => $address,
            'txid' => $info["hash"],
            'confirmations' => $info["confirmations"],
        ];

        return $data;
    }

    /**
     * @param string $symbol
     *
     * @return bool
     */
    protected function getRowsBySymbol($symbol = "ADZAC")
    {
        if (empty($symbol)) {
            return false;
        }

        $item = (new ExchangeItem())
            ->where('symbol', $symbol)
            ->first();

        return($item) ? $item->toArray() : false;
    }

    /**
     * Check if $tx_id string is a Valid TxID
     *
     * @param $tx_id
     *
     * @return bool
     */
    function isValidTXID($tx_id)
    {
        if (empty($tx_id)) {
            return false;
        }
        preg_match('/^0x([A-Fa-f0-9]{64})$/', $tx_id, $result);

        return (!empty($result) && isset($result[0]) && $result[0] == $tx_id);
    }


    /**
     * @param CoinAddress $coinAddress
     * @param $txId
     *
     * @return array|bool|mixed
     * @throws Exceptions\CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function getRawTxInfo(CoinAddress $coinAddress, $txId)
    {
        $result = parent::getRawTxInfo($coinAddress, $txId);

        return ["confirmations" => "NA", "outputs" => $result];
    }
}
