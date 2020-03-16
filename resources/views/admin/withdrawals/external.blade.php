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
                <div class="card-header">External Withdrawals</div>
                <div class="card-body">
                    
                    {!! Form::open(['method' => 'GET', 'url' => '/admin/withdrawals/external', 'class' => 'form-inline my-2 my-lg-0 float-right', 'role' => 'search', 'id'=>'filters'])  !!}
                    <div class="input-group border align-self-center searchbox-wrapper">
                        <input type="text" class="form-control searchbox-input" name="coin" placeholder="Filter by Coin" value="{{ request('coin') }}">
                        <span class="input-group-append">
                            <button class="btn btn-secondary" type="submit">
                            <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </div>
                    <button type="button" class="btn btn-info btn-resync"><span class="fa fa-refresh"></span> Resync Withdrawal History</button>
                    {!! Form::close() !!}
                    <br/>
                    <br/>
                    <div class="table-responsive">
                        <table class="table tabulator table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Coin</th>
                                    <th>Source</th>
                                    <th>Amount</th>
                                    <th>Address</th>
                                    <th tabulator-width="75">Status</th>
                                    <th>External ID</th>
                                    <th tabulator-width="75" tabulator-formatter="html">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $row)
                                <tr>
                                    <td>{{date('Y-m-d H:i',$row->timestamp/1000)}}</td>
                                    <td>{{$row->asset}}</td>
                                    <td>{{$row->source}}</td>
                                    <td>{{$row->amount}}</td>
                                    <td>{{$row->address}}</td>
                                    <td>{{ $row->status }}</td>
                                    <td>{{$row->external_id}}</td>
                                    <td>
                                        <button type="button" class="btn-withdrawal-details btn btn-info btn-sm" data-info="{{$row->raw_data}}">Details</button>
                                    </td>
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
        var resyncUrl = "{{ route('resyncWithdrawals') }}";

        $(document).ready(function() {

            $('body').on('click', '.btn-withdrawal-details', function(e){
                var json = $(this).data('info');
                swal({
                    title: '<span style="font-size:17px;">Withdrawal Details</span>',
                    buttonsStyling: false,
                    confirmButtonClass: 'btn btn-sm btn-primary px-5 rounded-0',
                    confirmButtonText: 'Close',
                    html: '<div style="text-align:left !important;">'+
                                '<pre><code class="text-left">'+
                                  JSON.stringify(json, null, 2)+
                                '</code></pre>'+
                            '<div>',
                    width: 800,
                });
            });

            $('.btn-resync').on('click', function(e){
                var button = $(this);
                button.btnProcessing('Resyncing... Please wait...');
                $.post(resyncUrl,{})
                .done(function(res){
                    button.btnReset();
                    window.location.reload();
                })
                .fail(function (xhr, status, error) {
                     alert({
                        title: window.Templates.getXHRMessage(xhr),
                        html: window.Templates.getXHRErrors(xhr),
                        type: 'error'
                    });
                    button.btnReset();
                });
            })

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