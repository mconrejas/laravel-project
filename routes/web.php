<?php

/**
 * ATTENTION DEVS!
 * Put all your test routes and logic in TestController.php
 */

/**
 * Admin routes
 */

Route::group(['namespace' => 'Admin', 'prefix' => 'admin', 'middleware' => ['auth','role:admin|super-admin|support', '2fa','verified'] ], function () {
    Route::get('/', 'DashboardController@index')->name('admin.dashboard');
    Route::get('/about', 'DashboardController@about')->name('admin.about');
    Route::post('/check', 'DashboardController@checkStatus')->name('admin.check');
    
    Route::group(['prefix' => 'statistics'], function () {
        Route::get('/user', 'ChartController@userChart')->name('statistics.user');
        Route::get('/user/filter', 'ChartController@filterUserChart')->name('statistics.user.filter');
    });

    Route::group(['middleware' => 'role:super-admin|admin|support', 'prefix' => 'project'], function () {
        Route::get('/pending', 'CoinProjectController@pending')->name('project.pending');
        Route::get('/to-be-listed', 'CoinProjectController@toBeListed')->name('project.tobelisted');
        Route::get('/votes', 'CoinProjectController@votes')->name('project.votes');
        Route::get('/show/{id}', 'CoinProjectController@show')->name('project.show');
        Route::get('/edit/{id}', 'CoinProjectController@edit')->name('project.edit');
        Route::post('/approve/{id}', 'CoinProjectController@approve')->name('project.approve');
        Route::post('/update/{id}', 'CoinProjectController@update')->name('project.update');

        Route::get('/competition', 'CoinProjectController@coinCompetition')->name('project.coincompetition');
    });

    Route::group(['middleware' => 'role:super-admin'], function () {
        Route::resource('/roles', 'RolesController');
        Route::resource('/permissions', 'PermissionsController');

        Route::get('/users/{user}/change-status/{status}', 'UsersController@changeAcountStatus')->name('users.change-status');
        Route::get('/users/{user}/coin-partner/{status}', 'UsersController@changeCoinpartnerStatus')->name('users.coinpartner-status');

        Route::get('/users/search', 'UsersController@search')->name('users.search');
        Route::resource('/users', 'UsersController');
        
        Route::get('/users/{user}/login-history', 'UsersController@showLoginHistory')->name('users.login-history');
        Route::get('/users/{user}/login-records', 'UsersController@getLoginHistory')->name('users.login-records');
        Route::get('/users/{user}/reload-funds', 'UsersController@getReloadFundsForm')->name('users.reload-funds');
        Route::get('/users/{user}/account-changes-history', 'UsersController@getAccountChangesHistory')->name('users.account-history');
        Route::post('/users/{user}/reload-funds', 'UsersController@reloadFunds')->name('users.reload-funds');

        Route::resource('/activitylogs', 'ActivityLogsController')->only([ 'index', 'show', 'destroy' ]);

        //KYC
        Route::prefix('kyc')->group(function () {
            Route::get('/list/{status}/{type}', 'UsersController@getKycList')->name('kyc.list');
            Route::get('/verification/modal/info/{id}', 'UsersController@getKycInformationModal')->name('kyc.information_modal');
            Route::get('/verification/modal/{image}/{id}', 'UsersController@getKycVerificationModal')->name('kyc.verification_modal');
            Route::get('{current}/{action}/{id}', 'UsersController@postKycAction')->name('kyc.action');
        });
    });
    
    Route::resource('/parameters', 'ParametersController');
    
    Route::post('/news/{id}/restore', 'NewsController@restore')->name('news.restore');
    Route::post('/news/{id}/remove', 'NewsController@remove')->name('news.remove');
    Route::get('/news/search', 'NewsController@search')->name('news.search');
    Route::resource('/news', 'NewsController')->except(['destroy']);

    Route::resource('/exchange-pairs', 'ExchangePairController')->except(['destroy']);
    Route::get('/exchange-pairs/{id}/destroy', 'ExchangePairController@destroy')->name('exchange-pairs.destroy');
    Route::get('/exchange-pairs/{id}/inlist', 'ExchangePairController@activate')->name('exchange-pairs.inlist');

    Route::resource('/exchange-items', 'ExchangeItemController')->except(['destroy']);
    Route::get('/exchange-items/{id}/upload', 'ExchangeItemController@uploadForm')->name('exchangeitems.uploadForm');
    Route::post('/exchange-items/{id}/upload', 'ExchangeItemController@upload')->name('exchangeitems.upload');
    Route::get('/exchange-items/{id}/destroy', 'ExchangeItemController@destroy')->name('exchange-items.destroy');
    Route::get('/exchange-items/{id}/inlist', 'ExchangeItemController@activate')->name('exchange-items.inlist');

    Route::group(['prefix' => 'exchange'], function () {
        Route::get('/markets/bases/select', 'ExchangeItemController@getMarketBases')->name('exchangemarkets.select');
        Route::post('/markets/bases/update', 'ExchangeItemController@updateMarketBases')->name('exchangemarkets.update');
        Route::get('/settings/api', 'ExchangeApiController@index')->name('exchangeapi');
        Route::get('/settings/api/create', 'ExchangeApiController@create')->name('exchangeapi.create');
        Route::post('/settings/api/store', 'ExchangeApiController@store')->name('exchangeapi.store');
        Route::get('/settings/api/edit/{id}', 'ExchangeApiController@edit')->name('exchangeapi.edit');
        Route::post('/settings/api/update', 'ExchangeApiController@update')->name('exchangeapi.update');
        Route::get('/settings/api/destroy/{id}', 'ExchangeApiController@destroy')->name('exchangeapi.destroy');
    });

    Route::group(['prefix' => 'withdrawals'], function () {
        Route::get('/', 'WithdrawalsController@index')->name('withdrawals');
        Route::get('/external', 'WithdrawalsController@externalWithdrawals')->name('withdrawals.external');
        Route::get('/edit/{id}', 'WithdrawalsController@edit')->name('withdrawals.edit');
        Route::post('/update/{id}', 'WithdrawalsController@update')->name('withdrawals.update');
    });

    Route::group(['prefix' => 'balances'], function () {
        Route::get('/', 'BalancesController@index')->name('index');
        Route::get('/external', 'ExternalBalancesController@externalBalances')->name('external.balances');
        Route::get('/all', 'BalancesController@getBalances')->name('balances');
        Route::get('/external/all', 'ExternalBalancesController@getExternalBalances')->name('get.external.balances');
    });
    Route::group(['prefix' => 'history'], function () {
        Route::get('/item/{ticker}', 'BalancesController@itemHistory')->name('history.item');
        Route::get('/item/{ticker}/{type}', 'BalancesController@getItemHistory');
        Route::get('/pair/{pair_id}', 'BalancesController@pairHistory')->name('history.pair');
        Route::get('/pair/{pair_id}/{type}', 'BalancesController@getPairHistory');
    });
    Route::group(['prefix' => 'history-external'], function () {
        Route::get('/item/{ticker}', 'ExternalBalancesController@itemHistory')->name('external_history.item');
        Route::get('/item/{ticker}/{type}', 'ExternalBalancesController@getItemHistory');
        Route::post('/resyncDeposits', 'ExternalBalancesController@resyncDeposits')->name('resyncDeposits');
        Route::post('/resyncWithdrawals', 'ExternalBalancesController@resyncWithdrawals')->name('resyncWithdrawals');
        // Route::get('/pair/{pair_id}/{type}', 'BalancesController@getPairHistory');
    });

    Route::group(['prefix' => 'user-requests'], function () {
        Route::get('/deposits', 'UserRequestController@showDepositIndex')->name('userrequests.deposit');
        Route::get('/deposits/all', 'UserRequestController@userRequestDeposits')->name('userrequests.deposit.all');
    });

    Route::group(['prefix' => 'events'], function () {
        Route::get('/total-trades', 'EventsController@index')->name('events.total_trades');
        Route::get('/total-trades/all', 'EventsController@getTotalRecords')->name('events.get_records');
    });
});


