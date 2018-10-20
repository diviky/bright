let mix = require("laravel-mix");

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

var bower = "./bower_components/";
var node = "./node_modules/";
var resources = "./resources/";
var public = "./public/";
var theme = "resources/themes/tabler/";

mix.scripts(
  ["resources/assets/js/*.js", "resources/assets/js/*/*.js"],
  "public/js/app.js"
);

mix.styles(
  ["resources/assets/js/*.css", "resources/assets/js/*/*.css"],
  "public/css/assets.css"
);

// Bower Scripts
mix.scripts(
  [
    node + "jquery/dist/jquery.min.js",
    node + "popper.js/dist/umd/popper.min.js",
    node + "bootstrap/dist/js/bootstrap.min.js",
    bower + "jquery.livequery/dist/jquery.livequery.min.js",
    bower + "nprogress/nprogress.js",
    bower + "jquery-pjax/jquery.pjax.js",
    //bower + "waypoints/lib/jquery.waypoints.min.js",
    node+ 'clipboard/dist/clipboard.min.js',
  ],
  public + "js/static.js"
);

mix.options({ processCssUrls: false });

// Bower Styles
mix.styles(
  [
    bower + "nprogress/nprogress.css",
    bower + "font-awesome/css/font-awesome.min.css",
    bower + "animate.css/animate.min.css"
  ],
  public + "css/static.css"
);

mix.copy(bower + "font-awesome/fonts", public + "fonts");
mix.copy(theme + "assets/scss/fonts", public + "fonts");


mix.sass(theme+"assets/scss/app.scss", public + "css");
