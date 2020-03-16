<?php

namespace Buzzex\Http\Controllers\Main;

use Buzzex\Events\ConfirmationCodeEvent;
use Buzzex\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class EmailCodeController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Request for email code
     *
     * @return
     */
    protected function requestEmailCode(Request $request)
    {
        $data = array('status' => 402);
        $type = $request->type?:'password_update';
        $user = Auth::user();

        $this->deleteOldRequest($type);

        $userRequest  = $user->userRequest()->create([ 'type' => $type ]);

        if ($userRequest) {
            Event::fire(new ConfirmationCodeEvent($user, $userRequest));

            $data = array(
                'status' => 200 ,
                'message' => __('Check your email for verification code.')
            );
        }
        return response()->json($data, 200);
    }

    /**
     * Soft delete old request for certain type
     *
     * @return void
     */
    public function deleteOldRequest($type)
    {
        $userRequests = Auth::user()->userRequest();
        $userRequests->where('type', '=', $type)->delete();
    }
}
