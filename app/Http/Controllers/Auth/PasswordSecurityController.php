<?php

namespace Buzzex\Http\Controllers\Auth;

use Buzzex\Contracts\Security\TwoFactorAuthenticable;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Http\Requests\VerifyAuthRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Session;

class PasswordSecurityController extends Controller
{
    /**
     * @var TwoFactorAuthenticable
     */
    private $twoFactorManager;

    /**
     * PasswordSecurityController constructor.
     *
     * @param TwoFactorAuthenticable $twoFactorManager
     */
    public function __construct(TwoFactorAuthenticable $twoFactorManager)
    {
        $this->twoFactorManager = $twoFactorManager;
    }

    /**
     * Show the application 2fa form.
     *
     * @return \Illuminate\Http\Response
     */
    public function show2faForm()
    {
        $data = [
            'user' => auth()->user(),
            'google2fa_url' => $this->twoFactorManager->getQRCodeUrl(auth()->user()),
        ];

        return view('auth.2fa')->with('data', $data);
    }

    /**
     * Generate 2FA secret.
     *
     * @return \Illuminate\Http\Response
     */
    public function generate2faSecret()
    {
        if (!$this->twoFactorManager->generateSecretKey(auth()->user())) {
            return redirect()->route('twofa.form')->with(
                'error',
                __('Oops! Something went wrong while generating secret key. Please try again.')
            );
        }

        return redirect()->route('twofa.form')->with(
            'success',
            __('Secret Key is generated, Please verify Code to Enable 2FA')
        );
    }

    /**
     * Enable 2FA
     *
     * @return \Illuminate\Http\Response
     */
    public function enable2fa(Request $request)
    {
        if (!$this->twoFactorManager->enable(auth()->user(), $request->get('verify-code'))) {
            return redirect()->route('twofa.form')->with('error', __('Invalid Verification Code, Please try again.'));
        }

        return redirect()->route('twofa.form')->with('success', __('2FA is Enabled Successfully.'));
    }

    /**
     * Enable 2FA
     *
     * @return \Illuminate\Http\Response
     */
    public function disable2fa(VerifyAuthRequest $request)
    {
        if (!$this->twoFactorManager->disable(auth()->user())) {
            return redirect()->back()->with(
                "error",
                __('Oops! Something went wrong when trying to disable 2FA. Please try again.')
            );
        }

        return redirect()->route('twofa.form')->with('success', __('2FA is now Disabled.'));
    }

    /**
     * redirect user to previous page
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectPrevious()
    {
        if (($session = Session::get('google2fa')) !== null) {
            if ($session['auth_passed']) {
                return redirect(route('home'));
            }
        }
        
        return redirect(url()->previous());
    }

    /**
     * validate code , normlly used on 2fa modal confirmation
     *
     * @param Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function verifyCode(Request $request)
    {
        $request->validate([ 'code' => 'required|valid_twofa_code' ]);

        return response()->json(['flash_message' => 'Code is valid'], 200);
    }
}
