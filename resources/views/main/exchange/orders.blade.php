@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 px-md-5">

		@includeWhen( $tab == 'current-order', 'main.exchange.order-tabs.tab-current-orders')

		@includeWhen( $tab == 'order-history', 'main.exchange.order-tabs.tab-order-history')

		@includeWhen( $tab == 'latest-execution', 'main.exchange.order-tabs.tab-latest-execution')

</div>
@endsection