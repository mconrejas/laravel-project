<div class="tab-pane fade show active" id="listed" role="tabpanel" aria-labelledby="listed-tab">
	<div class="row">
		<div class="col-md-8 py-3 ">
			<small> {{ $data['counts'] }} {{ __('projects in total') }}</small>
		</div>
		<div class="col-md-4 py-2">
			<div class="input-group border border-secondary searchbox-wrapper p-1">
				<input type="text" name="coin" class="coin-search form-control border-0 rounded-0 searchbox-input ui-autocomplete-input" placeholder="Search coin here" autocomplete="off">
				<div class="input-group-append">
					<button class="btn bg-transparent border-0 rounded-0 btn-searchbox" type="button"><span class="fa fa-search"></span></button>
				</div>
			</div>
			{{-- <form action="{{ route('vote.index', ['tab' => 'listed']) }}" method="get">
				
			</form> --}}
		</div>
	</div>
	<div class="card-columns my-3">
		
	@forelse($data['data'] as $index =>  $coin)
		
		@include('main.voting.components.list-card', ['coin' => $coin])
		
	@empty
		<p>No approved and submitted project.</p>
	@endforelse

		<div class="clearfix"></div>
	</div>

	@if($data['data']->total() > $data['data']->perPage())
	<div class="d-flex justify-content-center">
		{!! $data['data']->links() !!}
	</div>
	@endif
</div>