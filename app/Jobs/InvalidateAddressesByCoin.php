<?php

namespace Buzzex\Jobs;

use Buzzex\Crypto\Currency\CoinFactory;
use Buzzex\Models\ExchangeItem;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class InvalidateAddressesByCoin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShouldNotifyDevelopers;

    /**
     * @var ExchangeItem
     */
    private $exchangeItem;

    /**
     * InvalidateAddressesByCoin constructor.
     *
     * @param ExchangeItem $exchangeItem
     */
    public function __construct(ExchangeItem $exchangeItem)
    {
        $this->exchangeItem = $exchangeItem;
    }

    /**
     * @throws \Buzzex\Crypto\Currency\Coins\Exceptions\CoinUnsetPropertyException
     */
    public function handle()
    {
        $exchangeItem = $this->exchangeItem;

        $coin = CoinFactory::create($exchangeItem->symbol);

        $addresses = DB::table($exchangeItem->addresses_table)
            ->selectRaw('address_id, address')
            ->where('status_id', 0)
            ->limit(200)
            ->get();

        $notOursIds = [];

        foreach ($addresses as $address) {
            if (!$coin->isOurs($address->address)) {
                $notOursIds[] = $address->address_id;
            }
        }

        if (count($notOursIds) > 0) {
            DB::table($exchangeItem->addresses_table)
                ->whereIn('address_id', $notOursIds)
                ->update(['status_id' => 100]);
        }
    }
}
