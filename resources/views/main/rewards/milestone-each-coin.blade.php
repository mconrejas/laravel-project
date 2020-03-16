@forelse($data as $key => $reward)
    <div class="card-header border-top border-bottom bg-secondary text-light">
        <span class="card-title lead font-weight-bold">{{$key}}</span>
        <a href="{{ route('vote.view',['symbol' => $key ]) }}" class="float-right">{{ __('Go to competition page') }}</a>
    </div>
    <div class="card-body my-0 py-1">
    @foreach($reward as $index => $item)
    <div class="row border py-3">
        <div class="col-12 col-sm-6 col-md-9">
            <div>
                <span class="font-weight-bold">
                    {{__('Milestone')}} {{$item['milestone_number']}}
                </span>
             </div>
             @if($item['is_coin_partner'])
             <div>
                {{__('Coin Partner Rewards:')}}
                <span class="font-weight-bold">
                     {{ $item['coin_partner_rewards'] }} BZX
                </span><br />
                {{__('Coin Partner Rewards Claimed:')}}
                <span class="font-weight-bold">
                     {{ $item['coin_partner_reward_claimed'] }} BZX
                </span><br />
                {{__('Coin Partner Rewards Claimable:')}}
                <span class="font-weight-bold">
                     {{ $item['coin_partner_reward_claimable'] }} BZX
                </span>
             </div>
             @endif
             <div>
                {{__('Milestone Total Traded Volume')}}<sup class="text-danger">*</sup> :
                <span class="font-weight-bold">
                     {{$item['total_volume']}} BTC
                </span>
             </div>
             <div>
                {{__('Milestone Rank:')}}
                <span class="font-weight-bold">
                    {{ $item['rank']}}
                </span>
             </div>
             <div>
                {{__('Milestone Claimed:')}}
                <span class="font-weight-bold">
                    {{ $item['claimed']}} BZX
                </span>
             </div>
             <div>
                {{__('Milestone Claimable:')}}
                <span class="font-weight-bold">
                    {{ $item['claimable']}} BZX
                </span>
             </div>
             <div>
                {{__('Total Claimable:')}}
                <span class="font-weight-bold">
                    {{ $item['total_claimable']}} BZX
                </span>
             </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 d-flex  justify-content-end">
            @if($item['is_claiming_available'])
                @if($item['total_claimable'] > 0)
                    <button style="max-height: 40px;" data-id="{{$item['id']}}" class="btn-claim btn-claim-rewards d-flex btn btn-outline-info align-items-center" type="button" >{{ __('Claim Rewards') }}</button>
                @else
                    <button style="max-height: 40px;" class="btn-claim disabled d-flex btn btn-outline-secondary align-items-center" type="button" >{{ __('Claimed') }}</button>
                @endif
            @else
                <button style="max-height: 40px;" class="btn-claim d-flex btn btn-outline-info align-items-center" type="button" >{{ __('Claiming Soon') }}</button>

            @endif
        </div>
    </div>
    @endforeach
    </div>
@empty
<div class="alert alert-info py-5 m-1">
    <p class="lead text-center">
        No rewards to claim. <br><br>
        Join <a class="" target="_blank" href="{{ route('vote.view') }}">$13 million trading competition</a> to earns rewards.
    </p>
</div>
@endforelse