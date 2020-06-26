@extends('layouts.app')

@include('layouts.elements.title', ['title' => 'Aziende'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aziende</h3>

                    <div class="card-tools">
                        <div class="btn-group" role="group">

                            <div class="btn-group" role="group">
                                <button id="filter" type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" data-display="static">
                                    Filtra
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="filter">
                                    <a class="dropdown-item" href="{{url('companies')}}?partner=1">Partner</a>
                                    <a class="dropdown-item" href="{{url('companies')}}?fornitore=1">Fornitori</a>
                                    <a class="dropdown-item" href="{{url('companies')}}?tipo=Lead">Lead</a>
                                    <a class="dropdown-item" href="{{url('companies')}}?tipo=Prospect">Prospect</a>
                                    <a class="dropdown-item" href="{{url('companies')}}?tipo=Client">Client</a>
                                </div>
                            </div>

                            <div class="btn-group" role="group">
                                <button id="create" type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" data-display="static">
                                    Crea
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="create">
                                    <a class="dropdown-item" href="{{url('companies/create')}}">Crea Azienda</a>
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
                                <th>Provincia</th>
                                <th>Data</th>
                                <th data-orderable="false"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companies as $company)
                                <tr id="row-{{$company->id}}">
                                    <td><a class="defaultColor" href="{{$company->url}}">{{$company->rag_soc}}</a></td>
                                    <td>{{$company->provincia}}</td>
                                    <td data-sort="{{$company->created_at->timestamp}}">{{$company->created_at->format('d/m/Y')}}</td>
                                    <td class="text-center">
                                        {!! Form::open(['method' => 'delete', 'url' => $company->url, 'id' => "form-".$company->id]) !!}
                                            <a href="{{$company->url}}/edit" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                            <button type="submit" id="{{$company->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
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
