<div class="sidebar-footer">
    <div>
        <a href="javascript:void(0)" title="Logout" rel="tooltip" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fa fa-power-off"></i>
        </a>
        <form id="logout-form" action="{{ route('logout',['locale' => 'en']) }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
    <!-- <div class="dropdown">
        <a href="#" class="" id="dropdownMenuNotification" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-bell"></i>
            <span class="badge badge-pill badge-warning notification">3</span>
        </a>
        <div class="dropdown-menu notifications" aria-labelledby="dropdownMenuMessage">
            <div class="notifications-header">
                <i class="fa fa-bell"></i>
                Notifications
            </div>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">
                <div class="notification-content">
                    <div class="icon">
                        <i class="fa fa-check text-success border border-success"></i>
                    </div>
                    <div class="content">
                        <div class="notification-detail">Lorem ipsum dolor sit amet consectetur adipisicing elit. In totam explicabo</div>
                        <div class="notification-time">
                            6 minutes ago
                        </div>
                    </div>
                </div>
            </a>
            <a class="dropdown-item" href="#">
                <div class="notification-content">
                    <div class="icon">
                        <i class="fa fa-exclamation text-info border border-info"></i>
                    </div>
                    <div class="content">
                        <div class="notification-detail">Lorem ipsum dolor sit amet consectetur adipisicing elit. In totam explicabo</div>
                        <div class="notification-time">
                            Today
                        </div>
                    </div>
                </div>
            </a>
            <a class="dropdown-item" href="#">
                <div class="notification-content">
                    <div class="icon">
                        <i class="fa fa-exclamation-triangle text-warning border border-warning"></i>
                    </div>
                    <div class="content">
                        <div class="notification-detail">Lorem ipsum dolor sit amet consectetur adipisicing elit. In totam explicabo</div>
                        <div class="notification-time">
                            Yesterday
                        </div>
                    </div>
                </div>
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-center" href="#">View all notifications</a>
        </div>
    </div> -->
    
    <div class="dropdown">
        <a href="#" class="" id="dropdownMenuMessage" rel="tooltip"  title="Settings" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-cogs"></i>
            <!-- <span class="badge-sonar"></span> -->
        </a>
        <div class="dropdown-menu rounded-0" aria-labelledby="dropdownMenuMessage">
            <div class="w-100 dropdown-item">
                <div class="row">
                    <div class="form-group col-md-12">
                        <h6>Sidebar Themes</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-12">
                        <a href="javascript:void(0)"
                        data-url="{{route('my.settings',['locale'=>Auth::user()->settings('locale','en')])}}" data-bg="bg1" class="theme theme-bg"></a>
                        <a href="javascript:void(0)"
                        data-url="{{route('my.settings',['locale'=>Auth::user()->settings('locale','en')])}}" data-bg="bg2" class="theme theme-bg"></a>
                        <a href="javascript:void(0)"
                        data-url="{{route('my.settings',['locale'=>Auth::user()->settings('locale','en')])}}" data-bg="bg3" class="theme theme-bg"></a>
                        <a href="javascript:void(0)"
                        data-url="{{route('my.settings',['locale'=>Auth::user()->settings('locale','en')])}}" data-bg="bg4" class="theme theme-bg"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="dropdown">
        <a href="#" class="" id="dropdownMenuMessage" rel="tooltip"  title="Links" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-external-link"></i>
            <!-- <span class="badge badge-pill badge-success notification">7</span> -->
        </a>
        <div class="dropdown-menu messages rounded-0" aria-labelledby="dropdownMenuMessage">
            <div class="messages-header">
                <h6>Links </h6>
            </div>
            <a class="dropdown-item" href="{{ route('home',['locale'=>Auth::user()->settings('locale','en')]) }}">
                <i class="fa fa-home mr-2"></i> {{ __('Home') }}
            </a>
            <a class="dropdown-item" href="{{ route('my.profile',['locale'=>Auth::user()->settings('locale','en')]) }}">
                <i class="fa fa-user-circle mr-2"></i> {{ __('My Profile') }}
            </a>
            <a class="dropdown-item" href="{{ route('my.wallet',['locale'=>Auth::user()->settings('locale','en')]) }}">
                <i class="fa fa-credit-card mr-2"></i> {{ __('My Wallets') }}
            </a>
            <a class="dropdown-item" href="{{ route('my.referral',['locale'=>Auth::user()->settings('locale','en')]) }}">
                <i class="fa fa-users mr-2"></i> {{ __('My Referrals') }}
            </a>
            <a class="dropdown-item" href="{{ route('orderTab',['locale'=>Auth::user()->settings('locale','en'), 'tab' => 'order-history']) }}">
                <i class="fa fa-cube mr-2"></i> {{ __('Orders History') }}
            </a>
            <a class="dropdown-item" href="{{ route('exchange',['locale'=>Auth::user()->settings('locale','en')]) }}">
                <i class="fa fa-exchange mr-2"></i> {{ __('Exchange') }}
            </a>
            <!-- <a class="dropdown-item" href="#">
                <div class="message-content">
                    <div class="pic">
                        <img src="assets/img/user.jpg" alt="">
                    </div>
                    <div class="content">
                        <div class="message-title">
                            <strong> Jhon doe</strong>
                        </div>
                        <div class="message-detail">Lorem ipsum dolor sit amet consectetur adipisicing elit. In totam explicabo</div>
                    </div>
                </div>
            </a> -->
            <!-- <div class="dropdown-divider"></div> -->
            <!-- <a class="dropdown-item text-center" href="#">View all messages</a> -->
        </div>
    </div>
</div>