const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/videoRequest.js', 'public/js')
    .js('resources/js/notification.js', 'public/js')
    .postCss('resources/css/form.css', 'public/css')
    .postCss('resources/css/review.css', 'public/css')
    .postCss('resources/css/navbar.css', 'public/css')
    .postCss('resources/css/admin/viewOrder.css', 'public/css/admin')
    .postCss('resources/css/admin/viewOrders.css', 'public/css/admin')
    .postCss('resources/css/admin/dashboard.css', 'public/css/admin');