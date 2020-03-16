<?php

namespace Buzzex\Http\Controllers\Main;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\OauthClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApiSettingsController extends Controller
{
    /**
     * ApiTokenController constructor
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Show genarate api token page
     *
     * @return View
     */
    public function index()
    {
        $existing_apis = auth()->user()->clients;

        return view('main.profile.api', compact('existing_apis'));
    }

    /**
     * get api counts from the current user
     *
     * @return View
     */
    public function getCounts(Request $request)
    {
        $counts = auth()->user()->clients()->count();

        return response()->json(['counts' => $counts], 200);
    }

    /**
     * Store newly created api pair
     *
     * @return View
     */
    public function create(Request $request)
    {
        $request->validate(['code' => 'required|valid_twofa_code']);

        $data = array(
            'client_id' => Str::uuid(),
            'client_secret' => md5(str_random(20).time()),
            'grant_types' => 'client_credentials',
            'scope'	=> 'basic',
            'redirect_uri' => '/'
        );

        $apiclient = auth()->user()->clients()->create($data);

        return response()->json([
            'message' => __('API was successfully created.'),
            'key' => $apiclient->client_id,
            'secret' => $apiclient->client_secret
        ], 200);
    }

    /**
     * Delete an api key pair
     *
     * @return View
     */
    public function delete(Request $request)
    {
        $request->validate([
            'code' => $request->via == '2fa' ? 'required|valid_twofa_code' : 'required|valid_code_request' ,
            'client_id' => 'required|string|min:10'
        ]);
        
        $apiclient = OauthClient::where('client_id', $request->client_id)->where('user_id', auth()->user()->id)->first();
        
        if (!$apiclient) {
            return response()->json(['error_message' => __('Invalid API key') ], 402);
        }

        $apiclient->delete();

        return response()->json(['message' => __('API key was successfully deleted.') ], 200);
    }
}
