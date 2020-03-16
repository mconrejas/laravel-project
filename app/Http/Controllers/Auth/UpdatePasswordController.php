<?php

namespace Buzzex\Http\Controllers\Auth;

use Buzzex\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Buzzex\Models\PasswordSecurity;
use Illuminate\Support\Facades\Validator;
use Auth;

class UpdatePasswordController extends Controller
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
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showChangePasswordForm(Request $request)
    {
        return view('main.profile.update-password');
    }
    
    /**
     * Update the password for the user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function changePassword(Request $request)
    {
        $this->validator($request)->validate();
        
        Auth::logoutOtherDevices($request->new_password);

        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect()->route('login');
    }

    /**
     * Get the custom rules fo validator
     *
     * @return Validator
     */
    protected function validator($request)
    {
        return Validator::make($request->all(), [
            'current_password' => 'required|valid_current_password',
            'new_password' => 'required|string|min:6|confirmed|should_not_same_old_password',
            'email_confirmation_code' => 'required|valid_code_request'
        ]);
    }

    /**
     * Get the guard to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

}
