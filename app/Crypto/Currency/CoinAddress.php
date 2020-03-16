<?php

namespace Buzzex\Crypto\Currency;


use Buzzex\Crypto\Currency\Coins\Coin;
use Illuminate\Support\Facades\DB;

class CoinAddress
{
    /**
     * @var array
     */
    public $data = [];

    public $valid_status_ids = [1, 2, 3];

    public $debug = false;

    /**
     * @param $address
     * @param Coin $coin
     *
     * @return mixed
     * @throws Coins\Exceptions\CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function getAddress($address, Coin $coin)
    {
        $apiUrl = str_replace("[[var1]]", $address, $coin->getApiAddress());
        if($this->debug) echo "API URL: $apiUrl...";

        $data = get_from_server($apiUrl);

        return json_decode($data, true);
    }

    /**
     * @param $tx_id
     * @param Coin $coin
     *
     * @return bool|mixed
     * @throws Coins\Exceptions\CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function getRawTransactions($tx_id, Coin $coin)
    {
        if (!$coin->isValidTXID($tx_id)) {
            return false;
        }

        $apiUrl = str_replace("[[var1]]", $tx_id, $coin->getApiRawTx());

        if (false !== $result = get_from_server($apiUrl)) {
            return json_decode($result, true);
        }

        return false;
    }

    /**
     * Log debug messages
     *
     * @param  string $message
     * @param  boolean $enable
     *
     * @return void
     */
    public function log($message, $enable = true)
    {
        if (!$enable) {
            return;
        }

        echo $message;
    }

    /**
     * @param $address
     * @param Coin $coin
     *
     * @return array|bool
     */
    public function getRowbyAddress($address, Coin $coin)
    {
        $result = DB::table($coin->getTable())
            ->where('address', $address)
            ->first();

        if ($result) {
            $this->data = (array)$result;

            return $this->data;
        }

        return false;
    }

    /**
     * Get validated type
     *
     * @param  integer $type
     *
     * @return integer
     */
    protected function getValidatedType($type)
    {
        if (empty($type) || $type < 1 || $type > 3) {
            return 1;
        }

        return $type;
    }

    /**
     * @param $addressId
     * @param int $type
     * @param Coin $coin
     *
     * @return bool|mixed
     * @throws Coins\Exceptions\CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function blockChainTransactions($addressId, $type = 1, Coin $coin)
    {
        if (empty($addressId)) {
            return false;
        }

        $info = DB::table($coin->getTable())
            ->where('address_id', $addressId)
            ->first();

        if (!$info) {
            if($this->debug) echo "-- No address info for address_id: $addressId...";
            return false;
        }

        $info = (array)$info;

        $this->data = $info;

        if (!$coin->isOurs($info["address"])) {
            if($this->debug) echo "-- {$info["address"]} is not ours or invalid...";
            return false;
        }

        $results = $coin->getAddress($this, $info["address"]);

        if (!is_array($results)) {
            if($this->debug) echo "-- getAddress({$info["address"]}) is empty - $results... ";
            return false;
        }elseif($this->debug) print_r($results);

        return $coin->getTransactions($results, $this->getValidatedType($type));
    }

    /**
     * @param $addressId
     * @param bool $debug
     * @param Coin $coin
     *
     * @throws Coins\Exceptions\CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function downloadBlockChainDeposits($addressId, $debug = false, Coin $coin)
    {
        $this->debug = $debug;
        echo $debug?"COINADDRESSDEBUGTRUE":"COINADDRESSDEBUGFALSE";
        if (empty($addressId)) {
            if($debug) echo "-- Empty address ID...";
            return;
        }

        $received_txs = $this->blockChainTransactions($addressId, 2, $coin);

        $address = $this->data["address"];

        if (!$received_txs) {
            if($debug) echo "-- No transactions received for $address...";
            return;
        }

        foreach ($received_txs as $received_tx) {
            $tx_id = $coin->getTxId($received_tx);
            $transaction = isset($tx_id["transaction"]) ? $tx_id["transaction"] : [];

            if (isset($tx_id["tx_id"])) {
                $tx_id = $tx_id["tx_id"];
            }

            $transactionExists = DB::table($coin->getBlockChainTable())
                ->where('txid', $tx_id)
                ->where('address', $address)
                ->exists();

            if ($transactionExists) {
                if($debug) echo "-- TXID+Address combination, $tx_id + $address, already EXISTS in the database...";
                break;
            }elseif($debug) echo "-- TXID+Address combination, $tx_id + $address, DOES NOT exist in the database. Proceeding to this->downloadDepositsByTxID...";

            if (!empty($transaction)) {
                $tx_id = $transaction;
            }

            $this->downloadDepositsByTxID($tx_id, $debug, $coin);
        }
    }

    /**
     * @param $tx_id
     * @param bool $debug
     * @param Coin $coin
     *
     * @return bool
     * @throws Coins\Exceptions\CoinUnsetPropertyException
     * @throws \ErrorException
     */
    public function downloadDepositsByTxID($tx_id, $debug = false, Coin $coin)
    {
        if($debug) print_r($this->data);
        if (!$this->isRowDataSet()) {
            if($debug) echo "Data set is false";
            return false;
        }

        $address = $this->data["address"];
        $status_id = $this->data["status_id"];
        $address_id = $this->data["address_id"];

        $raw_tx_info = $coin->getRawTxInfo($this, $tx_id);
        $output_tx_key = $coin->getOutputTxKey();

        if($debug) print_r($raw_tx_info);
        if (!$raw_tx_info) {
            if($debug) echo "Raw TX Info is false";
            return false;
        }

        if (!array_key_exists($output_tx_key, $raw_tx_info)) {
            if($debug) echo "Output TX Key, $output_tx_key, does not exist!";
            return false;
        }

        $blockChainDataSets = $coin->buildBlockChainData($this, $raw_tx_info, $tx_id, $address, $debug);

        if($debug){
            echo "Blockchain Data Sets...";
            print_r($blockChainDataSets);
        }
        foreach ($blockChainDataSets as $blockChainData) {
            $this->blockChainInsert($blockChainData, $status_id, $address_id, $raw_tx_info["confirmations"], $debug,
                $coin);
        }

        return true;
    }

