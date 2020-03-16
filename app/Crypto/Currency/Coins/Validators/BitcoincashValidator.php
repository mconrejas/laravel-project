<?php

namespace Buzzex\Crypto\Currency\Coins\Validators;

use Buzzex\Crypto\Currency\CashAddress;

class BitcoincashValidator extends Validator
{
    const MAINNET = 'MAINNET';
    const TESTNET = 'TESTNET';
    const MAINNET_PUBKEY = '00';
    const MAINNET_SCRIPT = '05';
    const TESTNET_PUBKEY = '6F';
    const TESTNET_SCRIPT = 'C4';

    /**
     * Checks if the address is valid
     * @param  string  $addr
     * @param  int  $version
     * @return boolean
     */
    public function isValid($addr, $version=null)
    {
        $addr = CashAddress::convertOldAndNew($addr, false);
        
        return parent::isValid($addr);
    }
}
