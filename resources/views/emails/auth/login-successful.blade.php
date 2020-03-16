@component('mail::message')

{{ __('Dear Buzzex User') }} <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>:

{{ __('You have signed in at') }} {{ config('app.url') }} {{ __('at') }} {{ $dateTime }}.

{{ __('IP address') }}: {{ $data->ip }}<br>

@if( $data )

@if ( $data->device )
{{ __('Platform/Browser') }}: {{ $data->device['platform'] ?? '-' }}, {{ $data->device['browser'] ?? '-' }}  <br>

@if( $device->device['device'] ?? '' )
{{ __('Device') }}: {{ $device->device['device'] }}<br>
@endif

@endif

@if ( $data->location )
{{ __('Country') }}: {{ $data->location['country'] }} <br>
{{ __('State') }}: {{ $data->location['state_name'] }} <br>
{{ __('City') }}: {{ __('in or near') }} {{ $data->location['city'] }} <br>
@endif

@endif

If this activity is not your own operation, please contact us immediately. https://support.buzzex.io<br/>

{{ __('Thanks') }},<br>
{{ config('app.name') }}
@endcomponent
