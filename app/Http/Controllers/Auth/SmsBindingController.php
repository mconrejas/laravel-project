<?php

namespace Buzzex\Http\Controllers\Auth;

use Aloha\Twilio\Twilio;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SmsBindingController extends Controller
{
    protected $twilio;

    protected $user;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

        $this->twilio =  new Twilio(
                config('services.twilio.account_sid'),
                config('services.twilio.auth_token'),
                config('services.twilio.sender_number')
            );

        $this->user = Auth::user();
    }

    /**
     * Display the sms binding form
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showSmsForm(Request $request)
    {
        abort_unless((parameter('mobile_binding_on', 0) == 1), 404);

        return view('main.profile.sms-binding');
    }

    /**
    * Request for code
    *
    * @return
    */
    protected function requestOTP(Request $request)
    {
        $data = array('message' => "Invalid number");

        $type = 'twilio_verify';

        $this->validator($request)->validate();

        $this->deleteOldRequest($type);

        $userRequest  = Auth::user()->userRequest()->create([ 'type' => $type ]);

        if ($userRequest) {
            $response = $this->twilio->message(
                $request->countryCode.$request->number,
                __('Your OTP is : ').$userRequest->code
            );

            $data = array(
                'message' => __('Check your phone for OTP.')
            );

            return response()->json($data, 200);
        }

        return response()->json($data, 402);
    }

    /**
     * Bind the number to user if pass validation.
     *
     * @return json
     */
    public function bindNumber(Request $request)
    {
        $this->validator($request, true)->validate();
        
        $user =  User::find(Auth::user()->id);
        $user->mobile_number = $request->countryCode.$request->number;
        $user->save();

        Auth::setUser($user);
        return redirect()->route('my.security');
    }

    /**
     * Bind the number to user if pass validation.
     *
     * @return json
     */
    public function unBindNumber(Request $request)
    {
        $request->validate([
                'password' => 'required|valid_current_password'
            ]);

        $user =  User::find(Auth::user()->id);
        $user->mobile_number = null;
        $user->save();

        Auth::setUser($user);
        
        return response()->json(['flash_message' => __('Successfully unbinded!')], 200);
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

    /**
     * Get the custom rules fo validator
     *
     * @return Validator
     */
    protected function validator($request, $withOtp = false)
    {
        if (!$withOtp) {
            return Validator::make($request->all(), [
                'countryCode' => 'required|string|min:2',
                'number' => 'required|string|min:6',
            ]);
        }

        return Validator::make($request->all(), [
                'countryCode' => 'required|string|min:2',
                'number' => 'required|string|min:6',
                'otp'    => 'required|string|valid_code_request'
            ]);
    }
}
