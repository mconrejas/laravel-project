<?php

namespace Buzzex\Crypto\Currency\Coins;

use Buzzex\Crypto\Currency\CoinAddress;

class BitCoin extends Coin
{
    /**
     * @var string
     */
    protected $shortName = 'BTC';

    /**
     * @var string
     */
    protected $name = 'BITCOIN';

    /**
     * @var integer
     */
    protected $numberOfConfirmationsRequired = 3;

    /**
     * @var string
     */
    protected $apiDomain = 'https://blockexplorer.com/';

    /**
     * @var string
     */
    protected $apiRawTx = 'api/tx/[[var1]]';

    /**
     * @var string
     */
    protected $apiBlockHash = 'api/block-index/[[var1]]';

    /**
     * @var string
     */
    protected $apiBlockCount = 'api/status?q=getBlockCount';

    /**
     * @var string
     */
    protected $apiBlock = 'api/block/[[var1]]';

    /**
     * @var string
     */
    protected $apiAddress = 'api/addr/[[var1]]';

    /**
     * @var string
     */
    protected $apiAddressExplorer = 'address/[[var1]]';

    /**
     * @var string
     */
    protected $txExplorer = 'tx/'; // full tx explorer link

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

        $result = json_decode($result, true);

        if (isset($result["blockcount"])) {
            return $result["blockcount"];
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

        if (isset($result["blockHash"])) {
            return $result["blockHash"];
        }

        return false;
    }

    /**
     * Exchange
     *
     * @param  CoinAddress $coinAddress
     * @param  float      $amount
     * @param  integer      $assigneeId
     * @param  boolean      $debug
     * @return void
     */
    public function exchange(CoinAddress $coinAddress, $amount, $assigneeId, $debug)
    {
        // do nothing
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
     * Get Tx ID
     * @param  mixed $transaction
     * @return mixed
     */
    public function getTxId($transaction)
    {
        return $transaction;
    }
}
