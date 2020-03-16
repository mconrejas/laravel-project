<?php

namespace Buzzex\Providers;

use Buzzex\Contracts\Setting\ManageTheme;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        // Blade::setEchoFormat('e(utf8_encode(%s))');

        $this->customComposer();

        $this->customComponents();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Custom Blade components
     */
    protected function customComposer()
    {
        view()->composer('*', ManageTheme::class);

        view()->composer('admin.*', function ($view) {
            $menus = array();

            if (File::exists(base_path('resources/views/admin/menus.json'))) {
                $menus = json_decode(File::get(base_path('resources/views/admin/menus.json')));
            }

            $view->with('buzzexAdminMenus', $menus);
        });

        view()->composer('*', function ($view) {
            $links = array();

            if (File::exists(base_path('resources/views/partials/static_links.json'))) {
                $links = json_decode(File::get(base_path('resources/views/partials/static_links.json')));
            }

            $view->with('buzzexLinks', $links);
        });
    }

    /**
     * Custom Blade components
     */
    protected function customComponents()
    {
        Blade::component('components.smsprefix', 'smsprefix');
        Blade::component('components.monthselect', 'monthselect');
        Blade::component('components.yearselect', 'yearselect');
        Blade::component('components.coinselect', 'coinselect');
        Blade::component('components.pairselect', 'pairselect');
    }
}
