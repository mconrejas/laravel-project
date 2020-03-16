<?php

namespace Buzzex\Http\Controllers\Main;

use Illuminate\Http\Request;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\User;
use Auth;

class ReferralController extends Controller
{
    /**
     * Show the application homepage for referral
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->route('register');
    }

    /**
     * Get referred users
     *
     * @return \Illuminate\Http\Response
     */
    public function getReferred(Request $request)
    {
        $affiliate_id   = $request->has('affiliate_id') ? $request->affiliate_id : Auth::user()->affiliate_id;
        $referred = User::where('referred_by', $affiliate_id);
        
        return response()->json([
            'last_page' => ceil($referred->count() / $request->size),
            'data' => $referred->take($request->size)->skip($request->size * ($request->page - 1))->get()
        ], 200);
    }

    /**
     * Redirect the old pre-launch referral URL to buzzex referral URL
     *
     * @return \Illuminate\Http\Response
     */
    public function go(Request $request)
    {
        if ($request->has('r') && !empty($request->r)) {
            $user = User::where('username', $request->r)->first();
        
            if ($user) {
                return redirect(route('referral.join', [
                    'code' => $user->affiliate_id,
                    'locale' => $request->getLocale()
                ]));
            }
        }

        return redirect('home');
    }
}
