<?php

namespace Buzzex\Http\Controllers\Admin;

use Buzzex\Charts\UserChart;
use Buzzex\Events\TestRealtime;
use Buzzex\Http\Controllers\Controller;
use Carbon\Carbon;
use File;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    /**
     * Display dashboard page.
     *
     * @return void
     */
    public function index(Request $request)
    {
        $chart = new UserChart;

        $chart->labels(getMonths())->load(route('statistics.user'));

        $statistics = $chart->statistics();

        return view('admin.dashboard.index', compact('chart', 'statistics'));
    }

    
    /**
     * Display about page.
     *
     * @return void
     */
    public function about()
    {
        $dependency = json_decode(File::get(base_path('composer.json')));
        $info   = array(
                'Name'=> config('app.name'),
                'Url'=> config('app.url'),
                'Timezone'=> config('app.timezone'),
                'Locale' => config('app.locale'),
                'Debug Enable' => config('app.debug') ? 'true' : 'false',
                'Database' => config('database.default'),
                'Themes' => implode(",", config('theme.themes')),
            );

        return view('admin.dashboard.about', compact('dependency', 'info'));
    }

    /**
     * Display about page.
     *
     * @return void
     */
    public function checkStatus(Request $request)
    {
        $status = $request->has('status') ? $request->status : 'test.realtime.events';
        $message = "Error! Please inform the devs";

        switch ($status) {
            case 'test.queue.works':
                
                break;
            
            default:
                $message = "Realtime test only. Broadcasting events seems fined.";
                broadcast(new TestRealtime($message));
                break;
        }
        
        return response()->json(['message' => $message], 200);
    }
}
