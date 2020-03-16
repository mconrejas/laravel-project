<nav class="navbar navbar-expand-lg navbar-light bg-light" id="site-header-bottom">
    <a class="navbar-brand" href="{{ route('home') }}">
        <img class="img-fluid" src="{{asset('img/logo.png')}}?v=v1">
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        
        <!-- Left Side -->
        <ul class="navbar-nav mr-auto">
            <li class="nav-item {{active_route('exchange')}}">
                <a class="text-center text-lg-left nav-link" href="{{route('exchange')}}">{{ __('Exchange') }}</a>
            </li>
            <li class="nav-item {{active_route('my.wallet')}}">
                <a class="text-center text-lg-left nav-link" href="{{route('my.wallet')}}">{{ __('Wallet') }}</a>
            </li>
            <li class="nav-item d-block d-lg-none">
                <a class="text-center text-lg-left nav-link" href="{{ route('listing.index') }}">{{__('Listing')}}</a>
            </li>
            <li class="nav-item d-block d-lg-none">
                <a class="text-center text-lg-left nav-link" href="{{ route('vote.index',['tab'=>'vote']) }}">{{__('Vote')}}</a>
            </li>
            <li class="nav-item d-block d-lg-none">
                <a class="text-center text-lg-left nav-link" target="_blank" href="{{ $buzzexLinks->announcement->url }}">{{__('Announcement')}}</a>
            </li>
            <li class="nav-item d-block d-lg-none">
                <a class="mx-2 d-inline-block" target="_blank" href="{{ $buzzexLinks->referral_program->url }}">{{__('Referral Program')}}</a>
            </li>
            <li class="nav-item d-block d-lg-none">
                <a class="mx-2 d-inline-block" target="_blank" href="{{ $buzzexLinks->api_competition->url }}">{{__('API Competition')}}</a>
            </li>
            <li class="nav-item d-block d-lg-none">
                <a class="mx-2 d-inline-block" target="_blank" href="{{ $buzzexLinks->mining->url }}">{{__('Mining')}}</a>
            </li>
            <li class="nav-item {{active_route('help')}}">
                <a class="text-center text-lg-left nav-link" target="_blank" href="{{ $buzzexLinks->help_desk->url }}">{{ __('Help') }}</a>
            </li>
        </ul>


        <!-- Right Side -->
        <ul class="form-inline my-2 my-lg-0 mb-5 navbar-nav d-flex justify-content-center justify-content-md-end">
            <li class="nav-item dropdown mx-4">
                <a class="text-center text-lg-left nav-link btn btn-outline btn-green mx-sm-1 px-3" href="/en/project/listed">{{ __('$13 Million Trading Competition') }}</a>
            </li>

            @guest
            <li class="nav-item mx-auto my-1 ml-lg-3">
                <a class="text-center text-lg-left nav-link btn btn-outline btn-green mx-sm-1 px-3" href="{{ route('login') }}">{{ __('Log In') }}</a>
            </li>
            <li class="nav-item mx-auto my-1 ml-lg-3">
                <a class="text-center text-lg-left nav-link btn btn-outline btn-yellow mx-sm-1 px-3" href="{{ route('register') }}">{{ __('Sign Up') }}</a>
            </li>
            @else
            <li class="nav-item dropdown mx-4">
                <a id="navbarDropdown" class="text-center text-lg-left nav-link dropdown-toggle text-capitalize text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ __('All Orders')}} <span class="caret"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" style="z-index: 9999;" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item py-2" href="{{route('orderTab',['tab' => 'current-order' ])}}">
                        <span class="fa fa-cart-arrow-down mr-2"></span> {{ __('Current Orders') }}
                    </a>
                    <a class="dropdown-item py-2" href="{{route('orderTab',['tab' => 'order-history' ])}}">
                        <span class="fa fa-cube mr-2"></span> {{ __('Order History') }}
                    </a>
                    <a class="dropdown-item py-2" href="{{route('orderTab',['tab' => 'latest-execution' ])}}">
                        <span class="fa fa-cubes mr-2"></span> {{ __('Execution History') }}
                    </a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a id="navbarDropdown" class="text-center text-lg-left nav-link dropdown-toggle text-capitalize text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img id="profile_picture"  class="rounded-circle avatar-picture" src="{{ Auth::user()->getProfilePicture() }}" alt="">
                    {{ Auth::user()->first_name }} <span class="caret"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" style="z-index: 9999;" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item py-2" href="{{route('my.profile')}}">
                        <span class="fa mr-2 fa-address-card"></span> {{ __('Account') }}
                    </a>
                    <a class="dropdown-item py-2" href="{{route('rewards.index')}}">
                        <span class="fa mr-2 fa-gift"></span> {{ __('Events & Rewards') }}
                    </a>
                    <a class="dropdown-item py-2" href="{{route('notifications.message')}}">
                        <span class="fa mr-2 fa-envelope"></span> {{ __('Message') }}
                    </a>
                    <a class="dropdown-item py-2" href="{{route('my.referral')}}">
                        <span class="fa mr-2 fa-users"></span> {{ __('Referral') }}
                    </a>
                    <div class="dropdown-divider"></div>
                    @if(auth()->user()->hasAnyRole(['admin','super-admin','support']))
                    <a class="dropdown-item py-2" href="{{route('admin.dashboard')}}">
                        <span class="fa mr-2 fa-dashboard"></span> {{ __('Dashboard') }}
                    </a>
                    <div class="dropdown-divider"></div>
                    @endif
                    <a class="dropdown-item py-2" href="{{ route('logout',['locale' => 'en']) }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <span class="fa mr-2 fa-power-off"></span> {{ __('Logout') }}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </li>
        @endguest
    </ul>
</div>
</nav>