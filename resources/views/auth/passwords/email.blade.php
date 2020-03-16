@extends('masters.app')

@section('content')
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-5">
        <div class="card-deck my-cards">


          <div class="card">

            <div class="card-body px-md-5">
              <h4 class="text-center my-3 mb-5">{{ __('Reset Password') }}</h4>

              @if (session('status'))
                <div class="alert alert-success" role="alert">
                  {{ session('status') }}
                </div>
              @endif

              @include('partials.errors')

              <form class="myform" method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group row">
                  <div class="col-md-12">
                    <input id="email" type="email" placeholder="{{ __('E-mail Address') }}"
                           class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                           name="email" value="{{ old('email') }}" required autofocus>

                  </div>
                </div>

                @if ( (int) parameter('recaptcha_enable', 1) == 1)
                  {!! NoCaptcha::display(['data-theme' => $user_theme ]) !!}
                @endif

                <div class="form-group row my-3">
                  <div class="col-md-12">
                    <button type="submit" class="btn btn-gradient-green btn-block">
                      {{ __('Send Password Reset Link') }}
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
  @if (parameter('recaptcha_enable', 1) == 1)
    {!! NoCaptcha::renderJs() !!}
  @endif
@endsection