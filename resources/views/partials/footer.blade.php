
<footer class="page-footer font-small mdb-color pt-4">
    <!-- Footer Links -->
    <div class="container-fluid text-center text-md-left">
        <!-- Footer links -->
        <div class="row text-center text-md-left mt-3 pb-3">
            <!-- Grid column -->
            <div class="col-md-12 col-lg-3 col-xl-3 mx-auto mt-3">
                <img src="{{asset('/img/logo.png')}}" class="img-fluid mx-auto">
                <p class="text-md-center text-lg-left">{{ __('Exchange The Future') }}.</p>
                <!-- Social buttons -->
                <div class="text-center text-md-center text-lg-left prelaunch_socials">
                    <ul class="list-unstyled list-inline">
                        <li class="list-inline-item">
                            <a target="_blank" href="{{ $buzzexLinks->facebook->url }}" rel="tooltip" title="{{ $buzzexLinks->facebook->label}}" class="btn-floating btn-sm rgba-white-slight mx-1">
                                <i class="fa fa-facebook"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a target="_blank" href="{{ $buzzexLinks->twitter->url }}" rel="tooltip" title="{{ $buzzexLinks->twitter->label}}" class="btn-floating btn-sm rgba-white-slight mx-1">
                                <i class="fa fa-twitter"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a target="_blank" href="{{ $buzzexLinks->telegram->url }}" rel="tooltip" title="{{ $buzzexLinks->telegram->label}}" class="btn-floating btn-sm rgba-white-slight mx-1">
                                <i class="fa fa-telegram"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a target="_blank" href="{{ $buzzexLinks->linkedin->url }}" rel="tooltip" title="{{ $buzzexLinks->linkedin->label}}" class="btn-floating btn-sm rgba-white-slight mx-1">
                                <i class="fa fa-linkedin"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- Grid column -->
            <hr class="w-100 clearfix d-md-none">
            <!-- Grid column -->
            <div class="col-md-4 col-lg-1 mx-auto mt-3">
                <h6 class="text-uppercase mb-4 font-weight-bold">{{ __('Platform') }}</h6>
                @guest
                <p>
                    <a class="join" href="{{ route('register') }}"> {{ __('Register') }}</a>
                </p>
                @endguest
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->coin_partner_program->url }}"> {{ __('Coin Partner Program') }}</a>
                </p>
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->referral_program->url }}"> {{ __('Referral Program') }}</a>
                </p>
                <!-- <p>
                    <a target="_blank" href="{{ $buzzexLinks->whitepaper->url }}"> {{ __('Whitepaper') }}</a>
                </p> -->
            </div>
            <!-- Grid column -->
            <hr class="w-100 clearfix d-md-none">
            <!-- Grid column -->
            <div class="col-md-4 col-lg-1 mx-auto mt-3">
                <h6 class="text-uppercase mb-4 font-weight-bold"> {{ __('Coins') }}</h6>
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->intro_video->url }}"> {{ __('Intro Video ') }}</a>
                </p>
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->mining->url }}"> {{ __('Mining') }}</a>
                </p>
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->explorer->url }}"> {{ __('Explorer') }}</a>
                </p>
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->source_code->url }}"> {{ __('Source Code') }}</a>
                </p>
            </div>
            <!-- Grid column -->
            <hr class="w-100 clearfix d-md-none">
            <!-- Grid column -->
            <div class="col-md-4 col-lg-1 mx-auto mt-3">
                <h6 class="text-uppercase mb-4 font-weight-bold"> {{ __('Agreements') }}</h6>
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->terms_of_service->url }}"> {{ __('Terms Of Service') }}</a>
                </p>
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->privacy_policy->url }}"> {{ __('Privacy polic') }}y</a>
                </p>
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->fees->url }}"> {{ __('Fees') }}</a>
                </p>
            </div>
            <hr class="w-100 clearfix d-md-none">
            <!-- Grid column -->
            <div class="col-md-2 col-lg-1 mx-auto mt-3">
                <h6 class="text-uppercase mb-4 font-weight-bold">{{ __('Support') }}</h6>
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->latest_news->url }}"> {{ __('Latest News') }}</a>
                </p>
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->help_desk->url }}"> {{ __('Help Desk') }}</a>
                </p>
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->faqs->url }}"> {{ __("FAQ's") }}</a>
                </p>
            </div>
            <!-- Grid column -->
            <hr class="w-100 clearfix d-md-none">
            <!-- Grid column -->
            <div class="col-md-4 col-lg-1 mx-auto mt-3">
                <h6 class="text-uppercase mb-4 font-weight-bold">{{ __('Rewards') }}</h6>
                <p>
                    <a target="_blank" href="/en/project/listed">{{ __('Trading Competition') }}</a>
                </p>
                <!-- <p>
                    <a target="_blank" href="{{ $buzzexLinks->global_ambassador->url }}"> {{ __('Global Ambassadors') }}</a>
                </p>
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->bounties->url }}"> {{ __('Bounties') }}</a>
                </p> -->
                <p>
                    <a target="_blank" href="{{ $buzzexLinks->api_competition->url }}"> {{ __('API Competition') }}</a>
                </p>
            </div>
        </div>
        <!-- Footer links -->
    </div>
    <div class="container-fluid text-center text-md-left footer-bottom">
        <div class="row d-flex align-items-center  ">
            <!-- Grid column -->
            <div class="col-md-7 col-lg-8">
                <!--Copyright-->
                <p class="text-center text-md-left">Copyright &copy; {{date('Y')}}
                    <a href="#">
                    <strong class="mx-1">{{config('app.name')}}</strong>
                    </a> 
                    {{ __('All rights reserved') }}.
                </p>
            </div>
            <!-- Grid column -->
            <!-- Grid column -->
            <div class="col-md-5 col-lg-4 ml-lg-0">
                <div class="d-flex justify-content-md-between">
                    <div class="d-flex justify-content-md-end">
                        @auth
                        <span class="d-none mr-1 fa fa-circle text-success align-self-center"></span>
                        <span class="d-none mx-1 online-count">0</span>
                        <span class="d-none mx-1">{{ __('Online') }}</span>
                        @endauth
                    </div>
                    <div class="d-flex justify-content-md-end">
                        <span class="mr-1">{{ auth()->check() ? __('Connected') : __('Login to connect') }}</span>
                        <span class="mr-1 fa fa-signal {{ auth()->check() ? 'text-success' : 'text-danger' }} align-self-center"></span>
                    </div>
                </div>
            </div>
            <!-- Grid column -->
        </div>
    </div>
    <!-- Footer Links -->
</footer>
