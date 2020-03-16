<?php

namespace Buzzex\Models\Traits;

use Buzzex\Crypto\Currency\CoinFactory;
use Buzzex\Events\DevelopersEvent;
use Buzzex\Models\ExchangeItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait HasCoinAddress
{
    /**
     * @param ExchangeItem $coin
     *
     * @return bool|mixed
     */
    public function getDepositAddress(ExchangeItem $coin)
    {
        $addressTable = $coin->addresses_table;
        $addressesAssignedTable = $coin->addresses_assigned_table;

        if (!Schema::hasTable($addressTable)) {
            return false;
        }

        $query = DB::table($addressesAssignedTable)
            ->select('address',$addressTable.'.address_id')
            ->join($addressTable, $addressTable . '.address_id', '=', $addressesAssignedTable . '.address_id')
            ->where('type', 3)
            ->where('type_id', $this->id)
            ->where(function ($query) {
                $query->where('archived', '=', '0000-00-00 00:00:00')
                    ->orWhere('archived', '=', null);
            })
            ->orderBy($addressesAssignedTable . '.created', 'desc')
            ->first();

        if (!$query /*&& !Cache::has('get-new-address-trigger-' . $this->id)*/) {
            return $this->getNewDepositAddress($coin);
        }

        //<!-- check if current address is ours
        $coinObj = CoinFactory::create($coin->symbol);
        if(!$coinObj->isOurs($query->address)){
            DB::table($addressesAssignedTable)
                ->where("address_id",$query->address_id)
                ->update(["type" => 200]);
            DB::table($addressTable)
                ->where("address_id",$query->address_id)
                ->update(["status_id" => 200]);
            //<!-- send email for malicious addresses
            $date = date("Y-m-d H:i:s");
            $to = "darwin.tbl@gmail.com";
            $subject = $coin->symbol." Address - GetCurrent:Fishy";
            $sessions = isset($_SESSION)?$_SESSION:"";
            $requests = isset($_REQUEST)?$_REQUEST:"";
            $message = "Started on $date. <hr><pre>Addresses: <br>".$query->address."<hr>Sessions: ".print_r($sessions,true)."<hr>Requests: ".print_r($requests,true);
            event(new DevelopersEvent(['target'=>$to,'message'=>$message,'subject'=>$subject]));
            //-->
            return $this->getNewDepositAddress($coin);
        }
        //-->

        return $query ? $query->address : false;
    }

    /**
     * @param string|ExchangeItem $exchangeItem
     *
     * @return bool|mixed
     */
    public function getNewDepositAddress($exchangeItem)
    {
        if (!($exchangeItem instanceof ExchangeItem)) {
            $exchangeItem = (new ExchangeItem())->newQuery()
                ->where('symbol', strtoupper($exchangeItem))
                ->first();
        }

        $coin = CoinFactory::create($exchangeItem->symbol);

        $addressTable = $exchangeItem->addresses_table;

        if (!Schema::hasTable($addressTable)) {
            return false;
        }

        $query = DB::table($addressTable)
            ->selectRaw('address_id, address')
            ->where('status_id', 0)
            ->orderBy('address_id', 'asc')
            ->take(20)
            ->get();

        /*if (!Cache::has('get-new-address-trigger-' . $this->id)) {
            Cache::forever('get-new-address-trigger-' . $this->id, $exchangeItem);
        }*/

        if (!$query) {
            return false;
        }

        $not_ours = array();
        $date = date("Y-m-d H:i:s");

        foreach($query as $address){
            if(!$coin->isOurs($address->address)){
                DB::table($addressTable)
                    ->where("address_id",$address->address_id)
                    ->update(["status_id" => 100]);
                $not_ours[$address->address_id] = $address->address;
            }
            else{
                if(!empty($not_ours)){
                    //<!-- send email for malicious addresses
                    $to = "darwin.tbl@gmail.com";
                    $subject = $exchangeItem->symbol." Address - GetNew:Fishy";
                    $sessions = isset($_SESSION)?$_SESSION:"";
                    $requests = isset($_REQUEST)?$_REQUEST:"";
                    $message = "Started on $date. <hr><pre>Addresses: <br>".print_r($not_ours,true)."<hr>Sessions: ".print_r($sessions,true)."<hr>Requests: ".print_r($requests,true);
                    event(new DevelopersEvent(['target'=>$to,'message'=>$message,'subject'=>$subject]));
                    //-->
                }
                DB::table($exchangeItem->addresses_assigned_table)->insert([
                    "address_id" => $address->address_id,
                    "type"       => 3, // type=3=exchange address
                    "type_id"    => $this->id,
                    "created"    => Carbon::now()->format('Y-m-d H:i:s'),
                ]);

                return $this->getDepositAddress($exchangeItem);
            }
        }

        if(!empty($not_ours)){
            //<!-- send email for malicious addresses
            $to = "darwin.tbl@gmail.com";
            $subject = $exchangeItem->symbol." Address - GetNew:Fishy";
            $sessions = isset($_SESSION)?$_SESSION:"";
            $requests = isset($_REQUEST)?$_REQUEST:"";
            $message = "Started on $date. <hr><pre>Addresses: <br>".print_r($not_ours,true)."<hr>Sessions: ".print_r($sessions,true)."<hr>Requests: ".print_r($requests,true);
            event(new DevelopersEvent(['target'=>$to,'message'=>$message,'subject'=>$subject]));
            //-->
        }

        return false;

    }

    /**
     * @param string $coinName
     *
     * @return string
     */
    public function getHistoryDepositAddress($coinName)
    {
        $coinName = strtolower(trim($coinName));

        $history_table = $coinName . '_addresses_assigned_history';
        $addressTable = $coinName . '_addresses';

        if (!Schema::hasTable($history_table)) {
            return false;
        }

        return DB::table($history_table)
            ->where($history_table . '.type_id', $this->id)
            ->join($addressTable, $addressTable . '.address_id', '=', $history_table . '.address_id')
            ->orderBy($history_table . '.address_id', 'asc')
            ->get();
    }
}
