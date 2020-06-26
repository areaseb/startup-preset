@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}newsletters">Newsletters</a></li>
@stop

@include('layouts.elements.title', ['title' => 'Crea Newsletter'])


@section('content')

    {!! Form::open(['url' => url('newsletters'), 'autocomplete' => 'off', 'id' => 'newsletterForm']) !!}
        <div class="row">
            @include('components.errors')
            @include('models.contacts.newsletters.form')
        </div>
    {!! Form::close() !!}

@stop
