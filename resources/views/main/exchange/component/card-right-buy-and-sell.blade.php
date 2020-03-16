<div class="card rounded-0 mt-1" id="card-trade-depth" style="max-width: 30%;">
  <div class="card-block px-1 py-2">
    <div class="dropdown d-inline-block p-1">
      <small>{{ __('Merge') }}:</small>
      <button class="btn btn-outline-dark bg-transparent btn-sm dropdown-toggle border-0 rounded-0 font-11" type="button"
              id="dropdownMenuButton"
              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{$decimal}} {{__('Decimal')}}
      </button>
      <div class="dropdown-menu dropdown-menu-right rounded-0" aria-labelledby="dropdownMenuButton">
        @for($i = 1; $i <= 8; $i++ )
          <a class="dropdown-item text-right @if($decimal==$i) active @endif" href="#">{{$i}} {{__('Decimal')}}</a>
        @endfor
      </div>
    </div>
    <div class=" d-inline-block float-right mr-2 p-1">
      <span class="btn-toggle-trade-depth pointer-cursor mr-1 fa fa-credit-card fa-flip-vertical" data-item="1"
            data-toggle="tooltip" title="{{ __('Only show buy') }}"></span>
      <span class="btn-toggle-trade-depth pointer-cursor mr-1 fa fa-credit-card" data-item="2" data-toggle="tooltip"
            title="{{ __('Only show sell') }}"></span>
      <span class="btn-toggle-trade-depth pointer-cursor mr-1 fa fa-align-justify active" data-item="3"
            data-toggle="tooltip" title="{{ __('Show both buy and sell') }}"></span>
      <div class="clearfix"></div>
    </div>
  </div>
  <div class="card-block px-0 pt-2">
    @include('main.exchange.exchange-load-mask')
    <div id="table-trade-ask" class="table table-sm border table-compressed m-0" data-type="ask"></div>
    <div id="featured-row" class="text-center py-2 my-0 bg-light"></div>
    <div id="table-trade-bid" class="table table-sm border table-compressed m-0" data-type="bid"></div>
  </div>
</div>
<style type="text/css">

  .bid {
    color: green;
  }

  .ask {
    color: red;
  }

  .target-coin, .base-coin {
    text-transform: uppercase;
  }

  .transition {
    transition-property: all;
    transition-duration: .5s;
    transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
  }

  .btn-toggle-trade-depth.active[data-item="1"] {
    color: #1adebd;
  }

  .btn-toggle-trade-depth.active[data-item="2"] {
    color: #dc3545;
  }

  .btn-toggle-trade-depth.active[data-item="3"] {
    color: #ffc107;
  }

  #table-trade-bid .tabulator-header {
    height: 0;
  }
</style>

