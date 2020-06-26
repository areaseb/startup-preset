<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="baseURL" content="{{config('app.url')}}">
    <meta name="iva" content="{{config('app.iva')}}">
    <meta name="token" content="{{csrf_token()}}">

    @yield('meta_title')


    <link rel="stylesheet" href="{{asset('public/css/all.css')}}">
    <link rel="stylesheet" href="{{asset('public/css/style.css')}}">

</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        @include('layouts.elements.top-nav')

        @include('layouts.elements.side-nav')

        <div class="content-wrapper">

            @yield('title')

            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>

        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 0.1
            </div>
            <strong>Copyright &copy; 2020 <a href="https://www.areaseb.it">Areaseb srl</a>.</strong> All rights reserved.
        </footer>

    </div>

<div class="modal" tabindex="-1" role="dialog" id="global-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                <button type="submit" class="btn btn-primary btn-save-modal">Salva</button>
            </div>
        </div>
    </div>
</div>

    <script src="{{asset('public/js/all.js')}}"></script>
    <script src="{{asset('public/js/adminlte.min.js')}}"></script>
    <script src="{{asset('public/js/global.js')}}"></script>
    @yield('scripts')
    @stack('scripts')
    @include('components.session')

</body>
