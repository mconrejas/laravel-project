<?php

namespace Buzzex\Http\Controllers\Main;

use Buzzex\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class NotificationsController extends Controller
{

    /**
     * Show the application homepage for auth
     *
     * @return \Illuminate\Http\Response|View
     */
    public function index()
    {
        return view('main.profile.message');
    }

    /**
     * Get unread Notification for current users
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getNotifications(Request $request)
    {
        $user = Auth::user();
        $size = $request->size ?? 50;
        $page = $request->page ?? 1;

        $notifications = $user->notifications()
                ->latest()
                ->skip($size * ($page-1))
                ->take($size)
                ->get();

        $datum = array();
        
        if ($notifications) {
            foreach ($notifications as $key => $item) {
                if (!empty($item)) {
                    $data = (object) $item->data;
                    if (!isset($data->message) || empty(trim($data->message))) {
                        continue;
                    }
                    $datum[] = array(
                            'id' => $item->id,
                            'time' => Carbon::parse($item->created_at)->format('Y-m-d H:i:s'),
                            'type' => $item->type,
                            'sender' => $data->sender ?? "System",
                            'message' => $data->message,
                            'is_read' => !is_null($item->read_at) ? 1 : 0,
                        );
                }
            }
        }
     
        $counts = $user->notifications()->count();

        return response()->json([
            'last_page' => ceil($counts / $size),
            'data' => (array) $datum,
            'all_counts' => $counts,
            'unread_counts' => $user->unreadNotifications()->count()
        ], 200);
    }

    /**
     * Get unread Notification for current users
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getUnreadNotifications(Request $request)
    {
        $user = Auth::user();
        $notifications = $user->unreadNotifications;
        $notifications->markAsRead();

        return response()->json($notifications, 200);
    }

    /**
     * Mark as read the Notification for current users by id
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function markAsRead(Request $request)
    {
        $request->validate(['id' => 'required|string']);

        $user = Auth::user();
        if ($request->id == 'all') {
            $user->unreadNotifications()->update(['read_at' => now()]);
        } else {
            $user->unreadNotifications()->where('id', $request->id)->update(['read_at' => now()]);
        }

        return response()->json(['message' => 'Successfully marked.'], 200);
    }
}
