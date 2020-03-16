<?php
namespace Buzzex\Services;

use Buzzex\Models\BzxBalanceSnapshot;
use Buzzex\Models\ExchangeTransaction;
use Buzzex\Models\RevdistRevenue;
use Buzzex\Models\RevdistRunSetting;
use Buzzex\Models\RevdistScore;
use Carbon\Carbon;

class RevenueService extends  BaseService{
    //<!-- date variables used to query actions qualified for earnings
    public $start_date;
    public $start_seconds;
    public $run_date;
    public $run_seconds;
    //-->
    private $db;
    private $requirements; //must be set or reset for each pool
    private $pool_amount; //must be set or reset for each pool
    private $active_pools;
    private $pool_id;
    private $reference_pool_ids;
    private $is_rerun;
    private $debug;
    private $adzbuzzer_id;
    private $managers;
    private $table;
    private $id;
    private $data;
    private $minimum_revenue;
    private $currency;
    private $pools_user_id_not_required;
    private $source; //distribution source

    private $currency_id = false;
    private $is_running = false;

    public function __construct($debug=false){
        $this->is_rerun = false;
        $this->debug = $debug;
        $this->setCurrency("BZX");
        $this->source = "regular";
        $this->table = "exchange_transactions";
    }

    public function setId($id) {
        $this->id = (int) $id;
    }

    public function setCurrency($currency){
        if($this->debug) echo "$currency";
        if(empty($currency)) $currency = "BZX";
        $exchangeItem = \Buzzex\Models\ExchangeItem::where("symbol",strtoupper($currency))->first();
        if(!$exchangeItem){
            throw new \Buzzex\Crypto\Currency\Coins\Exceptions\CoinNotFoundException(__('Invalid currency.'));
        }
        if($this->debug) echo $exchangeItem->item_id."; Fees:".$exchangeItem->getTradeFeesCollected();
        $this->currency = $exchangeItem->symbol;
        $this->currency_id = $exchangeItem->item_id;
        $this->minimum_revenue = parameter("dividends.minimum_pool_amount.".strtolower($currency),1);
    }

    public function getRows(){
        $query = \Buzzex\Models\ExchangeTransaction::where('transaction_id',$this->id)->first();
        if(!$query){
            return $this->data = $query;
        }
        return false;
    }

    function run($frequency,$source="regular"){
        echo "Currency: ".$this->currency."; Source: $source<hr/>";
        if($frequency == "daily") $start_seconds = time()-86400;
        elseif($frequency == "weekly") $start_seconds = time()-604800;
        elseif($frequency == "monthly") $start_seconds = strtotime("-1 months");
        else $start_seconds = time()-3600; //default -- hourly;
        if(empty($source)) $source = "regular";
        $this->source = $source;
        $this->run_seconds = time();
        $this->start_seconds = $start_seconds;
        $this->start_date = date("Y-m-d H:i:s",$start_seconds);
        $this->run_date = date("Y-m-d H:i:s",$this->run_seconds);
        //<!-- Save Run Settings
        #1. Get Active Pools Array
        $active_pools = array();
        $revdist_pools = \Buzzex\Models\RevdistPool::where('status_id',1)->where('currency',$this->currency)->where('source',$source)->selectRaw('pool_id,name,function,requirement_ids,reference_pool_ids,share_percentage')->oldest('pool_id')->get();
        if($revdist_pools){
            foreach($revdist_pools as $revdist_pool){
                $pool_id = $revdist_pool->pool_id;
                unset($revdist_pool->pool_id);
                $active_pools[$pool_id] = $revdist_pool->toArray();
            }
        }
        $this->active_pools = $active_pools;

        #. Save run settings
        $settings = array(
            "active_pools" => $active_pools
        );
        $settings_data = [
            "settings" => base64_encode(serialize($settings)),
            "start_time" => $start_seconds,
            "end_time" => $this->run_seconds,
            "currency" => $this->currency,
            "source" => $source
        ];
        $run_settings = RevdistRunSetting::insert($settings_data);
        if(!$run_settings) return false; //run settings was not saved
        //-->
        $this->is_rerun = false;
        $this->is_running = true;
        $this->run_go($source);
    }

