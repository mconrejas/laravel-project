<nav class="navbar navbar-expand-lg navbar-dark bg-dark d-none d-lg-block" id="site-header-top">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <!-- <li class="nav-item active">
            <a class="mx-2 d-inline-block" target="_blank" href="{{ $buzzexLinks->buzzex_ambassador->url }}">{{__('Buzzex Ambassador')}}</a>
          </li> -->
          <!-- <li class="nav-item active">
            <a class="mx-2 d-inline-block" target="_blank" href="{{ $buzzexLinks->coin_partner_program->url }}">{{__('Coin Partner Program')}}</a>
          </li> -->
          <li class="nav-item active">
            <a class="mx-2 d-inline-block" href="{{ route('listing.index') }}">{{__('Listing')}}</a>
          </li>
          <li class="nav-item active">
            <a class="mx-2 d-inline-block" href="{{ route('vote.index',['tab'=>'vote']) }}">{{__('Vote')}}</a>
          </li>
          <li class="nav-item active">
            <a class="mx-2 d-inline-block" target="_blank" href="{{ $buzzexLinks->announcement->url }}">{{__('Announcement')}}</a>
          </li>
          <li class="nav-item active">
            <a class="mx-2 d-inline-block" target="_blank" href="{{ $buzzexLinks->referral_program->url }}">{{__('Referral Program')}}</a>
          </li>
          <li class="nav-item active">
            <a class="mx-2 d-inline-block" target="_blank" href="{{ $buzzexLinks->api_competition->url }}">{{__('API Competition')}}</a>
          </li>
          <li class="nav-item active">
            <a class="mx-2 d-inline-block" target="_blank" href="{{ $buzzexLinks->mining->url }}">{{__('Mining')}}</a>
          </li>
        </ul>
        <div class="form-inline my-2 my-lg-0">
          <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
            <li class="nav-item dropdown mx-3">
                <a id="navbarDropdown" class="dropdown-toggle text-light" href="#" role="button" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">{{ languages(app()->getLocale()) }}
                <span class="caret"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item text-secondary" href="{{ route('locale',['locale'=>'en','lang' => 'en']) }}?previous={{ request()->path() }}">
                    <img src="{{ asset('img/blank.gif') }}" class="flag flag-us mr-2" alt="American English"/> English </a>
                </div>
            </li>
            <li class="nav-item dropdown mx-3">
              <a id="navbarDropdown" class="dropdown-toggle text-light" href="#" role="button" data-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false"> {{ ucwords($user_theme) }}
              <span class="caret"></span>
              </a>
              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                  <a class="dropdown-item text-secondary" href="{{ route('theme',['theme' => 'light']) }}">
                  <span class="fa fa-sun-o mr-2 text-secondary"></span> {{__('Light')}}
                  </a>
                  <a class="dropdown-item text-secondary" href="{{ route('theme',['theme' => 'dark']) }}">
                  <span class="fa fa-certificate mr-2 text-dark"></span> {{__('Dark')}}
                  </a>
              </div>
            </li>
          </ul>
        </div>
    </div>
</nav>

