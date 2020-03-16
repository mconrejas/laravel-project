@extends('masters.admin')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <img src="{{asset('img/namelogo-1500x500.png')}}" class="img-fluid card-img-top rounded-0">
        <div class="card-body p-0">
          <div class="row mx-0 my-1">
            <div class="col-sm-12 col-md-6">
                <div class="p-3 border">
                  <span class="fa fa-server"></span> Info
                </div>
                <ul class="list-group border-0">
                @foreach($info as $item => $version)
                  <li class="list-group-item rounded-0 border-left-0 border-right-0 d-flex justify-content-between align-items-center">{{$item}} <small class="badge badge-pill">{{$version}}</small></li>
                @endforeach
                </ul>
                <div class="p-3 border">
                  <span class="fa fa-gears"></span> Status
                </div>
                <ul class="list-group border-0 list-status">
                  <li class="list-group-item rounded-0 border-left-0 border-right-0 d-flex justify-content-between align-items-center">Realtime Events 
                    <button rel="tooltip" title="If a toast shows then the realtime events are working fined else there might be error on the laravel echo server" data-status="test.realtime.events" class="btn btn-sm btn-buzzex">Test</button>
                  </li>
                </ul>
            </div>
            <div class="col-sm-12 col-md-6">
                <div class="p-3 border">
                  <span class="fa fa-file-archive-o"></span> Dependency
                </div>
                <ul class="list-group border-0">
                  @foreach($dependency->require as $item => $version)
                  <li class="list-group-item rounded-0 border-left-0 border-right-0 d-flex justify-content-between align-items-center">{{$item}} <small class="badge badge-secondary badge-pill">{{$version}}</small></li>
                  @endforeach
                </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
  $(document).ready(function(argument) {
     $(document).on('click', '.list-status .btn', function(e){
        var button = $(this);
        button.btnProcessing('.');
        var status = $(this).data('status');
        $.post('{{ route("admin.check") }}', {
            status : status
        }).done(function(argument) {
          button.btnReset();
        }).fail(function(err){
          button.btnReset();
        })
     })
  });
</script>
<script type="text/javascript">
  window.Echo.channel('Test_Channel')
      .listen('TestRealtime', function(data) {
          console.log(data)
          toast({ title : data.message, type : 'success' });
      });
</script>
@endpush