    function run_go($source="regular"){
        if(empty($source)) $source = "regular";
        $sstime_start = microtime(true);
        if(!($revenue=$this->getRevenueAmount($source))){
            if($this->debug) echo "No Revenue to Distribute;";
            return false; //no revenue to distribute
        }
        if($revenue < $this->minimum_revenue){
            if($this->debug) echo "Revenue amount, $revenue, is below the ".$this->minimum_revenue." minimum required.";
            return false;
        }
        if(!is_array($this->active_pools) || empty($this->active_pools)){
            if($this->debug) echo "No Active Pools to Distribute Revenue to;";
            return false; //no active pools
        }
        foreach($this->active_pools as $pool_id=>$info){
            $sstime = microtime(true);
            if($this->debug) echo "Distribution to ".$info["name"].";";
            if(empty($info["requirement_ids"])) $this->requirements = false;
            else $this->requirements = explode(",",$info["requirement_ids"]);
            $this->pool_id = $pool_id;
            $this->reference_pool_ids = $info["reference_pool_ids"];
            $percentage = $info["share_percentage"];
            if(empty($percentage) && $percentage <= 0 || !is_numeric($percentage)){
                if($this->debug) echo "No percentage is set to this pool - $percentage;";
                continue;
            }
            $this->pool_amount = $revenue*($percentage/100);
            if(method_exists($this,$info["function"])){
                if($this->debug) echo $info["function"]." method Exists;";
                call_user_func(__CLASS__.'::'.$info["function"]);
            }elseif($this->debug) echo $info["function"]." method does NOT exist;";
            if($this->debug) echo "Pool #$pool_id execution completed: " . round(microtime(true) - $sstime, 4)." -- ";
        }
        if(!$this->is_rerun){
            $revdist_revenue = RevdistRevenue::where('currency',$this->currency)->where('source',$source)->where('distributed',null)->update(["distributed"=>$this->run_date]);
            if(!$revdist_revenue){
                if($this->debug) echo "Revenue transactions NOT marked as distributed;";
            }
        }
        if($this->debug) echo "Execution completed: " . round(microtime(true) - $sstime_start, 4);
    }

    function getRevenueAmount($source="regular",$distribution_status=0){
        if(empty($source)) $source="regular";
        if($this->is_running) $this->importRevenueAmount($source);
        $revenue = \Buzzex\Models\RevdistRevenue::where('currency',$this->currency)->where('source',$source);
        if($distribution_status == 0){
            $revenue = $revenue->where('distributed',null);
        }elseif($distribution_status > 0){
            $revenue = $revenue->where('distributed','>',0);
        }
        $revenue = $revenue->sum('amount');
        if(!$revenue || $revenue < 0) return false;
        return $revenue;
    }

    function importRevenueAmount($source="regular"){
        if(empty($source)) $source="regular";
        $fees = ExchangeTransaction::where('item_id',$this->currency_id)
            ->where('module','exchange_fulfillments')
            ->where('cancelled',0)
            ->where('fee','>',0)
            ->where('processed',0)
            ->get();
        if(!$fees){
            return false;
        }
        $total = 0;
        $time = Carbon::now()->timestamp;
        foreach($fees as $fee){
            $total += $fee->fee;
            $fee->update(['processed' => $time]);
        }
        if($total > 0){
            $revenue = RevdistRevenue::create([
                'amount' => $total * (parameter('dividends.percentage_of_fees_to_distribute',40)/100),
                'currency' => $this->currency,
                'source' => $source,
                'added_by' => 0
            ]);
        }
    }
    #4. Calculate pool's value
    #5. Check pool's members scope
    #6. Check pool member's requirements qualification
    #7. Calculate share amount for every qualified pool members
    #8. Calculate pool's total share and value per share

