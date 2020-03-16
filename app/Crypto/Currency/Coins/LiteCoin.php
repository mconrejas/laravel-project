<?php

namespace Buzzex\Crypto\Currency\Coins;

use Buzzex\Crypto\Currency\CoinAddress;

class LiteCoin extends Coin
{
    /**
     * @var string
     */
    protected $shortName = 'LTC';

    /**
     * @var string
     */
    protected $name = 'LITECOIN';

    /**
     * @var integer
     */
    protected $numberOfConfirmationsRequired = 6;

    /**
     * @var string
     */
    protected $apiDomain = 'https://chain.so/';

    /**
     * @var string
     */
    protected $apiRawTx = 'api/v2/get_tx/LTC/[[var1]]';

    /**
     * @var string
     */
    protected $apiBlockHash = 'api/v2/get_blockhash/LTC/[[var1]]';

    /**
     * @var string
     */
    protected $apiBlockCount = 'api/v2/get_info/LTC';

    /**
     * @var string
     */
    protected $apiBlock = 'api/v2/get_block/LTC/[[var1]]';

    /**
     * @var string
     */
    protected $apiAddress = 'api/v2/get_tx_received/LTC/[[var1]]';

    /**
     * @var string
     */
    protected $apiAddressExplorer = 'address/LTC/[[var1]]';

    /**
     * @var string
     */
    protected $txExplorer = 'tx/LTC/'; // full tx explorer link

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

        if (isset($result['data']['blocks'])) {
            return $result['data']['blocks'];
        }

        return false;
    }

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

        if (!isset($result['data'])) {
            return false;
        }

        if (isset($result['data']['blockhash'])) {
            return $result['data']['blockhash'];
        }

        return false;
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
     * @return bool|mixed
     * @throws Exceptions\CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function getAddress(CoinAddress $coinAddress, $address)
    {
        $result = parent::getAddress($coinAddress, $address);

        if (!isset($result['data'])) {
            return false;
        }

        return $result['data'];
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
        return $transaction['txid'];
    }

    /**
     * @param CoinAddress $coinAddress
     * @param $txId
     *
     * @return bool|mixed
     * @throws Exceptions\CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function getRawTxInfo(CoinAddress $coinAddress, $txId)
    {
        $result = parent::getRawTxInfo($coinAddress, $txId);

        if (!isset($result['data'])) {
            return false;
        }

        return $result['data'];
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

        foreach ($rawTxInfo[$output_tx_key] as $index => $info) {
            if (!isset($info['address'])) {
                continue;
            }

            if ($address != $info['address']) {
                continue;
            }

            $category = ($rawTxInfo['inputs'][0]['address'] == 'coinbase') ? 'generate' : 'receive';

            $data[] = [
                'category' => $category,
                'time' => $rawTxInfo['time'],
                'amount' => $info['value'],
                'created' => date('Y-m-d H:i:s'),
                'address' => $address,
                'txid' => $txId,
            ];
        }

        return $data;
    }

    /**
     * Build block chain data v2
     *
     * @param  CoinAddress $coinAddress
     * @param  array $rawTxInfo
     * @param  string $txId
     * @param  boolean $debug
     *
     * @return array
     */
    public function buildBlockChainDataV2(CoinAddress $coinAddress, $rawTxInfo, $txId, $debug = false)
    {
        $output_tx_key = $this->getOutputTxKey();
        $data = [];

        foreach ($rawTxInfo[$output_tx_key] as $index => $info) {
            if (!isset($info['address'])) {
                continue;
            }

            $address = $info['address'];

            if (!$coinAddress->getRowbyAddress($address, $this)) {
                continue;
            }

            $category = ($rawTxInfo['inputs'][0]['address'] == 'coinbase') ? 'generate' : 'receive';

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
