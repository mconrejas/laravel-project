/**
 * Trading View chart for exchange
 * @author Kirby Capangpangan
 *
 */
( function( $ ) {
    $.fn.TradingViewWidget = function( params ) {

        var widget = this;
        widget.tvChart = null;

        widget.getSaveTimeframe = function (){
            return localStorage.getItem('bzx_save_timeframe') ? localStorage.getItem('bzx_save_timeframe').toUpperCase() : '1D';
        }
        widget.setSaveTimeframe = function (interval){
            if (interval.indexOf('d') >= 0 || interval.indexOf('m') >= 0 || interval.indexOf('y') >= 0) {
                localStorage.setItem('bzx_save_timeframe', interval);
            } else {
                localStorage.setItem('bzx_save_timeframe', interval+'d');
            }
        }
        widget.getParameterByName = function( name ) {
            name = name.replace( /[\[]/, "\\[" ).replace( /[\]]/, "\\]" );
            var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
                results = regex.exec( location.search );
            return results === null ? "" : decodeURIComponent( results[ 1 ].replace( /\+/g, " " ) );
        }
        widget.opt = $.extend( {
            debug: false,
            pair_id: 1,
            last_fid: 0,
            ohlcv_baseUrl: '',
            serverTimeUrl: '',
            last_fid: 0,
            theme: widget.getParameterByName( 'theme' ) || 'Dark',
            width: '100%',
            height: '380px',
            symbol: 'BTC/ETH',
            interval: 'D',
            timeframe: widget.getSaveTimeframe() || '1D',
            fullscreen: false,
            container_id: '',
            library_path: window.location.origin + "/vendor/tradingview/charting_library/",
            locale: widget.getParameterByName( 'lang' ) || "en",
            drawings_access: {
                type: 'black',
                tools: [ {
                    name: "Regression Trend"
                } ]
            },
            disabled_features: [
                "move_logo_to_main_pane",
                "header_saveload",
                "study_templates",
                "header_compare",
                "header_undo_redo"
            ],
            enabled_features: [
                "header_widget",
                "header_widget_dom_node",
                "use_localstorage_for_settings",
                "left_toolbar",
                "header_indicators",
                "header_settings",
                "side_toolbar_in_fullscreen_mode",
                "header_screenshot"
            ],
            charts_storage_url: 'http://saveload.tradingview.com',
            charts_storage_api_version: "1.1",
            client_id: 'tradingview.com',
            user_id: 'public_user_id',
            studies_overrides: {
                "volume.volume.color.0": "#00FFFF",
                "volume.volume.color.1": "#0000FF",
                "volume.volume.transparency": 70,
                "volume.volume ma.color": "#FF0000",
                "volume.volume ma.transparency": 30,
                "volume.volume ma.linewidth": 5,
                "volume.show ma": true
            }
        }, params );

        var DataFeedObject = {
            serverTimeUrl: widget.opt.serverTimeUrl,
            ohlcvUrl: widget.opt.ohlcv_baseUrl,
            pair_id: widget.opt.pair_id,
            last_fid: widget.opt.last_fid,
            setLastFid: function( value ) {
                this.last_fid = value;
            },
            setPairId: function( value ) {
                this.pair_id = value;
            },
            setServerTimeUrl: function( value ) {
                this.serverTimeUrl = value;
            },
            setOHLCVTimeUrl: function( value ) {
                this.ohlcvUrl = value;
            },
            getLastBar: function( pair_id, callback ) {
                var _this = this;

                $.get( _this.ohlcvUrl, {
                        pair_id: pair_id,
                        last_fid: _this.last_fid,
                        is_last: 1
                    } )
                    .done( function( data ) {
                        if ( data != "" ) {
                            bars = data.ohlc_final;
                            lastfid = data.last_fid;
                            setTimeout( function() {
                                callback( bars[ 0 ] );
                            }, 0 );
                            // console.log('Realtime updated!');
                        }
                    } );
            },
            onReady: function( cb ) {
                var config = {
                    supported_resolution: [ "1", "3", "5", "15", "30", "60", "120", "240", "D" ],
                    supports_marks: false
                    // supports_time : false
                }
                setTimeout( function() {
                    cb( config )
                }, 0 );
            },
            subscribeBars: function( symbolInfo, resolution, onRealtimeCallback, subscribeUID, onResetCacheNeededCallback ) {
                var split_name = symbolInfo.full_name.split( /[:/]/ );

                if ( split_name.length == 2 ) {
                    window.Echo.channel( 'TradingViewChannel_' + split_name[ 1 ] + '_' + split_name[ 0 ] )
                        .listen('TradingViewEvent', function( data ) {
                            console.log('TradingViewEvent', data)
                            if ( typeof data != 'undefined' && data.bars.length > 0 ) {
                                onRealtimeCallback( data.bars );
                            }
                        } );
                }
            },
            unsubscribeBars: function( subscriberUID ) {},
            searchSymbols: function( userInput, exchange, symbolType, onResultReadyCallback ) {},
            calculateHistoryDepth: function( resolution, resolutionBack, intervalBack ) {},
            resolveSymbol: function( symbolName, onSymbolResolvedCallback, onResolveErrorCallback ) {
                var _this = this;
                var split_data = symbolName.split( /[:/]/ );
                var symbol_stub = {
                    name: symbolName,
                    pair_id: _this.pair_id,
                    description: '',
                    type: 'bitcoin',
                    session: '24x7',
                    timezone: 'Asia/Singapore',
                    ticker: symbolName,
                    minmov: 1,
                    has_empty_bars: false,
                    has_no_volume: false,
                    pricescale: 10000000,
                    has_intraday: true,
                    intraday_multipliers: [ '1', '60' ],
                    supported_resolution: [ "1", "3", "5", "15", "30", "60", "120", "240", "D" ],
                    volume_precision: 8,
                };

                setTimeout( function() {
                    onSymbolResolvedCallback( symbol_stub )
                }, 0 );
            },
            getBars: function( symbolInfo, resolution, from, to, onHistoryCallback, onErrorCallback, firstDataRequest ) {
                var _this = this;
                $.get( _this.ohlcvUrl, {
                        from: from,
                        to: to,
                        pair_id: symbolInfo.pair_id,
                        last_fid: _this.last_fid,
                        is_last: 0
                    } )
                    .done( function( data ) {
                        var bars = [];
                        if ( typeof data != "undefined" && data.bars.length > 0 ) {
                            bars = data.bars;
                            _this.setLastFid( data.last_fid );
                            onHistoryCallback( bars, {
                                noData: false
                            } );
                        } else {
                            onHistoryCallback( bars, {
                                noData: true
                            } );
                        }
                    } ).fail( function( arg ) {
                        onErrorCallback( 'Error! ERRCODE:GETBARS' );
                    } )
            },
            getServerTime: function( callback ) {
                var _this = this;
                $.get( _this.serverTimeUrl, {
                        time: ( new Date ).getTime()
                    } )
                    .done( function( data ) {
                        if ( data != "" ) {
                            setTimeout( function() {
                                callback( data.timestamp );
                            }, 0 )
                            // console.log('Server Time : '+data.timestamp);
                        }
                    } );
            }
        }; //end datafeedobject

        widget.opt.datafeed = DataFeedObject;
        widget.init = function() {
            console.log( 'Initializing tradingview' );
            widget.tvChart = new TradingView.widget( widget.opt );
            widget.tvChart.onChartReady(function() {
                widget.tvChart.chart().onIntervalChanged().subscribe(null, function(interval, obj) {
                    if (typeof obj.timeframe !== 'undefined') {
                        widget.setSaveTimeframe(obj.timeframe);
                    }
                })
                /*function createHeaderButton( text, title, clickHandler, options ) {
                    var button = widget.tvChart.createButton( options );
                    button.setAttribute( 'title', title );
                    button.textContent = text;
                    button.addEventListener( 'click', clickHandler );
                }*/
            } );
        }
        return widget;
    }

}( jQuery ) );
