<?php

namespace Buzzex\Crypto\Currency\Coins\Validators;

use kornrunner\Keccak;

class EthereumValidator extends Validator
{

    /**
     * Checks if the given string is an address
     *
     * @method isValid
     * @param {String} $address the given HEX adress
     * @return {Boolean}
     */
    function isValid($address,$version=null) {
        if (!preg_match('/^(0x)?[0-9a-f]{40}$/i',$address)) {
            // check if it has the basic requirements of an address
            return false;
        } elseif (preg_match('/^(0x)?[0-9a-f]{40}$/',$address) || preg_match('/^(0x)?[0-9A-F]{40}$/',$address)) {
            // If it's all small caps or all all caps, return true
            return true;
        } else {
            // Otherwise check each case
            return $this->isChecksumAddress($address);
        }
    }

    /**
     * Checks if the given string is a checksummed address
     *
     * @method isChecksumAddress
     * @param {String} $address the given HEX adress
     * @return {Boolean}
     */
    function isChecksumAddress($address) { //based from https://github.com/ethereum/EIPs/blob/master/EIPS/eip-55.md
        //echo Sha3::hash("testing",256);
        //exit;
        // Check each case
        $address = str_replace('0x','',$address);
        $addressHash = Keccak::hash(strtolower($address),256);

        $addressArray=str_split($address);
        $addressHashArray=str_split($addressHash);

        for($i = 0; $i < 40; $i++ ) {
            // the nth letter should be uppercase if the nth digit of casemap is 1
            if ((intval($addressHashArray[$i], 16) > 7 && strtoupper($addressArray[$i]) !== $addressArray[$i]) || (intval($addressHashArray[$i], 16) <= 7 && strtolower($addressArray[$i]) !== $addressArray[$i])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Converts a non-checksum address to checksum address or return the correct checksum for an address
     * @param $address
     * @return string
     * @throws \Exception
     */
    function toCheckSumAddress($address){
        $address = strtolower($address);
        if (preg_match('/^(0x)?[0-9a-f]{40}$/',$address)) {
            $check_summed = "0x";
            $address = str_replace('0x','',$address);
            $addressHash = Keccak::hash(strtolower($address),256);

            $addressArray=str_split($address);
            $addressHashArray=str_split($addressHash);

            for($i = 0; $i < 40; $i++ ) {
                // the nth letter should be uppercase if the nth digit of casemap is 1
                if (intval($addressHashArray[$i], 16) > 7){
                    $check_summed .=  strtoupper($addressArray[$i]);
                }else $check_summed .=  $addressArray[$i];
            }

            return $check_summed;

        }
        return false;
    }
}
