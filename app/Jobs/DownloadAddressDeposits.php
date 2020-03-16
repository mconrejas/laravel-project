<?php

namespace Buzzex\Jobs;

use Buzzex\Contracts\User\Tradable;
use Buzzex\Models\ExchangeItem;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DownloadAddressDeposits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShouldNotifyDevelopers;

    private $address;

    /**
     * @var ExchangeItem
     */
    private $exchangeItem;

    /**
     * Determines if debugging is on or off
     * @var bool
     */
    private $debug = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($address, ExchangeItem $exchangeItem)
    {
        $this->address = $address;
        $this->exchangeItem = $exchangeItem;
    }

    /**
     * @param Tradable $trading
     *
     * @throws \Exception
     */
    public function handle(Tradable $trading)
    {
        if($this->debug) echo "<hr/>going to trading->downloadDepositsByAddress({$this->address->address})...";
        $trading->downloadDepositsByAddress($this->address, $this->exchangeItem, $this->debug);
    }
}
