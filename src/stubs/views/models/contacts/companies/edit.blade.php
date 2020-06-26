@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}companies">Aziende</a></li>
    <li class="breadcrumb-item"><a href="{{$company->url}}">{{$company->rag_soc}}</a></li>
@stop

@include('layouts.elements.title', ['title' => 'Modifica Azienda'])


@section('content')

    {!! Form::model($company, ['url' => $company->url, 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'companyForm']) !!}
        <div class="row">
            @include('components.errors')
            @include('models.contacts.companies.form')
        </div>
    {!! Form::close() !!}

@stop
