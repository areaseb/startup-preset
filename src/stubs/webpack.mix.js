const mix = require('laravel-mix');

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


 mix.styles([
     'public/plugins/fontawesome-free/css/all.min.css',
     'public/plugins/ionicons/ionicons.min.css',
     'public/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css',
     'public/plugins/datatables-responsive/css/responsive.bootstrap4.min.css',
     'public/plugins/daterangepicker/daterangepicker.css',
     'public/plugins/icheck-bootstrap/icheck-bootstrap.min.css',
     'public/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css',
     'public/plugins/select2/css/select2.min.css',
     'public/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css',
     'public/plugins/noty/noty.min.css',
     'public/css/adminlte.min.css'
 ], 'public/css/all.css');

 mix.scripts([
     'public/plugins/jquery/jquery.min.js',
     'public/plugins/bootstrap/js/bootstrap.bundle.min.js',
     'public/plugins/datatables/jquery.dataTables.min.js',
     'public/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js',
     'public/plugins/datatables-responsive/js/dataTables.responsive.min.js',
     'public/plugins/datatables-responsive/js/responsive.bootstrap4.min.js',
     'public/plugins/select2/js/select2.full.min.js',
     'public/plugins/moment/moment-with-locales.min.js',
     'public/plugins/inputmask/min/jquery.inputmask.bundle.min.js',
     'public/plugins/daterangepicker/daterangepicker.js',
     'public/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js',
     'public/plugins/bootstrap-switch/js/bootstrap-switch.min.js',
     'public/plugins/bs-custom-file-input/bs-custom-file-input.min.js',
     'public/plugins/noty/noty.min.js'
 ], 'public/js/all.js');

// mix.js('resources/js/app.js', 'public/js')
//     .sass('resources/sass/app.scss', 'public/css');
