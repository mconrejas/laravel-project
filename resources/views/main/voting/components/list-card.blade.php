<div class="card ">
	<div class="card-body btn-light-on-dark">
		<div class="media">
			<img class="d-flex mr-3" width="50" src="{{ $coin['iconUrl'] }}">
			<div class="media-body">
				<span class="pull-right">{{ currency_format($coin['volume'], 2) }}</span>

				<h5 class="mt-0 text-uppercase">{{ $coin['symbol'] }}</h5>
				<small>{{ $coin['name'] }}</small>
			</div>
		</div>
		<div class="text-center mt-3">
			<a href="{{ route('vote.view', ['symbol' => $coin['symbol']]) }}" class="text-center text-lg-center nav-link btn btn-outline btn-green mx-auto px-3 w-75">Join Competition</a>
		</div>
	</div>
</div>