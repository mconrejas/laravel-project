const mix = require('laravel-mix');
// const LaravelMixFilenameVersioning = require('laravel-mix-filename-versioning');
const themes = ['dark', 'light'];

mix.autoload({
    jquery: ['$', 'global.jQuery',"jQuery","global.$","jquery","global.jquery"]
})
mix.options({
    processCssUrls: false
});

themes.forEach((theme) => {
    let theme_url = 'resources/themes/' + theme;

    mix.js(theme_url + '/js/app.js', 'public/js/' + theme + '-global.js')
        .scripts(
            [
                theme_url + '/js/tradingview.js'
            ],
            'public/js/' + theme + '-concatenated.js'
        )
        .sass(theme_url + '/sass/app.scss', 'public/css/' + theme + '-global.css')
        .sass(theme_url + '/sass/style.scss', 'public/css/' + theme + '-concatenated.css')
        .copy('node_modules/font-awesome/fonts', 'public/fonts');

    mix.disableNotifications();

    /*mix.webpackConfig({
        plugins: [
            new LaravelMixFilenameVersioning
        ]
    });*/
    // if (mix.inProduction()) {
    // }
    mix.version();
});

/**
 * Admin 
 **/
mix.js('resources/themes/admin/js/app.js', 'public/js/admin-global.js')
    .sass('resources/themes/admin/sass/app.scss', 'public/css/admin-global.css')