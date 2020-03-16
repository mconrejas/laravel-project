<div class="list-group side-menu sticky-top d-none d-md-none d-lg-block">
    <a class="{{ active_route('my.wallet') }} collapse-trigger list-group-item list-group-item-action flex-column align-items-start  rounded-0" data-toggle="collapse" href="#accounts-menu-collapse" role="button" aria-expanded="false" aria-controls="accounts-menu-collapse">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1"><span class="fa fa-address-card fa-fw text-secondary"></span> {{ __('Account') }}</h5>
            <span class="toggle-arrow fa fa-angle-up font-weight-bold float-right"></span>
        </div>
    </a>
    <div class="collapse show" id="accounts-menu-collapse">
        <ul class="list-group m-0 p-0">
            <li class="border-bottom-0 list-group-item pl-5 d-flex justify-content-between align-items-center {{ active_route('my.profile') }}">
                <a href="{{route('my.profile')}}" class="text-light-on-dark">{{ __('My Info') }}</a>
            </li>
            
            <li class="border-bottom-0 {{ active_route('my.security') }} border-top-0 list-group-item pl-5 d-flex justify-content-between align-items-center">
                <a href="{{route('my.security')}}" class="text-light-on-dark">{{ __('Security') }}</a>
            </li>
            <li class="border-bottom-0 {{ active_route('apisetting.index') }} border-top-0 list-group-item pl-5 d-flex justify-content-between align-items-center">
                <a href="{{route('apisetting.index')}}" class="text-light-on-dark">{{ __('API') }}</a>
            </li>
        </ul>
    </div>
    @if(Auth::user()->settings()->get('is_coin_partner'))
    <a href="{{route('my.coinpartnerprogram')}}" class="{{ active_route('my.coinpartnerprogram') }} list-group-item list-group-item-action flex-column align-items-start rounded-0">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1"><span class="fa fa-handshake-o fa-fw"></span> {{ __('Coin Partner Program') }}</h5>
        </div>
    </a>
    @endif
    <a class="collapse-trigger list-group-item list-group-item-action flex-column align-items-start  rounded-0" data-toggle="collapse" href="#rewards-menu-collapse" role="button" aria-expanded="false" aria-controls="accounts-menu-collapse">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1"><span class="fa fa-gift fa-fw text-secondary"></span> {{ __('Events & Rewards') }}</h5>
            <span class="toggle-arrow fa fa-angle-up font-weight-bold float-right"></span>
        </div>
    </a>
    <div class="collapse show" id="rewards-menu-collapse">
        <ul class="list-group m-0 p-0">
            @if(parameter('claim_trading_competition_available', 1) == 1 )
            <li class="border-bottom-0 list-group-item pl-5 d-flex justify-content-between align-items-center {{ active_route('rewards.milestone') }}">
                <a href="{{route('rewards.milestone')}}" class="text-light-on-dark">{{ __('Trading Competition') }}</a>
            </li>
            @endif
            <li class="border-bottom-0 list-group-item pl-5 d-flex justify-content-between align-items-center {{ active_route('rewards.index') }}">
                <a href="{{route('rewards.index')}}" class="text-light-on-dark">{{ __('Trans-Fee Mining') }}</a>
            </li>
            @if(parameter('dividends_distribution_available', 0) == 1 )
            <li class="border-bottom-0 list-group-item pl-5 d-flex justify-content-between align-items-center {{ active_route('rewards.dividends') }}">
                <a href="{{route('rewards.dividends')}}" class="text-light-on-dark">{{ __('Dividends') }}</a>
            </li>
            @endif
        </ul>
    </div>
    <a href="{{route('notifications.message')}}" class="{{ active_route('notifications.message') }} list-group-item list-group-item-action flex-column align-items-start">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1"><span class="fa fa-envelope fa-fw"></span> {{ __('Message Center') }}</h5>
        </div>
    </a>
    <a href="{{route('my.referral')}}" class="{{ active_route('my.referral') }} list-group-item list-group-item-action flex-column align-items-start rounded-0">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1"><span class="fa fa-users fa-fw"></span> {{ __('Referral') }}</h5>
        </div>
    </a>
</div>


<div class="floating d-lg-none">
    <a class="floating-fab floating-btn-large buzzex-active" id="floatingBtn">
        <span class="fa text-light fa-fw fa-list"></span>
    </a>
    <ul class="floating-menu">
        <li>
            <a rel="tooltip" title="{{ __('My Info') }}" href="{{route('my.profile')}}" class="floating-fab floating-btn-sm btn-secondary scale-transition scale-out"><span class="fa fa-fw fa-user"></span></a>
        </li>
        <li>
            <a rel="tooltip" title="{{ __('Events & Rewards') }}" href="{{route('rewards.index')}}" class="floating-fab floating-btn-sm btn-secondary scale-transition scale-out"><span class="fa fa-fw fa-gift"></span></a>
        </li>
        <li>
            <a rel="tooltip" title="{{ __('My Info') }}" href="{{route('my.security')}}" class="floating-fab floating-btn-sm  btn-secondary scale-transition scale-out">
                <span class="fa fa-fw fa-shield"></span>
            </a>
        </li>
        <li rel="tooltip" title="{{ __('Referral') }}">
            <a  href="{{route('my.referral')}}" class="floating-fab floating-btn-sm  btn-secondary scale-transition scale-out">
                <span class="fa fa-fw fa-users"></span>
            </a>
        </li>
    </ul>
</div>


@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    $('#accounts-menu-collapse,#rewards-menu-collapse').on('hidden.bs.collapse', function(e) {
        var clicker = $(document).find("[href='#" + $(e.target).attr('id') + "']");
        clicker.find('.toggle-arrow').css({
            'transform': 'rotate(180deg)'
        });
    }).on('shown.bs.collapse', function(e) {
        var clicker = $(document).find("[href='#" + $(e.target).attr('id') + "']");
        clicker.find('.toggle-arrow').css({
            'transform': 'rotate(360deg)'
        });
    })
})

$(document).on('click','#floatingBtn',function() {
    $('.floating-btn-sm').toggleClass('scale-out');
    if (!$('.floating-card').hasClass('scale-out')) {
        $('.floating-card').toggleClass('scale-out');
    }
});


</script>
@endpush