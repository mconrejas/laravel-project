<div class="card rounded-0" id="market-widget">
  <div class="card-block px-2 pt-3">
    <div class="input-group border border-secondary searchbox-wrapper p-1">
      <input type="text" class="form-control border-0 rounded-0 searchbox-input" placeholder="Coin pair">
      <div class="input-group-append">
        <button class="btn bg-transparent border-0 rounded-0 btn-searchbox" type="button"><span
            class="fa fa-search"></span></button>
      </div>
    </div>
  </div>
  <div class="card-block px-0 pt-1 pb-0 mt-1">
    <div class="w-100 my-1 d-flex px-2 market-options">
      <div class="pretty p-default p-round">
        <input type="radio" value="value" checked name="icon_solid" class="radio-toggle-table-view"/>
        <div class="state p-success-o">
          <label>{{ __('24H volume') }}</label>
        </div>
      </div>
      <div class="pretty p-default p-round">
        <input type="radio" value="change" name="icon_solid" class="radio-toggle-table-view"/>
        <div class="state p-success">
          <label>{{ __('24H change') }}</label>
        </div>
      </div>
      <div class="pretty p-icon right">
        <input type="checkbox" id="showStarred"/>
        <div class="state p-success-o">
          <i class="icon fa fa-check"></i>
          <label>{{ __('Show') }} <i class="fa fa-star text-warning"></i></label>
        </div>
      </div>
    </div>
    <div class="btn-group w-100 bases" role="group" aria-label="">
      @if (!empty($bases))
        @foreach($bases as $baseName)
          <button type="button" data-id="{{$baseName}}"
                  class="btn-sm btn btn-base-currency border rounded-0">{{$baseName}}</button>
        @endforeach
      @endif
    </div>
  </div>
  <div class="card-block">
    <div id="table-market" class="table-sm table border mb-0"></div>
  </div>
</div>
<style type="text/css">
  .left-market-autocomplete.ui-menu.ui-widget.ui-widget-content {
    padding: 2px 0px; 
    z-index: 99999;
  }
  #market-widget .custom-control-inline .custom-control-label {
    font-size: 0.8rem;
  }
</style>

