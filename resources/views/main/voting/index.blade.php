@extends('masters.app')

@section('styles')
<style type="text/css">
	#vote-content {
		margin-top: -3rem;
		z-index: 5;
		position: relative;
	}
</style>
@endsection

@section('content')

@include('partials.sections.header-banner-competition',[
		'header_text' => __('Join any coin\'s trading competition on Buzzex and win a chance to earn daily crypto forever!') . '<br><br>',
		'header_text2' => __('100 million BZX prize money ($13 million value) + lifelong daily BTC, ETH or USDT earnings'),
		'subheader' => '',
		'guestsubheader' => '',
		'class' => 'list'
	])

<div class="card container mb-5 pl-5 pr-5 py-5" id="vote-content">
	<nav>
		<div class="nav nav-tabs" id="nav-tab" role="tablist">
			<a class="rounded-0 nav-item nav-link {{ active_query(route('vote.index', ['tab'=>'vote']))}}" href="{{ route('vote.index', ['tab' => 'vote']) }}">
				{{ __('Voting') }}
			</a>
			<a class="rounded-0 nav-item nav-link {{ active_query(route('vote.index', ['tab'=>'to-be-listed']))}}" href="{{ route('vote.index', ['tab' => 'to-be-listed']) }}">
				{{ __('To be listed') }}
			</a>
			<a class="rounded-0 nav-item nav-link {{ active_query(route('vote.index', ['tab'=>'listed']))}}" href="{{ route('vote.index', ['tab' => 'listed']) }}" >
				{{ __('Listed') }}
			</a>
		</div>
	</nav>

	<div class="tab-content" id="nav-tabContent">
		
		@includeWhen($data['tab'] == "vote", 'main.voting.tabs.voting')
		
		@includeWhen($data['tab'] == "to-be-listed", 'main.voting.tabs.to-be-listed')
		
		@includeWhen($data['tab'] == "listed", 'main.voting.tabs.listed')
		
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		$('body').on('keyup', 'input.coin-search', function(e) {
			var val = $(this).val();

			if(e.keyCode == 13)
				search(val);
		}).on('change', 'input.coin-search', function() {
			var val = $(this).val();
				search(val);
		});

		$('body').on('click', 'button.btn-searchbox', function() {
			$('body').find('input.coin-search').trigger('change');
		});
	});

	function search(coin = '') {
		$.get("{{ route('vote.index', ['tab' => 'listed']) }}", { coin:coin })
		.done(function(data) {
			$('div#listed').html(data);
		})
		.fail(function(xhr, status, error) {
			alert({
				title: window.Templates.getXHRMessage(xhr),
				html: window.Templates.getXHRErrors(xhr),
				type: 'error'
			});
		});
	}
</script>
@endpush