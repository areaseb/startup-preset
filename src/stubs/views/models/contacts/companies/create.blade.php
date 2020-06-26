@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}companies">Aziende</a></li>
@stop

@include('layouts.elements.title', ['title' => 'Crea Azienda'])


@section('content')

    {!! Form::open(['url' => url('companies'), 'autocomplete' => 'off', 'id' => 'companyForm']) !!}
        <div class="row">
            @include('components.errors')
            @include('models.contacts.companies.form')
        </div>
    {!! Form::close() !!}

@stop
