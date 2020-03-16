@component('mail::message')

{{ __('Dear Buzzex Devs') }} <a href="mailto:{{ $data['target'] }}">{{ $data['target'] }}</a>:

{{ $data['message'] }}

{{ __('Thanks') }},<br>

{{ config('app.name') }}

@endcomponent
