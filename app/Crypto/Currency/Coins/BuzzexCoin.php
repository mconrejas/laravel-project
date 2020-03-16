<?php

namespace Buzzex\Crypto\Currency\Coins;

use Buzzex\Crypto\Currency\CoinAddress;

class BuzzexCoin extends Coin
{
    /**
     * @var string
     */
    protected $shortName = 'BZX';

    /**
     * @var string
     */
    protected $name = 'BUZZEXCOIN';

    /**
     * @var integer
     */
    protected $numberOfConfirmationsRequired = 6;

    /**
     * @var string
     */
    protected $apiDomain = 'https://explorer.buzzex.io/';

    /**
     * @var string
     */
    protected $apiRawTx = 'api/getrawtransaction?txid=[[var1]]&decrypt=1';

    /**
     * @var string
     */
    protected $apiBlockHash = 'api/getblockhash?index=[[var1]]';

    /**
     * @var string
     */
    protected $apiBlockCount = 'api/getblockcount';

    /**
     * @var string
     */
    protected $apiBlock = 'api/getblock?hash=[[var1]]';

    /**
     * @var string
     */
    protected $apiAddress = 'ext/getaddress/[[var1]]';

    /**
     * @var string
     */
    protected $apiAddressExplorer = 'address/[[var1]]';

    /**
     * @var string
     */
    protected $txExplorer = 'tx/'; // full tx explorer link

    /**
     * Get transaction key
     *
     * @return string
     */
    public function getTransactionKey()
    {
        return 'last_txs';
    }
}
