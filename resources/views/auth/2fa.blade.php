@extends('masters.app')

@section('content')
<div class="container-fluid my-5 my-cards">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-12 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-block p-4">
                        @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                        @endif
                        
                        @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        @endif
                        
                        @if(empty($data['user']->passwordSecurity) || $data['user']->passwordSecurity()->count() === 0)
                        <div class="px-4 py-2">
                            <p
                            class="lead">{{ __('To Enable Two Factor Authentication on your Account, you need to do following steps') }}</p>
                            <ol class="mt-5">
                                <li>{{ __('Click on "Generate Secret Key" button, to generate a unique secret QR code for your profile') }}</li>
                                <li class="mt-3">{{ __('Verify the OTP from Google Authenticator Mobile App') }}</li>
                            </ol>
                        </div>
                        <form class="form-horizontal" method="POST" action="{{ route('generate2faSecret') }}">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <div class="col-8 mx-auto">
                                    <button type="submit"
                                    class="btn-block btn btn-gradient-green">{{__('Generate Secret Key to Enable 2FA') }}</button>
                                </div>
                            </div>
                        </form>
                        
                        @elseif(!$data['user']->passwordSecurity->google2fa_enable)
                        <div class="px-3">
                            <div class="lead">{{ __('Enable Two Factor Authentication') }}</div>
                            <span
                            class="d-block mt-5">{{ __('1. Scan this barcode with your Google Authenticator App:') }}</span>
                            
                            <img src="{{$data['google2fa_url'] }}" class="mx-auto my-3 d-block">
                            
                            <span class="d-block mt-5">{{ __('2. Enter the pin code to Enable 2FA') }}</span>
                            
                            <form class="myform form-horizontal" autocomplete="off" method="POST"
                                action="{{ route('enable2fa') }}">
                                {{ csrf_field() }}
                                
                                <div class="mt-4 form-group row {{ $errors->has('verify-code') ? ' has-error' : '' }}">
                                    
                                    <div class="col-12">
                                        <input id="verify-code" autocomplete="no-password" type="text" class="text-center form-control"
                                        name="verify-code" required>
                                        
                                        @if ($errors->has('verify-code'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('verify-code') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-gradient-green btn-block"> {{ __('Enable 2FA') }} </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        @elseif($data['user']->passwordSecurity->google2fa_enable)
                        <div class="alert alert-info my-md-4 mx-md-4" role="alert">
                            {!!  __('2FA is Currently <strong>Enabled</strong> for your account.') !!}
                        </div>
                        <p
                        class="lead px-md-4">{{ __('If you are looking to disable Two Factor Authentication. Please confirm your password, enter 2FA code and Click Disable 2FA Button.') }}</p>
                        <form class="myform form-horizontal mt-4 px-md-4" autocomplete="off" method="POST"
                            action="{{ route('disable2fa') }}">
                            <div class="form-group row {{ $errors->has('current-password') ? ' has-error' : '' }}">
                                <label for="change-password"
                                class="col-12 d-block control-label">{{ __('Current Password') }}</label>
                                <div class="col-12">
                                    <input id="current-password" autocomplete="no-password" type="password" class="form-control"
                                    name="current-password" required>
                                    
                                    @if ($errors->has('current-password'))
                                    <span class="invalid-feedback d-block text-center" role="alert">
                                        <strong>{{ $errors->first('current-password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row {{ $errors->has('twofa_code') ? ' has-error' : '' }}">
                                <label for="change-password"
                                class="col-12 d-block control-label">{{ __('2FA code') }}</label>
                                <div class="col-12">
                                    <input type="text" class="text-uppercase form-control" name="twofa_code" required>
                                    
                                    @if ($errors->has('twofa_code'))
                                    <span class="invalid-feedback d-block text-center" role="alert">
                                        <strong>{{ $errors->first('twofa_code') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-12">
                                    @csrf
                                    <button type="submit" class="btn btn-block btn-gradient-green ">{{ __('Disable 2FA') }}</button>
                                </div>
                            </div>
                            
                        </form>
                        @endif
                    </div>
                    
                    @include('auth.component.2fa-info')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection