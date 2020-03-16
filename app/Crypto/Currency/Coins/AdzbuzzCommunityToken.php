<?php

namespace Buzzex\Crypto\Currency\Coins;

class AdzbuzzCommunityToken extends Erc20Token
{
    /**
     * @var string
     */
    protected $shortName = 'ACT';

    /**
     * @var string
     */
    protected $name = 'ADZBUZZCOMMUNITYTOKEN';

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
     * @return mixed
     */
    public function setShortName($short_name)
    {
        if(!empty($short_name)) $this->shortName = $short_name;
    }

    /**
     * Set the Community ID the ACT represents
     * @param $id
     */
    public function setCommunityID($id){
        if(!empty($id) && $id > 0) $this->communityID = $id;
    }

    /**
     * Override for getBlockChainTable
     * @return string
     */
    public function getBlockChainTable()
    {
        return "blockchain_transactions_act"; // the same blockchain table for all ACTs
    }

    /**
     * Override for getAddressFilename
     * @return string
     */
    public function getAddressFileName()
    {
        return '.sesserddtca'; // the same address filename for all ACTs
    }

    /**
     * Override for getTable
     * @return string
     */
    public function getTable()
    {
        return "act_addresses"; // the same address* tables for all ACTs
    }

}
