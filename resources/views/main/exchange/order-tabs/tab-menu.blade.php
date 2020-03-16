<div class="card-header">
    <div class="row">
        <div class="col-12 col-md-12 col-lg-6 mt-2">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item flex-fill">
                    <a class="text-center nav-link rounded-0 {{ $tab == 'current-order' ? 'buzzex-active active disabled' : '' }} "
                    href="{{route('orderTab',['tab' => 'current-order'])}}">{{__('Current Orders')}}</a>
                </li>
                <li class="nav-item flex-fill">
                    <a class="text-center nav-link rounded-0 {{ $tab == 'order-history' ? 'buzzex-active active disabled' : '' }} "
                    href="{{route('orderTab',['tab' => 'order-history'])}}">{{__('Order History')}}</a>
                </li>
                <li class="nav-item flex-fill">
                    <a class="text-center nav-link rounded-0 {{ $tab == 'latest-execution' ? 'buzzex-active active disabled' : '' }} "
                    href="{{route('orderTab',['tab' => 'latest-execution'])}}">{{__('Execution History')}}</a>
                </li>
            </ul>
            
        </div>
    </div>
</div>