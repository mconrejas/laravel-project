
@extends('masters.app')
@section('styles')
<style type="text/css">
    .li-header{
        font-size: 16px;
    }

    .competition-container {
        background: #ffffff;
    }
    .competition-bar { 
        height: 30px; 
        background-image: linear-gradient(0deg,#192330,#113541 50%,#192330);
        border-radius: 28px;
    }
    .competition-bar .progress-bar {
        color: #000000 !important;
        background-image: linear-gradient(270deg, #22e6b8, #00c1ce);
        border-radius: 28px; 
        margin-left:-1px;
    }
    #getting-started {
        font-size: 18px;
    }
</style>
@endsection
@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-md-3">
            @include('main.profile.component.side-menu')
        </div>
        <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0" id="card-api">
                <div class="card-body mt-3 justify-content-between">
                    <h5 class="lead text-center">{{ __('Coin Partner Program') }}</h5>
                </div>
                <div class="card-body card-each-wrapper">
                    <h3 class="text-center">{!! __('Be a coin partner, and get a chance <br />to earn daily dividends paid in BTC, ETH and USDT!') !!}</h3>
                </div>


                <div class="card-body">
                    <div class="card-block p-4 d-flex border-top">
                        <div class="w-75 d-flex justify-content-md-start flex-wrap">
     
                        </div>
                    </div>


                </div>
            </div>

        </div>


    </div>
</div>

<style type="text/css">
    .card-block .form-label {
        display: inline-block;
        min-width: 200px;
    }

    #card-security .btn {
        min-width: 100px;
    }

</style>
@endsection

@section('scripts')
<script type="text/javascript">

</script>
@endsection