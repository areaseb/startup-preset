@extends('layouts.app')

@include('layouts.elements.title', ['title' => 'Dashboard'])

@section('content')
    <div class="row">

        @can('companies.read')
            @include('home-components.companies')
        @endcan
        @can('contacts.read')
            @include('home-components.contacts')
        @endcan

        @can('costs.read')
            @include('home-components.costi-in-scadenza')
        @endcan
        @can('invoices.read')
            @include('home-components.fatture-in-scadenza')
        @endcan

    </div>
@stop


@section('scripts')
    <script src="{{asset('plugins/chart.js/Chart.min.js')}}"></script>
@stop
