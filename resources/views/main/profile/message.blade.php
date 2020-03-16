@extends('masters.app')

@section('content')
<div class="container-fluid my-md-5 my-3 px-lg-5">
    <div class="row justify-content-center">
        <div class="col-md-3">
            @include('main.profile.component.side-menu')
        </div>
        <div class="col-12 col-md-12 col-lg-9 my-cards">
            <div class="card rounded-0 pt-3 px-0" id="card-message-center">
                <div class="card-block p-4 d-flex">
                    <div class="w-100 d-flex justify-content-start">
                        <div class="lead align-self-center"> Message Center</div>
                    </div>
                </div>
                <div class="card-block py-1 px-4 d-flex ">
                    <div class="w-75 d-flex justify-content-start">
                        <span class="font-weight-bold align-self-center">{{__('Type')}} :</span> 
                        <button class="btn-message-all mx-1 px-3 btn-sm btn border rounded-0 buzzex-active">{{__('All')}} <span class="total_counts"></span></button>
                        <button class="btn-light-on-dark btn-message-unread mx-1 px-3 btn-sm btn border rounded-0">{{__('Unread')}} <span class="unread_counts"></span></button>
                    </div>
                    <div class="w-25 d-flex justify-content-end">
                        <button class="btn-mark-all-read btn btn-sm btn-buzzex px-3 border rounded-0">{{__('Mark all as read')}}</button>
                    </div>
                </div>
                <div class="card-block py-1">
                    <div class="table border" id="messageTable"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    .card-block .form-label {
        display: inline-block;
        min-width: 200px;
    }
</style>
@endsection

@section('scripts')
<script type="text/javascript">
/**
* Left market widget
*/
(function($) {

    $.fn.MessageCenterWidget = function(params) {
        var widget = this;
        var opt = $.extend({
            messageUrl: '',
            markReadurl: '',
            tableSelector: '.table',
            paginationSize: 20
        }, params);
        var total_counts = widget.find('.total_counts');
        var unread_counts = widget.find('.unread_counts');

        widget.dataTable = new Tabulator(opt.tableSelector, {
            layout: "fitColumns",
            index: 'id',
            pagination: 'remote',
            paginationSize: opt.paginationSize,
            placeholder: window.Templates.noDataAvailable(),
            data: [], //set initial table data
            columns: [{
                    title: "Time",
                    field: "time",
                    resizable: false,
                    width: 150
                },
                {
                    title: "Sender",
                    field: "sender",
                    align: 'left',
                    resizable: true,
                    headerSort: false,
                    width: 80
                },
                {
                    title: "Message",
                    field: "message",
                    align: 'left',
                    resizable: true,
                    headerSort: false,
                },
                {
                    title: "",
                    align: 'left',
                    resizable: false,
                    headerSort: false,
                    width: 50,
                    formatter: function(row) {
                        var data = row.getData();
                        return data.is_read == 1 ? '': "<button data-id='"+data.id+"' class='mark-as-read btn btn-sm btn-buzzex rounded-0' title='Mark as Read' rel='tooltip'><span class='fa fa-eye'></span></button>";
                    }
                },
                {
                    field: 'is_read',
                    visible: false
                },
                {
                    field: 'id',
                    visible: false
                },
            ],
            layoutColumnsOnNewData: false,
            ajaxURL: opt.messageUrl,
            ajaxParams: {
                filter: 'all'
            },
            ajaxConfig: {
                method: "GET",
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken.content,
                },
            },
            rowFormatter: function(row) {},
            ajaxError: function(xhr, textStatus, errorThrown) {
                if (xhr.status == 500 || xhr.status == 0) {
                    widget.dataTable.clearData()
                }
            },
            ajaxResponse: function(url, params, response) {
                $(total_counts).text(' (' + response.all_counts + ')');
                $(unread_counts).text(' (' + response.unread_counts + ')');
                return response;
            }
        });
        widget.find('.btn-message-unread').on('click', function(e) {
            var button = $(this)
            button.btnProcessing('.');
            widget.find('.btn-message-all').btnInActive();
            widget.dataTable.setFilter('is_read', '=', 0);
            button.btnReset().btnActive();
        });
        widget.find('.btn-message-all').on('click', function(e) {
            var button = $(this)
            button.btnProcessing('.');
            widget.find('.btn-message-unread').btnInActive();
            widget.dataTable.setFilter('is_read', '>=', 0);
            button.btnReset().btnActive();
        });
        widget.find('.btn-mark-as-read').on('click', function(e) {
            var button = $(this)
            button.btnProcessing('Marking...');
            var id = $(this).data('id');
            $.post(opt.markReadurl, {
                    id: id
                })
                .done(function(e) {
                    widget.dataTable.setData();
                    button.btnReset().btnActive();
                })
                .fail(function(e) {
                    toast({
                        text: 'fail todo',
                        type: 'error'
                    })
                    button.btnReset().btnActive();
                });
        });
        widget.find('.btn-mark-all-read').on('click', function(e) {
            var button = $(this)
            button.btnProcessing('Marking...');
            $.post(opt.markReadurl, {
                    id: 'all'
                })
                .done(function(e) {
                    widget.dataTable.setData();
                    button.btnReset().btnActive();
                })
                .fail(function(e) {
                    toast({
                        text: 'fail todo',
                        type: 'error'
                    })
                    button.btnReset().btnActive();
                });
        })
        return widget;
    }
}(jQuery));

$(document).ready(function() {
    $("#card-message-center").MessageCenterWidget({
        messageUrl: "{{route('notifications.list')}}",
        markReadurl: "{{route('notifications.markasread')}}",
        tableSelector: '#messageTable'
    });
})
</script>
@endsection
