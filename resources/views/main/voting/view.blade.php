@extends('masters.app')

@section('styles')
<style type="text/css">
	#vote-content {
		z-index: 5;
		position: relative;
	}
	.competition-container {
		/*background: #ffffff;*/
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
	.card{
		border: none !important;
	}
	.card-body{
		padding:0 !important;
	}
</style>
@endsection

@section('content')

@include('partials.sections.header-banner-competition',[
		'header_text' => strtoupper($item->symbol) . " TRADING COMPETITION",
		'header_text2' => 'Enter now and get a chance to earn life long daily BTC, ETH, or USDT!',
		'subheader' => '',
		'header_signup_button' => '<a class="text-center text-lg-center nav-link btn btn-gradient-yellow mx-sm-auto px-3" style="max-width: 200px;" href="' . route('register') . '">' . __("Sign Up") . '</a>',
		'header_social_links' => '<ul class="list-unstyled list-inline">
								<li class="list-inline-item">
				                    <a target="_blank" href="' . $buzzexLinks->facebook->url . '" rel="tooltip" title="' . $buzzexLinks->facebook->label . '" class="btn-floating btn-lg rgba-white-slight mx-1">
				                        <i class="fa fa-facebook"></i>
				                    </a>
				                </li>
				                <li class="list-inline-item">
				                    <a target="_blank" href="' . $buzzexLinks->twitter->url . '" rel="tooltip" title="' . $buzzexLinks->twitter->label . '" class="btn-floating btn-lg rgba-white-slight mx-1">
				                        <i class="fa fa-twitter"></i>
				                    </a>
				                </li>
				                <li class="list-inline-item">
				                    <a target="_blank" href="' . $buzzexLinks->telegram->url . '" rel="tooltip" title="' . $buzzexLinks->telegram->label . '" class="btn-floating btn-lg rgba-white-slight mx-1">
				                        <i class="fa fa-telegram"></i>
				                    </a>
				                </li>
				                <li class="list-inline-item">
				                    <a target="_blank" href="' . $buzzexLinks->linkedin->url . '" rel="tooltip" title="' . $buzzexLinks->linkedin->label . '" class="btn-floating btn-lg rgba-white-slight mx-1">
				                        <i class="fa fa-linkedin"></i>
				                    </a>
				                </li>
				            </ul>',
		'class' => 'result',
		'icon' => $item->icon
	])

<div class="container-fluid competition-container container">
	<div class=" card pl-5 pr-5 py-5" id="vote-content">

		<div class="mb-5">	
			<center>
			<h3>{{__('Start trading and help this coin project earn rewards while you do the same!')}}</h3>
			
			<p class="mb-5">
 
			</p>
			</center>
				@php
					$milestone_volume = 0;
				@endphp

				<!-- START -->
				<div class="row mt-5 border-top mt-5  pt-4">
	            	<div class="col-md-6">
	            		<h4 class="header-title ">{{__('Earnings')}}</h4>
						<ul class="list-unstyled list-inline ml-3 mt-3">
			                <li><i class="fa fa-long-arrow-right "></i> 1st 40,000 BZX</li>
			                <li><i class="fa fa-long-arrow-right "></i> 2nd 30,000 BZX</li>
			                <li><i class="fa fa-long-arrow-right "></i> 3rd 20,000 BZX</li>
			                <li><i class="fa fa-long-arrow-right "></i> {{ __('Places')}} 4-10: 1,000 BZX</li>
			            </ul>

			            <div class="alert alert-secondary" role="alert">
                            <b>{{__('Note:')}} </b>{{__('The top 3 traders of each milestone will be able to start earning BTC, ETH and USDT forever!')}}
                        </div>

                        <h4 class="header-title mt-5">{{__('Trading Rules')}}</h4>

                        <ul class="list-unstyled list-inline ml-3 mt-3">
			                <li><i class="fa fa-long-arrow-right "></i> {{ __('All buys and sells are counted')}}</li>
			                <li><i class="fa fa-long-arrow-right "></i> {{ __('Competition end with the last trade to reach the milestone')}}</li>
			                <li><i class="fa fa-long-arrow-right "></i> {{ __('After the competition ends, your volume goes back to zero')}}</li>
			                <li><i class="fa fa-long-arrow-right "></i> {{ __('Winners will get their BZX in their account within 48 hours of reaching the milestone')}}</li>
			                <li><i class="fa fa-long-arrow-right "></i> {{ __('The BTC trade value is dependent on the index value of BTC at the time of trade fulfillment -- not the Buzzex BTC price of actual fulfilled trade')}}.</li>
			            </ul>
			            <div class="alert alert-secondary" role="alert">
			            	<b>{{__('Note:')}} </b>{{__('Competition Ends When Next MileStone Is Reached')}}
			            </div>
		            </div>

	                <div class="col-md-6">
	                	@if(!$transaction->amount_btc)
							<div class="alert alert-warning" role="alert">
		                        <b>{{__('Note:')}} </b>{{__('No transactions for current milestone, be the first to trade now!')}}
		                    </div>
						@endif
						<div class="row">
			                <h5 class="header-title mb-3 col-5">{{__('Current '.strtoupper($item->symbol).' Milestone')}}</h5>
							<h6 class="header-title pull-right col-7 text-right">
								<span>{{__('Trading Volume')}} : </span>
								<span class="text-secondary">{{ $transaction->amount_btc ?? 0 }} BTC</span>
							</h6> 
						</div>

		                @foreach(milestoneOptions() as $mile => $milestone)
						<p>
							
							<span class="pull-left">{{__('Milestone')}} {{$mile+1}}</span>
							<span class="pull-right">{{$milestone['volume']}} BTC {{__('in total')}}</span>
							@if($bar_width[$mile] > 0)
							<div style="clear:both;"></div>
							<div class="progress competition-bar">
				  				<div class="progress-bar" role="progressbar" style="width: {{$bar_width[$mile]}}%" aria-valuenow="{{$bar_width[$mile]}}" aria-valuemin="0" aria-valuemax="{{$milestone['volume']}}"></div>
							</div>
							@else
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

	             <h4 class="header-title mb-3 text-center mt-5">{{__('All Traders Rankings')}}</h4>
		                <table class="table table-bordered">
							<thead class="text-center">
								<tr>
									<th>{{__('Ranking')}}</th>
									<th>{{__('Email')}}</th>
									<th>{{__('Trading Value')}} (BTC)</th>
								</tr>
							</thead>
							<tbody>
							@forelse($users as $key => $user)
								@if($key < 20)
								<tr>
									<td class="text-left">{{ $key + 1 }}&nbsp;&nbsp;&nbsp;
									@if($key < 3) 
										<span class="fa fa-star text-warning"></span> 
									@endif
									</td>
									<td style="text-align:center">{{ $user->email_blured }}</td>
									<td style="text-align:right">{{ $user->volume }}</td>
								</tr>
								@endif
							@empty
								<tr>
									<td colspan="3" class="text-center">{{__('No trade for this coin yet')}}</th>
								</tr>
							@endforelse
							</tbody>
						</table>


				<!-- END -->
			
			<p>
				



			</p>
		</div>

		
		<div class="row border-top"></div>
		<h4 class="header-title text-center mb-3 mt-3">{{__('Available Markets')}}</h4>
		<div class="card border-top mt-5">
	        <div class="card-body">                                              
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
@endsection

@push('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		$('#getting-started').countdown('2019/09/28', function(event) {
		    $(this).html(event.strftime('%w weeks %d days %H:%M:%S'));
		});
	});
</script>
@endpush
