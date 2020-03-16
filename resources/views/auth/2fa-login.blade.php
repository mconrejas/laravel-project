@extends('masters.app')

@section('content')
<div class="container-fluid my-5 my-cards">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-12">
            <div class="card p-4">
                <div class="card-body">
                    <div class="card-block py-md-4 px-md-5">
                        <strong
                        class="lead d-block mb-5">{{ __('Enter the pin from Google Authenticator Enable 2FA') }}</strong>
                        @include('partials.errors')
                        <form class="myform form-horizontal" autocomplete="off" action="{{ route('2faVerify',['locale' => app()->getLocale()]) }}" method="POST">
                            {{ csrf_field() }}
                            <div class="form-group row{{ $errors->has('one_time_password-code') ? ' has-error' : '' }}">
                                <label for="one_time_password"
                                class="col-md-12 d-block control-label">{{__('One Time Password') }}</label>
                                <div class="col-md-12">
                                    <input name="one_time_password" autofocus class="form-control text-center text-uppercase" type="text"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-12">
                                    <button class="btn btn-gradient-green btn-block" type="submit">{{__('Authenticate') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @include('auth.component.2fa-info')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function(){
        $(document).on('keyup',"input[name='one_time_password']", function(e){
            var value = $(this).val();
            if (value.length >= 6) {
                $(this).parents('form').submit();
            }
        });
    });
</script>
@endsection