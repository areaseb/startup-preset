@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}users">Utenti</a></li>
@stop

@include('layouts.elements.title', ['title' => 'Crea Utente'])


@section('content')

    {!! Form::open(['url' => url('users'), 'autocomplete' => 'off', 'id' => 'userForm']) !!}
        <div class="row">
            @include('components.errors')
            @include('models.users.form')
        </div>
    {!! Form::close() !!}

@stop
