@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}contacts">Contatti</a></li>
@stop

@include('layouts.elements.title', ['title' => 'Crea Contatto'])


@section('content')

    {!! Form::open(['url' => url('contacts'), 'autocomplete' => 'off', 'id' => 'contactForm']) !!}
        <div class="row">
            @include('components.errors')
            @include('models.contacts.form')
        </div>
    {!! Form::close() !!}

@stop
