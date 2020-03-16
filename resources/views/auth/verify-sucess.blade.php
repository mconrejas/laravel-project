@extends('masters.app')

@section('content')
  <div class="container mb-5 mt-5">
    <div class="row justify-content-center">
      <div class="col-md-11">
        <div class="card-deck">

          <div class="card">
            <div class="card-body p-4">
              <h1 class="text-center my-3 mb-5">{{ __('Thank You!') }}</h1>
              <div class="card-block">

                <h5 class="text-center">
                  {{ __('You have successfully verified your account.') }}
                </h5>
                <hr>
                <div class="text-center">
                  {{ __('Having trouble?') }} <a href="{{ config('buzzex_support.base_url') }}">{{ __('Contact us') }}</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
