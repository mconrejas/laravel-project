<?php

namespace Buzzex\Providers;

use Buzzex\Console\Commands\ValidateWithdrawals;
use Buzzex\Contracts\Exchange\Marketable;
use Buzzex\Contracts\Security\JWTSSO;
use Buzzex\Contracts\Security\TwoFactorAuthenticable;
use Buzzex\Contracts\Setting\ManageParameter;
use Buzzex\Contracts\Setting\ManageTheme;
use Buzzex\Contracts\User\CanManageOwnFund;
use Buzzex\Contracts\User\CanManageUser;
use Buzzex\Contracts\User\Tradable;
use Buzzex\Services\Google2FAService;
use Buzzex\Services\MarketService;
use Buzzex\Services\SiteParameterService;
use Buzzex\Services\ThemeService;
use Buzzex\Services\TradingService;
use Buzzex\Services\UserService;
use Buzzex\Services\ZendeskService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        $this->customValidators();

        URL::forceScheme(env('APP_ENV') == 'local' ? 'http' : 'https');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (config('app.env') === 'production') {
            $this->app['url']->forceScheme('https');
        }

        $this->bindServices();
    }

    /**
     * Bind contracts with services
     */
    protected function bindServices()
    {
        $services = [
            CanManageUser::class => UserService::class,
            Marketable::class => MarketService::class,
            TwoFactorAuthenticable::class => Google2FAService::class,
            Tradable::class => TradingService::class,
            ManageParameter::class => SiteParameterService::class,
            ManageTheme::class => ThemeService::class,
            JWTSSO::class => ZendeskService::class,
            CanManageOwnFund::class => UserService::class,
        ];

        foreach ($services as $contract => $service) {
            $this->app->bind($contract, $service);
        }
    }

    /**
     * Custom Validators
     */
    protected function customValidators()
    {
        Validator::extend('valid_exchange_item', 'Buzzex\Rules\ValidExchangeItem@passes');

        Validator::extend('active_account', 'Buzzex\Rules\ActiveUserAccount@passes');

        Validator::extend('valid_pair', 'Buzzex\Rules\ValidPair@passes');

        Validator::extend('valid_coin_address', 'Buzzex\Rules\ValidCoinAddress@validAddress');

        Validator::extend('verify_logged_in_user', function ($attribute, $value, $parameters, $validator) {
            return Hash::check($value, Auth::user()->password);
        });
        
        Validator::extend('valid_twofa_code', 'Buzzex\Rules\Valid2FA@passes');

        Validator::extend('valid_code_request', 'Buzzex\Rules\ValidCodeRequest@passes');

        Validator::extend('valid_current_password', 'Buzzex\Rules\CurrentPassword@passes');

        Validator::extend('should_not_same_old_password', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();
            $old_value = $data['current_password'];

            return (strcmp($old_value, $value) !== 0);
        });

        Validator::extend('valid_token_explorer_url', 'Buzzex\Rules\ValidTokenExplorerUrl@passes');

        Validator::extend('valid_withdrawal_amount',function($attribute, $value, $parameters, $validator){
            $data = $validator->getData();
            $amount = $data["amount"];
            #1. check minimum withdrawal amount
            #2. check maximum withdrawal amount
            $item = ExchangeItem::where("symbol","=",strtoupper(trim($data['coin'])))->first();
            if($item){
                if($amount <= $item->getWithdrawalFee()) return false;
                $withdrawMin = $item->getWithdrawMinimum();
                $withdrawMax = $item->getWithdrawMaximum();
                $pass_item_limits = ($amount >= $withdrawMin && ($amount <= $withdrawMax || $withdrawMax == 0));
            }else{
                $pass_item_limits = ($amount >= 0.00000001);
            }
            if(!$pass_item_limits) return false;

            #3. check user daily withdrawal limits
            $validateWithdrawalsObj = new ValidateWithdrawals();
            $auth_user = Auth::user();
            $user = User::where("id",$auth_user->id)->first();
            if(!$user) return false;
            $currentWithdrawals = $validateWithdrawalsObj->getCurrentDayTotalActiveWithdrawalsBy($user);
            if(false === $currentWithdrawals) return false;
            $userDailyLimit = $user->dailyWithdrawLimit();
            $amount = $amount * $item->index_usd_price;
            return (($amount + $currentWithdrawals) <= $userDailyLimit);
        });
    }
}
