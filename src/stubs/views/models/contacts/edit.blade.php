@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}contacts">Contatti</a></li>
        <li class="breadcrumb-item"><a href="{{$contact->url}}">{{$contact->fullname}}</a></li>
@stop

@include('layouts.elements.title', ['title' => 'Modifica Contatto'])


@section('content')

    {!! Form::model($contact, ['url' => $contact->url, 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'contactForm']) !!}
        <div class="row">
            @include('components.errors')
            @include('models.contacts.form')
        </div>
    {!! Form::close() !!}

@stop
