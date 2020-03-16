<div class="tab-pane fade show active" id="to-be-listed" role="tabpanel" aria-labelledby="to-be-listed-tab">
	<div class="row">
		<div class="col-12 py-3">
			<small> {{ $data['counts'] }} {{ __('projects in total') }}</small>
		</div>
	</div>
	<div class="card-columns my-3">
		
	@forelse($data['data'] as $index =>  $coin)
		
		@include('main.voting.components.list-card', ['coin' => $coin])
		
	@empty
		<p>No available project.</p>
	@endforelse

		<div class="clearfix"></div>
	</div>
	
	@if($data['data']->total() > $data['data']->perPage())
	<div class="d-flex justify-content-center">
		{!! $data['data']->links() !!}
	</div>
	@endif
</div>