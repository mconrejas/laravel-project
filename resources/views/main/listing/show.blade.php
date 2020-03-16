@extends('masters.app')

@section('styles')
<style type="text/css">
	#listing-content {
		margin-top: -3rem;
		z-index: 5;
		position: relative;
	}
	#listing-content #left-card .card-body {
		background: #f9f9f9;
	}
</style>
@endsection

@section('content')

@include('partials.sections.header-banner')

<div class="container mb-5" id="listing-content">
	<!-- <p class="card-title lead fa-2x">{{ __('Project') }} {{ $coin->name }}</p>
	<hr> -->
	<div class="card-group">
		<div class="card" id="left-card">
			<div class="card-body p-5">
				<div class="card-block w-75 mx-auto media my-2">
					<img class="align-self-center mr-5" src="{{ $coin->iconUrl }}">
					<div class="d-flex flex-column align-self-center">
						<h2 class="mt-0 text-uppercase"> {{ $coin->symbol }}</h2>
						<h4 class="">{{ $coin->name }}</h4> 
					</div>
				</div>
				<div class="card-block w-75 mx-auto my-2 justify-content-center">
					<div class="row">
						<div class="col-md-6 py-2">
							<h6>{{ __('Coin Type') }}</h6>
							<h4>{{ $coin->infos['coin_type'] }}</h4>
						</div>
						<div class="col-md-6 py-2">
							<h6>{{ __('Date of Issue') }}</h6>
							<h4>{{ $coin->infos['date_of_issue'] ?? __('Not set') }}</h4>
						</div>
						<div class="col-md-12 py-2">
							<h6>{{ __('Total Supply') }}</h6>
							<h4>{{ $coin->infos['total_supply'] }}</h4>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="card">
			<div class="card-body p-5">
				<div class="card-block d-flex justify-content-center flex-wrap my-3">
					
					<a href="{{ $coin->infos['official_website'] }}" class="btn mx-1 my-1 btn-sm border">
						{{ __('Official Website') }}
					</a>
					<a href="{{ $coin->infos['whitepaper'] ?? '#' }}" class="btn mx-1 my-1 btn-sm border">
						{{ __('Whitepaper') }}
					</a>

					@if($coin->blockExplorer)
						@foreach($coin->blockExplorer as $index => $explorer )
							<a href="{{ $explorer }}" class="btn mx-1 my-1 btn-sm border">
								{{ __('Block Explorer') }} {{ $index+1 }}
							</a>
						@endforeach
					@endif
					<a href="{{ $coin->infos['source_code'] }}" class="btn mx-1 my-1 btn-sm border">
						{{ __('Source Code') }}
					</a>
				</div>

				<div class="card-block my-3">
					<h5>{{ __('Project Description') }}</h5>
					<p class="">
						{{ $coin->infos['project_description'] }}
					</p>
					<div class="my-5 text-center">
						<a href="{{ route('vote.index',['tab' => 'vote'])}}" class="btn btn-buzzex px-5">{{ __('Vote') }}</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection