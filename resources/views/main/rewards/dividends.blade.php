@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-md-3">
            @include('main.profile.component.side-menu')
        </div>
        <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0 pb-5 px-3" id="card-api">
            	<div class="card-body mt-3 d-flex justify-content-between">
            		<h5 class="align-self-center">{{ __('Dividends') }}</h5>
            	</div>
                <div class="card-block">
                    <ul class="nav nav-tabs nav-fill" id="market-tabs">
                        @foreach($base_markets as $index => $symbol)
                        <li class="nav-item">
                            <a class="nav-link {{$index == 0 ? 'active' : ''}} " id="{{$symbol}}-tab" data-toggle="tab" href="#{{$symbol}}" role="tab" aria-controls="{{$symbol}}">{{ $symbol }}</a>
                        </li>
                        @endforeach
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        @foreach($base_markets as $index => $symbol)
                        <div class="tab-pane px-0 fade {{$index == 0 ? 'show active' : ''}}" id="{{$symbol}}" role="tabpanel" aria-labelledby="{{$symbol}}-tab">
                            <div class="card-block mx-0">
                                <div class="card-block alert border m-1 mb-3 p-0">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            {{ __('Total Fees Collected') }}
                                            <span>{{ $data[$symbol]['total_fee_collected'] ?? 0 }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            {{ __('Total Dividend Pool Amount') }}
                                            <span>{{ $data[$symbol]['total_div_pool_amount'] ?? 0 }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            <span class="ml-2"><i class="fa fa-arrow-right mr-2"></i>{{ __('Dividends Pool Amount Distributed') }}</span>
                                            <span>{{ $data[$symbol]['div_pool_amount_distributed'] ?? 0 }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            <span class="ml-2"><i class="fa fa-arrow-right mr-2"></i>{{ __('Dividends Pool Amount Undistributed') }}</span>
                                            <span>{{ $data[$symbol]['div_pool_amount_undistributed'] ?? 0 }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            <span class="ml-4"><i class="fa fa-long-arrow-right mr-2"></i>{{ __('Total Active Shares') }}*</span>
                                            <span>{{ $data[$symbol]['total_active_share'] ?? 0 }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            <span class="ml-4"><i class="fa fa-long-arrow-right mr-2"></i>{{ __('Value Per Share') }}*</span>
                                            <span>{{ currency($data[$symbol]['value_per_share']) ?? 0 }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-block alert border m-1 mb-3 p-0">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            {{ __('Your BZX Available Balance') }}
                                            <span>{{ $data[$symbol]['bzx_balance'] ?? 0 }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            {{ __('Number of Shares You Have') }}*
                                            <span>{{ $data[$symbol]['no_of_shares'] ?? 0 }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            {{ __('Current Estimated Share Value') }}
                                            <span>{{ $data[$symbol]['estimate_share_value'] ?? 0 }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-block alert border m-1 p-0">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            {{ __('Total Dividend Transactions Received') }}
                                            <span>{{ $data[$symbol]['total_div_trans_received'] ?? 0 }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                            {{ __('Total Dividend Amount Received') }}
                                            <span>{{ $data[$symbol]['total_div_amount_received'] ?? 0 }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="alert alert-info rounded-0 my-2 pb-0">
                                <ul>
                                    <li>*{{ __('Total active shares, value per share and number of share you have are just estimations and the actual numbers may differ from the actual distribution.') }}</li>
                                    <li>{{ __('Dividends are set for automated and daily distribution when the pool amount to distribute is at least') }} {{ $data[$symbol]['min_pool_amount'] }} {{$symbol }}</li>
                                </ul>
                            </div>
                        </div>
                        @endforeach
                        <div class="card-block mb-2 mt-4">
                            <h4 class="lead font-weight-bold">{{ __('Transactions') }} (<span class="coin-target">{{$base_markets[0]}}</span>)</h4>
                            <div class="table table-compressed" id="dividend-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    .nav-tabs .nav-item .nav-link.active {
        background-image: linear-gradient(270deg, #22e6b8, #00c1ce);
    }
</style>
@endsection

@section('scripts')
    <script type="text/javascript">
        var dividendDataUrl = "{{ route('rewards.dividends-records') }}";
        var item = "{{$base_markets[0]}}";

        $(document).ready(function(){
            window.divtable = new Tabulator('#dividend-table', {
                layout: "fitColumns",
                index: 'id',
                pagination: "remote",
                paginationSize: 25,
                columnMinWidth: 80,
                placeholder: window.Templates.noDataAvailable(),
                data: [], //set initial table data
                columns: [
                    {
                        title : 'Transaction ID',
                        field : 'id',
                    },
                    {
                        title: "Time",
                        field: "time"
                    },
                    {
                        title: "Amount",
                        field: "amount",
                    },
                    {
                        title: "USD Value",
                        field: "usd_value"
                    }
                ],
                layoutColumnsOnNewData: false,
                ajaxURL: dividendDataUrl,
                ajaxParams: {
                    item: item
                },
                ajaxLoader:false,
                ajaxConfig: {
                    method: "GET",
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': window.csrfToken.content,
                    },
                }
            });

            $('#market-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var target = $(e.target).attr('aria-controls');
                window.divtable.setData(dividendDataUrl, { item : target});
                $('.coin-target').text(target);
            })
        });
    </script>
@endsection