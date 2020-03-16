<?php

namespace Buzzex\Http\Controllers\Main\Traits;

use Buzzex\Http\Requests\GetMarketRequest;
use Buzzex\Http\Requests\GetPairRequest;
use Buzzex\Http\Requests\GetTradeRequest;
use Buzzex\Http\Requests\LatestExecutionRequest;
use Buzzex\Repositories\ExchangeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Cookie;

trait Marketable
{
    /**
     * @param GetMarketRequest $request
     * @param ExchangeRepository $exchange
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMarket(GetMarketRequest $request, ExchangeRepository $exchange)
    {
        $data = $exchange->getMarketFor($request->get('base', 'BTC'), null);
        
        $requestedPairText = $request->get('base', 'BTC') . '_' . $request->get('target', 'ADZ');
        
        return response()->json((array) $this->mapAdditionalMarketsData($data, $requestedPairText, $request->base), 200);
    }

    /**
     * @param $data
     *
     * @return array
     */
    protected function mapAdditionalMarketsData($data, $requestedPairText, $base)
    {
        // check if user is logged in
        $fave_pairs = (Auth::check() && isset(Auth::user()->settings['fave_pairs'])) ? Auth::user()->settings['fave_pairs'] : [];

        return collect($data)->map(function ($item) use ($requestedPairText, $fave_pairs, $base) {
            if ($base == 'selected') {
                return in_array($item['pair_id'], $fave_pairs) ? array_merge($item, [
                    'active' => $this->isActive($item['pair_text'], $requestedPairText),
                    'starred' => 1,
                    'url' => route('exchange') . '?base=' . ($item['base'] == 'selected' ? 'BTC' : $item['base']) . '&target=' . $item['coin'],
                ]) : '';
            } else {
                return array_merge($item, [
                    'active' => $this->isActive($item['pair_text'], $requestedPairText),
                    'starred' => in_array($item['pair_id'], $fave_pairs) ? 1 : 0,
                    'url' => route('exchange') . '?base=' . ($item['base'] == 'selected' ? 'BTC' : $item['base']) . '&target=' . $item['coin'],
                ]);
            }
        })->toArray();
    }

    /**
     * @param $pairText
     * @param $requestedPairText
     *
     * @return bool
     */
    protected function isActive($pairText, $requestedPairText)
    {
        return $pairText === $requestedPairText;
    }

    /**
     * @param LatestExecutionRequest $request
     * @param ExchangeRepository $exchange
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getLatestExecution(LatestExecutionRequest $request, ExchangeRepository $exchange)
    {
        if (!auth()->check() && $request->target === 'self') {
            return [];
        }

        return $exchange->getLatestExecution(
            $request->pair_id,
            $request->target === 'self' ? auth()->user() : null,
            $request->limit ?? 30,
            0,
            $request->only(['side', 'page', 'size', 'from', 'to', 'fulfilled_only'])
        );
    }

    /**
     * @param GetTradeRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTradeDepth(GetTradeRequest $request)
    {
        if ($request->has('decimal')) {
            $decimal = $request->decimal;
            $cookie = Cookie::queue(Cookie::make('buzzex_decimal', $decimal, 2628000)); // assign decimal value to cookie
        } else {
            $decimal = Cookie::get('buzzex_decimal') ? Cookie::get('buzzex_decimal') : 8;
        }

        $data = $this->exchangeRepository->getOrderBook(
            $request->pair_id,
            $decimal,
            $request->limit,
            $request->type,
            $request->order
        );

        if ($request->type === 'ask') {
            $data = array_reverse($data);
        }

        return response()->json($data);
    }

    /**
     * @param GetTradeRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOldversionTradeDepth(GetTradeRequest $request)
    {
        if ($request->has('decimal')) {
            $decimal = $request->decimal;
            $cookie = Cookie::queue(Cookie::make('buzzex_decimal', $decimal, 2628000)); // assign decimal value to cookie
        } else {
            $decimal = Cookie::get('buzzex_decimal') ? Cookie::get('buzzex_decimal') : 8;
        }

        $data = $this->exchangeRepository->getOrderBook(
            $request->pair_id,
            $decimal,
            $request->limit,
            $request->type,
            $request->order
        );

        $totalAmountAggregate = 0;
        $updatedData = [];
        $counter = 1;

        foreach ($data as $item) {
            $totalAmountAggregate += (float)str_replace(',', '', $item['total_amount']);
            $item['total_amount_aggregate'] = currency($totalAmountAggregate, $decimal);
            $item['total_amount_aggregate_non_formatted'] = $totalAmountAggregate;
            $item['unique'] = $counter;
            $updatedData[] = $item;
            $counter++;
        }

        $data = $updatedData;

        $dataSecondOrderBook = $this->exchangeRepository->getOrderBook(
            $request->pair_id,
            $decimal,
            $request->limit,
            (($request->type === 'ask') ? 'bid' : 'ask'),
            (($request->type === 'ask') ? 'desc' : 'asc')
        );

        $updatedDataSecondOrderBook = [];
        $totalAmountAggregate = 0;

        foreach ($dataSecondOrderBook as $item) {
            $totalAmountAggregate += (float)str_replace(',', '', $item['total_amount']);
            $item['total_amount_aggregate'] = currency($totalAmountAggregate, $decimal);
            $item['total_amount_aggregate_non_formatted'] = $totalAmountAggregate;
            $item['unique'] = $counter;
            $updatedDataSecondOrderBook[] = $item;
            $counter++;
        }

        $combined = array_merge($data, $updatedDataSecondOrderBook);
        $data = $this->addedPercentage($combined, $data);

        if ($request->type === 'ask') {
            $data = array_reverse($data);
        }

        return response()->json($data);
    }

    /**
     * @param $orderBook
     * @param $data
     *
     * @return array
     */
    protected function addedPercentage($orderBook, $data)
    {
        $orderBook = collect($orderBook)->sortByDesc('total_amount_aggregate_non_formatted')
            ->toArray();

        $first = array_first($orderBook);

        foreach ($orderBook as $key => $bookData) {
            if ($first['unique'] === $bookData['unique']) {
                $orderBook[$key]['percent_aggregate'] = 100;
                continue;
            }

            $orderBook[$key]['percent_aggregate'] = (100 * ((float)str_replace(
                ',',
                '',
                        $bookData['total_amount_aggregate']
            ))) / (float)str_replace(
                            ',',
                            '',
                    $first['total_amount_aggregate']
                        );
        }

        $updated = [];

        foreach ($data as $item) {
            foreach ($orderBook as $bookData) {
                if ($item['unique'] === $bookData['unique']) {
                    $updated[] = $bookData;
                }
            }
        }

        return $updated;
    }

    /**
     * @param GetPairRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPairInfo(GetPairRequest $request)
    {
        $data = $this->exchangeRepository->getPairInfoByPairId($request->pair_id);
        $fave_pairs = Auth::check() ? Auth::user()->fave_pairs : [];

        $data = array_merge($data, [
            'starred' => in_array($data['pair_id'], $fave_pairs) ? 1 : 0
        ]);

        return response()->json($data, 200);
    }

    /**
     * @param Request $request
     * @param ExchangeRepository $exchangeRepository
     *
     * @return array
     */
    public function searchPair(Request $request, ExchangeRepository $exchangeRepository)
    {
        return $exchangeRepository->searchPair($request->get('term', ''));
    }

    /**
     * @param Request $request
     * @param ExchangeRepository $exchangeRepository
     *
     * @return array
     */
    public function searchCoin(Request $request, ExchangeRepository $exchangeRepository)
    {
        return $exchangeRepository->searchCoin($request->get('term', ''));
    }
}
