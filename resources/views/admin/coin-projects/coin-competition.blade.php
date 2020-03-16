@extends('masters.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ $current_coin }} {{__('Competition')}}</div>
                <div class="card-body">
                <div class="row">
                    <div class="col-4 offset-8">
                        <form action="{{route('project.coincompetition')}}" method="GET" id="frm-competition">
                            <div class="input-group align-self-center">
                                <div class="input-group-prepend">
                                    <label class="input-group-text border-0 bg-transparent" for="role-select">{{__('Coin')}} :</label>
                                </div>
                              
                                  @php
                                    $selected_coin = Request::get('coin') ?? 'ADZ';
                                  @endphp
                                  @coinselect(['selected' => $selected_coin, 'class'=> 'rounded-0', 'name' => 'coin'])
                                  @endcoinselect
                            </div>
                        </form> 
                    </div>
                    <div class="col-lg-12 mt-2">
                        @php
                            $count = 1;
                        @endphp
                        @forelse($coins as $item_id => $coin)
 
                        <div id="accordion_{{$item_id}}" class="">
                            <div class="card mb-0">
                                <div class="card-header" id="heading_{{$item_id}}">
                                    <h5 class="m-0">
                                        <a class="text-dark " data-toggle="collapse" href="#collapse_{{$item_id}}" aria-expanded="true"> 
                                            {{__('Milestone')}} {{ $count }}
                                        </a>
                                    </h5>
                                </div>
                    
                                <div id="collapse_{{$item_id}}" class="collapse show p-2" aria-labelledby="heading_{{$item_id}}" data-parent="#accordion_{{$item_id}}">

                                   
                                    <p>
                                        <b class="header-title">{{__('Coin Partner Winner')}}</b>
                                         
                                         <ul>
                                            @if(isset($coin->winners['partner_winner']))
                                             <li>{{$coin->winners['partner_winner']['email']}} ({{$coin->winners['partner_winner']['reward']}} BZX) 
                                                @if($coin->winners['partner_winner']['claimed_at']!==NULL)
                                                    <code>{{__('claimed')}} &#x2713;</code>
                                                @endif
                                            </li>
                                            @else
                                                <li>{{__('No Coin partner!')}}</li>
                                            @endif
                                         </ul>
                                    </p>
                                  

                                    <p>
                                        <b class="header-title">{{__('General Winners')}}</b>
                                        <table class="table table-compressed table-tabulator border mb-0">
                                            <thead>
                                                <th>Rank</th>
                                                <th>Email</th>
                                                <th>Total Volume Traded</th>
                                                <th>Rewards</th>
                                                <th>Claimed</th>
                                            </thead>
                                            <tbody>
                                            @if(isset($coin->winners['general_winners']))
                                                @foreach($coin->winners['general_winners'] as $rank => $general_winner)
                                                <tr>
                                                    <td>
                                                        @if($rank < 3) 
                                                        <b class="text-danger">{{str_ordinal($rank+1)}}</b>
                                                        @endif
                                                    </td>
                                                    <td>{{$general_winner['email']}}</td>
                                                    <td>{{$general_winner['total_volume']}}</td>
                                                    <td>({{number_format($general_winner['reward'])}} BZX)</td>
                                                    <td>
                                                        @if($general_winner['claimed_at']!==NULL)
                                                            <code>{{ date('Y-m-d H:i:s', $general_winner['claimed_at']) }}</code>
                                                        @else
                                                            <code>{{ __('Not yet claimed') }}</code>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr><td colspan="5">{{__('No traders')}}</td></tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </p>
                                       
                                </div>
                            </div>
                        @php
                            $count++;
                        @endphp
                        </div> <!-- end #accordions--> 
                        @empty
                            <div id="accordion" class="">
                                <div class="card mb-0">
                                    <div class="card-header" id="heading">
                                        <h5 class="m-0">
                                            {{__('No milestone completed!')}}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        @endforelse

                    </div> <!-- end col --> 
                </div> <!-- end row -->  
                </div> <!-- end card-body-->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')

<script type="text/javascript">
    $(document).ready(function(){
        $(document).on('change', '[name="coin"]', function(){
            $('#frm-competition').submit();
        });
    });
</script>
@endsection

