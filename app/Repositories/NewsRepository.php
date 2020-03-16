<?php

namespace Buzzex\Repositories;

use Buzzex\Models\News;
use Illuminate\Http\Request;

class NewsRepository
{
    /**
     *
     * @return \Buzzex\Models\News
     */
    public function getAll($take = 5, $skip = 0)
    {
        return News::withTrashed()->take($take)->skip($skip)->get();
    }

    /**
     *
     * @return \Buzzex\Models\News
     */
    public function getInActiveNews($take = 50, $skip = 0)
    {
        return News::onlyTrashed()->take($take)->skip($skip)->get();
    }

    /**
     *
     * @return \Buzzex\Models\News
     */
    public function getActiveNews($take = 50, $skip = 0)
    {
        return News::take($take)->skip($skip)->get();
    }

    /**
     * Search from repository
     * @param Illuminate\Http\Request
     * @return mixed
     */
    public function search(Request $request)
    {
        $news = News::select('*');
        
        if ($request->has('filter') && in_array($request->filter, ['all','active','inactive'])) {
            switch ($request->filter) {
                case 'active':
                    break;
                case 'inactive':
                    $news = $news->onlyTrashed();
                    break;
                default: //all
                    $news = $news->withTrashed();
                    break;
            }
        }

        if ($request->has('term') && !empty($request->term)) {
            $news = $news->where(function ($query) use ($request) {
                return $query->where('link', 'like', '%'.$request->term.'%')
                    ->orWhere('text', 'like', '%'.$request->term.'%');
            });
        }

        return array(
            'counts' => ceil($news->count()  / $request->size),
            'data' => $news->latest()->take($request->size)
                    ->skip($request->size * ($request->page - 1))
                    ->get()
        );
    }
}
