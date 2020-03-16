<?php

namespace Buzzex\Contracts\Setting;

use Illuminate\Contracts\View\View;

interface ManageTheme
{
    /**
     * @param View $view
     *
     * @return mixed
     */
    public function compose(View $view);
}
