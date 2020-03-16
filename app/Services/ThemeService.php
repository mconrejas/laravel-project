<?php

namespace Buzzex\Services;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Buzzex\Contracts\Setting\ManageTheme;

class ThemeService implements ManageTheme
{

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $global_theme = session()->has('theme') ? session()->get('theme') : config('theme.default', 'light');

        if (Auth::check()) {
            $global_theme = Auth::user()->settings('theme', config('theme.default'));
        }

        $view->with('user_theme', $global_theme);
    }
}
