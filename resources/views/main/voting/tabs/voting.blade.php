<div class="tab-pane fade show active" id="voting" role="tabpanel" aria-labelledby="voting-tab">
	<div class="row">
		<div class="col-12 py-3">
			<small> {{ $data['counts'] }} {{ __('projects in total') }}</small>
		</div>
	</div>
	<div class="card-columns my-3">
		
	@forelse($data['data'] as $index =>  $coin)

		@include('main.voting.components.vote-card', ['coin' => $coin])
		
	@empty
		<p>No project yet.</p>
	@endforelse

		<div class="clearfix"></div>
	</div>
	
	@if($data['data']->total() > $data['data']->perPage())
	<div class="d-flex justify-content-center">
		{!! $data['data']->render() !!}
	</div>
	@endif
</div>


@section('scripts')
<script type="text/javascript">
	var facebook_url = "{{ config('social.facebook') }}";
	var linkedin_url = "{{ config('social.linkedin') }}";
	var twitter_url = "{{ config('social.twitter') }}";
	var telegram_url = "{{ config('social.telegram') }}";

	var share_text ="{{ __('I just voted for %%COIN%% to be listed on @Buzzexio and can earn 500 BZX Coins for doing so! Vote too for free on https://buzzex.io/vote') }}";

	$(document).ready(function() {

		window.Echo.channel('CoinVotingChannel')
	        .listen('CoinProjectIsVotedEvent', function(project) {
	        	console.log(project)
	            $('.card.card-'+project.id).find('.num-votes span').text(project.num_votes);
	        });

	    $(document).on('click', '.btn-share-link', function(e) {
	    	e.preventDefault();

	    	var social = $(this).data('id');
	    	var url = "";
	    	var coin = $(this).parents('div.d-block').data('coin');

	    	if (social == 'twitter') { 
	    		url = twitter_url +encodeURIComponent(window.location.href)+'&text=' + encodeURIComponent(share_text.replace("%%COIN%%",coin));
	    	} else if (social == 'facebook') {
	    		url = facebook_url + encodeURIComponent(window.location.href);
	    	} else if (social == 'linkedin') {
	    		url =  linkedin_url +window.location.href +'&title='+ encodeURIComponent('Buzzex.io')+'&summary='+ encodeURIComponent('Exchange the future');
	    	} else if (social == 'telegram') {
	    		url = telegram_url +encodeURIComponent(window.location.href)+'&text=' + encodeURIComponent(share_text.replace("%%COIN%%",coin));;
	    	}
	    	window.Templates.popupCenter(url,coin +' Share', 500, 500);
	    });

		$(document).on('click','.card .btn-promote',function(e){
			e.preventDefault();
			var coin = $(this).parents('.card').data('coin');
			swal({
			  	title: '<h5 class="my-5">Invite Friends to Vote</h5>',
			  	html : '<strong>The more people that vote, the higher your chances to win and the faster your 500 BZX will be released!</strong><div class="d-block my-5" data-coin="'+coin+'">'+
			  	'<button class="btn-share-link btn mx-2" data-id="twitter"><span class="fa fa-twitter"></span></button>'+
			  	'<button class="btn-share-link btn mx-2" data-id="facebook"><span class="fa fa-facebook"></span></button>'+
			  	'<button class="btn-share-link btn mx-2" data-id="linkedin"><span class="fa fa-linkedin"></span></button>'+
			  	'<button class="btn-share-link btn mx-2" data-id="telegram"><span class="fa fa-telegram"></span></button></div>',
			  	showCloseButton: true,
			  	showCancelButton: true,
			  	showConfirmButton: false,
			  	cancelButtonClass: 'btn btn-dark rounded-0 px-5',
				buttonsStyling: false,
			  	focusCancel: true,
			  	cancelButtonText: 'Close'
			})
		})

		$(document).on('click','.card .btn-vote',function(e){
			e.preventDefault();
			var coin = $(this).parents('.card').data('coin');
			var button = $(this);
				button.btnProcessing('Voting...');

			confirmation('Vote for '+ coin +' ?', function() {

				$.post("{{ route('vote.store') }}", {
					coin : coin,
					action : 1
				})
				.done(function(data){
					toast({type:'success' , title : data.flash_message })
					button.btnReset().btnActive();
				})
				.fail(function (xhr, status, error) {
	              	alert({
	                  	text: window.Templates.getXHRMessage(xhr),
	                  	html: window.Templates.getXHRErrors(xhr),
	                  	type: 'error'
	              	});
	              	button.btnReset();
				});
			}, function(){
				button.btnReset();
			})
		})
	})
</script>
@endsection