<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Contracts\User\Tradable;
use Buzzex\Events\CoinBalanceEvent;
use Buzzex\Http\Controllers\Controller;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\KycMedia;
use Buzzex\Models\Permission;
use Buzzex\Models\Role;
use Buzzex\Models\User;
use Buzzex\Repositories\UserRepository;
use Buzzex\Rules\ValidExchangeItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return void
     */
    public function index(Request $request)
    {
        $roles = Role::all();
        $permissions = Permission::all();
        $exclude = array_map(
            function($value) { return (int) str_replace('"', '', $value); },
            User::where('settings->is_coin_partner',true)->pluck('settings->coin_partner as coin')->toArray()
        );

        $allCoins = json_encode(getCoinItems($exclude));

        return view('admin.users.index', compact('roles', 'permissions','allCoins'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return void
     */
    public function search(Request $request)
    {
        $data = [];
        $users = User::select('*');
        $allCoins = getCoinItems();

        if ($request->has('role') && $request->role != 0) {
            $users = $users->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', '=', $request->role);
        }

        if ($request->has('term') && !empty($request->term)) {
            $users = $users->where(function ($query) use ($request) {
                return $query->where('email', 'like', '%' . $request->term . '%')
                    ->orWhere('first_name', 'like', '%' . $request->term . '%')
                    ->orWhere('last_name', 'like', '%' . $request->term . '%')
                    ->orWhere('id', '=', $request->term);
            });
        }

        $pages = ceil($users->count() / $request->size);

        $usersData = $users->latest()->take($request->size)->skip($request->page - 1)->get();


        foreach ($usersData as $key => $user) {
            $data[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->reverse()->pluck('id', 'name')->toArray(),
                'blocked' => (bool) $user->blocked['isBlocked'] ,
                'is_coin_partner' => $user->settings()->get('is_coin_partner'),
                'coin_partner' => $user->settings()->get('coin_partner'),
                'permissions' => $user->getAllPermissions()->pluck('id', 'name')->toArray(),
                'coin_symbol' => $user->settings()->get('coin_partner') ? $allCoins[$user->settings()->get('coin_partner')] :
                 ''
            ];
        }

        $response = [
            'last_page' => $pages,
            'data' => $data,
        ];

        return response()->json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     */
    public function create()
    {
        $roles = Role::select('id', 'name', 'guard_name')->get();
        $roles = $roles->pluck('name', 'name');

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|string|max:255|email|unique:users,email',
                'roles' => 'required',
            ]
        );

        $data = $request->except('password');
        $data['password'] = bcrypt($request->password);
        $data['email_verified_at'] = Carbon::now();

        $user = User::create($data);

        foreach ($request->roles as $role) {
            $user->assignRole($role);
        }

        toast('User added!', 'success', 'top-right');

        return redirect('admin/users')->with('flash_message', 'User added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return void
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return void
     */
    public function edit($id)
    {
        $roles = Role::select('id', 'name', 'guard_name')->get();
        $roles = $roles->pluck('name', 'name');

        $user = User::with('roles')->findOrFail($id);
        $user_roles = [];
        foreach ($user->roles as $role) {
            $user_roles[] = $role->name;
        }

        return view('admin.users.edit', compact('user', 'roles', 'user_roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return void
     */
    public function update(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|string|max:255|email|unique:users,email,' . $id,
                'roles' => 'required',
            ]
        );

        $data = $request->except('password');
        
        if ($request->has('password') && !empty($request->password)) {
            $data['password'] = bcrypt($request->password);
        }

        if ($request->has('email_verified_at') && $request->email_verified_at == 'on') {
            $data['email_verified_at'] = Carbon::now()->format('Y-m-d H:i:s');
        }

        $user = User::findOrFail($id);
        $user->update($data);
        $user->roles()->detach();

        foreach ($request->roles as $role) {
            $user->assignRole($role);
        }

        toast('User updated!', 'success', 'top-right');

        return redirect('admin/users');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return void
     */
    public function destroy($id)
    {
        return redirect('admin/users')->with('flash_message', 'User deleting is disabled!');
    }

    /**
     * display list of submitted kyc identification
     *
     * @return void
     */
    public function getKycList(Request $request)
    {
        $perExchangeItem = 25;
        $type = isset($request->type) ? $request->type : 'personal';
        $list = (new KycMedia)->newQuery();
        $status = isset($request->status) ? $request->status : 'pending';
        
        if ($request->has('search') && !empty($request->search)) {
            if (is_numeric($request->search) && $request->search > 0) {
                $list = $list->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->search)
                                    ->orWhere('id_number', $request->search);
                });
            } elseif (filter_var($request->search, FILTER_VALIDATE_EMAIL)) {
                $users = User::where('email', 'like', '%'.$request->search.'%')->pluck('id')->toArray();
                $list = $list->whereIn('user_id', $users);
            } else {
                $list = $list->where(function ($query) use ($request) {
                    return $query->where('first_name', 'like', '%'.$request->search.'%')
                        ->orWhere('last_name', 'like', '%'.$request->search.'%')
                        ->orWhere('id_number', '=', $request->search);
                });
            }
        }

        //filters
        if ($status == "pending") {
            $list = $list->where('approved', '<=', 0);
        } elseif ($status == "approved") {
            $list = $list->where('approved', '=', 1);
        } else {
            $list = $list->where('approved', '=', 2);
        }
        
        $list = $list->orderBy('first_name', 'asc');

        //$list = $list->where('type','=',$type);
        $list = $list->paginate($perExchangeItem);
        return view('admin.know-your-customers.index', compact('list', 'status'));
    }

    /**
     * query kycMedia details
     *
     * @return void
     */
    public function getKycVerificationModal(Request $request)
    {
        // Set storage
        $disk = Storage::disk('local');
        $media = KycMedia::findOrFail($request->id);
        $photo = url('img/no-photo-uploaded.gif');

        if (is_array($media->images) && !empty($media->images)) {
            $photos = $media->images;
            if (isset($photos[$request->image])) {
                if ($disk->exists($photos[$request->image])) {
                    $photo =  makeImage($disk->get($photos[$request->image]), false);
                }
            }
        }

        if ($request->image == 'front') {
            $image = 'ID Front Part';
        }
        if ($request->image == 'back') {
            $image = 'ID Back Part';
        }
        if ($request->image == 'selfie') {
            $image = 'Selfie Image';
        }

        return view('admin.know-your-customers.verification_modal', compact('media', 'photo', 'image'));
    }

    /**
     * query kycMedia details
     *
     * @return void
     */
    public function getKycInformationModal(Request $request)
    {
        $info = KycMedia::findOrFail($request->id);
        return view('admin.know-your-customers.information_modal', compact('info'));
    }
    /**
     * query kyc action recject or approve verification images
     *
     * @return void
     */
    public function postKycAction(Request $request)
    {
        $id = $request->id;
        $action = ($request->action == "approved") ? 1 : 2;
        $media = KycMedia::findOrFail($id);

        $media->approved = $action;
        $media->save();

        toast('ID Verification ' . $request->action, 'success', 'top-right');

        return redirect(route('kyc.list', ['status' => $request->current, 'type' => 'all']));
    }

    /**
     * @param Request $request
     * @param User $user
     * @param UserRepository $userRepository
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getReloadFundsForm(Request $request, UserRepository $userRepository)
    {
        abort_unless(config('account.reload_funds'), 403);

        $user = User::findOrFail($request->user);

        $funds = $userRepository->getFunds(false, $user);
        $items = (new ExchangeItem())->newQuery()
            ->active()
            ->where('type', '<>', 4)
            ->orderBy('symbol', 'asc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->symbol => $item->symbol];
            });

        return view('admin.users.reload-funds', ['user' => $user, 'funds' => $funds, 'items' => $items]);
    }

    /**
     * @param Request $request
     * @param User $user
     *
     * @return string
     */
    public function reloadFunds(Request $request, Tradable $trading, Marketable $markets)
    {
        abort_unless(config('account.reload_funds'), 403);

        $this->validate(
            $request,
            [
                'coin' => ['required', new ValidExchangeItem($markets)],
                'amount' => 'required|numeric|min:0.00000001',
            ]
        );

        $user = User::findOrFail($request->user);

        $exchangeItem = (new ExchangeItem())->newQuery()
            ->active()
            ->where('symbol', $request->coin)
            ->first();
        
        $trading->reloadFunds($user, $exchangeItem, $request->amount);

        toast('Fund successfully added.', 'success', 'top-right');

        broadcast(new CoinBalanceEvent($exchangeItem, $user));

        return redirect(route('users.reload-funds', ['user' => $user]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return json
     */
    public function changeAcountStatus(Request $request)
    {
        $status = null;

        if ($request->status=='block') {
            $status = [
                'isBlocked' => true,
                'blockeDate' => Carbon::now()->timestamp,
                'duration' => 0
            ];
        }

        $user = User::findOrFail($request->user);
        $user->blocked = $status;
        $user->update();
 
        return redirect(url('/admin/users'));
    }

    /**
     * set user's coin partner program status
     *
     * @return json
     */
    public function changeCoinpartnerStatus(Request $request)
    {
        $is_coin_partner = 'is_coin_partner';
        $coin_partner = 'coin_partner';

        $user = User::findOrFail($request->user);
        $status = $request->status == 'add' ? true:false; 
        $coin = $request->coin;

        if((User::where('settings->coin_partner',$coin)->count() > 0) && $request->status!='remove'){
            toast('Coin already taken!', 'warning', 'top-right');
            return redirect(url('/admin/users'));
        }
        
        if($user->settings()->has($coin_partner)){
            $user->settings()->update($is_coin_partner, $status);
            $user->settings()->update($coin_partner, $coin);
        }else{
            $user->settings()->set($is_coin_partner, $status);
            $user->settings()->set($coin_partner, $coin);
        }

        if(!$coin)            
            $user->settings()->delete($coin_partner);

       return redirect(url('/admin/users'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\View
     */
    public function showLoginHistory(Request $request, $id)
    {
        $user = User::find($id);

        abort_unless($user, 404, 'User not found!');
        
        return view('admin.users.login-history', compact('id', 'user'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\Response | mixed
     */
    public function getLoginHistory(Request $request, $id)
    {
        $data = array();
        $size = $request->size ?? 50;
        $page = $request->page ?? 1;
        $counts = 0;

        $user = User::find($id);

        abort_unless($user, 404, 'User not found!');
        
        if ($user) {
            $histories = $user->userSignin()
                        ->latest()
                        ->skip($size * ($page-1))
                        ->take($size)
                        ->get();

            if ($histories) {
                foreach ($histories as $key => $log) {
                    $data[] =   [
                            'time' => $log->created_at->format('Y-m-d H:i:s') ,
                            'device' => implode(',', [$log->device['platform'],$log->device['browser'],$log->device['device']]),
                            'location' => implode(',', [$log->location['country'], $log->location['state'], $log->location['city'] ]),
                            'ip' => $log->ip,
                            'details' => json_encode(array_merge([ 'Date'=>$log->created_at->format('Y-m-d H:i:s')], $log->device, $log->location)),
                        ];
                }
            }
            $counts = $user->userSignin()->count();
        }

        return response()->json([
            'last_page' => ceil($counts / $size),
            'data' => (array) $data,
        ], 200);
    }

    /**
     *
     *
     *
     */
    public function getAccountChangesHistory(Request $request)
    {
        $user = User::findOrFail($request->user);
        $data = array();
        $size = $request->size ?? 50;
        $page = $request->page ?? 1;
        $counts = 0;

        $revisions = $user->revisionHistory()
                    ->where('key', '<>', 'settings')
                    ->orderByDesc('id', 'desc')
                    ->skip($size * ($page-1))
                    ->take($size)
                    ->get();

        if ($revisions) {
            foreach ($revisions as $key => $log) {
                $responsible = "Self";
                if (isset($log->userResponsible()['id'])) {
                    $responsible = User::find($log->userResponsible()['id']);
                    $responsible = $responsible ? $responsible->name : "Unknown";
                }

                $data[] =  array(
                    'id' => $log->id,
                    'field' => $log->key,
                    'old_value' => $log->old_value,
                    'new_value' => $log->key == 'password' ? privatize($log->new_value) : $log->new_value,
                    'updated_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'updated_by' =>  $responsible,
                    'details' => !is_null($log->details) ? json_encode($log->details) : ""
                );
            }
        }
        $counts = $user->revisionHistory()->count();

        return response()->json([
            'last_page' => ceil($counts / $size),
            'data' => $data,
        ], 200);
    }
}
