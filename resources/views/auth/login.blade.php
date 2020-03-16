@extends('masters.app')

@section('content')
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-5">
        <div class="card-deck my-cards">
          <div class="card">
            <div class="card-body px-md-5">
              <div class="text-center mb-3 mt-2">
                <img class="rounded-circle" width="96" src="{{ asset('img/photo.png') }}"/>
              </div>

              <div class="text-center border-bottom mb-2">
                <h4>{{ __('Log In') }}</h4>
              </div>

              <div class="mb-5">
                <label for="app-url">{{ __("Be sure you're visiting") }}</label>
                <div class="input-group mb-3 app-url-info">
                  <div class="input-group-prepend">
                    <span class="input-group-text border-0"><i class="text-success fa fa-lock"></i></span>
                  </div>
                  <input class="form-control border-0" id="app-url" type="text" placeholder="{{ config('app.url') }}" readonly>
                </div>

              </div>



              @include('partials.errors')

              <form class="form-auth form-auth-login" role="form" method="post" action="{{ route('login') }}"
                    autocomplete="off">
                <input type="text" class="form-control" placeholder="{{ __('E-Mail Address') }}" name="email"
                       value="{{ old('email') }}"
                       required autofocus autocomplete="new-password">
                <input type="password" class="form-control" autocomplete="new-password" placeholder="{{ __('Password') }}" name="password" required>

                @if (parameter('recaptcha_enable', 1) == 1)
                  {!! NoCaptcha::display(['data-theme' => $user_theme ]) !!}
                @endif

                <div class="text-right mb-4">
                  <a class="d-block mt-2" href="{{ route('password.request') }}">
                    {{ __('Forgot Your Password?') }}
                  </a>
                </div>

                <button class="btn btn-gradient-green btn-block" type="submit" name="login-button">
                  {{ __('Log In') }}
                </button>
                
                <div class="form-group last mt-2">
                  {{ __('Not on Buzzex yet?') }} <a href="{{ route('register') }}">{{ __('Register') }}</a>
                </div>
                @csrf
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  @if ((int) parameter('recaptcha_enable', 1) == 1)
    {!! NoCaptcha::renderJs() !!}
  @endif
@endsection