@push('scripts')

  <script type="text/javascript">

      var globallimit = 11;
      var decimal_place = $('#dropdownMenuButton').text().replace(/\D/g, '');

      //modules on components page are temporary here while working out... willl be transfer to js file when finished
      (function ($) {
          var TradeDepthTabulator = function (param) {
              var datatabulator = {};
              datatabulator.opt = $.extend({

                  type: 'bid',
                  baseCoin: '',
                  targetCoin: '',
                  showIn: 'USD',
                  tradeDepthUrl: '',
                  pairInfoUrl: '',
                  pairId: 0,
                  requestType: 'GET',
                  limit: globallimit,
                  height: 300,
                  data : [],
              }, param);

              datatabulator.tabulator = new Tabulator(datatabulator.opt.tableSelector, {
                  height: datatabulator.opt.height,//isBid ? 310 : 335,
                  layout: "fitColumns",
                  responsiveLayout: true,
                  index: 'price',
                  ajaxLoader: false,
                  placeholder: window.Templates.noDataAvailable(),
                  data: [], //set initial table data
                  columns: [
                      {
                          title: "Price (" + datatabulator.opt.baseCoin + ")",
                          field: "price",
                          align: 'left',
                          headerSort: false,
                          formatter : function( cell, formatterParams, onRendered ){
                            var decimal_place = $('#dropdownMenuButton').text().replace(/\D/g, '');
                            return parseFloat(cell.getValue()).toFixed(decimal_place)
                          }
                      },
                      {
                          title: "Amount (" + datatabulator.opt.targetCoin + ")",
                          field: "total_amount",
                          align: 'left',
                          headerSort: false,
                          formatter : function( cell, formatterParams, onRendered ){
                            var decimal_place = $('#dropdownMenuButton').text().replace(/\D/g, '');
                            return parseFloat(cell.getValue()).toFixed(decimal_place)
                          }
                      },
                      {
                          title: "Total (" + datatabulator.opt.targetCoin + ")",
                          field: "total_amount_aggregate",
                          align: 'left',
                          headerSort: false,
                          formatter : function( cell, formatterParams, onRendered ){
                            var decimal_place = $('#dropdownMenuButton').text().replace(/\D/g, '');
                            return parseFloat(cell.getValue()).toFixed(decimal_place)
                          }
                      }
                  ],
                  layoutColumnsOnNewData: false,
                  data : datatabulator.opt.data,
                  rowFormatter: function (row) {
                      var data = row.getData();

                      if (typeof data.percent_aggregate != 'undefined') {
                        $(row.getElement()).addClass((datatabulator.opt.type == 'bid') ? 'tr-progress-bid' :'tr-progress-ask' );
                        $(row.getElement()).addClass('tr-progress').css({'background-size': data.percent_aggregate + '%'});
                      }

                  },
                  rowTap: function (e, row) {
                      if (typeof window.tradingForm != 'undefined') {
                          var data = row.getData();
                          window.tradingForm.setPrice(data.full_price);
                          window.tradingForm.setAmount(data.full_total_amount);
                          if(data.breakdowns){
                              window.tradingForm.setModule(data.breakdowns);
                              window.tradingForm.setMargin(data.margin);
                              window.tradingForm.setOriginalPrice(data.orig_price );
                          }
                      }
                  },
                  rowClick: function (e, row) {
                      if (typeof window.tradingForm != 'undefined') {
                          var data = row.getData();
                          window.tradingForm.setPrice(data.full_price);
                          window.tradingForm.setAmount(data.full_total_amount);
                          if(data.breakdowns){
                              window.tradingForm.setModule(data.breakdowns);
                              window.tradingForm.setMargin(data.margin);
                              window.tradingForm.setOriginalPrice(data.orig_price );
                          }

                      }
                  }
              });

              /*todo add listener for realtime updates*/
              return datatabulator;
          }
          /**
           * Main widget for trade wall
           *
           */
          $.fn.TradeDepth = function (params) {
              var widget = this;
              widget.opt = $.extend({
                  baseCoin: '',
                  targetCoin: '',
                  showIn: 'USD',
                  tradeDepthUrl: '',
                  pairId: 0,
                  requestType: 'GET',
                  limit: globallimit,
                  pairInfoUrl: '',
                  binanceProfitMargin : 0,
                  externalBalanceBase : 0,
                  externalBalanceTarget : 0,
                  defaultTolerance: 0,
                  pairTolerance: 0,
                  hasExternalSocket : 0,
                  external_snapshot_fetch_interval : 0
              }, params);

              var toggler = widget.find('.btn-toggle-trade-depth');
              var tradebodybid = widget.find('#table-trade-bid');
              var tradebodyask = widget.find('#table-trade-ask');

              function mergeArrays(baseArray, secondArray) {
                var merge = [];
                var duplicated = [];
                    if (secondArray.length <= 0 || typeof secondArray == 'undefined') {
                        secondArray = [];
                    }
                    baseArray.forEach(function(base){
                        var hasSamePricePoint = secondArray.find(function(element) {
                            return parseFloat(element.full_price).toFixed(8) == parseFloat(base.full_price).toFixed(8);
                        });
                        if(typeof hasSamePricePoint != 'undefined') {
                            var newamount = parseFloat(base.total_amount ) + parseFloat(hasSamePricePoint.amount);
                            var newbase = base;
                                newbase.amount = newamount;
                                newbase.total_amount = newamount;
                                newbase.full_total_amount = newamount;
                            
                            var breakdowns = {};
                                breakdowns[base.module] = base.total_amount;
                                breakdowns[(hasSamePricePoint.module == "" ? 'local' : hasSamePricePoint.module)] = hasSamePricePoint.amount;
                                newbase.breakdowns = breakdowns;

                            merge.push(newbase);
                            duplicated.push(hasSamePricePoint);

                        } else {
                            var breakdowns = {};
                                breakdowns[base.module] = base.total_amount;
                                base.breakdowns = breakdowns;
                            merge.push(base);
                        }
                    });
                    
                    if (secondArray.length > 0) {
                      secondArray.forEach(function(item){
                          var inDuplicated = duplicated.find(function(order){
                              return parseFloat(order.full_price) == parseFloat(item.full_price);
                          });

                          if (!inDuplicated) {
                              var breakdowns = {};
                                  breakdowns[(item.module == "" ? 'local' : item.module)] = item.amount;
                                  item.breakdowns = breakdowns;
                              merge.push(item);
                          }
                      });
                    }
                  //console.log("Merge Arrays:");
                  //console.log(merge);
                  return merge;
                //return merge.sort((a, b) => a - b);
              };

              //set external balance
              widget.setExternalBalance = function(balance = 0, platform = 'binance', coin = 'BTC') {
                    if (coin == widget.opt.baseCoin) {
                        widget.opt.externalBalanceBase = balance;
                    }
                    if (coin == widget.opt.targetCoin) {
                        widget.opt.externalBalanceTarget = balance;
                    }
                    sessionStorage.setItem(`${platform}-${coin}-balance`, balance);
              }

              widget.fetchSnapshot = function(){
                
                if (!document.hidden) {
                  $.getJSON( "/api/snapshot/"+widget.opt.targetCoin+''+widget.opt.baseCoin, function( data ) {
                        if (!data || typeof data == 'undefined') {
                            data.asks = data.bids = []; 
                        }
                       sessionStorage.setItem('snapshotexternalasks', JSON.stringify(data.asks));
                       sessionStorage.setItem('snapshotexternalbids', JSON.stringify(data.bids));
                       sessionStorage.setItem('lastUpdateId', JSON.stringify(data.lastUpdateId));
                       //console.log('snapshot');
                    })
                }
              }
              //fetch external orders snapshot
              widget.fetchExternalOrder = function(){
                widget.fetchSnapshot();

                // window.Visibility.every(widget.opt.external_snapshot_fetch_interval, function(){
                //     widget.fetchSnapshot();
                // });
                setInterval(widget.fetchSnapshot, widget.opt.external_snapshot_fetch_interval);
                
              };

              //fetch local orders snapshot
              widget.fetchLocalOrders = function(type, callback){
                $.get(widget.opt.tradeDepthUrl, {
                    type : typeof type != 'undefined' ? type : 'ask',
                    pair_id : widget.opt.pairId,
                    limit: 21,
                    decimal : widget.opt.decimal,
                    order : typeof type != 'ask' ? 'desc' : 'asc',
                    time: (new Date) 
                }).done(function(result){
                    callback(result);
                }).fail(function(argument) {
                    // console.log('Error fetching local orders', argument)
                    return [];
                })
              };

              widget.initAskTable = function () {
                    widget.askTable = TradeDepthTabulator({
                      type: 'ask',
                      order: 'asc',
                      baseCoin: widget.opt.baseCoin,
                      targetCoin: widget.opt.targetCoin,
                      showIn: widget.opt.showIn,
                      tradeDepthUrl: widget.opt.tradeDepthUrl,
                      pairId: widget.opt.pairId,
                      height: 0,
                      tableSelector: '#table-trade-ask',
                      data : []
                    });
               }
               widget.refreshAskTable = function(){
                    var data = widget.mergeAskOrders();  
                        data = widget.calculateAggregate(data);
                        sessionStorage.setItem('topAsk', data[0] ? data[0].total_amount_aggregate : 0);
                        sessionStorage.setItem('snapshotmergeasks', JSON.stringify(data));
                        data = widget.applyPercentAgregates('ask');
                        data = data.slice(0,widget.opt.limit);
                    widget.askTable.tabulator.setData(data).then(function (arg) {
                      $('.exchange-load-mask').remove();
                    });
               };

              widget.initBidTable = function () {
                    widget.bidTable = TradeDepthTabulator({
                        type: 'bid',
                        order: 'desc',
                        baseCoin: widget.opt.baseCoin,
                        targetCoin: widget.opt.targetCoin,
                        showIn: widget.opt.showIn,
                        tradeDepthUrl: widget.opt.tradeDepthUrl,
                        pairId: widget.opt.pairId,
                        height: 0,
                        tableSelector: '#table-trade-bid',
                        data : []
                    });
              }
              widget.refreshBidTable = function(){
                   var data = widget.mergeBidOrders();
                       data = widget.calculateAggregate(data);
                       sessionStorage.setItem('bottomBid', data[data.length-1] ? data[data.length-1].total_amount_aggregate : 0);
                       sessionStorage.setItem('snapshotmergebids', JSON.stringify(data));
                       data = widget.applyPercentAgregates('bid');
                       data = data.slice(0,widget.opt.limit);
                    widget.bidTable.tabulator.setData(data).then(function (arg) {
                      $('.exchange-load-mask').remove();
                    });
               };

               widget.applyPercentAgregates = function(type){
                    
                    var askMerge = JSON.parse(sessionStorage.getItem('snapshotmergeasks'));
                    var bidMerge = JSON.parse(sessionStorage.getItem('snapshotmergebids'));
                    var bottomBid = sessionStorage.getItem('bottomBid');
                    var topAsk    = sessionStorage.getItem('topAsk');

                    var highestAggregate = (topAsk > bottomBid) ? topAsk : bottomBid;

                    if (!isNaN(highestAggregate)) {
                        if (type == 'ask') {
                            var calculated_percent_ask = widget.calculatePercentAggregate(askMerge, highestAggregate);

                            sessionStorage.setItem('snapshotmergeasks', JSON.stringify(calculated_percent_ask));
                            data = calculated_percent_ask;
                        } else {

                            var calculated_percent_bid = widget.calculatePercentAggregate(bidMerge, highestAggregate);
                                sessionStorage.setItem('snapshotmergebids', JSON.stringify(calculated_percent_bid));
                            data = calculated_percent_bid;
                        }
                    } else {
                        return type == 'bid' ? bidMerge : askMerge;
                    }
                    return data;
               }

              // type should be 'both','ask','bid'
              widget.refreshTable = function(type){
                //reload the 2 table
                    if (type == 'ask') {
                        widget.refreshAskTable();
                    }else if (type == 'bid') {
                        widget.refreshBidTable();
                    } else {
                        widget.refreshAskTable();
                        widget.refreshBidTable();
                    }
                    
                    widget.checkForMatchOrders();
              };

              widget.checkForMatchOrders =  function(){
                    var askMerge = JSON.parse(sessionStorage.getItem('snapshotmergeasks'));
                    var bidMerge = JSON.parse(sessionStorage.getItem('snapshotmergebids'));
                    var decimal_place = $('#dropdownMenuButton').text().replace(/\D/g, '');
                    var margin = parseFloat(widget.opt.binanceProfitMargin);

                    if (askMerge && bidMerge && askMerge.length > 0 && bidMerge.length > 0) {
                        var askOrder = askMerge[askMerge.length-1];  //last of ask wall
                        var bidOrder = bidMerge[0]; //first of bid wall
                        if ((askOrder.module == "" && bidOrder.module != "") || (askOrder.module != "" && bidOrder.module == "") ) {
                            if (parseFloat(askOrder.price) <= parseFloat(bidOrder.price)) {
                                var localorder = askOrder.module == "" ? askOrder : bidOrder;
                                var externalorder = askOrder.module != "" ? askOrder : bidOrder;
                                var streamPrice = parseFloat(externalorder.price).toFixed(decimal_place);

                                if(sessionStorage.getItem('pending_matched_'+localorder.order_id) === null ){

                                    // console.log('Match orders detected.', localorder.order_id);
                                    sessionStorage.setItem('pending_matched_'+localorder.order_id, 'pending');

                                    $.post( widget.opt.matchedOrderUrl, { 
                                        order_id: localorder.order_id, 
                                        module: JSON.stringify(externalorder.breakdowns), 
                                        streamPrice: streamPrice,
                                        margin : parseFloat(margin * externalorder.orig_price),
                                        orig_price :  parseFloat(externalorder.orig_price),
                                        time: (new Date) 
                                    })
                                    .done(function( data ) {
                                        if(data.status == 'ok'){
                                            widget.fetchLocalSnapshot();
                                        }
                                        sessionStorage.removeItem('pending_matched_'+data.order_id)
                                    })
                                    .fail(function(err){
                                        sessionStorage.removeItem('pending_matched_'+localorder.order_id)
                                    });
                                }
                            }
                        }
                    }
              }

              // set the feature row between ask and bid table
              widget.setFeatureRow = function () {
                    $.get(widget.opt.pairInfoUrl, {
                            pair_id: widget.opt.pairId,
                            time: (new Date) 
                        }, function(result) {
                          var type = result.type === 'SELL' ? 'ask' : 'bid';
                          var arrow = result.type === 'SELL' ? 'down' : 'up';
                          window.currentPairWidget.setType(result.type);
                          $('#featured-row').html("<span class='" + type + "'>" + result.price + "</span> <i class='" + type + " fa fa-arrow-" + arrow + "'></i> ≈ <span>" + result.price_usd + "</span> " + widget.opt.showIn);
                    }, 'json');
              }

              //caclculate aggregates for asks
              widget.calculateAggregate = function(depthData){

                var count_aggregate = 0;
                var total_data_amount_aggregate = 0;
                //console.log(depthData.length);
                if (depthData.length >= 1){
                    // assign total_amount_aggregate
                    depthData.forEach(function (arrayItemDepthData) {
                        
                        var i = count_aggregate;
                        //console.log(depthData);
                        if(depthData[0].depth_type==="SELL"){
                            var i = (depthData.length-1) - count_aggregate;
                        }

                        // assign
                        var total_amount_aggregate = parseFloat(depthData[i].total_amount).toFixed(decimal_place);
                        depthData[i].total_amount_aggregate = total_amount_aggregate;
                        // compute
                        if(count_aggregate > 0){
                            depthData[i].total_amount_aggregate = (parseFloat(total_data_amount_aggregate) + parseFloat(depthData[i].total_amount)).toFixed(decimal_place);
                        }
                        total_data_amount_aggregate = parseFloat(depthData[i].total_amount_aggregate).toFixed(decimal_place);
                        count_aggregate++;
                    });
                }

                return depthData;
              };


              //
              widget.updateExternalSnapshotAsk = function(newupdates){
                    var askDepth = JSON.parse(sessionStorage.getItem('snapshotexternalasks'));
                    var nonCapturedAsks = [];

                    if (newupdates && askDepth) {
                        newupdates.forEach(function (arrayStreamAskDepth) {

                            if(parseFloat(arrayStreamAskDepth.quantity) <= 0){
                                askFiltered = askDepth.filter(function(askitem) {
                                   return parseFloat(askitem[0]) !== parseFloat(arrayStreamAskDepth.price);
                                });

                                sessionStorage.setItem('snapshotexternalasks', JSON.stringify(askFiltered));
                            }else{

                                found =  askDepth.find(function(element) {
                                    return parseFloat(element[0]) === parseFloat(arrayStreamAskDepth.price);
                                })
                                
                                // match local orders from current streams
                                // matchBid = widget.matchLocalBidToAskStreams(arrayStreamAskDepth.price, arrayStreamAskDepth.quantity);

                                if(!found){
                                    askDepth.push([parseFloat(arrayStreamAskDepth.price), parseFloat(arrayStreamAskDepth.quantity)]);
                                }else{
                                    updateAsk = askDepth.filter(function(askitem) {
                                       return parseFloat(askitem[0]) !== parseFloat(arrayStreamAskDepth.price);
                                    });
                                    if (parseFloat(arrayStreamAskDepth.quantity) > 0 ) {

                                        updateAsk.push([parseFloat(arrayStreamAskDepth.price), parseFloat(arrayStreamAskDepth.quantity)]);
                                        sessionStorage.setItem('snapshotexternalasks', JSON.stringify(updateAsk));
                                    }
                                }

                            }
                        });

                        // sort desc
                        askDepth.sort(function(a, b){
                            return a[0] - b[0];
                        });

                        // add limit
                        //askDepth = askDepth.slice(0, widget.opt.limit);

                        sessionStorage.setItem('snapshotexternalasks', JSON.stringify(askDepth));
                    }
              };

              widget.updateExternalSnapshotBid = function(newupdates){
                    var bidDepth = JSON.parse(sessionStorage.getItem('snapshotexternalbids'));

                    if(bidDepth && newupdates) {
                        newupdates.forEach(function (arrayStreamBidDepth) {

                            if(arrayStreamBidDepth.quantity <= 0){
                                bidFiltered = bidDepth.filter(function(biditem) {
                                   return biditem[0] !== arrayStreamBidDepth.price;
                                });
                                sessionStorage.setItem('snapshotexternalbids', JSON.stringify(bidFiltered));
                            } else {
                                var found =  bidDepth.find(function(element) {
                                    return element[0] === arrayStreamBidDepth.price;
                                });

                                // match local orders from current streams
                                //matchAsk = widget.matchLocalAskToBidStreams(arrayStreamBidDepth.price, arrayStreamBidDepth.quantity);
                                 
                                if(!found){
                                     bidDepth.push([arrayStreamBidDepth.price, arrayStreamBidDepth.quantity]);
                                }else{
                                    updateBid = bidDepth.filter(function(biditem) {
                                       return biditem[0] !== arrayStreamBidDepth.price;
                                    });
                                    if (parseFloat(arrayStreamBidDepth.quantity) > 0 ) {
                                        updateBid.push([arrayStreamBidDepth.price, arrayStreamBidDepth.quantity]);
                                        sessionStorage.setItem('snapshotexternalbids', JSON.stringify(updateBid));
                                    }
                                }

                            }
                        });

                        // sort desc
                        bidDepth.sort(function(a, b){
                            return b[0] - a[0];
                        });

                        // add limit
                        //bidDepth = bidDepth.slice(0, widget.opt.limit);

                        sessionStorage.setItem('snapshotexternalbids', JSON.stringify(bidDepth));
                    }
              };

              widget.updateExternalSnapshot = function(stream){
                    var lastUpdateId = JSON.parse(sessionStorage.getItem('lastUpdateId'));

                    var newAskStreamUpdate = stream.askDepth;
                    var newBidStreamUpdate = stream.bidDepth;

                    widget.updateExternalSnapshotAsk(newAskStreamUpdate);
                    widget.updateExternalSnapshotBid(newBidStreamUpdate);

                    var ask_orders = widget.mergeAskOrders();
                    var bid_orders = widget.mergeBidOrders();
              }

              widget.calculatePercentAggregate = function (depthData, highest) {
                    var percentCount = 0;
                    depthData.forEach(function (arrayItem) {
                        var percent = (100 * ( parseFloat(arrayItem.total_amount_aggregate) / parseFloat(highest)));
                        depthData[percentCount].percent_aggregate = isNaN(percent) ? 0 : percent;
                        percentCount++;
                    });
                    return depthData;
              }

              //merge local and external sell order
              // sort order
              widget.mergeAskOrders = function() {
                var externalaskDepth = JSON.parse(sessionStorage.getItem('snapshotexternalasks'));
                var localaskDepth = JSON.parse(sessionStorage.getItem('snapshotlocalasks'));
                var balance = JSON.parse(sessionStorage.getItem(`binance-${widget.opt.baseCoin}-balance`)); // 'binance' string should be replaced with the platform with balance
                var decimal_place = $('#dropdownMenuButton').text().replace(/\D/g, '');
                var askLimit = 0;
                var margin = parseFloat(widget.opt.binanceProfitMargin);
                var externalFormattedDepth = [];
                var start = widget.opt.pairTolerance >= 0 ? parseInt(widget.opt.pairTolerance) : parseInt(widget.opt.defaultTolerance);
                var end = start + parseInt(widget.opt.limit);

                if (externalaskDepth) {

                    externalaskDepth = externalaskDepth.slice(start, end);

                    externalaskDepth.forEach(function (arrayItemAskDepth, key) {
                            var price = parseFloat(arrayItemAskDepth[0]);
                            var quantity = parseFloat(arrayItemAskDepth[1]);

                            if (quantity >= 0) {
                                var full_price = (price * (1 + margin));

                                if(parseInt(widget.opt.filterBalance)) {
                                    if(balance > askLimit && (askLimit + (price * quantity)) > balance) quantity =  (balance - askLimit)/price;
                                    askLimit += (price * quantity);
                                    if(askLimit > balance) return false;
                                }

                                askArray = {
                                    orig_price: price,
                                    full_price: full_price,
                                    full_total_amount: quantity,
                                    price: parseFloat(full_price).toFixed(decimal_place),
                                    total_amount: quantity,
                                    base: widget.opt.baseCoin,
                                    coin: widget.opt.targetCoin,
                                    pair_id: widget.opt.pairId,
                                    pair_text: widget.opt.baseCoin+'_'+widget.opt.targetCoin,
                                    depth_type: "SELL",
                                    module: 'binance',
                                    margin: (margin * price)
                                };
                                externalFormattedDepth.push(askArray);
                            }
                    });
                }

                var merge_data = mergeArrays(externalFormattedDepth, localaskDepth);
                  //console.log("Merged Asked: ");
                  //console.log(merge_data);

                  merge_data.sort(function(a, b){
                            return a['full_price'] - b['full_price'];
                        });
                  //console.log("Merged Asked Sorted ASC:");
                  //console.log(merge_data);
                  merge_data = merge_data.slice(0, widget.opt.limit);
                   //console.log("Merged Asked Sliced: ");
                   //console.log(merge_data);
                  merge_data.sort(function(a, b){
                      return b['full_price'] - a['full_price'];
                  });
                  //console.log("Merged Asked Sorted DESC:");

                sessionStorage.setItem('snapshotmergeasks', JSON.stringify(merge_data));

                return merge_data;
              };


              //merge local and external buy order
              //sort order
              widget.mergeBidOrders = function(details) {
                var externalbidDepth = JSON.parse(sessionStorage.getItem('snapshotexternalbids'));
                var localbidDepth = JSON.parse(sessionStorage.getItem('snapshotlocalbids'));
                var balance = JSON.parse(sessionStorage.getItem(`binance-${widget.opt.targetCoin}-balance`)); // Todo: 'binance' string should be replaced with the platform where balance resides
                var bidLimit = 0;
                var decimal_place = $('#dropdownMenuButton').text().replace(/\D/g, '');
                var margin = widget.opt.binanceProfitMargin;
                var externalFormattedDepth = [];
                var start = widget.opt.pairTolerance >= 0 ? parseInt(widget.opt.pairTolerance) : parseInt(widget.opt.defaultTolerance);
                var end = start + parseInt(widget.opt.limit);

                if (externalbidDepth) {

                    externalbidDepth.sort(function(a, b){
                        return b[0] - a[0];
                    });

                    externalbidDepth = externalbidDepth.slice(start, end);

                    externalbidDepth.forEach(function (arrayItemBidDepth) {
                        var price = parseFloat(arrayItemBidDepth[0]);
                        var quantity = parseFloat(arrayItemBidDepth[1]);
                        var full_price = (price * (1 - margin));

                        if(parseInt(widget.opt.filterBalance) ){
                            if( balance > bidLimit && (bidLimit + quantity) > balance) { 
                                quantity =  balance - bidLimit;
                            }
                            bidLimit += quantity;
                            if(bidLimit > balance) {
                                return false;
                            }
                        } 

                        bidArray = {
                            orig_price: price,
                            full_price: full_price,
                            full_total_amount: quantity,
                            price: parseFloat(full_price).toFixed(decimal_place),
                            total_amount:quantity,
                            base: widget.opt.baseCoin,
                            coin: widget.opt.targetCoin,
                            pair_id: widget.opt.pairId,
                            pair_text: widget.opt.baseCoin+'_'+widget.opt.targetCoin,
                            depth_type: "BUY",
                            module: 'binance',
                            margin: (margin * price)
                        };
                        externalFormattedDepth.push(bidArray);
                    });
                }

                var merge_data = mergeArrays(externalFormattedDepth, localbidDepth);
                    merge_data.sort(function(a, b){
                        return b.price - a.price;
                    });
                    merge_data = merge_data.slice(0, widget.opt.limit);

                sessionStorage.setItem('snapshotmergebids', JSON.stringify(merge_data));
                return merge_data;
              };

              var timer;
              toggler.on('click', function (e) {
                  e.preventDefault();
                  var item = $(this).data('item');
                  if ($(this).hasClass('active')) {
                      return;
                  }
                  toggler.removeClass('active');
                  if (item == 1) { //bid only
                      timer = setTimeout(function () {
                          tradebodyask.animate({height: '30px'}, {duration: 200, queue: false});
                          tradebodybid.animate({height: '650px'}, {duration: 200, queue: false});
                          widget.opt.limit = 22;
                          widget.refreshTable('bid');
                          clearTimeout(timer);
                      }, 0)
                  } else if (item == 2) { //ask only
                      timer = setTimeout(function () {
                          tradebodyask.animate({height: '680px'}, {duration: 200, queue: false});
                          tradebodybid.animate({height: '0px'}, {duration: 200, queue: false});
                          widget.opt.limit = 22;
                          widget.refreshTable('ask');
                          clearTimeout(timer);
                      }, 0)
                  } else { //both
                      timer = setTimeout(function () {
                          tradebodyask.animate({height: '360px'}, {duration: 200, queue: false});
                          tradebodybid.animate({height: '325px'}, {duration: 200, queue: false});
                          widget.askTable.refreshTable('ask');
                          widget.bidTable.refreshTable('bid');
                          clearTimeout(timer);
                      }, 0)
                  }
                  $(this).addClass('active');

              });

              widget.listenForExternalWebSocket = function(){
                    var pair_ticker = widget.opt.targetCoin+''+widget.opt.baseCoin;
                    var previous_finalUpdateId = 0;

                    window.BinanceClient.ws.depth(pair_ticker, stream => {
                        //only execute inside once page is visible to save resources
                        // window.Visibility.onVisible(function(){
                          if(!document.hidden){
                            var lastUpdateId = JSON.parse(sessionStorage.getItem('lastUpdateId'));
                            if( (parseInt(previous_finalUpdateId) == parseInt(stream.firstUpdateId) )
                                && parseInt(previous_finalUpdateId) !=0 && stream.finalUpdateId >= lastUpdateId)
                            {
                                widget.updateExternalSnapshot(stream);
                                widget.refreshTable('both');
                            }
                            previous_finalUpdateId = parseInt(stream.finalUpdateId) + 1;
                            // console.log(stream);
                          }
                        // });
                    });
              };

              widget.listenForLocalWebSocket = function(){
                    //add realtime event listener
                    window.Echo.channel('OrderBookChannel_'+widget.opt.pairId)
                        .listen('OrderBookAddedOrUpdatedEvent', (data) => {
                            // console.log('OrderBookAddedOrUpdatedEvent',data)
                            widget.fetchLocalSnapshot();
                        });
              };

              widget.listenForExternalBalance = function() {
                    //add realtime event listener for base coin
                    window.Echo.channel('ExternalBalanceChannel_'+widget.opt.baseCoin)
                        .listen('ExternalBalanceUpdatedEvent', (data) => {
                            // console.log('ExternalBalanceUpdatedEvent',data)
                            widget.setExternalBalance((typeof data.balance != 'undefined' ? data.balance : 0), 'binance', widget.opt.baseCoin);
                        });

                    //add realtime event listener for target coin
                    window.Echo.channel('ExternalBalanceChannel_'+widget.opt.targetCoin)
                        .listen('ExternalBalanceUpdatedEvent', (data) => {
                            // console.log('ExternalBalanceUpdatedEvent',data)
                            widget.setExternalBalance((typeof data.balance != 'undefined' ? data.balance : 0), 'binance', widget.opt.targetCoin);
                        });
              }

              widget.fetchLocalSnapshot = function() {
                //get snapshot for local sell order
                widget.fetchLocalOrders('ask', function(res){
                  sessionStorage.setItem('snapshotlocalasks', JSON.stringify(res));
                  widget.refreshTable('ask');
                });

                //get snapshot for local buy order
                widget.fetchLocalOrders('bid', function(res){
                  sessionStorage.setItem('snapshotlocalbids', JSON.stringify(res));
                  widget.refreshTable('bid');
                });
              };

              //empty storage to avoid mixed pairs orders
              widget.emptyStorage = function (){
                 sessionStorage.clear();
              }
              //initialize widget on first load
              widget.init = function(){
                    //init tables
                    widget.initBidTable();
                    widget.initAskTable();

                    //set external balance
                    widget.setExternalBalance(widget.opt.externalBalanceBase, 'binance', widget.opt.baseCoin);
                    widget.setExternalBalance(widget.opt.externalBalanceTarget, 'binance', widget.opt.targetCoin);

                    if (widget.opt.hasExternalSocket == 1) {
                        //get snapshot of external order
                        widget.fetchExternalOrder();
                    }
    
                    //get all local snapshot
                    widget.fetchLocalSnapshot();

                    //get snapshot for featured row
                    widget.setFeatureRow();

                    if (widget.opt.hasExternalSocket == 1) {
                        //add realtime listener for external websocket
                        widget.listenForExternalWebSocket();
                    }
                    //add realtime listener for local websocket
                    widget.listenForLocalWebSocket();

                    if (widget.opt.hasExternalSocket == 1) {
                        //add realtime listener for external balance changes
                        widget.listenForExternalBalance();
                    }

              };

              return widget;
          };
      }(jQuery));

      $(document).ready(function () {

          window.cardTradeDepth = $("#card-trade-depth").TradeDepth({
              baseCoin: '{{$base}}',
              targetCoin: '{{$target}}',
              pairId: '{{$pair_id}}',
              showIn: 'USD',
              tradeDepthUrl: '{{route("tradeDepth")}}',
              pairInfoUrl: "{{route('pairInfo')}}",
              binanceProfitMargin : parseFloat('{{ $profit_margin ?? 0 }}'),
              externalBalanceBase : parseFloat('{!! $external_balance_base ?? 0 !!}'),
              externalBalanceTarget : parseFloat('{!! $external_balance_target ?? 0 !!}'),
              matchedOrderUrl: "{{route('exchange.matchedOrder')}}",
              filterBalance : "{{$filterBalance}}",
              defaultTolerance: "{{$tolerance ?? 0}}",
              pairTolerance: "{{$pair_tolerance ?? 0}}",
              hasExternalSocket: parseInt("{{ $binance_is_market_available ? 1 : 0 }}"),
              external_snapshot_fetch_interval : 300000
          });
          
          window.cardTradeDepth.emptyStorage();
          window.cardTradeDepth.init();

          $(document).on('click', 'div[aria-labelledby="dropdownMenuButton"] .dropdown-item', function () {
              $('#dropdownMenuButton').text($(this).text()); // this assigns the decimal to bs dropdown
              $('div[aria-labelledby="dropdownMenuButton"] .dropdown-item').removeClass('active');
              $(this).addClass('active');
              window.cardTradeDepth.init();
              // window.cardTradeDepth.refreshTable('both'); // this will refresh the tabulators
          });


      });
  </script>

@endpush
