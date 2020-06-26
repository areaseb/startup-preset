@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}users">Utenti</a></li>
@stop

@include('layouts.elements.title', ['title' => 'Modifica Utente'])


@section('content')

    {!! Form::model($element, ['url' => url('users/'.$element->id), 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'userForm']) !!}
        <div class="row">
            @include('components.errors')
            @include('models.users.form')
        </div>
    {!! Form::close() !!}

@stop
