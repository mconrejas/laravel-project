@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-md-3">
            @include('main.profile.component.side-menu')
        </div>
        <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0 pb-5 px-3" id="card-api">
            	<div class="card-body mt-3 d-flex justify-content-between">
            		<h5 class="align-self-center">{{ __('Trans-Fee Mining Rewards') }}</h5>
                    <a href="{{ $buzzexLinks->mining->url }}" target="_blank" class="btn btn-link lead"><span class="fa fa-question"></span> {{ __('Learn more')}}.</a>
            	</div>
                <div class="card-block">
                    <div class="card-group w-100">
                        <div class="card card-body py-5 border-secondary border">
                            <h6 class="text-secondary align-self-center">{{__('Total Number of Trades Fulfilled')}}</h6>
                            <span class="text-warning align-self-center">{{ $total_trades }}</span>
                        </div>
                        <div class="card card-body py-5 border-secondary border">
                            <h6 class="text-secondary align-self-center">{{__('Total Worth in USD')}}</h6>
                            <span class="text-warning align-self-center">{{ currency($total_usd) }}</span>
                        </div>
                        <div class="card card-body py-5 border-secondary border">
                            <h6 class="text-secondary align-self-center">{{__('Total Worth in BTC')}}</h6>
                            <span class="text-warning align-self-center">{{ currency($total_btc) }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row border py-3">
                        <div class="col-12 col-sm-6 col-md-9">
                            <div>{{ __('Total') }} : <span class="font-weight-bold">{{ currency($total_rewards) }}</span> {{$rewards_in}}</div>
                            <div>{{ __('Claimed') }} : <span class="font-weight-bold">{{ currency($total_claimed) }}</span> {{$rewards_in}}</div>
                            <div>{{ __('Claimable') }} : <span class="font-weight-bold">{{ currency($claimable) }}</span> {{$rewards_in}}</div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3 d-flex  justify-content-end">
                            @if($claiming_rewards_available)
                                @if($claimable > 0)
                                <button style="max-height: 40px;" class="btn-claim d-flex btn btn-outline-success align-items-center" type="button" >{{ __('Claim Rewards') }}</button>
                                @else
                                <button style="max-height: 40px;" class="d-flex btn btn-outline-secondary disabled align-items-center" type="button">{{ __('Claim Rewards') }}</button>
                                @endif
                            @else
                                <span style="max-height: 40px;" class="d-flex align-items-sm-center btn btn-outline-secondary disabled">{{ __('Claiming Coming Soon') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @if($claiming_rewards_available)
    <script type="text/javascript">
        $(document).ready(function(){
            $(document).on('click','.btn-claim:not(.processing)', function(e){
                var btn = $(this);
                    btn.btnProcessing('Claiming...');
                    confirmation('Claim rewards?', function(e){
                        $.post('{{ route("rewards.claim")}}', {})
                        .done(function(response){
                            toast({
                                type: 'success',
                                text: 'Successfully claim rewards.',
                                timer: 1000
                            }).then(function(){
                                window.location.reload();
                            })
                            btn.btnReset();
                        })
                        .fail(function(xhr, status, error){
                            alert({
                                type: 'error',
                                text: xhr.responseJSON.message 
                            });
                            btn.btnReset();
                        });
                    }, function(){
                        btn.btnReset();
                    })
            });
        });
    </script>
    @endif
@endsection