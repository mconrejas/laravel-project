<?php

namespace Buzzex\Crypto\Currency\Coins\Validators;

class UsdexValidator extends Validator
{
    const MAINNET = 'MAINNET';
    const TESTNET = 'TESTNET';
    const MAINNET_PUBKEY = '12'; //18 src/base58.h line 279
    const MAINNET_SCRIPT = '55'; //85 src/base58.h line 280
    const TESTNET_PUBKEY = '6F';
    const TESTNET_SCRIPT = 'C4';
}
