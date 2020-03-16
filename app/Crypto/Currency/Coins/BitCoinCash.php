<?php

namespace Buzzex\Crypto\Currency\Coins;

use Buzzex\Crypto\Currency\CashAddress;
use Buzzex\Crypto\Currency\CoinAddress;

class BitCoinCash extends Coin
{
    /**
     * @var string
     */
    protected $shortName = 'BCH';

    /**
     * @var string
     */
    protected $name = 'BITCOINCASH';

    /**
     * @var integer
     */
    protected $numberOfConfirmationsRequired = 6;

    /**
     * @var string
     */
    protected $apiDomain = 'https://blockdozer.com/';

    /**
     * @var string
     */
    protected $apiRawTx = 'insight-api/tx/[[var1]]';

    /**
     * @var string
     */
    protected $apiBlockHash = 'insight-api/block-index/[[var1]]';

    /**
     * @var string
     */
    protected $apiBlockCount = 'insight-api/blocks?limit=1';

    /**
     * @var string
     */
    protected $apiBlock = 'insight-api/block/[[var1]]';

    /**
     * @var string
     */
    protected $apiAddress = 'insight-api/addr/[[var1]]';

    /**
     * @var string
     */
    protected $apiAddressExplorer = 'insight/address/[[var1]]';

    /**
     * @var string
     */
    protected $txExplorer = 'insight/tx/';

    /**
     * @param $blockHeight
     *
     * @return bool
     * @throws Exceptions\CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function getBlockHash($blockHeight)
    {
        $result = parent::getBlockHash($blockHeight);

        if (!$result) {
            return false;
        }

        $result = json_decode($result, true);

        if (isset($result["blockHash"])) {
            return $result["blockHash"];
        }

        return false;
    }


    /**
     * @return bool|int|string
     * @throws Exceptions\CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function getBlockCount()
    {
        $result = parent::getBlockCount();

        if (!$result) {
            return false;
        }

        $result = json_decode($result, true);

        if (isset($result["blocks"][0]['height'])) {
            return $result["blocks"][0]['height'];
        }

        return false;
    }

    /**
     * Get transactions
     *
     * @override
     * @param  array $results
     * @param  integer $type
     * @return mixed
     */
    public function getTransactions(array $results, $type)
    {
        $transactionKey = $this->getTransactionKey();

        if (!array_key_exists($transactionKey, $results)) {
            return false;
        }

        return $results[$transactionKey];
    }

    /**
     * Get transaction key
     *
     * @return string
     */
    public function getTransactionKey()
    {
        return 'transactions';
    }

    /**
     * Get Tx ID
     * @param  mixed $transaction
     * @return mixed
     */
    public function getTxId($transaction)
    {
        return $transaction;
    }

    /**
     * Build block chain data
     *
     * @param  CoinAddress $coinAddress
     * @param  array      $rawTxInfo
     * @param  string      $txId
     * @param  string      $address
     * @param  boolean     $debug
     * @return array
     */
    public function buildBlockChainData(CoinAddress $coinAddress, $rawTxInfo, $txId, $address, $debug = false)
    {
        $output_tx_key = $this->getOutputTxKey();
        $data = [];
        //converts new format to legacy format if address is new format(with prefix bitcoincash:)
        $address = CashAddress::convertOldAndNew($address);

        foreach ($rawTxInfo[$output_tx_key] as $index => $info) {
            if (!isset($info['scriptPubKey']['addresses'])) {
                continue;
            }

            if (!in_array($address, $info['scriptPubKey']['addresses'])) {
                continue;
            }

            $category = (isset($rawTxInfo['vin'][0]['coinbase'])) ? 'generate' : 'receive';

            $data[] = [
                'category' => $category,
                'time' => isset($rawTxInfo['time']) ? $rawTxInfo['time'] : '',
                'amount' => $info['value'],
                'created' => date('Y-m-d H:i:s'),
                'address' => $address,
                'txid' => $txId,
            ];
        }

        return $data;
    }
}
