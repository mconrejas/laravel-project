<?php

namespace Buzzex\Crypto\Currency\Coins;

use Buzzex\Crypto\Currency\CoinAddress;
use Buzzex\Crypto\Currency\CoinValidatorFactory;
use Buzzex\Models\ExchangeItem;
use Illuminate\Support\Facades\Storage;
use Buzzex\Crypto\Currency\Coins\Exceptions\CoinUnsetPropertyException;

abstract class Coin
{
    /**
     * @var string
     */
    protected $shortName = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var integer
     */
    protected $numberOfConfirmationsRequired = 6;

    /**
     * @var string
     */
    protected $apiDomain = '';

    /**
     * @var string
     */
    protected $apiRawTx = '';

    /**
     * @var string
     */
    protected $apiBlockHash = '';

    /**
     * @var string
     */
    protected $apiBlockCount = '';

    /**
     * @var string
     */
    protected $apiBlock = '';

    /**
     * @var string
     */
    protected $apiAddress = '';

    /**
     * @var string
     */
    protected $apiAddressExplorer = '';

    /**
     * @var string
     */
    protected $txExplorer = '';

    /**
     * @var integer
     */
    protected $blockCount = 0;

    /**
     * @var int
     */
    protected $communityID = 0;

    /**
     * @var bool
     */
    protected $isErc20 = false;

    /**
     * Get coin short name
     *
     * @throws CoinUnsetPropertyException
     * @return string
     */
    public function getShortName()
    {
        if (empty($this->shortName)) {
            throw new CoinUnsetPropertyException('Short Name not set.');
        }

        return $this->shortName;
    }

    /**
     * Get coin name
     *
     * @throws CoinUnsetPropertyException
     * @return string
     */
    public function getName()
    {
        if (empty($this->name)) {
            throw new CoinUnsetPropertyException('Name not set.');
        }

        return $this->name;
    }

    /**
     * @return bool|int|string
     * @throws CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function getBlockCount()
    {
        if ($this->blockCount > 0) {
            return $this->blockCount;
        }

        $result = get_from_server($this->getApiBlockCount());

        if ($result) {
            $this->blockCount = $result;

            return $result;
        }

        return false;
    }

    /**
     * @param $blockHeight
     *
     * @return bool
     * @throws CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function getBlockHash($blockHeight)
    {
        if (empty($blockHeight)) {
            return false;
        }

        if ($blockHeight < 1) {
            return false;
        }

        if ($blockHeight > $this->getBlockCount()) {
            return false;
        }

        $apiUrl = str_replace("[[var1]]", $blockHeight, $this->getApiBlockHash());
        $result = get_from_server($apiUrl);

        return $result ?: false;
    }

    /**
     * Returns the Community ID the Coin Represents
     *
     * @return int
     */
    public function getCommunityID()
    {
        return $this->communityID;
    }

    /**
     * Should Return true if the item is an ERC20 token (and not ACT)
     *
     * @return bool
     */
    public function getIsErc20()
    {
        return $this->isErc20;
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

        preg_match('/^[0-9a-f]{64}$/i', $tx_id, $result);

        return (!empty($result) && isset($result[0]) && $result[0] == $tx_id);
    }

    /**
     * Exchange
     *
     * @param  CoinAddress $coinAddress
     * @param  float $amount
     * @param  integer $assigneeId
     * @param  boolean $debug
     *
     * @return void
     */
    public function exchange(CoinAddress $coinAddress, $amount, $assigneeId, $debug)
    {
        // do nothing
    }

    /**
     * Get coin addresses table
     *
     * @return string
     */
    public function getTable()
    {
        return strtolower($this->shortName) . '_addresses';
    }

    /**
     * Get coin item ID
     * @return bool
     */
    public function getItemID(){
        $exchangeItem = (new ExchangeItem())->newQuery()
            ->active()
            ->where('symbol', strtoupper(trim($this->shortName)))
            ->first();

        if (!$exchangeItem) {
            return false;
        }

        return $exchangeItem->item_id;
    }

    /**
     * Get coin blockchain table
     *
     * @return string
     */
    public function getBlockChainTable()
    {
        return 'blockchain_transactions_' . strtolower($this->shortName);
    }

    /**
     * Get number of confirmations required
     *
     * @return integer
     */
    public function numberOfConfirmationsRequired()
    {
        return $this->numberOfConfirmationsRequired;
    }

    /**
     * Get api domain url
     *
     * @throws CoinUnsetPropertyException
     * @return string
     */
    public function getApiDomain()
    {
        if (empty($this->apiDomain)) {
            throw new CoinUnsetPropertyException('API Domain not set.');
        }

        return $this->apiDomain;
    }

    /**
     * Get apiRawTx
     *
     * @throws CoinUnsetPropertyException
     * @return string
     */
    public function getApiRawTx()
    {
        if (empty($this->apiRawTx)) {
            throw new CoinUnsetPropertyException('apiRawTx not set.');
        }

        return $this->getApiDomain() . $this->apiRawTx;
    }

