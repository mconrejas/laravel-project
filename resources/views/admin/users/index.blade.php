  @extends('masters.admin')

@section('content')
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card" id="card-users">
          <div class="card-header">Users</div>
          <div class="card-body">
            <div class="row">
              <div class="col">
                <a href="/admin/users/create" class="btn btn-success">Add New</a>
              </div>
              <div class="col-2">
                <div class="input-group align-self-center">
                  <div class="input-group-prepend">
                    <label class="input-group-text border-0 bg-transparent" for="role-select">Role :</label>
                  </div>
                  <select class="custom-select rounded-0" id="role-select">
                    <option value="0">All</option>
                    @forelse($roles as $role)
                      <option value="{{$role->id}}">{{ ucwords($role->name) }}</option>
                    @empty
                      <option></option>
                    @endforelse
                  </select>
                </div>
              </div>
              <div class="col-4">
                <div id="searchform" class="input-group align-self-center" data-url="{{ route('users.search') }}">
                  <span class="fa fa-close text-danger align-self-center"></span>
                  <input type="text" class="form-control" name="search" placeholder="Search...">
                  <span class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                <i class="fa fa-search"></i>
                                </button>
                            </span>
                </div>
              </div>
            </div>

            <div id="user-table" class="my-2 table-sm table-stripped"></div>

          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script type="text/javascript">
      (function ($) {

          $.fn.UserTableWidget = function (params) {
              var widget = $(this);
              var opt = $.extend({
                  searchUrl: '',
                  paginationSize: 50,
                  allCoins: '',
              }, params);

              var actionButtons = function (cell, formatterParams, onRendered) {
                  var data = cell.getData();
                  var buttons = "<a rel='tooltip' data-placement='left' class='btn btn-sm btn-info mr-1' href='/admin/users/" + data.id + "' title='{{ __('View User') }}'><i class='fa fa-eye'></i></a>" +
                      "<a rel='tooltip' data-placement='left' class='btn btn-sm btn-primary mr-1' href='/admin/users/" + data.id + "/edit' title='{{ __('Edit User') }}'><i class='fa fa-edit'></i></a>" +
                      "<a rel='tooltip' data-placement='left' class='btn btn-sm btn-info mr-1' href='/admin/users/" + data.id + "/reload-funds' title='{{ __('Reload Funds')  }}'><i class='fa fa-dollar'></i></a>"+
                      "<a rel='tooltip' data-placement='left' class='btn btn-sm btn-secondary mr-1' href='/admin/users/" + data.id + "/login-history' title='{{ __('Login History')  }}'><i class='fa fa-sign-in'></i></a>";

                  buttons +="<a rel='tooltip' data-placement='left' class='btn btn-sm btn-outline-primary mr-1' id='"+data.id+"' href='/admin/history/item/all?user=" + data.id +"' title='{{ __('Review Transactions')  }}'><i class='fa fa-list'></i></a>" ;
                  if(data.blocked){
                      buttons +="<button rel='tooltip' data-placement='left' data-action='unblock' class='btn-blocking btn btn-sm btn-success mr-1' id='blk_"+data.id+"' data-href='/admin/users/" + data.id + "/change-status/unblock' title='{{ __('Unblock User')  }}'><i class='fa fa-check-circle'></i></button>";
                  }else{                      
                      buttons +="<button rel='tooltip' data-placement='left' data-action='block' class='btn-blocking btn btn-sm btn-danger mr-1' id='unblk_"+data.id+"' data-href='/admin/users/" + data.id + "/change-status/block' title='{{ __('Block User')  }}'><i class='fa fa-ban'></i></button>" ;
                  }

                  if(!data.is_coin_partner){
                     buttons +="<a class='btn btn-sm btn-success mr-1 add-coin-partner' href='javascript:;' title='{{ __('Add as coin partner') }}' data-user='" + data.id + "'><i class='fa fa-user-plus'></i></a>";
                  }else{    
                    buttons +="<a class='btn btn-sm btn-danger mr-1' href='/admin/users/" + data.id + "/coin-partner/remove' title='{{ __('Remove as coin partner')  }}'><i class='fa fa-user-times'></i></a>";
                  }
                  return buttons;
              };

              var dropdown = function (cell, formatterParams, onRendered) {
                  var field = formatterParams.field;
                  var data = cell.getData()[field];
                  if (data.length <= 0) {
                      return "none";
                  }
                  if (Object.keys(data).length == 1) {
                      return Object.keys(data)[0];
                  }
                  var html = "<select class='pl-0 bg-transparent text-left custom-select rounded-0 border-0'>"
                  $.each(data, function (item) {
                      html += "<option>" + item + "</option>";
                  });
                  html += "</select>";
                  return html;
              };

              widget.table = new Tabulator('#user-table', {
                  fitColumns: true,
                  layout: "fitColumns",
                  responsiveLayout: 'hide',
                  index: 'id',
                  placeholder: window.Templates.noDataAvailable(),
                  data: [],
                  layoutColumnsOnNewData: false,
                  pagination: "remote",
                  paginationSize: opt.paginationSize,
                  ajaxURL: opt.searchUrl,
                  ajaxParams: {
                      term: '',
                      role: 0
                  },
                  ajaxConfig: {
                      method: "GET",
                      headers: {
                          'Accept': 'application/json',
                          'X-Requested-With': 'XMLHttpRequest',
                          'X-CSRF-TOKEN': window.csrfToken.content,
                      },
                  },
                  columns: [
                      {
                          title: 'ID',
                          field: "id",
                          width: 75
                      },
                      {
                          title: "Name",
                          field: "name",
                          width: 200,
                          responsive : 1
                      },
                      {
                          title: "Email",
                          field: "email",
                          width: 200,
                          responsive : 1
                      },
                      {
                          title: 'Roles',
                          field: "roles",
                          sortable: false,
                          headerSort: false,
                          width : 100,
                          formatter: dropdown,
                          formatterParams: {
                              field: 'roles'
                          },
                          responsive : 3
                      },
                      {
                          title: "Permissions",
                          field: 'permissions',
                          width: 120,
                          sortable: false,
                          headerSort: false,
                          formatter: dropdown,
                          formatterParams: {
                              field: 'permissions'
                          },
                          responsive : 3
                      },
                      {
                          title: "Coin Partner",
                          field: "coin_symbol",
                          width: 75,
                          responsive : 2
                      },
                      {
                          title: "Action",
                          sortable: false,
                          headerSort: false,
                          width: 300,
                          formatter: actionButtons,
                          responsive : 0
                      }
                  ],
                  rowFormatter: function (row) {
                  },
                  dataLoaded: function (data) {
                  }

              });

              widget.find('#role-select').on('change', function (e) {
                  widget.find('#searchform button').click();
              });

              $(document).on('click','.add-coin-partner', function (e) {

                var html = "<form method='GET' action='/admin/users/" +$(this).attr('data-user')+ "/coin-partner/add'><select name='coin' class='pl-0 bg-transparent text-left custom-select rounded-0 border-0 form-control'>";

                    $.each(opt.allCoins, function (item) {
                      html += "<option value='"+item+"'>" + opt.allCoins[item] + "</option>";
                    });

                    html += "</select><input class='btn btn-buzzex mt-2' type='submit' value='Add Coin Partner'></form>";

                  Swal.fire({
                    title: "<i>Select Coin</i>", 
                    html: html,                        
                    showCloseButton: true, 
                    showConfirmButton: false,
                  });
              });

              widget.find('#searchform button').on('click', function (e) {
                  e.preventDefault();
                  var role = widget.find('#role-select').val();
                  var term = widget.find('#searchform input').val();
                  widget.table.setData(opt.searchUrl, {role: role, term: term, size: opt.paginationSize, page: 1});
              })

              widget.find('#searchform input').on('keyup', function (event) {
                  widget.find("#searchform .fa.fa-close").show();

                  var keycode = (event.keyCode ? event.keyCode : event.which);

                  if (keycode == '13') {
                      widget.find('#searchform button').click();
                  }

                  if ($.trim($(this).val()) == '') {
                      widget.find("#searchform .fa.fa-close").hide();
                  }
              })
              widget.find("#searchform .fa.fa-close").on('click', function (e) {
                  $(this).parents('div').find('input').val('');
                  $(this).hide();
                  widget.find('#searchform button').click();
              })
              return widget;
          };
      }(jQuery));

      $(document).ready(function () {
          var userCard = $('#card-users').UserTableWidget({
              searchUrl: "{{ route('users.search') }}",
              paginationSize: 50,
              allCoins: JSON.parse('{!!$allCoins!!}')
          });
          $(document).on('click', '.btn-blocking', function(e){
            var button = $(this);
            var action = button.data('action');
            var message = action == 'unblock' ? 'Unblock user?' : 'Block user?';
            var href = button.data('href');
            button.btnProcessing('.')
            confirmation(message, function(){
              window.location = href;
              button.btnReset();
              button.tooltip('hide');
            }, function(){
              button.btnReset();
              button.tooltip('hide');
            });
          })
      })
  </script>
@endsection