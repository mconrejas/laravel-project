@extends('masters.admin')
@section('content')
<style>
.record-autocomplete{
    overflow-x: hidden;
    overflow-y: auto;
    max-height: 300px;
    background: #f8f9fa;
    border: 1px solid #7e7777cc;
    list-style: none;
    width: 197px;
    padding: 0px;
}

.record-autocomplete li{
    padding: 7px;
    cursor: pointer;
}
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Internal Withdrawals</div>
                <div class="card-body">
                    
                    {!! Form::open(['method' => 'GET', 'url' => '/admin/withdrawals', 'class' => 'form-inline my-2 my-lg-0 float-right', 'role' => 'search', 'id'=>'filters'])  !!}
                    <div class="input-group border align-self-center searchbox-wrapper">
                        <input type="text" class="form-control searchbox-input" name="coin" placeholder="Filter by Coin" value="{{ request('coin') }}">
                        <span class="input-group-append">
                            <button class="btn btn-secondary" type="submit">
                            <i class="fa fa-search"></i>
                            </button>
                        </span>
                        &nbsp;&nbsp;
                        <select class="form-control mr-1 custom-select" id="item_type" name="status">
                            <option value="all" @if(( (Request::get('status')== 'all') && Request::get('status') != '' )) selected @endif>All</option>
                            @if(exchangeTxnStatuses())
                                @foreach(exchangeTxnStatuses() as $index => $status)
                                    <option value="{{$index}}" @if(( (Request::get('status')==$index) && Request::get('status') != '' )) selected @endif>{{$status}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    {!! Form::close() !!}
                    <br/>
                    <br/>
                    <div class="table-responsive">
                        <table class="table tabulator table-sm table-hover">
                            <thead>
                                <tr>
                                    <th tabulator-width="100">Transaction ID</th>
                                    <th>Date</th>
                                    <th>Coin</th>
                                    <th>Net Amount</th>
                                    <th>Address</th>
                                    <th>User</th>
                                    <th tabulator-formatter="html">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $row)
                                <tr>
                                    <td>{{$row->transaction_id}}</td>
                                    <td>{{date('Y-m-d H:i',$row->created) }}</td>
                                    <td>{{$row->exchangeItem->symbol}}</td>
                                    <td>{{ currency(abs($row->amount + $row->fee)) }}</td>
                                    <td>{{!empty($row->address) ? $row->address: $row->remarks }}</td>
                                    <td>{{$row->User->email}}</td>
                                    <td><a class="btn btn-sm btn-outline-secondary" href="/admin/withdrawals/edit/{{$row->transaction_id}}">{{ $row->getStatus() }}</a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="pagination-wrapper"> {!! $records->appends(['status' => Request::get('status')])->render() !!} </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(function() {
            $(document).on('change','#item_type', function(){
                $('#filters').submit();
            });
        });

        $(document).ready(function() {

            $(".searchbox-input").autocomplete({
                classes: {
                    "ui-autocomplete": "record-autocomplete",
                },
                source: function(request, response) {
                    $.post("/en/exchange/search/coin", {
                            term: request.term,
                        })
                        .done(function(data) {
                            response(data.length > 0 ? data.slice(1) : data);
                        });
                },
                minLength: 2,
                select: function(event, ui) {
                    $('.searchbox-input').val(ui.item.value);
                    $('#filters').submit();
                    return false;
                }
            }).autocomplete("instance")._renderItem = function(ul, item) {
                return $("<li>").append("<div><img src='" + item.icon + "' width='20'> " + item.value + " <span class='font-12 float-right text-secondary'>" + item.label + "</span></div>").appendTo(ul);
            };
        });
    </script>
@endsection