    /**
     * Check if row data is set
     *
     * @return boolean
     */
    protected function isRowDataSet()
    {
        return (is_array($this->data) || isset($this->data["address"]) || isset($this->data["status_id"]) || isset($this->data["address_id"]));
    }

    /**
     * @param $data
     * @param $status_id
     * @param $address_id
     * @param $confirmations
     * @param bool $debug
     * @param Coin $coin
     *
     * @return bool
     */
    public function blockChainInsert($data, $status_id, $address_id, $confirmations, $debug = false, Coin $coin)
    {
        if (empty($data) || !is_array($data) || !isset($data["category"]) || !isset($data["time"])
            || !isset($data["amount"]) || !isset($data["created"]) || !isset($data["address"])
            || !isset($data["txid"]) || $address_id <= 0) {
            if($debug) echo "Insufficient array data...";
            return false;
        }

        $raw_data = base64_encode(serialize($data));

        $data["raw_data"] = $raw_data;
        $data["address_id"] = $address_id;

        if (!isset($data["confirmations"])) {
            $data["confirmations"] = $confirmations;
        } else {
            $confirmations = $data["confirmations"];
        }

        $blockchainDataId = DB::table($coin->getBlockChainTable())->insertGetId($data, 'transaction_id');

        if ($blockchainDataId) {
            if (in_array($status_id, $this->valid_status_ids)) {
                $blockchain_insert_id = $blockchainDataId;

                unset($data["address_id"]);

                $web_wallet_data = $data;
                $web_wallet_data["module_id"] = $blockchain_insert_id;
                $web_wallet_data["module"] = $coin->getBlockChainTable();
                $web_wallet_data["item_id"] = $coin->getItemID();
                $web_wallet_data["created"] = strtotime($web_wallet_data["created"]);
                $web_wallet_data["released"] = ($confirmations < $coin->numberOfConfirmationsRequired())?0:$web_wallet_data["created"];
                $web_wallet_data["type"] = "deposit";

                if (false !== $assignee_id = $this->getAssigneeID($address_id, $coin)) {
                    $web_wallet_data["user_id"] = $assignee_id;
                }

                $webWalletInsertedStatus = DB::table($coin->getWalletTable())->insert($web_wallet_data);

                if ($webWalletInsertedStatus) {
                    if ($confirmations < $coin->numberOfConfirmationsRequired()) {
                    } else {
                        $coin->exchange($this, $data["amount"], $assignee_id, $debug);
                    }
                    if($this->debug) echo "Successfully inserted to wallet table, ".$coin->getWalletTable();
                    return true;
                } else {
                    if($this->debug) echo "-- wallet data NOT inserted to ".$coin->getWalletTable()." table...";
                    DB::table($coin->getBlockChainTable())
                        ->where('transaction_id', $blockchain_insert_id)
                        ->delete();
                }
            }elseif($this->debug) echo "-- invalid status ID, $status_id...";
        }elseif($this->debug) echo "-- blockchain transactions NOT inserted...";

        return false;
    }

