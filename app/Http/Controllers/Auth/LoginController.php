<?php

namespace Buzzex\Http\Controllers\Auth;

use Buzzex\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Buzzex\Services\ZendeskService;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * App\Services\ZendeskService
     *
     * @var string
     */
    protected $service;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ZendeskService $service)
    {
        $this->middleware('guest')->except('logout');

        $this->service = $service;
    }

    /**
     * Show the application login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm(Request $request)
    {
        if ($request->has('return_to')) {
            session()->put('return_to', $request->return_to);
        }

        return view('auth.login');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        return redirect()->route('home');
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $rules = [
            $this->username() => 'required|active_account|string',
            'password' => 'required|string',
        ];
        
        if ((int) parameter('recaptcha_enable', 1) == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }
        
        $this->validate($request, $rules);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if (session()->has("return_to")) {
            $location = $this->service->buildRedirect(session()->get('return_to'));

            session()->forget('return_to');

            return redirect()->away($location);
        }
        return false;
    }
}
