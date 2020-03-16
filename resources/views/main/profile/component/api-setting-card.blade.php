<div class="card border my-1 card-api-each" id="{{$api->client_id}}">
	<div class="card-body">
		<div class="d-flex justify-content-between">
			<h4 class="lead">API {{($key + 1)}}</h4>
			<div class="btn-group">
				<button class="btn-delete-api btn btn-danger btn-sm" rel="tooltip" title="Delete" data-id="{{$api->client_id}}">
					<span class="fa fa-trash"></span>
				</button>
			</div>
		</div>
	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-lg-3 col-md-4 col-sm-5">
				<img class="border img-fluid" width="200" src="//chart.apis.google.com/chart?cht=qr&chs=300x300&chl={{$api->client_id}}" alt="Buzzex API">
			</div>
			<div class="col-lg-9 col-md-8 col-sm-7">
				<small class="m-0">{{ __('API Key') }}</small>
				<h6>{{ $api->client_id }}</h6>
				<small class="m-0">{{ __('API Secret') }}</small>
				<h6>******{{ __('Cannot be shown') }}*******</h6>
				<small class="m-0">{{ __('API Scope') }}</small>
				<h6>{{ $api->scope }}</h6>
				<small class="m-0">{{ __('Created On') }}</small>
				<h6>{{ $api->created_at }}</h6>
			</div>
		</div>
	</div>
</div>