    /**
     * Get apiBlockHash
     *
     * @throws CoinUnsetPropertyException
     * @return string
     */
    public function getApiBlockHash()
    {
        if (empty($this->apiBlockHash)) {
            throw new CoinUnsetPropertyException('apiBlockHash not set.');
        }

        return $this->getApiDomain() . $this->apiBlockHash;
    }

    /**
     * Get apiBlockCount
     *
     * @throws CoinUnsetPropertyException
     * @return string
     */
    public function getApiBlockCount()
    {
        if (empty($this->apiBlockCount)) {
            throw new CoinUnsetPropertyException('apiBlockCount not set.');
        }

        return $this->getApiDomain() . $this->apiBlockCount;
    }

    /**
     * Get apiBlock
     *
     * @throws CoinUnsetPropertyException
     * @return string
     */
    public function getApiBlock()
    {
        if (empty($this->apiBlock)) {
            throw new CoinUnsetPropertyException('apiBlock not set.');
        }

        return $this->getApiDomain() . $this->apiBlock;
    }

    /**
     * Get apiAddress
     *
     * @throws CoinUnsetPropertyException
     * @return string
     */
    public function getApiAddress()
    {
        if (empty($this->apiAddress)) {
            throw new CoinUnsetPropertyException('apiAddress not set.');
        }

        return $this->getApiDomain() . $this->apiAddress;
    }

    /**
     * Get apiAddressExplorer
     *
     * @throws CoinUnsetPropertyException
     * @return string
     */
    public function getApiAddressExplorer()
    {
        if (empty($this->apiAddressExplorer)) {
            throw new CoinUnsetPropertyException('apiAddressExplorer not set.');
        }

        return $this->getApiDomain() . $this->apiAddressExplorer;
    }

    /**
     * Get txExplorer
     *
     * @throws CoinUnsetPropertyException
     * @return string
     */
    public function getTxExplorer()
    {
        if (empty($this->txExplorer)) {
            throw new CoinUnsetPropertyException('txExplorer not set.');
        }

        return $this->getApiDomain() . $this->txExplorer;
    }

    /**
     * Get address file name
     *
     * @throws CoinUnsetPropertyException
     * @return string
     */
    public function getAddressFileName()
    {
        return '.sesserdd' . strrev(strtolower($this->getShortName()));
    }

    /**
     * @param string $address
     *
     * @throws CoinUnsetPropertyException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function isOurs($address)
    {
        if (!$this->isValid($address)) {
            return false;
        }
        
        $addresses = Storage::disk('addresses')->get($this->getAddressFileName());

        return strpos($addresses, $address) !== false;
    }

    /**
     * Check if address is  valid
     *
     * @param  string $address
     *
     * @return boolean
     * @throws \Exception
     */
    public function isValid($address)
    {
        if (empty($address)) {
            return false;
        }

        $validator = CoinValidatorFactory::create($this);

        if (!$validator) {
            return false;
        }

        return $validator->isValid($address);
    }

    /**
     * @param CoinAddress $coinAddress
     * @param $address
     *
     * @return mixed
     * @throws CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function getAddress(CoinAddress $coinAddress, $address)
    {
        return $coinAddress->getAddress($address, $this);
    }

    /**
     * Get wallet table
     *
     * @return string
     */
    public function getWalletTable()
    {
        return 'exchange_transactions';
    }

    /**
     * Get transaction key
     *
     * @return string
     */
    abstract public function getTransactionKey();

    /**
     * Get transactions
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

        $last_txs = array_reverse($results[$transactionKey]);

        $transactions = [];

        foreach ($last_txs as $index => $info) {
            if ($type == 2 && $info['type'] == 'vin') {
                continue;
            }

            if ($type == 3 && $info['type'] == 'vout') {
                continue;
            }

            $transactions[] = $info;
        }

        return $transactions;
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
        return $transaction['addresses'];
    }

    /**
     * @param CoinAddress $coinAddress
     * @param $txId
     *
     * @return bool|mixed
     * @throws CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function getRawTxInfo(CoinAddress $coinAddress, $txId)
    {
        return $coinAddress->getRawTransactions($txId, $this);
    }

    /**
     * Get output tx key
     *
     * @return string
     */
    public function getOutputTxKey()
    {
        return 'vout';
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
            if (!isset($info['scriptPubKey']['addresses'])) {
                continue;
            }

            foreach ($info["scriptPubKey"]["addresses"] as $address) {
                if (!$coinAddress->getRowbyAddress($address, $this)) {
                    continue;
                }

                $category = (isset($rawTxInfo["vin"][0]["coinbase"])) ? "generate" : "receive";

                $data[] = [
                    'category' => $category,
                    'time' => isset($rawTxInfo['time']) ? $rawTxInfo['time'] : '',
                    'amount' => $info['value'],
                    'created' => date('Y-m-d H:i:s'),
                    'address' => $address,
                    'txid' => $txId,
                ];
            }
        }

        return $data;
    }

    /**
     * Add ability for methods/properties to be accessible statically
     *
     * @param  string $method
     * @param  array $arguments
     *
     * @return mixed|null
     */
    public static function __callStatic($method, $arguments)
    {
        $object = new static;

        if (method_exists($object, $method)) {
            return call_user_func_array([$object, $method], $arguments);
        }

        return null;
    }
}
