@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-md-3">
            @include('main.profile.component.side-menu')
        </div>
        <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0 pt-3 px-0" id="card-security">
                <div class="card-block p-4 d-flex">
                    <div class="w-100 d-flex justify-content-start flex-wrap">
                        <div class="font-weight-bold form-label align-self-center"> {{__('Security level') }}:</div>
                        <div class="align-self-center">
                            @if ($security_star_count)
                                @for ($i = 1; $i <= $security_star_count; $i++)
                                    @if($i <= $user_security_status)
                                        <span class="font-20 fa fa-star text-warning"></span>
                                    @else
                                        <span class="font-20 fa fa-star "></span>
                                    @endif
                                @endfor
                            @endif
                            <small class="d-block">
                            @if($user_security_status < 5)
                                {{__('We strongly suggest you finish the following settings to improve account security.') }}
                            @else
                                {{__('Good! Keep this up, remember to update your password from time to time for better security') }}
                            @endif
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="card-block p-4 border-top ">
                    <div class="row">
                        <div class="col-12 col-md-10 d-flex flex-wrap">
                            <span class="font-weight-bold form-label align-self-center">
                                {{__('Sign in password')}} :
                            </span> 
                            <span class="align-self-center">
                                {{ __('Last updated') }} : 
                                @if($lastpasswordupdated)
                                    {{ $lastpasswordupdated }} 
                                @else
                                {{ __('No record yet') }}
                                @endif
                            </span>
                        </div>
                        <div class="col-12 col-md-2 d-flex justify-content-end">
                            <a href="{{route('password.form')}}" class="btn-light-on-dark my-2 my-md-1 btn border">{{__('Update')}}</a>
                        </div>
                    </div>
                </div>
                <div class="card-block p-4 border-top">
                    <div class="row">
                        <div class="col-12 col-md-10 d-flex flex-wrap">
                            <span class="font-weight-bold form-label align-self-center">{{__('2-Factor Authentication')}} :</span> 
                            @if(Auth::user()->is2FAEnable())
                            <span class="align-self-center text-success">
                                {{ __('Enabled') }}
                            </span>
                            @else 
                            <span class="align-self-center text-danger"> 
                                {{ __('Not enabled') }}
                                <small class="d-block text-secondary">{{__('Install Google Authenticator on your mobile phone')}}</small>
                            </span>
                            @endif
                        </div>
                        <div class="col-12 col-md-2 d-flex justify-content-end">
                            <a href="{{route('twofa.form')}}" class="btn-light-on-dark my-2 my-md-1 btn border align-self-center">
                                {{ Auth::user()->is2FAEnable() ? __('Disable') : __('Enable') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-block p-4 border-top">
                    <div class="row">
                        <div class="col-12 col-md-10 d-flex flex-wrap">
                            <span class="font-weight-bold form-label align-self-center">
                                {{__('Bind Mobile Number')}} :
                            </span> 
                            <span class="align-self-center">
                                @if (Auth::user()->mobile_number)
                                    {{ privatize(Auth::user()->mobile_number) }}
                                    <a href="javascript:void(0)" class="btn-remove-mobile-number btn-link text-warning mx-2">
                                        <span class="fa fa-warning"></span>
                                        <small>{{ __('Remove') }}</small>
                                    </a>
                                @else
                                    {{ __('Not binded') }}
                                @endif
                            </span>
                        </div>
                        <div class="col-12 col-md-2 d-flex justify-content-end">
                            @if($mobile_binding_on == 1)
                            <a href="{{route('sms.showForm')}}" class="btn-light-on-dark my-2 my-md-1 btn border align-self-center" >
                                {{ Auth::user()->mobile_number ? __('Change') : __('Bind')}}</a>
                            @else
                            <span class="btn-light-on-dark my-2 my-md-1 btn border align-self-center">{{ __('Coming soon') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
    .card-block .form-label {
        display: inline-block;
        min-width: 200px;
    }

    #card-security .btn {
        min-width: 100px;
    }

</style>
@endsection

@section('scripts')
<script type="text/javascript">
    var unbindMobileUrl = "{{route('sms.unbind',['locale' => 'en'])}}";

    $(document).ready(function(){
        $(document).on('click','.btn-remove-mobile-number ',function(e) {
            e.preventDefault();
            swal({
                title: '<span style="font-weight:normal;">Confirmation required!</span>',
                text: 'Enter your password',
                input: 'password',
                inputPlaceholder: 'Enter your password',
                inputClass: 'text-center rounded-0 form-control w-75 ',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                confirmButtonClass: 'btn btn-buzzex rounded-0 px-5 mr-2',
                cancelButtonClass: 'btn btn-dark rounded-0',
                buttonsStyling: false,
                confirmButtonColor: 'linear-gradient(270deg, #22e6b8, #00c1ce)',
                showCancelButton: true,
                confirmButtonText: 'Remove it!',
                type: 'warning',
            }).then((result) => {
                if (result.value) {
                    $.post(unbindMobileUrl,{
                        password : result.value
                    })
                    .done(function(data){
                        toast({text: data.flash_message, type: 'success'})
                        .then(function(){
                            window.location.reload();
                        });
                    })
                    .fail(function (xhr, status, error) {
                        alert({
                            title: window.Templates.getXHRMessage(xhr),
                            html: window.Templates.getXHRErrors(xhr),
                            type: 'error'
                        });
                    })
                }
            })
        })
    })
</script>
@endsection