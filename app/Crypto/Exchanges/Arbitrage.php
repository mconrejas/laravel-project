<?php

namespace Buzzex\Crypto\Exchanges;


class Arbitrage
{
    /**
     * @var array
     */
    protected $orderBook;

    /**
     * Arbitrage constructor.
     *
     * @param array $orderBook
     */
    public function __construct(array ...$orderBooks)
    {
        $data = [];

        foreach($orderBooks as $orderBook) {
            $data = array_merge($data, $orderBook);
        }

        $this->orderBook = collect($data);
    }

    /**
     * @return array
     */
    public function getOrderBook()
    {
        return $this->orderBook->toArray();
    }

    /**
     * @param $price
     * @param $type
     *
     * @return mixed
     */
    public function getOrder($price, $type)
    {

        $query = $this->orderBook->where('price', $price)
            ->where('depth_type', $type);

        if (strtoupper($type) === 'SELL') {
            return $query->sortByDesc('price')
                ->first();
        }

        return $query->sortBy('price')
            ->first();
    }
}