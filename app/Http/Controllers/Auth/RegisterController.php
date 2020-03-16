<?php

namespace Buzzex\Http\Controllers\Auth;

use Buzzex\Events\SendUserPasswordGeneratedEvent;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\User;
use Buzzex\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * @var UserRepository
     */
    private $userManager;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userManager)
    {
        abort_unless(config('account.registration'), 403);

        $this->userManager = $userManager;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];

        if ((int) parameter('recaptcha_enable', 1) == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        return Validator::make($data, $rules);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     *
     * @return \Buzzex\Models\User
     */
    protected function create(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $data['referred_by'] = Cookie::get('referral') ?: null;

        return $this->userManager->create($data);
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        $referrer = Cookie::get('referral', null);

        if ($referrer !== null) {
            $referrer = $this->userManager->getUserByAffiliateId($referrer);
        }

        return view('auth.register', compact('referrer'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showRegistrationSucceess()
    {
        return view('auth.register-success');
    }

    /**
     * @return string
     */
    public function redirectTo()
    {
        return route('register.success');
    }

    /**
     * Register a user via email address
     * @return Response|json
     */
    public function registerViaEmailOnly(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email'
        ]);

        $generatedPassword = Str::random(20).time();

        $data = array(
            'first_name' => $request->email,
            'last_name'  => "",
            'email' => $request->email,
            'password' => bcrypt($generatedPassword)
         );

        $user = User::create($data);

        if ($user) {
            $user->assignRole('user');
            
            //send the generated password to user email
            event(new SendUserPasswordGeneratedEvent($user, $generatedPassword));

            return response()->json(['message' => 'Successfully signed up. Please check your email for login details.'], 200);
        }

        return response()->json(['message' => 'Something went wrong. Please try again later.'], 200);
    }
}
