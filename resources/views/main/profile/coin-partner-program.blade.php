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
                <div class="row header-block-result">
                    <div class="w-75 mx-auto d-flex">
                        <h1 class="text-center mx-auto align-self-center header-text">{{ __($item->symbol.' TRADING COMPETITION') }}</h1>
                    </div>
                    <div class="w-75 mx-auto d-flex">
                        <h5 class="lead text-center">{!! __('Earn BZX Coins For Your Coin Project, And Get A Chance  To Earn Daily Dividends Paid In BTC, ETH and USDT!') !!}</h5>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">                                        
                                        
                        <div class="row mt-5">
    
                                <div class="col-md-6">
                                <h4 class="header-title mb-3">Information</h4>
                                    <p class="text-muted m-b-20 font-13">
                                        <i class="fa fa-arrow-right"></i> Share listing of your coin on social media and homepage as instructed in invitation mail from applications @<code class="highlighter-rouge">buzzex.io</code>
                                    </p>

                                     <p class="text-muted m-b-20 font-13">
                                        <i class="fa fa-arrow-right"></i> Get trading going for your coin and reach these trading volume milestones
                                    </p>
                                    <ul class="list-unstyled list-inline ml-3 mt-3">
                                        <li><i class="fa fa-long-arrow-right "></i> {{ __('1 BTC volume - 20,000 BZX') }}</li>
                                        <li><i class="fa fa-long-arrow-right "></i> {{ __('10 BTC volume - 30,000 BZX') }}</li>
                                        <li><i class="fa fa-long-arrow-right "></i> {{ __('50 BTC volume - 40,000 BZX') }}</li>
                                        <li><i class="fa fa-long-arrow-right "></i> {{ __('100 BTC volume - 50,000 BZX') }}</li>
                                        <li><i class="fa fa-long-arrow-right "></i> {{ __('200 BTC volume - 100,000 BZX') }}</li>
                                        <li><i class="fa fa-long-arrow-right "></i> {{ __('500 BTC volume - 200,000 BZX') }}</li>
                                        <li><i class="fa fa-long-arrow-right "></i> {{ __('1000 BTC volume - 1,000,000 BZX') }}</li>
                                    </ul>

                                    <div class="alert alert-secondary mt-5" role="alert">
                                        <b>Note: </b>{{__('Competition Ends When Next MileStone Is Reached!')}}
                                    </div>      
                                
                                </div>

                                <div class="col-md-6">

                                    <h4 class="header-title mb-3">{{__('Current '.strtoupper($item->symbol).' milestone')}}</h4>
                                    
                                        @php
                                            $milestone_volume = 0;
                                        @endphp

                                        @if(!$transaction->amount_btc) 
                                            <div class="alert alert-warning " role="alert">
                                                <b>Note: </b>{{__('No transactions for current milestone, be the first to trade now!')}}
                                            </div>                                        
                                        @endif
                                        
                                        @foreach(milestoneOptions() as $mile => $milestone)
                                        <p>
                                            
                                            @if($bar_width[$mile] > 0)
                                            <span class="pull-left"><b>{{__('Trading Volume')}}</b> 
                                                {{ $current_competition->volume == $milestone->volume ? $transaction->amount_btc:$milestone['volume'] }} BTC
                                            </span> 
                                            <span class="pull-right">{{$milestone['volume']}} BTC {{__('in total')}}</span>
                                            <div style="clear:both;"></div>
                                            <div class="progress competition-bar">
                                                <div class="progress-bar" role="progressbar" style="width: {{$bar_width[$mile]}}%" aria-valuenow="{{$bar_width[$mile]}}" aria-valuemin="0" aria-valuemax="{{$milestone['volume']}}"></div>
                                            </div>
                                            @else
                                            <span class="pull-left">{{__('pending...')}}</span> 
                                            <span class="pull-right">{{$milestone['volume']}} BTC {{__('in total')}}</span>
                                            <div style="clear:both;"></div>
                                            <div class="progress competition-bar" style="background: gray !important;">
                                                <div class="progress-bar"  role="progressbar"></div>
                                            </div>
                                            @endif
                                        </p>
                                        @php                    
                                            $milestone_volume = $milestone['volume'];
                                        @endphp
                                        @endforeach                                  

                                </div>
                        
                        </div>       
                                                       
                        <!-- end row -->                           

                        <div class="card border-top mt-5">
                            <div class="card-body">                                                
                               <h4 class="header-title text-center mb-3 ">{{__('Available Markets')}}</h4>

                                <div class="row ">                
                                    <div class="col-lg-6 col-xl-4">                
                                        <div class="card d-block">
                                             <div class="card-body">
                                                <h5 class="card-title text-center">{{strtoupper($item->symbol)}}/BTC</h5>
                                                <a class="btn btn-outline-info btn-block"  href="{{ route('exchange', ['base' => 'BTC', 'target' => strtoupper($item->symbol)]) }}" target="_new">{{__('Trade Now!')}}</a>
                                            </div> 
                                        </div>                
                                    </div><!-- end col --> 

                                    <div class="col-lg-6 col-xl-4">                
                                        <div class="card d-block">
                                             <div class="card-body">
                                                <h5 class="card-title text-center">{{strtoupper($item->symbol)}}/ETH</h5>
                                                <a class="btn btn-outline-info btn-block"  href="{{ route('exchange', ['base' => 'ETH', 'target' => strtoupper($item->symbol)]) }}" target="_new">{{__('Trade Now!')}}</a>
                                            </div>
                                        </div>                
                                    </div><!-- end col --> 

                                    <div class="col-lg-6 col-xl-4">                
                                        <div class="card d-block">
                                             <div class="card-body">
                                                <h5 class="card-title text-center">{{strtoupper($item->symbol)}}/USDT</h5>
                                                <a class="btn btn-outline-info btn-block"  href="{{ route('exchange', ['base' => 'USDT', 'target' => strtoupper($item->symbol)]) }}" target="_new">{{__('Trade Now!')}}</a>
                                            </div> 
                                        </div>                
                                    </div><!-- end col --> 
                                </div>
                                <!-- end row -->   
                            </div> <!-- end card-body-->
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