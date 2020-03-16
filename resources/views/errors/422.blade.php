@extends('errors::illustrated-layout')

@section('code', '422')
@section('title', __('Too Many Requests'))

@section('image')
<div style="background-image: url({{ asset('/svg/500.svg') }});" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
</div>
@endsection

@section('message', $exception->getMessage() ?: __('Oops, something went wrong. Please check if the url is correct.'))
