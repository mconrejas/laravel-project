<?php

namespace Buzzex\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cookie;

trait UserVotes
{

    /**
     * Indicates if the model has voted in general or on a given coin project id
     *
     */
    public function hasVoted($id = false)
    {
        if ($id) {
            return $this->votes()->where('project_id', $id)->count() > 0;
        }

        return $this->votes()->with(['coin_projects' => function ($query) {
            $query->where('status', '<>', 3);
        }])->count() > 0;
    }
}
