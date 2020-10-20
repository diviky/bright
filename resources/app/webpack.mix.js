let mix = require('laravel-mix');
const glob = require('glob');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

var node = './node_modules/';
var resources = './resources/';
var public = './public/assets/';
var theme = 'resources/themes/tabler/';
mix.options({ processCssUrls: false });

mix.babel(
    glob.sync('vendor/diviky/bright/resources/assets/js/bootstrap/*.js')
        .concat(glob.sync(
            'vendor/diviky/bright/resources/assets/js/bright/*.js'))
        .concat(glob.sync(
            'vendor/diviky/bright/resources/assets/js/plugins/*.js')),
    public + 'js/bright.js');

mix.babel(
    glob.sync(resources + 'js/*.js').concat(glob.sync(resources + 'js/*/*.js')),
    public + 'js/app.js');

mix.styles(
    glob.sync('resources/css/*.css').concat(glob.sync('resources/css/*/*.css')),
    public + 'css/assets.css');

// node Scripts
mix.scripts(
    [
        node + 'jquery/dist/jquery.min.js',
        node + 'popper.js/dist/umd/popper.min.js',
        node + 'bootstrap/dist/js/bootstrap.min.js',
        node + 'nprogress/nprogress.js', node + 'jquery-pjax/jquery.pjax.js',
        node + 'moment/min/moment.min.js', node + 'noty/lib/noty.min.js',
        node + 'microplugin/src/microplugin.js', node + 'sifter/sifter.min.js',
        node + 'selectize/dist/js/selectize.min.js',
        node + 'password-strength-meter/dist/password.min.js',
        // node +
        // "eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js",
        // node + "daterangepicker/daterangepicker.js",

    ],
    public + 'js/static.js');


// node Styles
mix.styles(
    [
        node + 'nprogress/nprogress.css', node + 'animate.css/animate.min.css',
        node + 'password-strength-meter/dist/password.min.css',
        // node +
        // "eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css",
        // node + "daterangepicker/daterangepicker.css",

    ],
    public + 'css/static.css');

mix.copy(
    node + 'password-strength-meter/dist/passwordstrength.jpg',
    public + 'images/');
mix.copy(theme + 'assets/scss/fonts', public + 'fonts');

mix.sass(theme + 'assets/scss/theme.scss', public + 'css');
mix.sass(theme + 'assets/scss/app.scss', public + 'css');
mix.sass(theme + 'assets/scss/pages.scss', public + 'css');

mix.version();
