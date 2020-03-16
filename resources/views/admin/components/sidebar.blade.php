<nav id="sidebar" class="sidebar-wrapper">
    <div class="sidebar-content">
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{asset('img/logo.png')}}" class="w-50 img-fluid"> 
                <span class="fa-1x">Admin</span>
            </a>
            <div id="close-sidebar">
                <i class="fa fa-outdent"></i>
            </div>
        </div>
        <div class="sidebar-header">
            <div class="user-pic">
                <img class="img-responsive img-rounded" src="{{ asset('img/user.jpg') }}" alt="User picture">
            </div>
            <div class="user-info">
                <span class="user-name">
                    <strong>{{ str_limit(auth()->user()->name,16) }}</strong>
                </span>
                <span class="user-role">{{ucwords(str_limit(auth()->user()->allRoles(),23)) }}</span>
                <span class="user-status">
                    <i class="fa fa-circle"></i>
                    <span>Online</span>
                </span>
            </div>
        </div>
        <!-- sidebar-header  -->
        <!-- <div class="sidebar-search">
            <div>
                <div class="input-group">
                    <input type="text" class="form-control search-menu" placeholder="Search...">
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- sidebar-search  -->
        <div class="sidebar-menu">
            <ul>
            @foreach($buzzexAdminMenus->menus as $section)
                @if($section->items)
                    @if(auth()->user()->hasAnyRole($section->roles))
                        <li class="header-menu">
                            <span>{{ $section->section }}</span>
                        </li>
                        @foreach($section->items as $menu)
                            @if(auth()->user()->hasAnyRole($menu->roles))
                                @if(count($menu->submenu) > 0)
                                    <li class="sidebar-dropdown">
                                        <a href="javascript:void(0)">
                                            <i class="fa {{$menu->icon}}"></i>
                                            <span>{{ $menu->title }}</span>
                                        </a>
                                        <div class="sidebar-submenu">
                                            <ul>
                                                @foreach($menu->submenu as $submenu)
                                                    @if(auth()->user()->hasAnyRole($submenu->roles))
                                                    <li>
                                                        <a href="{{ url($submenu->url) }} ">
                                                            <i class="fa {{$submenu->icon}}"></i>
                                                            {{ $submenu->title}}
                                                        </a>
                                                    </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </li>
                                @else
                                    <li>
                                        <a href="{{ url($menu->url) }}">
                                            <i class="fa {{$menu->icon}}"></i>
                                            <span>{{$menu->title}}</span>
                                        </a>
                                    </li>
                                @endif
                            @endif
                        @endforeach
                    @endif
                @endif
            @endforeach
            </ul>
        </div>
        <!-- sidebar-menu  -->
    </div>
    <!-- sidebar-content  -->
    
    @include('admin.components.setting')
</nav>