@push('scripts')
  <script type="text/javascript">

      /**
       * Left market widget
       */
      (function ($) {

          $.fn.marketWidget = function (param) {
              var widget = this;
              var opt = $.extend({
                  defaultBaseCurrency: 'BTC',
                  activeTabCurrency: 'BTC',
                  defaultTarget: '',
                  showStarred: false,
                  searchUrl: '',
                  marketUrl: '',
                  height: 500,
                  limit: 100,
                  tableSelector: '',
                  starringUrl: ''
              }, param);
              var searchboxInput = widget.find('.searchbox-input');
              var searchboxBtn = widget.find('.btn-searchbox');
              var buttons = widget.find('.btn-base-currency');
              var table = widget.find('#table-market');
              var toggleView = widget.find('.radio-toggle-table-view');
              var toggleStar = widget.find('.checkbox-toggle-view');
              var btnStarred = widget.find('#showStarred');
              var label = $('#pair-card').find('._value_pair_label');

              var starIcon = function (value, data, cell, row, options) {
                  return "<i class='fa fa-star'></i>";
              };
              var starIt = function (e, cell, val, data, options) {
                  var pair_id = cell.getRow().getData().pair_id;
                  var starred = cell.getRow().getData().starred ? 'remove' : 'save';

                  $.get(opt.starringUrl + "/" + pair_id)
                      .done(function (data) {
                          if (data.status == 'success' && data.action == 'save') {
                              toast({
                                  type: 'success',
                                  text: 'Added to favorite pair'
                              })
                              $(cell.getElement()).find('i.fa').addClass('text-warning');
                              $('#pair-card').find(`._${pair_id}_value_starred`).addClass('text-warning');
                          }
                          if (data.status == 'success' && data.action == 'remove') {
                              toast({
                                  type: 'success',
                                  text: 'Removed from favorite pair'
                              })
                              $(cell.getElement()).find('i.fa').removeClass('text-warning');
                              $('#pair-card').find(`._${pair_id}_value_starred`).removeClass('text-warning');
                          }
                      }).fail(function (xhr, textStatus, errorThrown) {
                      alert({
                          title: window.Templates.getXHRMessage(xhr),
                          html: window.Templates.getXHRErrors(xhr),
                          type: 'error'
                      });
                  });

                  e.stopPropagation();
              };

              searchboxInput.autocomplete({
                  classes: {
                      "ui-autocomplete": "left-market-autocomplete",
                  },
                  source: function (request, response) {
                      $.post(opt.searchUrl, {
                          term: request.term,
                      })
                          .done(function (data) {
                              response(data);
                          });
                  },
                  minLength: 2,
                  select: function (event, ui) {
                      window.location = ui.item.url;
                      return false;
                  }
              }).autocomplete("instance")._renderItem = function (ul, item) {
                  return $("<li>").append("<div><span class='fa fa-star " + (item.starred ? 'text-warning' : '') + "'></span> " + item.label + "</div>").appendTo(ul);
              };

              widget.dataTable = new Tabulator(opt.tableSelector, {
                  height: opt.height,
                  layout: "fitColumns",
                  responsiveLayout: true,
                  index: 'coin',
                  ajaxLoader:false,
                  placeholder: window.Templates.noDataAvailable(),
                  data: [], //set initial table data
                  columns: [{
                      formatter: starIcon,
                      field: 'starred',
                      align: 'center',
                      width: 15,
                      cellClick: starIt,
                      resizable: false,
                      headerSort: false
                  },
                      {
                          title: "Coin",
                          field: "coin",
                          width: 70,
                          resizable: false
                      },
                      {
                          title: "Price",
                          field: "price",
                          align: 'left',
                          formatter: function (cell, formatterParams, onRendered) {
                              var usd = cell.getData().price_in_usd;
                              return '<span data-toggle="tooltip" title="' + usd + ' USD">' + cell.getValue() + '</span>';
                          }
                      },
                      {
                          title: "Volume",
                          field: "h24_value",
                          align: 'left'
                      },
                      {
                          title: "Change",
                          field: "h24_change",
                          align: 'left',
                          visible: false
                      },
                      {
                          title: "Active",
                          field: "active",
                          visible: false
                      }
                  ],
                  layoutColumnsOnNewData: false,
                  ajaxURL: opt.marketUrl,
                  ajaxParams: {
                      base: opt.defaultBaseCurrency,
                      target: opt.defaultTarget,
                      limit: opt.limit
                  },
                  ajaxConfig: {
                      method: "GET",
                      headers: {
                          'Accept': 'application/json',
                          'X-Requested-With': 'XMLHttpRequest',
                          'X-CSRF-TOKEN': window.csrfToken.content,
                      },
                  },
                  rowFormatter: function (row) {
                      var data = row.getData();

                      if (typeof data.active != 'undefined' && data.active == 1) {
                          $(row.getElement()).addClass('active')
                      }
                      if (typeof data.starred != 'undefined' && data.starred == 1) {
                          $(row.getElement()).find('.fa').addClass('text-warning')
                      }
                      if (typeof data.pair_id != 'undefined') {
                          $(row.getElement()).attr('data-pair-id', data.pair_id);
                      }
                      if (typeof data.price_usd != 'undefined') {
                          $(row.getCell("price")).attr('data-toggle', 'tooltip').attr('title', data.price_usd)
                      }
                      if (typeof data.url != 'undefined') {
                          $(row.getElement()).attr('data-url', data.url);
                      }
                      if (typeof data.base != 'undefined') {
                          $(row.getElement()).attr('data-base', data.base);
                      }
                  },
                  rowTap: function (e, row) {
                      window.location = row.getData().url
                  },
                  rowClick: function (e, row) {
                      window.location = row.getData().url
                  },
                  ajaxError:function(xhr, textStatus, errorThrown){
                    // console.error(xhr);
                  },
                  ajaxResponse:function(url, params, response){
                        if (response.error == 404) {
                            return [];
                        }
                        return response; //return the response data to tabulator
                    }
              });

              widget.ListenForMarketUpdates = function (base) {
                  window.Echo.channel('MarketBaseChannel_' + base)
                      .listen('ExchangePairStatUpdatedEvent', function (data) {
                         // console.log('ExchangePairStatUpdatedEvent',data)
                          widget.dataTable.updateData([data]);
                      });
              }

              widget.init = function () {
                  widget.find('.btn-base-currency[data-id="' + opt.activeTabCurrency + '"]').trigger('click');
                  widget.ListenForMarketUpdates(opt.defaultBaseCurrency);
              }

              widget.toggleColumnView = function (value) {
                  if (value == 'value') {
                      widget.dataTable.hideColumn("h24_change");
                      widget.dataTable.showColumn("h24_value");
                  } else {
                      widget.dataTable.showColumn("h24_change");
                      widget.dataTable.hideColumn("h24_value");
                  }
                  widget.dataTable.redraw();
              }

              toggleView.on("change", function () {
                  widget.toggleColumnView($(this).val())
              });
              searchboxBtn.on('click', function (e) {
                  searchboxInput.focus().trigger('change');
              })

              buttons.on("click", function () {
                  buttons.btnInActive();
                  var button = $(this);
                  button.btnProcessing('.');
                  var base = button.data('id');
                  var pair_id = 0;
                  widget.dataTable.setData(opt.marketUrl, {
                      base: base,
                      target: opt.defaultTarget,
                      limit: opt.limit
                  });
                  
                  button.btnReset().btnActive();

                  widget.ListenForMarketUpdates(base);

                  $.get(opt.changeBaseUrl, {base: base, target: opt.defaultTarget})
                      .done(function (data) {
   
                      });

              });
              
              btnStarred.on('change', function (e) {
                  var filter = this.checked == true ? '>' : '>=';
                  widget.dataTable.replaceData();
                  widget.dataTable.setFilter('starred', filter, 0);
                  widget.dataTable.redraw(); 
              })
              return widget;
          };

      }(jQuery));

      $(document).ready(function () {

          $(":checkbox,:radio").attr("autocomplete", "off");

          var marketWidget = $("#market-widget").marketWidget({
              height: 535,
              defaultBaseCurrency: "{{ request()->has('base') ? request()->base : 'BTC' }}",
              defaultTarget: "{{ request()->has('target') ? request()->target : 'ETH' }}",
              defaultToggle: 'value',
              showStarred: false,
              starringUrl: "{{route('exchange.updateFavePair')}}",
              searchUrl: "{{ route('searchPair')}}",
              marketUrl: "{{ route('market') }}",
              exchangeUrl: "{{ route('exchange') }}",
              changeBaseUrl: "{{ route('base') }}",
              tableSelector: '#table-market',
              activeTabCurrency: "{{ request()->has('base') ? request()->base : 'BTC' }}"
          });

          marketWidget.init();

      })
  </script>

@endpush