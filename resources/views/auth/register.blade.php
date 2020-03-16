@extends('masters.app')

@section('content')
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-8">
        <div class="card-deck my-cards registration-page">
          <div class="card">
            <div class="card-body p-md-5">
              <h4 class="mb-5">{{ __('Register') }}</h4>

              @include('partials.errors')

              <form class="myform" autocomplete="off" method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group row">
                  <div class="col-12 col-md-6">
                    <input type="text" placeholder="{{ __('First Name') }}"
                           class="form-control{{ $errors->has('first_name') ? ' is-invalid' : '' }}"
                           name="first_name" value="{{ old('first_name') }}" required autofocus>
                  </div>

                  <div class="col-12 col-md-6">
                    <input type="text" placeholder="{{ __('Last Name') }}"
                           class="form-control{{ $errors->has('last_name') ? ' is-invalid' : '' }}"
                           name="last_name" value="{{ old('last_name') }}" required>
                  </div>

                </div>

                <div class="form-group row">
                  <div class="col">
                    <input type="email" placeholder="{{ __('E-mail Address') }}"
                           class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                           name="email" value="{{ old('email') }}" required>
                  </div>
                </div>

                <div class="form-group row">
                  <div class="col-12 col-md-6">
                    <input id="password" type="password" placeholder="{{ __('Password') }}"
                           class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                           name="password" value="{{ old('password') }}" required>
                  </div>

                  <div class="col-12 col-md-6">
                    <input type="password" placeholder="{{ __('Repeat Password') }}"
                           class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                           name="password_confirmation" value="{{ old('password_confirmation') }}" required>
                  </div>
                </div>

                @if (parameter('recaptcha_enable', 1) == 1)
                <div class="form-group row">
                  <div class="col">
                  {!! NoCaptcha::display(['data-theme' => $user_theme ]) !!}
                  </div>
                </div>
                @endif

                <div class="form-group row">
                  <div class="col">
                    {!! __('By clicking <kbd>Register</kbd>, you agree to the <a href="">Terms of Service</a> set out by this site.') !!}
                  </div>
                </div>

                <div class="form-group row mt-4 ">
                  <div class="col-12 col-lg-4 mx-auto">
                    <button disabled type="submit" class="btn-block btn mx-auto btn-gradient-yellow">
                      {{ __('Register') }}
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

@section('scripts')
  @if ( (int) parameter('recaptcha_enable', 1) == 1)
    {!! NoCaptcha::renderJs() !!}
  @endif

  <script type="text/javascript">
      $(document).ready(function () {
          $('#password').password({
              shortPass: 'The password is too short',
              badPass: 'Weak; try combining letters & numbers',
              goodPass: 'Medium; try using special characters',
              strongPass: 'Strong password',
              containsUsername: 'The password contains the username',
              enterPass: 'Type your password',
              showPercent: false,
              showText: true, // shows the text tips
              animate: true, // whether or not to animate the progress bar on input blur/focus
              animateSpeed: 'fast', // the above animation speed
              username: false, // select the username field (selector or jQuery instance) for better password checks
              usernamePartialMatch: true, // whether to check for username partials
              minimumLength: 6 // minimum password length (below this threshold, the score is 0)
          }).on('password.score', (e, score) => {
              var button = $(e.target).parents('form').find("button[type='submit']");
              if (score < 50) {
                  button.prop('disabled', true);
              } else {
                  button.prop('disabled', false);
              }
          })
      })
  </script>
@endsection