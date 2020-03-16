<?php

namespace Buzzex\Crypto\Currency\Coins\Validators;

class BitcoinValidator extends Validator
{
    const MAINNET = 'MAINNET';
    const TESTNET = 'TESTNET';
    const MAINNET_PUBKEY = '00';
    const MAINNET_SCRIPT = '05';
    const TESTNET_PUBKEY = '6F';
    const TESTNET_SCRIPT = 'C4';
}
