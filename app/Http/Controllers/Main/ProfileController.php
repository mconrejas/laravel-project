<?php

namespace Buzzex\Http\Controllers\Main;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\KycMedia;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangeTransaction;
use Buzzex\Models\CoinCompetition;
use Buzzex\Models\CoinCompetitionRecord;
use Buzzex\Models\User;
use Carbon\Carbon;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Image;

class ProfileController extends Controller
{
    /**
     * Show the application homepage for auth
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $last_log = $user->userSignin()->latest()->first();

        $data = array(
            'user' => $user,
            'last_ip' => $last_log->ip,
            'last_signin' => $last_log->created_at,
            'personal_verification' => $user->getPersonalVerification()->count() ? $user->getPersonalVerification : 0,
            'profile_picture' => $user->getProfilePicture()
        );
        return view('main.profile.accounts', $data);
    }

    /**
     * Show the application homepage for auth
     *
     * @return \Illuminate\Http\Response
     */
    public function security()
    {
        $revision = auth()->user()->revisionHistory()->where('key', 'password')->latest()->first();

        $data = array(
            'mobile_binding_on' => parameter('mobile_binding_on', 0),
            'security_star_count' => 5,
            'user_security_status'=> $this->evaluateSecurity($revision),
            'lastpasswordupdated' => $revision ? Carbon::createFromTimeStamp(strtotime($revision->created_at))->diffForHumans() : null
        );
        return view('main.profile.security', $data);
    }

    /**
     * Show the application homepage for auth
     *
     * @return \Illuminate\Http\Response
     */
    public function notification()
    {
        return view('main.profile.notification');
    }

    /**
     * Show the application homepage for auth
     *
     * @return \Illuminate\Http\Response
     */
    public function referral()
    {
        $reffered_count = User::where('referred_by', Auth::user()->affiliate_id)->count();

        $data = array(
            'referred_friend_count' => $reffered_count,
            'referral_reward_total' => "Coming soon",
            'referral_ratio' => 0,
        );
        return view('main.profile.referral', $data);
    }

    /**
     * Show the application homepage for auth
     *
     * @return \Illuminate\Http\Response
     */
    public function loginRecord()
    {
        $data = array(
            'logs' => Auth::user()->userSignin()->last30Days()
        );
        return view('main.profile.login-record', $data);
    }


    /**
     * Show user the selection of verification methods
     *
     * @return \Illuminate\Http\Response
     */
    public function selectVerificationMethod()
    {
        $user = Auth::user();

        $data = array(
            'personal_verification' => $user->getPersonalVerification()->count() ? $user->getPersonalVerification : 0
        );
        return view('main.profile.select-verification-method', $data);
    }