// this redirects the old pre-launch referral URL to buzzex referral URL
Route::group(['namespace' => 'Main'], function () {
    Route::get('/go', 'ReferralController@go')->name('referral.go');
});

/**
 * Main routes
 */
Route::group(['middleware' => 'localization', 'prefix' => '{locale?}'], function () {
    Route::namespace('Auth')->group(function () {
        // Authentication Routes...
        Route::middleware('guest')->group(function () {
            Route::get('login', 'LoginController@showLoginForm')->name('login');
            Route::post('login', 'LoginController@login');

            // Registration Routes...
            Route::get('register', 'RegisterController@showRegistrationForm')->name('register');
            Route::post('register', 'RegisterController@register');
            Route::post('register-via-email', 'RegisterController@registerViaEmailOnly')->name('register.via.email');
        });

        Route::post('logout', 'LoginController@logout')->name('logout')->middleware('auth');

        // Password Reset Routes...
        Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
        Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
        Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
        Route::post('password/reset', 'ResetPasswordController@reset')->name('password.update');

        // Email Verification Routes...
        Route::get('email/verify', 'VerificationController@show')->name('verification.notice');
        Route::get('email/verify/{id}', 'VerificationController@verify')->name('verification.verify');
        Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');

        Route::middleware('auth')->group(function () {
            //2FA routes
            Route::get('/2fa', 'PasswordSecurityController@show2faForm')->name('twofa.form');
            Route::post('/generate2faSecret', 'PasswordSecurityController@generate2faSecret')->name('generate2faSecret');
            Route::post('/2fa', 'PasswordSecurityController@enable2fa')->name('enable2fa');
            Route::post('/disable2fa', 'PasswordSecurityController@disable2fa')->name('disable2fa');
            Route::post('/2faVerify', 'PasswordSecurityController@redirectPrevious')->name('2faVerify')->middleware('2fa');
            Route::post('/verifycode', 'PasswordSecurityController@verifyCode')->name('verifycode');

            Route::post('/2fa/authenticate', 'UpdatePasswordController@twofaAuthenticate')->name('twofa.authenticate')->middleware('2fa');
            Route::get('/password/change', 'UpdatePasswordController@showChangePasswordForm')->name('password.form')->middleware('2fa');
            Route::post('/password/change', 'UpdatePasswordController@changePassword')->name('password.change')->middleware('2fa');
            
            Route::get('register/success', 'RegisterController@showRegistrationSucceess')->name('register.success');
            Route::get('register/verification/success', 'VerificationController@showVerificationSuccess')->name('register.verification.success');

            Route::prefix('sms')->group(function () {
                Route::get('/', 'SmsBindingController@showSmsForm')->name('sms.showForm');
                Route::post('/', 'SmsBindingController@requestOTP')->name('sms.requestOTP');
                Route::post('/bind', 'SmsBindingController@bindNumber')->name('sms.bind');
                Route::post('/unbind', 'SmsBindingController@unBindNumber')->name('sms.unbind');
            });
        });
    });


    Route::group(['namespace' => 'Main'], function () {
        Route::get('/', 'HomeController@index')->name('home')->middleware('2fa');

        Route::get('/join/{code?}', 'ReferralController@index')->name('referral.join')->middleware('referral');

        Route::get('/locale/{lang}', 'HomeController@setLocale')->name('locale');
        Route::get('/theme/{theme}', 'HomeController@setTheme')->name('theme');

        Route::post('/email/request-code', 'EmailCodeController@requestEmailCode')->name('email.requestEmailCode');

        Route::get('/settings/update/fave-pair/{pairid?}', 'HomeController@updateFavePair')->name('exchange.updateFavePair');

        Route::prefix('exchange')->group(function () {
            Route::get('/', 'ExchangeController@index')->name('exchange')->middleware('2fa');
            Route::get('/set-base', 'ExchangeController@setBase')->name('base');
            Route::get('/market', 'ExchangeController@getMarket')->name('market');
            Route::get('/latest-execution', 'ExchangeController@getLatestExecution')->name('latestExecution');
            Route::get('/order/latest-execution', 'ExchangeController@getLatestExecutionByUser')->name('latestExecutionByUser');

            Route::get('/tradingview/bars', 'ExchangeController@getBars')->name('tradingBars');
            Route::get('/tradingview/server-time', 'ExchangeController@getServerTime')->name('servertime');

            Route::get('/trade-depth', 'ExchangeController@getTradeDepth')->name('tradeDepth');
            Route::get('/pair-info', 'ExchangeController@getPairInfo')->name('pairInfo');

            Route::post('/search/pair', 'ExchangeController@searchPair')->name('searchPair');
            Route::post('/search/coin', 'ExchangeController@searchCoin')->name('searchCoin');

            Route::get('/orders/{tab}', 'ExchangeController@showOrderTab')->name('orderTab')->middleware('2fa');
            Route::get('/order/current', 'ExchangeController@getCurrentOrders')->name('currentOrder')->middleware('2fa');
            Route::get('/order/history', 'ExchangeController@getOrderHistory')->name('orderHistory')->middleware('2fa');

            Route::post('/form', 'TradeController@processForm')->name('exchange.form');
            Route::post('/matched-order', 'TradeController@processMatchedLocalFromStream')->name('exchange.matchedOrder');
        });
        
        Route::prefix('orders')->group(function () {
            Route::post('/current/cancel', 'OrderController@cancelOrder')->name('order.cancelOrder');
        });

        Route::middleware(['auth', '2fa', 'verified'])->group(function () {
            Route::prefix('my')->group(function () {
                Route::get('/wallet', 'WalletController@index')->name('my.wallet');
                Route::get('/wallet/offline', 'WalletController@offlineWallet')->name('my.wallet-offline');
                Route::get('/trade-link', 'WalletController@getTradeLinks')->name('trade.links');
                Route::post('/wallet', 'WalletController@getWallets')->name('my.wallets');
                Route::post('/wallet/deposit/pending', 'WalletController@getPendingDeposit')->name('my.pendingDeposit');

                //type is 'deposit' or 'withdrawal'
                Route::get('wallet/record/{type}', 'WalletController@record')->name('my.record');
                Route::post('wallet/records/{type}', 'WalletController@getRecords')->name('my.getRecords');

                Route::get('/wallet/deposit/{coin}', 'WalletController@showDepositForm')->name('my.depositForm');
                Route::get('/wallet/deposit/{coin}/{amount?}', 'WalletController@showDepositForm')->name('my.depositForm');
                Route::get('/wallet/withdraw/{coin}', 'WalletController@showWithdrawalForm')->name('my.withdrawalForm');
                Route::post('/wallet/deposit/new/{coin}', 'WalletController@newDepositAddress')->name('my.newDepositAddress');
                Route::post('/wallet/withdraw/{coin}', 'WalletController@withdraw')->name('my.withdraw');

                Route::get('/assets', 'AssetsController@index')->name('assets.my');
                Route::post('/assets/records', 'AssetsController@getRecords')->name('assets.records');

                Route::get('/info/basic', 'ProfileController@index')->name('my.profile');
                Route::post('/info/basic/update-name', 'ProfileController@updateName')->name('my.update_name');
                Route::get('/info/basic/login-record', 'ProfileController@loginRecord')->name('my.signin');
                Route::get('/info/security', 'ProfileController@security')->name('my.security');
                Route::get('/info/notification', 'ProfileController@notification')->name('my.notification');
                Route::get('/info/referral', 'ProfileController@referral')->name('my.referral');

                Route::get('/coin-partner-program', 'ProfileController@showCoinPartnerProgram')->name('my.coinpartnerprogram');

                Route::middleware('optimizeImages')->post('/info/profile-picture', 'ProfileController@saveProfilePicture')->name('my.profile_picture');

                Route::get('/info/auth/verification/select', 'ProfileController@selectVerificationMethod')->name('my.selectMethod');
                Route::get('/info/auth/verification/personal', 'ProfileController@verifyPersonal')->name('my.verifyPersonal');
                Route::post('/info/auth/verification/save/personal', 'ProfileController@savePersonalVerification')->name('my.savePersonalVerification');
                Route::post('/info/auth/verification/upload', 'ProfileController@verifyUpload')->name('my.verifyUpload');

                Route::post('/info/settings', 'ProfileController@settings')->name('my.settings');
            });

            Route::prefix('api')->group(function () {
                Route::get('/', 'ApiSettingsController@index')->name('apisetting.index');
                Route::post('/delete', 'ApiSettingsController@delete')->name('apisetting.delete');
                Route::get('/counts', 'ApiSettingsController@getCounts')->name('apisetting.counts');
                Route::post('/create', 'ApiSettingsController@create')->name('apisetting.create');
            });

            Route::prefix('referral')->group(function () {
                Route::get('/', 'ReferralController@getReferred')->name('ref.getReferred');
            });

            Route::prefix('listing')->group(function () {
                Route::get('/', 'ListingController@index')->name('listing.index');
                Route::post('/store', 'ListingController@store')->name('listing.store');
                Route::get('/show/{id}', 'ListingController@show')->name('listing.show');
                Route::get('/search', 'ListingController@search')->name('listing.search');
            });
        });
        
        Route::prefix('project')->group(function () {
            Route::get('/{tab?}', 'VotingController@index')->name('vote.index');
            Route::post('/store', 'VotingController@store')->name('vote.store')->middleware('auth');
        });

        Route::prefix('competition')->group(function () {
            Route::get('/', 'VotingController@view')->name('vote.view');
        });

        Route::group(['middleware' => 'auth', 'prefix' => 'notifications'], function () {
            Route::get('/', 'NotificationsController@index')->name('notifications.message');
            Route::get('/list', 'NotificationsController@getNotifications')->name('notifications.list');
            Route::get('/unread', 'NotificationsController@getUnreadNotifications')->name('notifications.unread');
            Route::post('/mark-as-read', 'NotificationsController@markAsRead')->name('notifications.markasread');
        });

        Route::prefix('rewards')->group(function () {
            Route::get('/trans-fee-mining', 'RewardsController@rewards')->name('rewards.index');
            Route::post('/trans-fee-mining/claim', 'RewardsController@claimRewards')->name('rewards.claim');

            Route::get('/dividends', 'RewardsController@dividends')->name('rewards.dividends');
            Route::get('/dividends/records', 'RewardsController@getDividendsTransactions')->name('rewards.dividends-records');

            Route::get('/trading-competition', 'RewardsController@milestone')->name('rewards.milestone');
            Route::get('/trading-competition/list', 'RewardsController@getMilestoneRewardsList')->name('milestone.list');
            Route::post('/trading-competition', 'RewardsController@claimMilestoneRewards')->name('milestone.claim');
        });
    });
    
    /**
     * Test Routes
     */

    Route::get('/test', 'TestController@index');
    Route::get('/testing/{coin}', 'TestController@testCoinIntegration');
    Route::get('/mailable', 'TestController@mailable');
});
