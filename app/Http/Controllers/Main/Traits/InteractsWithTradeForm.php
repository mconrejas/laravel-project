<?php

namespace Buzzex\Http\Controllers\Main\Traits;

use Buzzex\Http\Requests\BuySellRequest;
use Buzzex\Models\ExchangePair;
use Buzzex\Repositories\ExchangeRepository;
use Exception;

trait InteractsWithTradeForm
{
    /**
     * @param BuySellRequest $request
     * @param ExchangeRepository $exchanges
     *
     * @return array
     */
    public function getData(BuySellRequest $request, ExchangeRepository $exchanges)
    {
        $data = $request->all();
        $pair = ExchangePair::findOrFail($request->pair_id);

        if ($request->form_type === 'market') {
            $depth = ($request->action === 'buy')
                ? $exchanges->getOrderBook($request->pair_id, 8, 1, 'sell', 'asc')
                : $exchanges->getOrderBook($request->pair_id, 8, 1, 'buy', 'desc');

            if (!empty($depth)) {
                $data['amount'] = (strtolower($request->action == 'buy')) ?
                            $this->adjustBuyAmountForFee($pair, $request->amount, $depth['price']) :
                            $this->adjustSellAmountForFee($pair, $request->amount, $depth['price']);
                
                $data['pair_text'] = $depth['pair_text'];
                $data['price'] = $depth['price'];
            }
        }

        if ($request->form_type === 'stop-limit') {
            $data['price'] = $request->limit;
        }

        if ($request->form_type === 'limit' && !isset($data['price'])) {
            throw new Exception(__("Price cannot be empty."));
        }
        
        $data['pair'] = $pair;
        $data['module'] = isset($request->module) && !empty($request->module) ? json_decode($request->module, true) : [];
        $data['margin'] = isset($request->margin) && !empty($request->margin) ? (float) $request->margin : 0;
        
        return $data;
    }

    /**
     * Adjust the amount to allocate the fee for a given price and amount for market buy type
     * @param \Buzzex\Models\ExchangePair $pair
     * @param float $overall_total
     * @param float $price
     * @return float
     */
    protected function adjustBuyAmountForFee(ExchangePair $pair, $overall_total, $price)
    {
        $fee_percentage = $pair->fee_percentage > 0 ? $pair->fee_percentage : parameter('exchange.trade_fee', 0);
        $userPercentDiscount = auth()->user()->getPercentageFeeDiscounts();
        if ($userPercentDiscount == 100) {
            return $overall_total;
        }
        $discounted_fee = ($fee_percentage - ($fee_percentage * ($userPercentDiscount/100)));
        // $fee_amount = $overall_total / ($overall_total  * $discounted_fee);
        $total =  ($overall_total / (100 + $discounted_fee) * 100);
        return $total / $price;
    }

    /**
     * Adjust the amount to allocate the fee for a given price and amount for market sell type
     * @param \Buzzex\Models\ExchangePair $pairId
     * @param float $overall_total
     * @param float $price
     * @return float
     */
    protected function adjustSellAmountForFee(ExchangePair $pair, $overall_total, $price)
    {
        $balance = auth()->user()->getFundsByCoin($pair->exchangeItemOne->symbol);
        $fee_amount = $this->calculateFee($pair, $overall_total, $price);
        $final_total = ($fee_amount + $overall_total);
        if ($final_total >= $balance) {
            return $balance - $fee_amount;
        }
        return $overall_total;
    }

    /**
     * Adjust the amount to allocate the fee for a given price and amount for market type
     * @param \Buzzex\Models\ExchangePair $pairId
     * @param float $amountl
     * @param float $price
     * @return float
     */
    protected function calculateFee(ExchangePair $pair, $amount, $price)
    {
        $fee_percentage = $pair->fee_percentage > 0 ? $pair->fee_percentage : parameter('exchange.trade_fee', 0);
        $userPercentDiscount = auth()->user()->getPercentageFeeDiscounts();
        if ($userPercentDiscount == 100) {
            return 0;
        }
        $total = $amount * $price;
        $fee_amount = $total * ($fee_percentage / 100);

        if ($fee_amount > 0 && $userPercentDiscount > 0) {
            $fee_amount =  $fee_amount - ($fee_amount * ($userPercentDiscount / 100));
        }
        return $fee_amount;
    }
}
