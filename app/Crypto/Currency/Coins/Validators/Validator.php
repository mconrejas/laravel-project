<?php

namespace Buzzex\Crypto\Currency\Coins\Validators;

abstract class Validator
{
    CONST MAINNET = "MAINNET";
    CONST TESTNET = "TESTNET";
    CONST MAINNET_PUBKEY = "14";
    CONST MAINNET_SCRIPT = "05";
    CONST TESTNET_PUBKEY = "6F";
    CONST TESTNET_SCRIPT = "C4";

    /**
     * Check if address is valid
     *
     * @param  string  $addr
     * @param  mixed  $version
     * @return boolean
     */
    public function isValid($addr, $version = null)
    {
        $type = static::typeOf($addr);

        if ($type === false) {
            return false;
        }

        if (is_null($version)) {
            $version = static::MAINNET;
        }

        switch ($version) {
            case static::MAINNET:
                $valids = [static::MAINNET_PUBKEY, static::MAINNET_SCRIPT];
                break;
            case static::TESTNET:
                $valids = [static::TESTNET_PUBKEY, static::TESTNET_SCRIPT];
                break;
            case static::MAINNET_PUBKEY:
            case static::MAINNET_SCRIPT:
            case static::TESTNET_PUBKEY:
            case static::TESTNET_SCRIPT:
                $valids = [$version];
                break;
            default:
                throw new \Exception('Unknown version constant');
        }

        return in_array($type, $valids);
    }

    /**
     * @param  string $addr
     * @return bool
     */
    public static function typeOf($addr)
    {
        if (preg_match('/[^1-9A-HJ-NP-Za-km-z]/', $addr)) {
            return false;
        }

        $decoded = static::decodeAddress($addr);

        if (strlen($decoded) != 50) {
            return false;
        }

        $version = substr($decoded, 0, 2);
        $check = substr($decoded, 0, strlen($decoded) - 8);
        $check = pack('H*', $check);
        $check = hash('sha256', $check, true);
        $check = hash('sha256', $check);
        $check = strtoupper($check);
        $check = substr($check, 0, 8);
        $isValid = ($check == substr($decoded, strlen($decoded) - 8));

        return ($isValid ? $version : false);
    }

    /**
     * Decode address
     *
     * @param  string $data
     * @return mixed
     */
    protected static function decodeAddress($data)
    {
        $charsetHex = '0123456789ABCDEF';
        $charsetB58 = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $raw = '0';

        for ($i = 0; $i < strlen($data); $i++) {
            $current = (string) strpos($charsetB58, $data[$i]);
            $raw = (string) bcmul($raw, '58', 0);
            $raw = (string) bcadd($raw, $current, 0);
        }

        $hex = '';

        while (bccomp($raw, 0) == 1) {
            $dv = (string) bcdiv($raw, '16', 0);
            $rem = (integer) bcmod($raw, '16');
            $raw = $dv;
            $hex = $hex . $charsetHex[$rem];
        }

        $withPadding = strrev($hex);

        for ($i = 0; $i < strlen($data) && $data[$i] == '1'; $i++) {
            $withPadding = '00' . $withPadding;
        }

        if (strlen($withPadding) % 2 != 0) {
            $withPadding = '0' . $withPadding;
        }

        return $withPadding;
    }
}
