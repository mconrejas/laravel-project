<?php

namespace Buzzex\Crypto\Currency\Coins;

use Buzzex\Crypto\Currency\CoinAddress;
use Buzzex\Models\ExchangeItem;

class EthereumClassic extends Coin
{
    /**
     * @var string
     */
    protected $shortName = 'ETC';

    /**
     * @var string
     */
    protected $name = 'ETHEREUMCLASSIC';

    /**
     * @var integer
     */
    protected $numberOfConfirmationsRequired = 24;

    /**
     * @var string
     */
    protected $apiDomain = 'https://etcchain.com/';

    /**
     * @var string
     */
    protected $apiRawTx = 'gethProxy/eth_getTransactionByHash?txHash=[[var1]]';

    /**
     * @var string
     */
    protected $apiBlockHash = 'gethProxy/eth_blockNumber'; // to be added (dummy value only)

    /**
     * @var string
     */
    protected $apiBlockCount = 'gethProxy/eth_blockNumber';

    /**
     * @var string
     */
    protected $apiBlock = 'gethProxy/eth_getBlockByNumber?number=[[var1]]';

    /**
     * @var string
     */
    protected $apiAddress = 'api/v1/getTransactionsByAddress?address=[[var1]]&sort=desc';

    /**
     * @var string
     */
    protected $apiAddressExplorer = 'addr/[[var1]]';

    /**
     * @var string
     */
    protected $txExplorer = 'tx/';

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
        return [
            "tx_id" => $transaction["hash"],
            "transaction" => $transaction,
        ]; // just return the full transaction array as it in the case of ETC API, there's no need to requery the individual transaction API
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
            return $data;
        }

        if (!isset($info["to"])) {
            return $data;
        }

        if (strtolower($address) != strtolower($info["to"])) {
            return $data;
        }

        $value = hexdec($info["value"]) / (pow(10, 18));

        if ($value <= 0.000000005) {
            return $data;
        }

        if (false === $item_info = $this->getRowsBySymbol($this->shortName)) {
            return $data;
        }

        if ($value < ($item_info["withdrawal_fee"] * 10)) {
            return $data;
        }

        //Ethereum's transactions only has one from and one to
        $data[] = [
            'category' => "receive",
            'time' => $info['timestamp'],
            'amount' => $value,
            'created' => date('Y-m-d H:i:s'),
            'address' => $address,
            'txid' => $info["hash"],
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
     * @param $txInfo
     *
     * @return array|bool|mixed
     * @throws Exceptions\CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function getRawTxInfo(CoinAddress $coinAddress, $txInfo)
    {
        if (is_array($txInfo)) {
            return ["confirmations" => $txInfo["confirmations"], "outputs" => $txInfo];
        } // no need to requery API for individual tx info as it is already provided by the transactions api
        $result = parent::getRawTxInfo($coinAddress, $txInfo);
        if (empty($result)) {
            return false;
        }
        //<!-- get confirmations
        $block_number = hexdec($result["blockNumber"]);
        $current_block = $this->getBlockCount();
        $confirmations = $current_block - $block_number;

        //-->
        return ["confirmations" => $confirmations, "outputs" => $result];
    }


    /**
     * Get block count
     *
     * @return integer|boolean
     */
    public function getBlockCount()
    {
        $result = parent::getBlockCount();

        if (!$result) {
            return false;
        }

        return hexdec($result);
    }
}
