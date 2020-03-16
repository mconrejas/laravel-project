@extends('masters.app')

@section('content')
    
    
    @guest
	    @include('main.home.component.new-main-banner')
    @endguest

    @include('main.home.component.market-tabs')

    @include('main.home.component.features')

    @include('main.home.component.about')

    <!-- include('main.home.component.team') -->
    @guest
    	@include('main.home.component.signup-form')
    @endguest
@endsection