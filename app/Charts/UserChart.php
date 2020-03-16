<?php

namespace Buzzex\Charts;

use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use Buzzex\Models\User;
use Buzzex\Models\ExchangeItem;
use Buzzex\Models\ExchangePair;

class UserChart extends Chart
{
    /**
     * Initializes the chart.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Initializes the statistics
     *
     * @return array
     */
    public function statistics()
    {
        return  array(
                    collect([
                        'title' => "Users",
                        'background' => 'bg-secondary',
                        'icon'  => 'fa-users',
                        'text' => "Registered users",
                        'value' => User::count()
                    ]),
                    collect([
                        'title' => "Online",
                        'background' => 'bg-success',
                        'icon'  => 'fa-user-circle-o',
                        'text' => "Online users",
                        'value' => 0
                    ]),
                    collect([
                        'title' => "Coins",
                        'background' => 'bg-warning',
                        'icon'  => 'fa-database',
                        'text' => "Integrated coins",
                        'value' => ExchangeItem::where('deleted', 0)->count()
                    ]),
                    collect([
                        'title' => "Pairs",
                        'background' => 'bg-primary',
                        'icon'  => 'fa-exchange',
                        'text' => "Integrated pairs",
                        'value' => ExchangePair::where('deleted', 0)->count()
                    ])
                );
    }
    /**
     * filter for user monthly
     * @todo manage filter
     * @param $filter string
     * @param $year integer
     * @return array
     */
    public function monthly($filter = 'signup', $year = 0)
    {
        $dataset = collect([]);

        $year = $year == 0 ? date('Y') : $year;

        for ($month = 1; $month <= 12; $month++) {
            $monthly  = User::whereMonth('created_at', $month)->whereYear('created_at', $year)->get()
                        ->groupBy(function ($date) {
                            return \Carbon\Carbon::parse($date->created_at)->format('m');
                        })
                        ->sortBy('created_at')
                        ->map(function ($item) {
                            return $item->count();
                        });
            $dataset->push($monthly->values());
        }
        return array(
            'datasets'  => $dataset,
            'labels'    => getMonths()
        );
    }
    /**
     * filter for user yearly
     * @todo manage filter
     * @param $filter string
     * @param $years array
     * @return array
     */
    public function yearly($filter = 'signup', $years = [])
    {
        $dataset = collect([]);

        if (empty($years) || !is_array($years)) {
            $years = range((date('Y') - 5), date('Y'));
        }

        foreach ($years as $year) {
            $yearly  = User::whereYear('created_at', $year)->get()
                        ->groupBy(function ($date) {
                            return \Carbon\Carbon::parse($date->created_at)->format('Y');
                        })
                        ->sortBy('created_at')
                        ->map(function ($item) {
                            return $item->count();
                        });
            $dataset->push($yearly->values());
        }

        return array(
            'datasets'  => $dataset,
            'labels'    => $years
        );
    }
}
