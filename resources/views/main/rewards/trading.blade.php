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
            		<h5 class="align-self-center">{{ __('Trading Competition Rewards') }}</h5>
                    @coinselect(['class' => 'rounded-0 w-25', 'includeAll' => true, 'id' => 'select-coin-rewards', 'selected' => request()->has('coin') ? request()->get('coin') : 'all'])
                    @endcoinselect
            	</div>

                <div class="card-block border py-0">
                    <div class="exchange-load-mask">
                        <span class='fa fa-spin fa-spinner text-info mr-2' style='font-size: 2.5rem;'></span><span class='text-light'>Loading.<br> <small style='font-size:.9rem;'>Please wait...</small></span>
                    </div>
                    <div id="rewards-list">
                        
                    </div>
                </div>
                <div class="card-block my-1 alert alert-secondary">
                    <p class="py-0 my-0"><sup class="font-weight-bold text-danger">*</sup> {{ __('The BTC trade value is dependent on the index value of BTC at the time of trade fulfillment -- not the Buzzex BTC price of actual fulfilled trade')}}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts') 
    <script type="text/javascript">
        var claimUrl = "{{ route('milestone.claim') }}";

        function getMilestoneRewardList(coin) {
            $('.exchange-load-mask').removeClass('d-none');
            $.get("{{route('milestone.list')}}", { coin : coin })
            .done(function(response){
                $('#rewards-list').html(response.html);
                $('.exchange-load-mask').addClass('d-none');
            })
            .fail(function(argument) {
                $('.exchange-load-mask').addClass('d-none');
            })
        }

        $(document).ready(function(){
            
            getMilestoneRewardList($('#select-coin-rewards').val()); 

            $(document).on('change', '#select-coin-rewards', function(e){
                var coin = $(this).val();
                getMilestoneRewardList(coin); 
            });  

            $(document).on('click', '.btn-claim-rewards:not(.processing)', function(e){
                var button = $(this);
                var id = button.data('id');
                button.btnProcessing('.Claiming...');
                confirmation('Claim rewards?', function(e){
                    $.post(claimUrl, { id : id })
                    .done(function(response){
                        toast({
                            type: 'success',
                            text: 'Successfully claim rewards.',
                            timer: 1000
                        }).then(function(){
                            window.location.reload();
                        })
                        button.btnReset();
                    })
                    .fail(function(xhr, status, error) {
                        alert({
                            type: 'error',
                            text: xhr.responseJSON.message 
                        });
                        button.btnReset();
                    });
                }, function(){
                    button.btnReset();
                })
            });
        });
    </script> 
@endsection