    /**
     * @param $address_id
     * @param Coin $coin
     *
     * @return bool
     */
    public function getAssigneeID($address_id, Coin $coin)
    {
        if (empty($address_id)) {
            return false;
        }

        $query = DB::table($coin->getTable() . '_assigned')
            ->where('address_id', $address_id)
            ->orderBy('created', 'desc')
            ->first();

        if ($query) {
            $info = (array)$query;

            return $info["type_id"];
        }

        return false;
    }

    /**
     * Update blockchain confirmations
     *
     * @param  integer $transaction_id
     * @param  integer $limit
     * @param  boolean $debug
     * @return string
     */
    public function updateBlockChainConfirmations($transaction_id = 0, $limit = 100, $debug = false, Coin $coin)
    {
        $where = "confirmations < ".$coin->numberOfConfirmationsRequired();

        if (!empty($transaction_id) && $transaction_id > 0) {
            $where = "transaction_id = $transaction_id AND confirmations < ".$coin->numberOfConfirmationsRequired();
            $limit = 1;
        }

        if($debug) echo $where;

        $query = DB::table($coin->getBlockChainTable())
            ->select("transaction_id","txid","confirmations","amount","address_id","address")
            ->whereRaw($where)
            ->orderBy("created","asc")
            ->take($limit)
            ->get();

        if (!$query) {
            if($debug) echo "No more rows to update deposits confirmations";
            return false;
        }

        $log = "Total Rows: " . $query->count();

        foreach($query as $info){
            //print_r($info);
            $log .= "\n >> TXN ID: " . $info->transaction_id . "; Confirmations: " . $info->confirmations;

            $raw_tx = $coin->getRawTxInfo($this,$info->txid);
            //echo "<pre>"; // uncomment only when testing
            //print_r($raw_tx); //uncomment only when testing

            if($debug) echo "--RawTXConf: ".$raw_tx["confirmations"];

            if(!isset($raw_tx["confirmations"]) || $raw_tx["confirmations"] == "NA"){
                $raw_tx["confirmations"] = isset($raw_tx[$coin->getOutputTxKey()]["confirmations"])?$raw_tx[$coin->getOutputTxKey()]["confirmations"]:0;
                if($debug) echo "--RawTXConf is NOT set or NA";
            }else if($debug) echo "--RawTXConf is Set";


            if (!$raw_tx) {
                continue;
            }

            if ($raw_tx["confirmations"] <= $info->confirmations) {
                $log .= "Live Conf: ".$raw_tx["confirmations"];
                continue;
            }

            $success = DB::table($coin->getBlockChainTable())
                ->where("transaction_id",$info->transaction_id)
                ->update(["confirmations" => $raw_tx["confirmations"]]);

            if (!$success) {
                $log .= "; Update FAILED!";
                continue;
            }

            $log .= "; Updated";

            if($raw_tx["confirmations"] < $coin->numberOfConfirmationsRequired()) {
                continue;
            }

            $assignee_id = $this->getAssigneeID($info->address_id,$coin);
            $address_info = $this->getRowbyAddress($info->address,$coin);

            if (!$address_info) {
                //$this->releaseCache($assignee_id, $this->currency, 3);
                continue;
            }

            $update_wallet_data = array("confirmations" => $raw_tx["confirmations"]);

            $this->is_exchange = false;
            $wallet_table = $coin->getWalletTable();

            if($address_info["status_id"] == 3) {
                $this->is_exchange = true;
                $update_wallet_data["released"] = time();
            }if($address_info["status_id"] == 2){
                continue; //no need to update wallet table for gateway addresses
            }

            DB::table($wallet_table)
                ->where([
                    ["module","=",$coin->getBlockChainTable()],
                    ["module_id","=",$info->transaction_id]
                ])
                ->update($update_wallet_data);

            $coin->exchange($this, $info->amount, $assignee_id, $debug);
        }



        if($debug) echo $log;
        return true;
    }
}