    function save_earnings(){
        if(empty($this->pool_id) || $this->pool_id <= 0 || !is_numeric($this->pool_id)){
            if($this->debug) echo "Pool ID is not set or invalid - ".$this->pool_id.";";
            return false;
        }
        if(empty($this->pool_amount) || $this->pool_amount <= 0 || !is_numeric($this->pool_amount)){
            if($this->debug) echo "Pool amount is empty or invalid - ".$this->pool_amount.";";
            return false;
        }

        #1. get pool's total qualified shares

        $total_shares = RevdistScore::where('type',$this->pool_id)->where('score','>',0)->where('is_qualified','>',0)->where('start_time',$this->start_seconds)->where('end_time',$this->run_seconds)->where('is_rerun',0)->sum('score');

        if(!$total_shares || $total_shares < 0){
            if($this->debug) echo "Total Shares is less than or equal to zero - $total_shares;";
            return false;
        }

        $value_per_share = $this->pool_amount/$total_shares;

        #2. get individual shares and distribute equivalent amount
        $query_ishares = RevdistScore::where('type',$this->pool_id)->where('score','>',0)->where('is_qualified','>',0)->where('start_time',$this->start_seconds)->where('end_time',$this->run_seconds)->where('is_rerun',0)->selectRaw('score_id,score,type_id,type_owner_id as user_id')->get();
        if($query_ishares){
            $earnings_holding = parameter("dividends.earnings_holding_period",-1);
            if(!$earnings_holding) $earnings_holding = 30; //default
            if($earnings_holding == -1) $earnings_holding = 0; //no holding
            $release_seconds = $this->run_seconds +($earnings_holding * 86400);

            foreach($query_ishares as $query_ishare){
                $info_ishares = $query_ishare->toArray();
                if($info_ishares["score"] <= 0) continue; //SKIP no shares
                $share_amount = $value_per_share * $info_ishares["score"];
                if(!$info_ishares["user_id"] > 0) continue;
                //<!-- insert data to earnings table
                $earnings_data = [
                    "user_id" => $info_ishares["user_id"],
                    "type" => "dividend",
                    "item_id" => $this->currency_id,
                    "module" => "revdist_scores",
                    "module_id" => $info_ishares["score_id"],
                    "amount" => $share_amount,
                    "created" => $this->run_seconds,
                    "released" => $release_seconds
                ];

                $revenue = ExchangeTransaction::insert($earnings_data);
                if(!$revenue){
                    if($this->debug) echo "Earning amount NOT distributed to ".$info_ishares["user_id"]." for P".$this->pool_id."-".$info_ishares["type_id"].";";
                }else{
                    if($this->debug) echo "Earning amount distributed to ".$info_ishares["user_id"]." for P".$this->pool_id."-".$info_ishares["type_id"].";";
                }
                //-->

            }
        }
    }


    /**
     * @param $user_id
     * @return int|string:
     * -> -x = not qualified where x is the requirement not being meet
     * -> 0 = not qualified
     * -> 1 = qualified, passed all requirements
     * -> 2 = qualified, no requirements
     * -> 3 = qualified, ADZbuzzer ID (system account)
     * -> 4 = qualified, pools exempted from qualification check
     */
    function is_qualified($user_id){
        if(empty($user_id) || $user_id <= 0){
            if(in_array($this->pool_id,$this->pools_user_id_not_required)) return 4; //true for pools that does not require user ID
            else return 0; //false;
        }
        if($user_id == $this->adzbuzzer_id) return 3; //true; //default account always qualified
        if(false !== $this->requirements){
            foreach($this->requirements as $requirement){
                if($requirement <= 0 || !is_numeric($requirement)) continue; //only check for valid IDs
                if(false !== ($query_requirements=$this->db->db_get("revdist_requirements",array("requirement_id"=>$requirement)))){
                    $info_requirements = $query_requirements->fetch_assoc();
                    if(method_exists($this,$info_requirements["function"])){
                        $params = array(
                            "user_id"=>$user_id,
                            "hours"=>$info_requirements["hours"],
                            "count"=>$info_requirements["count"],
                            "custom1"=>$info_requirements["custom1"],
                            "custom2"=>$info_requirements["custom2"],
                            "custom3"=>$info_requirements["custom3"]
                        );
                        if(false === $this->$info_requirements["function"]($params)){
                            if($this->debug) echo "User ID #$user_id NOT qualified for ".$info_requirements["function"].";";
                            return -$requirement; //false
                        }elseif($this->debug) echo "User ID #$user_id QUALIFIED for ".$info_requirements["function"].";";
                    }elseif($this->debug) echo "Function, ".$info_requirements["function"]." does NOT exist";
                }
            }
        }else return 2; //true - no requirements
        return 1; //true - passed all requirements
    }

