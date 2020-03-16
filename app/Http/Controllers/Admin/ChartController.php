<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Http\Controllers\Controller;
use Buzzex\Charts\UserChart;
use Illuminate\Http\Request;

class ChartController extends Controller
{
	/**
     * allowed filter
     *
     * @return Response
     */
	protected $allowedFilter =  array('daily','monthly','yearly');

	/**
     * Computes the user chart.
     *
     * @return Response
     */
    public function userChart()
    {
        
		$chart = new UserChart;

 		$data =  $chart->monthly() ;
		
		$chart->dataset('Registered Users ', 'bar', $data['datasets']->values()->toArray())->fill(true)->backgroundColor('#dbf2f2');

        return $chart->api();
    }

    /**
     * Regenerate chart by filter
     *
     * @return Response
     */
    public function filterUserChart(Request $request)
    {
    	$chart = new UserChart;

		$data = collect([
			'datasets' => [],
			'labels'=> []
		]);

		if ($request->filter == 'yearly' ) {
			$data = $chart->yearly('signup');
		} elseif ($request->filter == 'monthly') {
			$data =$chart->monthly('signup', $request->year);
		}
		
		return response()->json($data, 200) ;
    }
}