<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Http\Requests;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeTransaction;
use Buzzex\Models\ExternalWithdrawalHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;

class WithdrawalsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $perPage = 25;
        $status = (string) $request->status;

        $records = ExchangeTransaction::where('exchange_transactions.type', 'withdrawal-request')
                ->select("exchange_transactions.*")
                ->join('exchange_items', 'exchange_transactions.item_id', 'exchange_items.item_id')
                ->join('users', 'exchange_transactions.user_id', 'users.id');

        if ($request->coin) {
            $records = $records->where('exchange_items.symbol', $request->coin);
        }

        switch ($status) {
            case 'approved':
                $records = $records->where('exchange_transactions.approved', '>', 0)->where('exchange_transactions.released', 0);
                break;

            case 'released':
                $records = $records->where('exchange_transactions.released', '>', 0);
                break;

            case 'cancelled':
                $records = $records->where('exchange_transactions.cancelled', '>', 0);
                break;
            
            case 'pending':
                $records = $records->where('exchange_transactions.approved', 0)->where('exchange_transactions.released', 0)->where('exchange_transactions.cancelled', 0);
                break;
        }



        $records = $records->paginate($perPage);

        

        return view('admin.withdrawals.index', compact('records'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function externalWithdrawals(Request $request)
    {
        $perPage = 25;

        $records = ExternalWithdrawalHistory::join('exchange_items', 'external_withdrawal_history.asset', 'exchange_items.symbol');

        if ($request->coin) {
            $records = $records->where('exchange_items.symbol', $request->coin);
        }

        $records = $records->paginate($perPage);
        // dd($records);
        return view('admin.withdrawals.external', compact('records'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $item = ExchangeTransaction::findOrFail($id);

        $statuses = exchangeTxnStatuses();
        $status = strtolower($item->getStatus());

        $coins_config = config("coins");
        $coins_with_tag = (isset($coins_config["coins_with_tag"]))?$coins_config["coins_with_tag"]:array();

        return view('admin.withdrawals.edit', compact('item', 'statuses', 'status', 'coins_with_tag'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $item = ExchangeTransaction::findOrFail($id);
        $data = array();
        $status =  strtolower($request->status);
        $continue = true;
        //if release cant be change status
        //if approve can be back to pending but cant be cancel, can be release
        //if pending can be cancel
        //if cancelled cant be change status
        //if approve to cancel only super admin
        

        if ($item->isReleased()) {
            toast("Error Processing Request. Transaction is already on released and cant be modified further.", 'error', 'top-right');
            $continue = false;
        } elseif ($item->isCancelled()) {
            throw new Exception("Error Processing Request. Transaction is already on cancelled and cant be modified further.", 433);
        } elseif ($item->isApproved() && $status == 'cancelled') {
            toast("Error Processing Request. Cancelling approved transaction is not allowed.", 'error', 'top-right');
            $continue = false;
        } elseif ($item->isApproved() && $status == 'pending' && !auth()->user()->hasRole('super-admin')) {
            toast("Error Processing Request. For updating approved transaction to pending transaction requires super admin privilege.", 'error', 'top-right');
            $continue = false;
        } elseif ($item->isPending() && $status == 'released') {
            toast("Error Processing Request. Transaction need to be approved first.", 'error', 'top-right');
            $continue = false;
        } elseif ($item->isProcessed() && in_array($status, ['pending', 'cancelled'])) {
            toast("Error Processing Request. Transaction is currently on processed and cannot be set to $status.", 'error', 'top-right');
            $continue = false;
        } elseif (strtolower($item->getStatus()) == $status) {
            toast("Error Processing Request. Transaction is already on ".$status." status.", 'error', 'top-right');
            $continue = false;
        }

        if ($continue) {
            switch (strtolower($request->status)) {
                case 'approved':
                    $data['approved'] = $item->approved == 0 ? Carbon::now()->timestamp : $item->approved ; //dont override previous value
                    $data['cancelled'] = 0;
                    $data['released'] = 0;
                    break;

                case 'released':
                    $data['cancelled'] = 0;
                    $data['released'] = $item->released == 0 ? Carbon::now()->timestamp : $item->released; //dont override previous value
                    break;

                case 'cancelled':
                    $data['approved'] = 0;
                    $data['cancelled'] = $item->cancelled == 0 ? Carbon::now()->timestamp : $item->cancelled; //dont override previous value
                    $data['released'] = 0;
                    $data['processed'] = 0;
                    break;

                case 'processed':
                    $data['cancelled'] = 0;
                    $data['approved'] = $item->approved == 0 ? Carbon::now()->timestamp : $item->approved; //dont override previous value
                    $data['processed'] = $item->processed == 0 ? Carbon::now()->timestamp : $item->processed; //dont override previous value
                    $data['released'] = 0;
                    break;

                default: //pending
                    $data['approved'] = 0;
                    $data['cancelled'] = 0;
                    $data['released'] = 0;
                    $data['processed'] = 0;
                    break;
            }
        
            if ($request->has('notes') && !empty(trim($request->notes))) {
                $data['remarks2'] =  trim($request->notes);

                $current_logs = $item->logs;
                $current_logs[] = array(
                    'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                    'notes' => trim($request->notes),
                    'updated_by' => auth()->user()->id
                );
                $data['logs'] = $current_logs;
            }

            $item->update($data);
            $item = $item->fresh();

            toast('Status updated!', 'success', 'top-right');
        }
        if ($request->ajax()) {
            return response()->json($item->toArray(), 200);
        }

        if ($continue) {
            return redirect('admin/withdrawals');
        }
        
        return redirect()->back();
    }
}
