<?php

namespace Buzzex\Crypto\Currency\Coins;

use Buzzex\Crypto\Currency\CoinAddress;
use Buzzex\Models\ExchangeItem;

class Erc20Token extends Coin
{
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
    protected $apiAddress = 'getAddressHistory/[[var1]]?apiKey=freekey&token=[[var2]]&type=transfer';

    /**
     * @var string
     */
    protected $apiAddressExplorer = 'https://etherscan.io/token/[[var1]]?a=';

    /**
     * @var string
     */
    protected $txExplorer = 'tx/';

    /**
     * @var string
     */
    protected $tokenAddress = '';


    public function getApiAddress()
    {
        $url = parent::getApiAddress();

        return str_replace("[[var2]]", $this->tokenAddress, $url);
    }

    /**
     * Override Address Explorer link
     *
     * @return mixed
     */
    public function getApiAddressExplorer()
    {
        return str_replace("[[var1]]", $this->tokenAddress, $this->apiAddressExplorer);
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
        return 'operations';
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

        return $results[$transactionKey];
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
        return $transaction["transactionHash"];
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
            return $data;
        }

        if ($info["success"] != 1) {
            return $data;
        }

        $confirmations = $info["confirmations"];

        if (!isset($info["operations"][0])) {
            return $data;
        }

        $info = $info["operations"][0];
        $tx_id = $info["transactionHash"];

        if (!isset($info["to"])) {
            return $data;
        }

        if (strtolower($address) != strtolower($info["to"])) {
            return $data;
        }

        if (!isset($info["tokenInfo"]["address"])) {
            return $data;
        }

        if (strtolower($info["tokenInfo"]["address"]) != strtolower($this->tokenAddress)) {
            return $data;
        }

        if (!isset($info["tokenInfo"]["decimals"])) {
            return $data;
        }

        if (false === $item_info = $this->getRowsBySymbol($this->shortName)) {
            if($debug) echo "Exchange item not found, ".$this->shortName;
            return $data;
        }

        $value = $info["value"] / pow(10, $info["tokenInfo"]["decimals"]);
        if ($value <= 0.000000005) {
            return $data;
        }

        if ($value < ($item_info["withdrawal_fee"] * 10)) {
            if($debug) echo "Value is below the withdrawal fee x 10";
            return $data;
        }

        //Ethereum's transactions only has one from and one to
        $data[] = [
            'category' => "receive",
            'time' => $info['timestamp'],
            'amount' => $value,
            'created' => date('Y-m-d H:i:s'),
            'address' => $address,
            'txid' => $tx_id,
            'confirmations' => $confirmations,
        ];

        return $data;
    }

    /**
     * Check if $tx_id string is a Valid TxID
     *
     * @param $tx_id
     *
     * @return bool
     */
    public function isValidTXID($tx_id)
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
