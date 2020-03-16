@extends('masters.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="card-news">
                    <div class="card-header">News</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-md-between">
                            <a href="{{ url('/admin/news/create') }}" class="align-self-center btn btn-success btn-sm" title="Add New News">
                                <i class="fa fa-plus" aria-hidden="true"></i> Add New
                            </a>
                            <div class="input-group w-25 align-self-center">
                                <div class="input-group-prepend">
                                    <label class="input-group-text border-0 bg-transparent" for="role-select">Filter :</label>
                                </div>
                                <select class="custom-select rounded-0" id="filter-select">
                                    <option value="all">All</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div id="searchform" class="input-group w-25 align-self-center" data-url="{{ route('news.search') }}">
                                <span class="fa fa-close text-danger align-self-center"></span>
                                <input type="text" class="form-control" name="search" placeholder="Search...">
                                <span class="input-group-append">
                                    <button class="btn btn-primary" type="button">
                                    <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <div class="table my-4 news-table">
                            
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script type="text/javascript">
(function($) {

    $.fn.NewsTableWidget = function(params) {
        var widget = $(this);
        var opt = $.extend({
            searchUrl: '',
            paginationSize: 50,
            tableSelector : ''
        }, params);

        var actionButtons = function(cell, formatterParams, onRendered) {
            var data = cell.getData();

            var html =  "<a rel='tooltip' data-placement='left' class='btn btn-sm btn-info mr-1' href='/admin/news/" + data.id + "' title='View News'><i class='fa fa-eye'></i></a>" +
                "<a rel='tooltip' data-placement='left' class='btn btn-sm btn-primary mr-1' href='/admin/news/" + data.id + "/edit' title='Edit News'><i class='fa fa-edit'></i></a>";

            if (data.active) {
                html += "<button data-placement='left' onClick='confirmAction(this,\"Confirm remove ?\")' class='btn btn-sm btn-danger mr-1' rel='tooltip' data-href='/admin/news/" + data.id + "/remove' title='Remove News'><i class='fa fa-thumbs-o-down'></i></button>";
            } else {
                html += "<button data-placement='left' onClick='confirmAction(this,\"Confirm restore ?\")' class='btn btn-sm btn-warning mr-1' rel='tooltip' data-href='/admin/news/" + data.id + "/restore' title='Restore News'><i class='fa fa-thumbs-o-up'></i></button>";
            }
            return html;
        };

        widget.table = new Tabulator(opt.tableSelector, { 
                        fitColumns: true,
                        layout: "fitColumns",
                        responsiveLayout: true,
                        index: 'id',
                        placeholder: window.Templates.noDataAvailable(),
                        data: [],
                        layoutColumnsOnNewData: false,
                        pagination: "remote",
                        paginationSize: opt.paginationSize ,
                        ajaxURL: opt.searchUrl,
                        ajaxParams: { term: '', filter : 'all' },
                        ajaxConfig: {
                            method: "GET",
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': window.csrfToken.content,
                            },
                        },
                        columns: [
                            { title : 'ID', field: 'id', width : 75 },
                            { title : 'Link', field : 'link' },
                            { title : 'Text', field : 'text', formatter : 'html' },
                            { title : 'Active', field : 'active' , formatter : 'tickCross', align: 'center' , width : 100 },
                            { title : 'Action', sortable : false, width : 150 , formatter : actionButtons },
                        ],
                        rowFormatter: function(row) {},
                        dataLoaded: function(data) {}
                    });

        widget.find('#filter-select').on('change', function(e){
            widget.find('#searchform button').click();
        });

        widget.find('#searchform button').on('click', function(e){
            e.preventDefault();
            var term = widget.find('#searchform input').val();
            var filter = widget.find('#filter-select').val();

            widget.table.setData(opt.searchUrl, {  filter: filter, term : term, size: opt.paginationSize, page : 1 });
        })

        widget.find('#searchform input').on('keyup', function(event){
            widget.find("#searchform .fa.fa-close").show();

            var keycode = (event.keyCode ? event.keyCode : event.which);

            if(keycode == '13'){
                widget.find('#searchform button').click(); 
            }

            if ($.trim($(this).val()) =='') {
                widget.find("#searchform .fa.fa-close").hide();
            }
        })
        widget.find("#searchform .fa.fa-close").on('click', function(e) {
            $(this).parents('div').find('input').val('');
            $(this).hide();
            widget.find('#searchform button').click();
        })
        return widget;
    };
}(jQuery));

$(document).ready(function(){

    $('#card-news').NewsTableWidget({
        tableSelector : '.table.news-table',
        searchUrl : "{{ route('news.search') }}"
    });

})
</script>
@endsection
