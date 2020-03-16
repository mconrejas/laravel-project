@component('mail::message')

{{ __('Dear Buzzex User') }} <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>:

{{ __('You have signed up at') }} {{ config('app.url') }} {{ __('at') }} {{ $dateTime }}.

{{ __('Your credentials is :')}} <br>

{{ __('Username : ')}} {{ $user->email }}<br>
{{ __('Password : ')}} {{ $password }}<br>

{{ __('To secure your account, please update your password at') }} <a href="{{ route('password.form',['locale' => 'en']) }}"> {{ route('password.form') }}</a>

{{ __('Thanks') }},<br>
{{ config('app.name') }}
@endcomponent