    function insert_score($data){
        if(!is_array($data)) return false;
        $data["is_rerun"] = $this->is_rerun?1:0;
        $score = RevdistScore::insert($data);
        if($score) return true;
        else return false;
    }

    public function get_managers(){
        return $this->managers = parameter("revenue.managers",1);
    }

    public function getRevenueHistory(){
        $logged_in_user = $_SESSION["user_id"];
        if(!in_array($logged_in_user,$this->get_managers())) return false;

        global $themeData;
        $history = "";
        if(false !==($query=$this->db->db_get("revdist_revenue",array("currency"=>$this->currency)))){
            while($info = $query->fetch_assoc()){
                $themeData["txn_id"] = $info["revenue_id"];
                $themeData["date"] = $info["created"];
                $themeData["amount"] = $info["amount"];
                $themeData["revenue_source"] = strtoupper($info["source"]);
                $themeData["distributed"] = $info["distributed"];
                $themeData["added_by"] = $info["added_by"];
                $history .= \SocialKit\UI::view('earnings/list-revenue');
            }
        }
        $themeData["revenue_history"] = $history;
    }


    function getUndistributedAmount($source=""){
        $where = array("distributed"=>0,"currency"=>$this->currency);
        if(!empty($source)) $where["source"] = $source;
        $amount = 0;
        if (false !== ($query=$this->db->db_get("revdist_revenue",$where,"sum(amount) as total"))) {
            $info = $query->fetch_assoc();
            if($info["total"] !== null) $amount = $info["total"];
        }
        return $amount;
    }


    public function BZXHoldersRevShare(){
        $users = BzxBalanceSnapshot::where('time','>',$this->start_seconds)->select('user_id')->groupBy('user_id')->get();
        foreach($users as $user){
            if($this->debug) echo $user->user_id."<br/>";
            $average_balance = BzxBalanceSnapshot::where('time','>',$this->start_seconds)->where('user_id',$user->user_id)->avg('amount');
            if($this->debug) echo $average_balance."<br/>";
            $share_cost = parameter('dividends.share_cost_bzx',20000);
            $score = floor($average_balance/$share_cost);
            $minimum_share_amount = parameter('dividends.minimum_share_amount',1);
            if($minimum_share_amount < 0.0001) $minimum_share_amount = 1;
            if($average_balance > $share_cost && $minimum_share_amount < 1){
                $minimum_share_cost = $minimum_share_amount * $share_cost;
                $minimum_shares = floor($average_balance / $minimum_share_cost);
                $score = $minimum_shares / ( 1 / $minimum_share_amount);
            }
            if($score > 0){
                $insert_data = array("score" => $score, "type" => $this->pool_id, "type_id" => 0, "type_owner_id" => $user->user_id, "is_qualified" => 1, "end_time" => $this->run_seconds, "start_time" => $this->start_seconds);
                if (false === $this->insert_score($insert_data)) {
                    if ($this->debug) echo "Failed to insert earnings for PCB" . $this->pool_id . " for User ID #" . $user->user_id . ";";
                }
            }
        }
        $this->save_earnings();
    }

}