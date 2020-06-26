@extends('layouts.app')

@include('layouts.elements.title', ['title' => 'Utenti'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Utenti</h3>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <a href="{{url('roles/create')}}" data-toggle="modal" data-target="#modal" class="btn btn-default btn-sm btn-modal">Crea Ruolo</a>
                            <div class="btn-group" role="group">
                                <button id="filter" type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" data-display="static">
                                    Filtra
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    @foreach($roles as $role)
                                        <a class="dropdown-item" href="{{url('users')}}?role={{$role->id}}">{{$role->name}}</a>
                                    @endforeach
                                </div>
                            </div>
                            <div class="btn-group" role="group">
                                <button id="create" type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" data-display="static">
                                    Crea
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{url('users/create')}}">Crea Utente</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="table" class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Data</th>
                                <th data-orderable="false"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr id="row-{{$user->id}}">
                                    <td>{{$user->contact->fullname}}</td>
                                    <td>{{$user->email}}</td>
                                    <td data-sort="{{$user->created_at->timestamp}}">{{$user->created_at->format('d/m/Y')}}</td>
                                    <td class="text-center">
                                        {!! Form::open(['method' => 'delete', 'url' => $user->url, 'id' => "form-".$user->id]) !!}
                                            <a href="{{$user->url}}/edit" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                            <button type="submit" id="{{$user->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
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
    $("#table").DataTable(window.tableOptions);
</script>
@stop
