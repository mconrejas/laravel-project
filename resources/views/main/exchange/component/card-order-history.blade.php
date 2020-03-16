<div class="card mt-2 rounded-0 " style="min-height: 200px" id="card-order-history">
  <div class="card-header bg-transparent">
    <h5 class="lead card-title font-16 d-inline-block">{{ __('Order History') }}</h5>
    <button
      class="btn btn-sm btn-outline-dark mx-2 rounded buzzex-active btn-tab-order-history-normal">{{ __('Normal')}}</button>
    <button
      class="btn btn-sm btn-outline-dark mx-2 rounded btn-tab-order-history-stop-limit">{{ __('Stop-Limit')}}</button>
    <a href="{{route('orderTab',['tab'=>'order-history'])}}" class="btn-link float-right text-buzzex">More »</a>
  </div>
  <div class="card-block">
    <div class="table table-sm table-compressed" id="order-history-table"></div>
  </div>
</div>

@push('scripts')

  @guest
    <script type="text/javascript">
        window.Templates.generateEmptyTable('#order-history-table', columns);
    </script>
  @endguest

  @auth
    <script type="text/javascript">
        $(document).ready(function () {
            window.orderHistory = $("#card-order-history").OrdersWidget({
                baseCoin: "{{$base}}",
                targetCoin: "{{$target}}",
                tableType: 'order-history',
                requestUrl: "{{route('orderHistory')}}",
                height: 300,
                tableSelector: '#order-history-table',
                pair_id: '{{ $pair_id }}',
                currentUser : parseInt('{{ auth()->check() ? auth()->user()->id : 0 }}')
            });
            
            window.orderHistory.init();
        });
    </script>
  @endauth

@endpush