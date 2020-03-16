<div class="card text-center" id="card-home-market">
    <div class="card-header sticky-top bg-light m-0 rounded-0 py-1">
        <div class="row d-flex justify-content-md-between">
            <div class="col-12 col-md-12 col-lg-6 order-2 order-md-1 d-flex align-justify-start flex-wrap">
        @auth
                <button class="flex-fill rounded-0 btn text-secondary btn-toggle-market" data-id="selected">
                    <span class="fa fa-star text-warning"></span> {{__('Favorites')}}
                </button>
        @endauth
        
        @if (!empty($bases))
            @foreach ($bases as $base)
                <button class="flex-fill rounded-0 btn text-secondary btn-toggle-market" data-id="{{$base}}">
                    {{$base}} {{__('Market')}}
                </button>
            @endforeach
        @endif
            </div>

            <div class="col-12 col-md-12 col-lg-3 order-1 order-md-2 d-flex align-justify-end my-1 my-md-2 my-lg-0 float-right">
                <div class="input-group border searchbox-wrapper">
                    <input name="{{uniqid()}}" type="text" class="form-control border-0 rounded-0 searchbox-input" placeholder="Coin pair" value="">
                    <div class="input-group-append">
                        <button class="btn bg-transparent border-0 rounded-0 border-left-0 btn-searchbox" type="button"><span class="fa fa-search"></span></button>
                     </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body m-0 px-1 px-md-2">
        <div class="table-responsive table table-sm rounded-0" id="home-market-table"></div>
    </div>
</div>
<style type="text/css">
    #card-home-market .nav-item.active {
        background-image: linear-gradient(270deg,#22e6b8,#00c1ce);
    }
    .left-market-autocomplete.ui-menu.ui-widget.ui-widget-content {
        padding: 2px 0px; 
        z-index: 9999;
    }
    @media screen and (max-width: 768px) { /* Bootstrap's mobile view breakpoint */
        #card-home-market .card-header.sticky-top {
            position: relative !important;
        }
    }
</style>

@push('scripts')
<script type="text/javascript">
(function($) {
    $.fn.HomeMarketWidget = function(param) {
        var widget = this;
        var opt = $.extend({
            searchUrl: '',
            marketUrl: '',
            limit: 100,
            height: '',
            tableSelector: '',
            defaultBase: 'selected',
            starringUrl: ""
        }, param);

        var search = widget.find('.searchbox-input');
        var searchBtn = widget.find('.btn-searchbox');
        var tabButtons = widget.find('.btn-toggle-market');
        var starIt = function(e, cell, val, data) {
            var pair_id = cell.getRow().getData().pair_id;
            var starred = cell.getRow().getData().starred ? 'remove' : 'save';

            $.get(opt.starringUrl + "/" + pair_id)
                .done(function(data) {
                    if (data.status == 'success' && data.action == 'save') {
                        toast({
                            type: 'success',
                            text: 'Added to favorite pair'
                        })
                        $(cell.getElement()).find('i.fa').addClass('text-warning');
                    }
                    if (data.status == 'success' && data.action == 'remove') {
                        toast({
                            type: 'success',
                            text: 'Remove from favorite pair'
                        })
                        $(cell.getElement()).find('i.fa').removeClass('text-warning');
                    }
                }).fail(function(xhr, textStatus, errorThrown) {
                    alert({
                        title: window.Templates.getXHRMessage(xhr),
                        html: window.Templates.getXHRErrors(xhr),
                        type: 'error'
                    });
                });

            e.stopPropagation();
        };

        search.autocomplete({
            classes: {
                "ui-autocomplete": "left-market-autocomplete",
            },
            source: function(request, response) {
                $.post(opt.searchUrl, {
                        term: request.term,
                    })
                    .done(function(data) {
                        response(data);
                    });
            },
            minLength: 2,
            select: function(event, ui) {
                window.location = ui.item.url;
                return false;
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>").append("<div><span class='fa fa-star " + (item.starred ? 'text-warning' : '') + "'></span> " + item.label + "</div>").appendTo(ul);
        };

        widget.datatable = new Tabulator(opt.tableSelector, {
            height: opt.height,
            fitColumns: true,
            layout: "fitColumns",
            responsiveLayout: true,
            index: 'pair_id',
            placeholder: window.Templates.noDataAvailable(),
            data: [], //set initial table data
            layoutColumnsOnNewData: false,
            ajaxURL: opt.marketUrl,
            ajaxParams: {
                limit: opt.limit,
                base: opt.defaultBase
            },
            ajaxConfig: {
                method: "GET",
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken.content,
                },
            },
            ajaxResponse: function(url, params, response) {
                return typeof response == 'undefined' ? [] : response;
            },
            columns: [
                {
                    field : 'pair_id',
                    visible : false
                },
                {
                    field: "starred",
                    sortable: true,
                    width: 50,
                    formatter: function(cell) {
                        var data = cell.getData();
                        return "<i class='fa fa-star ml-3 " + (data.starred == 1 ? 'text-warning' : '') + "'></i>";
                    },
                    align: 'left',
                    cellClick: starIt,
                    headerSort: false
                },
                {
                    title: "Trading Pair",
                    field: "coin",
                    width: 120,
                    formatter: function(cell) {
                        var data = cell.getData();
                        return data.coin + '/' + data.base;
                    }
                },
                {
                    title: "Price",
                    field: "price",
                    sortable: true,
                },
                {
                    title: "24H High",
                    field: "h24_high",
                    sortable: true,
                },
                {
                    title: "24H Low",
                    field: "h24_low",
                    sortable: true
                },
                {
                    title: "24H Change",
                    field: 'h24_change',
                    sortable: true,
                },
                {
                    title: "24H Volume",
                    field: "h24_volume",
                    sortable: true,
                },
                {
                    title: "24H Value",
                    field: "h24_value",
                    sortable: true,
                }
            ],
            rowTap: function (e, row) {
                window.location = row.getData().url
            },
            rowClick: function (e, row) {
                window.location = row.getData().url
            },
        });

        widget.init = function() {
            widget.ListenForMarketUpdates(opt.defaultBase);
        }

        widget.ListenForMarketUpdates = function(base) {
            window.Echo.channel('MarketBaseChannel_' + base)
                .listen('ExchangePairStatUpdatedEvent', function(data) {
                    widget.datatable.updateData([data]);
                });
        }

        tabButtons.on("click", function() {
            tabButtons.btnInActive();
            var button = $(this);
            button.btnProcessing('.');
            var base = button.data('id');
            widget.datatable.setData(opt.marketUrl, {
                base: base,
                limit: opt.limit
            });
            button.btnReset().btnActive();
            widget.ListenForMarketUpdates(base);
        });
        searchBtn.on('click', function(e) {
            search.focus().trigger('change');
        })
        widget.find(".btn-toggle-market[data-id='" + opt.defaultBase + "']").addClass('buzzex-active');

        return widget;
    }
}(jQuery));

$(document).ready(function() {

    var homemarket = $("#card-home-market").HomeMarketWidget({
        searchUrl: "{{route('searchPair')}}",
        marketUrl: "{{route('market')}}",
        starringUrl: "{{route('exchange.updateFavePair')}}",
        tableSelector: '#home-market-table',
        height: 0,
        limit: 100,
        defaultBase: "BTC"
    });

    homemarket.init();
});
</script>
@endpush