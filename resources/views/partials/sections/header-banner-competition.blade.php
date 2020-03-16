@push('styles')
<style type="text/css">
    .header-block-list {
        height: 500px;
        background: url(/images/bg-futuristics.jpg);
        background-size: 100% 500px;
        display: block;
        padding-top: 150px;
    }
    .header-block-list .header-text {
        color: #ffffff;
    }
    .header-block-result { 
        height: 400px;
        min-height: 300px !important;
        overflow: hidden;
        background-color: #212a35;
        padding: 45px;
    }
    .header-block-result .header-text {
        color: #ffc107 !important;
    }
    @media (max-width: 767px){
        .header-text {
            font-size: 1.5rem;
        }
    }
</style>
@endpush

<div class="row m-0 header-block-{{ $class }}">
    <div class="w-75 mx-auto d-flex">
        <h1 class="text-center mx-auto align-self-center header-text">{!! $header_text ?? '' !!}</h1>
    </div>
    @if(@$icon)
        <div class="w-100"> 
            <center>
                <img src="{{url('/storage/icons/'.$icon)}}" width="100" height="100">
            </center>
        </div>
    @endif
    <div class="w-75 mx-auto d-flex">
        <h3 class="text-center mx-auto align-self-center header-text">{!! $header_text2 ?? '' !!}</h3>
    </div>
    <div class="w-75 mx-auto text-center">
        {!! $subheader ?? '' !!}
    </div>
    <div class="w-75 mx-auto text-center">
        {!! $header_countdown_timer ?? '' !!}
    </div>
    @guest
    <div class="w-75 mx-auto text-center">
        {!! $header_signup_button ?? '' !!}
    </div>
    @endguest
    <div class="w-75 mx-auto text-center">
        {!! $header_social_links ?? '' !!}
    </div>
</div>