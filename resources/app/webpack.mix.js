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

var bower = './bower_components/';
var node = './node_modules/';
var resources = './resources/';
var public = './public/assets/';
var theme = 'resources/themes/tabler/';
mix.options({processCssUrls: false});

mix.babel(
    glob.sync('vendor/sankar/laravel-karla/resources/assets/js/bootstrap/*.js')
        .concat(glob.sync(
            'vendor/sankar/laravel-karla/resources/assets/js/karla/*.js'))
        .concat(glob.sync(
            'vendor/sankar/laravel-karla/resources/assets/js/plugins/*.js')),
    public + 'js/karla.js');

mix.babel(
    glob.sync(resources + 'js/*.js').concat(glob.sync(resources + 'js/*/*.js')),
    public + 'js/app.js');

mix.styles(
    glob.sync('resources/css/*.css').concat(glob.sync('resources/css/*/*.css')),
    public + 'css/assets.css');

// Bower Scripts
mix.scripts(
    [
      node + 'jquery/dist/jquery.min.js',
      node + 'popper.js/dist/umd/popper.min.js',
      node + 'bootstrap/dist/js/bootstrap.min.js',
      bower + 'nprogress/nprogress.js', bower + 'jquery-pjax/jquery.pjax.js',
      bower + 'moment/min/moment.min.js', bower + 'noty/lib/noty.min.js',
      bower + 'microplugin/src/microplugin.js', bower + 'sifter/sifter.min.js',
      bower + 'selectize/dist/js/selectize.min.js',
      bower + 'password-strength-meter/dist/password.min.js',
      // bower +
      // "eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js",
      // node + "daterangepicker/daterangepicker.js",

    ],
    public + 'js/static.js');


// Bower Styles
mix.styles(
    [
      bower + 'nprogress/nprogress.css', bower + 'animate.css/animate.min.css',
      bower + 'select2/dist/css/select2.min.css',
      bower + 'password-strength-meter/dist/password.min.css',
      // bower +
      // "eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css",
      // node + "daterangepicker/daterangepicker.css",

    ],
    public + 'css/static.css');

mix.copy(
    bower + 'password-strength-meter/dist/passwordstrength.jpg',
    public + 'images/');
mix.copy(theme + 'assets/scss/fonts', public + 'fonts');

mix.sass(theme + 'assets/scss/theme.scss', public + 'css');
mix.sass(theme + 'assets/scss/app.scss', public + 'css');
mix.sass(theme + 'assets/scss/pages.scss', public + 'css');

mix.version();
