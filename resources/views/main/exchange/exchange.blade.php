@extends('masters.app')

@section('content')
  <div class="exchange-container container-fluid mx-auto bg-light px-lg-5 mt-3 mb-3">
    <div class="d-block m-0 p-0 w-100">

      <div class="index-sidebar">

        @include('main.exchange.component.card-left-market')

        @include('main.exchange.component.card-latest-execution')

      </div>

      <div class="index-content">

        @include('main.exchange.component.card-current-pair')

        <div class="card-group">
          
          <div class="card mt-1 mr-1">

            @include('main.exchange.component.card-trading-chart')

            @include('main.exchange.component.card-trading-form')

          </div>

          @include('main.exchange.component.card-right-buy-and-sell')

        </div>

        @include('main.exchange.component.card-current-order')
        @include('main.exchange.component.card-order-history')
      </div>

      <div class="clearfix"></div>
    </div>
  </div>
@endsection

@auth
    @push('scripts')
    <script type="text/javascript">
      $(document).ready(function() {      
            window.Echo.channel('OrderHistoryChannel_{{$pair_id}}')
                .listen('OrderHistoryAddedEvent', (data) => {
                    if (typeof data != 'undefined') {
                        window.orderHistory.prependOrders(data);
                        if ( typeof data.user_id != 'undefined' 
                            && parseInt('{{auth()->user()->id}}') == parseInt(data.user_id)) {
                            window.currentOrdersTable.prependOrders(data);
                        }
                        window.cardTradeDepth.fetchLocalSnapshot(); // this will update the snapshotlocalasks
                    }
            });
         })
    </script>

    @endpush
@endauth