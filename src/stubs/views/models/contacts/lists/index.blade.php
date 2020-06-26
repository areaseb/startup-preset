@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}contacts">Contatti</a></li>
@stop

@include('layouts.elements.title', ['title' => 'Liste'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tutte le liste</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>N. contatti</th>
                                <th>Creata</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>                        
                            @foreach($lists as $list)
                                <tr id="row-{{$list->id}}" data-model="{{$list->class}}" data-id="{{$list->id}}">
                                    <td class="editable" data-field="nome">{{$list->nome}}</td>
                                    <td>{{$list->count_contacts}}</td>
                                    <td>{{$list->created_at->format('d/m/Y')}}</td>
                                    <td class="text-center">
                                        {!! Form::open(['method' => 'delete', 'url' => $list->url, 'id' => "form-".$list->id]) !!}
                                            <a href="{{$list->url}}" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-eye"></i></a>
                                            <button type="submit" id="{{$list->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>


</script>
@stop