    /**
     * allow user to input and upload their details
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyPersonal()
    {
        // Set storage
        $disk = Storage::disk('local');

        //** check if user has already submitted
        if (Auth::user()->isKycSubmitted()) {
            return redirect()->route('my.selectMethod');
        }

        $countryOptions = getCountryOptions();
        $kyc = Auth::user()->kycMedia;
        $photos = null;

        if ($kyc) {
            $photos = $kyc->images;

            if (!empty($photos)) {
                $front = array_key_exists('front', $photos) ? $photos['front'] : '';
                $back = array_key_exists('back', $photos) ? $photos['back'] : '';
                $selfie = array_key_exists('selfie', $photos) ? $photos['selfie'] : '';

                // Convert image from storage to thumbnail
                $photos->front = makeImage(@$disk->get("$front"), false);
                if ($kyc->id_type == 'driving-license') {
                    $photos->back = makeImage(@$disk->get("$back"), false);
                }
                $photos->selfie = makeImage(@$disk->get("$selfie"), false);
            }
        }
        return view('main.profile.verify-personal', compact('countryOptions', 'kyc', 'photos'));
    }

    /**
     * save user verification details
     *
     * @return \Illuminate\Http\Response
     */
    public function savePersonalVerification(Request $request)
    {
        $public = 'uploads/kyc/medias';
        $verification_type = "personal";
        $media_files = [];

        $request->merge(['date_of_birth' => $request->year.'-'.$request->month.'-'.$request->day]);

        $this->validate($request, [
            'front_id_path' => ['required','string',function ($attribute, $value, $fail) {
                if (!Storage::disk('local')->exists($value)) {
                    $fail('Front ID image does not exist.');
                }
            }],
            'selfie_id_path' => ['required','string',function ($attribute, $value, $fail) {
                if (!Storage::disk('local')->exists($value)) {
                    $fail('Selfie ID image does not exist.');
                }
            }],
            'street_address' => 'required_with:city,state,postal_code,street_address2|string',
            'street_address2' => 'string',
            'city' => 'required_with:address|string',
            'state' =>  'required_with:address|string',
            'postal_code' => 'required_with:address|string',
            'country' => 'required_with:address|string',
            'contact_number' =>  'required|string',
            'day' => 'required|string',
            'month' => 'required|string',
            'year' => 'required|string'
            
        ]);

        $data = [
            "nationality" => $request->nationality,
            "first_name" => $request->first_name,
            "last_name" => $request->last_name,
            "date_of_birth" => $request->date_of_birth,
            "street_address" => $request->street_address,
            "street_address2" => $request->street_address2,
            "city" => $request->city,
            "state" => $request->state,
            "postal_code" => $request->postal_code,
            "country" => $request->country,
            "contact_number" => $request->contact_number,
            "id_type" => $request->id_type,
            "id_number" => $request->id_number,
            "user_id" => Auth::user()->id,
            'type' => $verification_type,
            'approved' => false,
        ];
    
        //** file storing -- file stored in storage/public
        $front_filename = $request->front_id_path;
        $selfie_filename = $request->selfie_id_path;

        //** define data
        $media_files['front'] = $front_filename;
        $media_files['selfie'] = $selfie_filename;

        // only save back id if id_type = driving-license
        if ($request->id_type == 'driving-license'
            && isset($request->back_id_path)
            && Storage::disk('local')->exists($request->back_id_path)) {
            $back_filename = $request->back_id_path;
            $media_files['back'] = $back_filename;
        }

        $data['images'] = $media_files;
 
        //** using update or create for id to be used only once.
        $flag = KycMedia::updateOrCreate([
            'user_id' => Auth::user()->id,
            'type' => $verification_type,
        ], $data);

        return redirect()->route('my.profile');
    }
    
    /**
     * Upload ids
     * @param  Request $request
     * @return return
     */
    public function verifyUpload(Request $request)
    {
        $public = 'uploads'.'/'.'kyc'.'/'.'medias';

        $this->validate($request, [
            'file' => 'required|image|mimes:png,jpg,jpeg,gif|max:2048'
        ]);

        $image_path = Storage::disk('local')->put('public'.'/'.$public, $request->file('file'));
        $image_path_url = Storage::url($image_path);

        return response()->json([
            'flash_message' => 'Image uploaded!',
            'image_path_url' => $image_path_url,
            'image_path' => $image_path
        ], 200);
    }

