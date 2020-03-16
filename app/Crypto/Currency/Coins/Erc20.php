<?php

namespace Buzzex\Crypto\Currency\Coins;

class Erc20 extends Erc20Token
{
    /**
     * @var string
     */
    protected $shortName = 'ERC20';

    /**
     * @var string
     */
    protected $name = 'ERC20TOKENS';

    /**
     * Set ACT Token Address
     * @return mixed
     */
    public function setTokenAddress($token_address)
    {
        if(!empty($token_address)) $this->tokenAddress = $token_address;
    }

    /**
     * Set Item Symbol or Shortname
     */
    public function setShortName($short_name)
    {
        if(!empty($short_name)) $this->shortName = $short_name;
    }

    /**
     * Set isErc20 property to true
     */
    public function setIsErc20()
    {
        $this->isErc20 = true;
    }


    /**
     * Override for getBlockChainTable
     * @return string
     */
    public function getBlockChainTable()
    {
        return "blockchain_transactions_erc20"; // the same blockchain table for all ACTs
    }

    /**
     * Override for getAddressFilename
     * @return string
     */
    public function getAddressFileName()
    {
        return '.sesserddhte'; // the same address for ETH
    }

    /**
     * Override for getTable
     * @return string
     */
    public function getTable()
    {
        return "eth_addresses"; // the same address for ETH
    }

}
