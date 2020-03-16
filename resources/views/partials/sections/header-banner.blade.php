@push('styles')
<style type="text/css">
    .header-block { 
        height: 300px;
        min-height:  300px !important; 
        overflow: hidden;
        background-image: url("{{asset('/img/laptop-bg.png')}}");
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
        background-color: #000000;
    }
    @media (max-width: 767px){
        .header-text {
            font-size: 1.5rem;
        }
    }
</style>
@endpush

<div class="row m-0 header-block">
    <div class="w-75 mx-auto d-flex">
        <h1 class="text-center mx-auto align-self-center header-text">{{ $header_text ?? '' }}</h1>
    </div>
    <div class="w-75 mx-auto text-center">
        {!! $subheader ?? '' !!}
    </div>
</div>