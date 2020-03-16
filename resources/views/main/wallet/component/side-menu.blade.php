<div class="list-group side-menu sticky-top d-none d-md-none d-lg-block">
    <a href="{{route('my.wallet')}}" class="{{ active_route('my.wallet') }} list-group-item list-group-item-action flex-column align-items-start  rounded-0">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1"><span class="fa fa-credit-card"></span> {{ __('Wallet') }} </h5>
        </div>
    </a>
    <div class="collapse show" id="accounts-menu-collapse">
        <ul class="list-group m-0 p-0">
            <li class="border-bottom-0 list-group-item pl-5 d-flex justify-content-between align-items-center {{ active_route('my.wallet') }}">
                <a href="{{route('my.wallet')}}" class="text-light-on-dark">{{ __('Personal') }}</a>
            </li>
            <li class="border-bottom-0 {{ active_route('my.wallet-offline') }} border-top-0 list-group-item pl-5 d-flex justify-content-between align-items-center">
                <a href="{{route('my.wallet-offline')}}" class="text-light-on-dark">{{ __('Offline') }}</a>
            </li>
        </ul>
    </div>
    <a href="{{route('my.record',['type'=>'deposit'])}}" class="{{ active_query(route('my.record',['type'=>'deposit'])) }} list-group-item list-group-item-action flex-column align-items-start">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1"><span class="fa fa-cloud-download"></span>  {{ __('Deposit Record') }} </h5>
        </div>
    </a>
    <a href="{{route('my.record',['type'=>'withdrawal'])}}" class="{{ active_query(route('my.record',['type'=>'withdrawal'])) }} list-group-item list-group-item-action flex-column align-items-start">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1"><span class="fa fa-cloud-upload"></span> {{ __('Withdrawal Record') }}</h5>
        </div>
    </a>
    <a href="{{route('assets.my')}}" class="{{ active_route('assets.my') }} list-group-item list-group-item-action flex-column align-items-start rounded-0">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1"><span class="fa fa-clipboard"></span> {{ __('Asset History') }}</h5>
        </div>
    </a>
</div>

<div class="floating d-lg-none">
    <a class="floating-fab floating-btn-large buzzex-active" id="floatingBtn">
        <span class="fa text-light fa-fw fa-list"></span>
    </a>
    <ul class="floating-menu" style="bottom: 175px;">
        <li>
            <a rel="tooltip" data-placement="left" title="{{ __('Wallet') }}" href="{{route('my.wallet')}}" class="floating-fab floating-btn-sm btn-secondary scale-transition scale-out">
                <span class="fa fa-fw fa-credit-card"></span>
            </a>
        </li>
        <li>
            <a rel="tooltip" data-placement="left" title="{{ __('Deposit Record') }}" href="{{route('my.record',['type'=>'deposit'])}}" class="floating-fab floating-btn-sm  btn-secondary scale-transition scale-out">
                <span class="fa fa-fw fa-cloud-download"></span>
            </a>
        </li>
        <li rel="tooltip" data-placement="left" title="{{ __('Withdrawal Record') }}">
            <a  href="{{route('my.record',['type'=>'withdrawal'])}}" class="floating-fab floating-btn-sm  btn-secondary scale-transition scale-out">
                <span class="fa fa-fw fa-cloud-upload"></span>
            </a>
        </li>
        <li rel="tooltip" data-placement="left" title="{{ __('Asset History') }}">
            <a  href="{{route('assets.my')}}" class="floating-fab floating-btn-sm  btn-secondary scale-transition scale-out">
                <span class="fa fa-fw fa-clipboard"></span>
            </a>
        </li>
    </ul>
</div>

@push('scripts')
<script type="text/javascript">

    $(document).ready(function (argument) {
        $(document).on('click','#floatingBtn', function() {
            $('.floating-btn-sm').toggleClass('scale-out');
            if (!$('.floating-card').hasClass('scale-out')) {
                $('.floating-card').toggleClass('scale-out');
            }
        });
    })

</script>
@endpush