@component('mail::message')

{{ __('Dear Buzzex User') }} <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>:

{{ __('Your confirmation code is : ') }}  {{$request->code}}

{{ __('Thanks') }},<br>

{{ config('app.name') }}

@endcomponent