    /**
     * Update user setting by setting name
     *
     * @return \Illuminate\Http\Response
     */
    public function settings(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'setting_key' => 'required|string|min:2',
            'setting_value' => 'required',
            ]);

        $value = $request->has('cast') ? settype($request->setting_value, $request->cast) : $request->setting_value;

        if ($user->settings()->has($request->setting_key)) {
            return response()->json([
                    'status' => $user->settings()->update($request->setting_key, $value)
                ], 200);
        }

        return response()->json([
                    'status' => $user->settings()->set($request->setting_key, $value)
                ], 200);
    }

    /**
     * Upload and save user profile picture
     *
     * @return \Illuminate\Http\Response
     */
    public function saveProfilePicture(Request $request)
    {
        $fileuploadlimit = maximumFileUploadSize();

        $request->validate([
            'profile_picture' => "required|mimes:jpeg,jpg,png,bmp,gif,svg|max:$fileuploadlimit"
        ]);

        $user = Auth::user();
        $date = date('Ymd');
        $filename = "{$user->id}-{$date}.{$request->profile_picture->extension()}";
        $request->profile_picture->storeAs('public/profiles', $filename);
        
        $user->update(['profile_picture' => $filename]);

        return response()->json([
            'flash_message' => 'Profile picture uploaded!',
            'image_path' => $user->getProfilePicture()
        ], 200);
    }

    /**
     * Evaluate user security
     *
     * @param RevisionTrait $revision
     * @return integer
     */
    protected function evaluateSecurity($revision = false)
    {
        $passed = 1;
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            $passed += 1;
        } else {
            return $passed;
        }

        if ($user->is2FAEnable()) {
            $passed += 1;
        }

        if ($user->hasBindMobile()) {
            $passed += 1;
        }

        if ($revision && Carbon::createFromTimeStamp(strtotime($revision->created_at))->diffInMonths() < 6) {
            $passed += 1;
        }

        return $passed;
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\Respone|mixed
     */
    public function updateName(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|min:1|max:255',
            'last_name' => 'nullable|max:255'
        ]);

        $user = User::find(Auth::user()->id);
        $user->first_name = strip_tags($request->first_name);
        $user->last_name = strip_tags($request->last_name);
        $user->save();

        Auth::user()->fresh();

        return response()->json(['message' => __('Successfully update names.')], 200);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\Respone|mixed
     */
    public function showCoinPartnerProgram(Request $request)
    {   
        $current_user = Auth()->user();
        $coin_partner = $current_user->settings()->get('coin_partner');
        $ExchangeTransaction = ExchangeTransaction::class;
        $CoinCompetition = CoinCompetition::class;

        if(!$coin_partner){
            return view('main.profile.coin-partner-program-join');
        }

        $item = ExchangeItem::findOrFail($coin_partner);

        $transaction = $ExchangeTransaction::getTransactions($item->item_id);

        // specifying TRUE will make this function to fetch only those coin partners
        $users = $ExchangeTransaction::getUsersFromTransactions($item->item_id,true);
        $current_competition = $CoinCompetition::getCurrentCompetition($item->item_id);
        

        $rank = '';
        $volume = '';

        $competitions = $CoinCompetition::all(); 

        if($competitions){
            foreach ($competitions as $key => $competition) {
                if($competition->volume == $current_competition->volume){
                    $bar_width[$key] = ($transaction->amount_btc / $competition->volume) * 100;
                }elseif($competition->volume < $current_competition->volume){
                    $bar_width[$key] = $competition->volume * 100;
                }
                else{
                    $bar_width[$key] = 0;
                }
            }
        }

        // this will catch the empty current milestone
        if($transaction->amount_btc < 1 && $current_competition->volume > 1){
            $previous_volume = $CoinCompetition::getPreviousCompetition($item->item_id)->volume;
            $finished_competitions = $CoinCompetition::getFinishedCompetition($item->item_id);
            $transaction->amount_btc = $previous_volume;
            for($i=0;$i<count($finished_competitions);$i++){
                $bar_width[$i] = 100;
            }
        }

        if(count($users) > 0) {
            foreach ($users as $key => $user) {
                if($current_user !== null && $current_user->id == $user->id) {
                    $rank = $key + 1;
                    $volume = number_format($user->volume, 4, '.', ',');
                }

                $user->email_blured = blurEmail($user->email);
            }
        }
 
        $my_rewards = CoinCompetitionRecord::where('winners->partner_winner->id',$current_user->id)
        ->get();


        return view('main.profile.coin-partner-program',
        compact(
            'item', 
            'transaction', 
            'users', 
            'rank', 
            'volume', 
            'bar_width', 
            'coin_partner',
            'current_competition',
            'my_rewards'
        ));
    }

}
