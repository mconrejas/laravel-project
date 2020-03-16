<?php

Route::group(['namespace' => 'Api', 'middleware' => ['auth.api.withdrawal_key']], function () {
    Route::get('/withdrawals', 'WithdrawalsController@getApprovedWithdrawals');
    Route::post('/withdrawals', 'WithdrawalsController@postTransactions');
});


// Route::group(['namespace' => 'External', 'middleware' => ['auth.api.external']], function () {

Route::group(['namespace' => 'External'], function () {
    Route::get('/snapshot/{pairtext}', '\Buzzex\Http\Controllers\External\WebsocketApiController@getSnapShot');
});