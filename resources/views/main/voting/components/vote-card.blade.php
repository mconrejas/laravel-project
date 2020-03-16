<div class="card card-{{$coin['id']}}" data-coin="{{ $coin['symbol'] }}">
	<div class="card-header d-flex align-items-center">
		<div class="w-75">
			<div class="media">
				<img class="d-flex mr-3" width="50" src="{{ $coin['iconUrl'] }}">
				<div class="media-body">
					<h5 class="mt-0 text-uppercase">
						<a href="{{ route('listing.show',['id' => $coin['id'] ]) }}">
							{{ $coin['symbol'] }}
						</a>	
					</h5>
					<small>{{ $coin['name'] }}</small> 
				</div>
			</div>
		</div>
		<div class="w-25 num-votes">
			<span class="fa-2x">{{ $coin->votes()->count() }}</span>
		</div>
	</div>
	<div class="card-footer">
		<div class="d-flex justify-content-center">
			<button class="btn-promote btn mx-auto border btn-light" data-id="{{ $coin['id'] }}" >
				{{__('Promote')}}
			</button>

			@if( auth()->check() && auth()->user()->hasVoted($coin['id']) )
			<button class="btn-vote btn mx-auto border buzzex-active" disabled data-id="{{ $coin['id'] }}" >
				{{__('Voted')}}
			</button>
			@else
			<button class="btn-vote btn mx-auto border" data-id="{{ $coin['id'] }}" >
				{{__('Vote')}}
			</button>
			@endif
		</div>
	</div>
	<div class="clearfix"></div>
</div>