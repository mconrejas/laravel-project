@extends('masters.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card-deck">
                @if($statistics??[])
                @foreach($statistics as $stats)
                <div class="card text-white {{ $stats->get('background') }} mb-3" id="{{ str_slug($stats->get('text')) }}">
                    <div class="card-header">
                        <h5 class="card-text lead">{{ $stats->get('text') }}</h5>
                    </div>
                    <div class="card-body d-flex align-item-justify text-secondary">
                        <div class="w-25 align-self-center fa-3x fa {{$stats->get('icon')}}"></div>
                        <div class="w-75 align-self-center fa-2x text-center">{{ $stats->get('value') }}</div>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
            <div class="card">
                <div class="card-block p-3 d-flex align-item-justify">
                    <div class="w-25 d-flex align-item-justify">
                        <select class=" rounded-0 custom-select mr-1" name="filter">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                        @yearselect(['limit' => 5,'class'=>'rounded-0'])
                        @endyearselect
                    </div>
                </div>
                <div class="card-body">{!! $chart->container() !!}</div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('scripts')
    {!! $chart->script() !!}

    <script type="text/javascript">

    $(document).ready(function(){

        $("select[name='filter']").val('monthly');

        $("select[name='filter']").on('change', function(e){
        var filter = $(this).val();
        var year = $("select[name='year']").val();

        if (filter == 'yearly') {
          $("select[name='year']").attr('disabled','disabled');
        } else {
          $("select[name='year']").removeAttr('disabled');
        }

        $.get('/admin/statistics/user/filter', { filter : filter, year : filter == 'monthly' ? year : 0 })
        .done(function(data){
          window.{{$chart->id}}.data.datasets[0].data = data.datasets;
          window.{{$chart->id}}.data.labels = data.labels
          window.{{$chart->id}}.update();
        })

      });

      $("select[name='year']").on('change', function(e){
        var year = $(this).val();
        var month = $("select[name='month']").val();
        $.get('/admin/statistics/user/filter', {year: year, filter: 'monthly'})
        .done(function(data){
          window.{{$chart->id}}.data.datasets[0].data = data.datasets;
          window.{{$chart->id}}.labels = data.labels
          window.{{$chart->id}}.update();
        })
      });
    })

  </script>
@endsection

@push('scripts')
<script type="text/javascript">
    window.count = 0;
    $(document).ready(function(){
        window.Echo.join('PublicPresenceChannel')
            .here((users) => {
                window.count = users.length;
                $('#online-users .w-75').text(window.count)
            })
            .joining((user) => {
                window.count++;
                $('#online-users .w-75').text(window.count)
            })
            .leaving((user) => {
                window.count--;
                $('#online-users .w-75').text(window.count)
            });
    })
</script>
@endpush