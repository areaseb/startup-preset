@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}newsletters">Newsletters</a></li>
@stop

@include('layouts.elements.title', ['title' => 'Modifica Newsletter'])


@section('content')

    {!! Form::model($newsletter, ['url' => $newsletter->url, 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'newsletterForm']) !!}
        <div class="row">
            @include('components.errors')
            @include('models.contacts.newsletters.form')
        </div>
    {!! Form::close() !!}

@stop
