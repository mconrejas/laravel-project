<?php

namespace Buzzex\Http\Controllers\Api;

use Buzzex\Contracts\User\Tradable;
use Buzzex\Http\Requests\PostedWithdrawals;
use Illuminate\Http\Request;
use Buzzex\Http\Controllers\Controller;

class WithdrawalsController extends Controller
{
    /**
     * @var Tradable
     */
    protected $trading;

    /**
     * WithdrawalsController constructor.
     *
     * @param Tradable $trading
     */
    public function __construct(Tradable $trading)
    {
        $this->trading = $trading;
    }

    /**
     * @return mixed
     */
    public function getApprovedWithdrawals()
    {
        $symbol = request('symbol', null);

        return $this->trading->getApprovedWithdrawals($symbol);
    }

    /**
     * @param PostedWithdrawals $request
     */
    public function postTransactions(PostedWithdrawals $request )
    {
        $this->trading->postWithdrawals(
            json_decode($request->transactions, true)
        );
    }
}
