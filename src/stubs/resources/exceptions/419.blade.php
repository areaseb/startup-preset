@extends('exceptions.illustrated-layout')

@section('title', __('Pagina Scaduta'))
@section('code', '419')
@section('message', __('Pagina Scaduta'))

@section('scripts')
    <script>
        setTimeout(function(){ window.location.href="{{config('app.url')}}login" }, 2000);
    </script>
@stop
