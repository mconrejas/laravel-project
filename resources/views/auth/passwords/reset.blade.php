@extends('masters.app')

@section('content')
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-5">
        <div class="card-deck my-cards">

          <div class="card">
            <div class="card-body px-md-5">
              <h4 class="text-center my-3 mb-5">{{ __('Reset Password') }}</h4>

              @include('partials.errors')

              <form class="myform" method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group row">
                  <div class="col-md-12">
                    <input id="email" type="email" placeholder="{{ __('E-Mail Address') }}"
                           class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                           name="email" value="{{ $email ?? old('email') }}" required autofocus>
                  </div>
                </div>

                <div class="form-group row">

                  <div class="col-md-12">
                    <input id="password" type="password" placeholder="{{ __('Password') }}"
                           class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                           name="password" required>

                  </div>
                </div>

                <div class="form-group row">

                  <div class="col-md-12">
                    <input id="password-confirm" placeholder="{{ __('Confirm Password') }}" type="password"
                           class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}" name="password_confirmation" required>
                  </div>
                </div>

                <div class="form-group row my-3">
                  <div class="col-md-12">
                    <button type="submit" class="btn btn-gradient-green btn-block">
                      {{ __('Reset Password') }}
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
