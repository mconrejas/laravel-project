<?php

namespace Buzzex\Http\Controllers\Main;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\User;
use Buzzex\Repositories\NewsRepository;
use Buzzex\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;

class HomeController extends Controller
{
    /**
     * @var NewsRepository
     */
    protected $newsRepository;

    /**
     * @var UserRepository
     */
    private $userManager;
    
    /**
     * HomeController constructor.
     *
     * @param NewsRepository $newsRepository
     */
    public function __construct(NewsRepository $newsRepository, UserRepository $userManager)
    {
        $this->userManager = $userManager;
        $this->newsRepository = $newsRepository;
    }


    /**
     * Show the application homepage for auth
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bases = getBases();
        $news  = $this->newsRepository->getActiveNews();
        $referrer = Cookie::get('referral', null);

        if ($referrer !== null) {
            $referrer = $this->userManager->getUserByAffiliateId($referrer);
        }

        return view('main.home.home', compact('bases', 'news', 'referrer'));
    }

    /**
     * Show the application homepage for auth
     *
     * @return \Illuminate\Http\Response
     */
    public function setTheme(Request $request)
    {
        abort_unless(in_array($request->theme, config('theme.themes')), 404);

        session()->put('theme', $request->theme);

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->settings()->has('theme')) {
                $user->settings()->update('theme', $request->theme);
            } else {
                $user->settings()->set('theme', $request->theme);
            }
        }
        
        return redirect()->back();
    }
    /**
     * Show the application homepage for auth
     *
     * @return \Illuminate\Http\Response
     */
    public function setLocale(Request $request)
    {
        abort_unless(in_array($request->lang, ['en','ph']), 404);

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->settings()->has('locale')) {
                $user->settings()->update('locale', $request->lang);
            } else {
                $user->settings()->set('locale', $request->lang);
            }
        }

        URL::defaults(['locale' => $request->lang]);
        App::setLocale($request->lang);

        if ($request->has('previous')) {
            $surl = $this->replaceLocale($request->previous);

            return redirect($surl);
        }

        return redirect()->route('home');
    }
    /**
     * @param String $url
     * @return string
     */
    protected function replaceLocale($url)
    {
        $pathItems = explode('/', $url);

        if (count($pathItems) == 1) {
            return config('app.url') . '/' . App::getLocale();
        }

        unset($pathItems[0]);

        $uriWithoutLocale = implode('/', $pathItems);

        return config('app.url') . '/' . App::getLocale() . '/' . $uriWithoutLocale;
    }

    /**
     * save user's fave coin to settings column
     *
     * @return json
     */
    public function updateFavePair(Request $request)
    {
        abort_unless(Auth::check(), 403, __('Please login to continue!'));

        $pair_id = (int) $request->pairid;
        $user = User::findOrFail(Auth::user()->id);
        $settings = $user->settings;
        $fave_pairs = $user->fave_pairs;
        $continue = true;
        $response = [
            'pair_id' => $pair_id,
            'status' => 'failed'
        ];


        if (!in_array($pair_id, $fave_pairs)) {
            array_push($fave_pairs, $pair_id); // add to settings
            $settings['fave_pairs'] = $fave_pairs;
            $response['action'] = 'save';
        } elseif (($key = array_search($pair_id, $fave_pairs)) !== false) {
            unset($fave_pairs[$key]);
            $settings['fave_pairs'] = array_values($fave_pairs);
            $response['action'] = 'remove';
        } else {
            $continue = false;
            $response['status'] = 'no-changes'; // set response status
        }

        if ($continue) {
            $user->settings = $settings;
            
            if ($user->getDirty()) {
                $user->save();
                $response['status'] = 'success'; // set response status
            }
        }

        return response()->json($response, 200);
    }
}
