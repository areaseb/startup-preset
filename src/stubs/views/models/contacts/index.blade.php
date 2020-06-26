@extends('layouts.app')

@include('layouts.elements.title', ['title' => 'Contatti'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Contatti</h3>
                    <div class="card-tools">

                        <div class="btn-group" role="group">
                            <div class="form-group mr-3 mb-0 mt-1">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="customSwitch1" @if(request()->input()) checked @endif>
                                    <label class="custom-control-label" for="customSwitch1">Ricerca Avanzata</label>
                                </div>
                            </div>

                            <div class="btn-group" role="group">
                                <button id="lists" type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" data-display="static">
                                    Liste
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    @include('models.contacts.components.list-nav')
                                </div>
                            </div>
                            <div class="btn-group" role="group">
                                <button id="create" type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" data-display="static">
                                    Crea
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{url('contacts/create')}}">Crea Contatto</a>
                                    <a class="dropdown-item" href="{{url('imports/contacts')}}">Importa da csv</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-body">

                    @include('models.contacts.components.advanced-search')

                    {{-- @include('models.contacts.components.search') --}}

                    <table id="table" class="table table-sm table-bordered table-striped table-php">
                        <thead>
                            <tr>
                                <th data-field="nome" data-order="asc">Nome <i class="fas fa-sort"></i></th>
                                <th>Liste</th>
                                <th data-field="tipo" data-order="asc">Tipo <i class="fas fa-sort"></i></th>
                                <th data-field="updated_at" data-order="asc">Modificato <i class="fas fa-sort"></i></th>
                                <th>Creato</th>
                                <th>Provincia</th>
                                <th data-orderable="false"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contacts as $contact)
                                <tr id="row-{{$contact->id}}">
                                    <td><a class="defaultColor" href="{{$contact->url}}">{{$contact->fullname}}</a></td>
                                    <td>
                                        @foreach($contact->lists as $list)
                                            {{$list->nome}}
                                        @endforeach
                                    </td>
                                    <td>{{$contact->tipo}}</td>
                                    <td data-sort="{{$contact->updated_at->timestamp}}">{{$contact->updated_at->format('d/m/Y')}}</td>
                                    <td data-sort="{{$contact->created_at->timestamp}}">{{$contact->created_at->format('d/m/Y')}}</td>
                                    <td>{{$contact->provincia}}</td>
                                    <td class="text-center">
                                        {!! Form::open(['method' => 'delete', 'url' => $contact->url, 'id' => "form-".$contact->id]) !!}
                                            <a href="{{$contact->url}}/edit" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                            <button type="submit" id="{{$contact->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>


                </div>
                <div class="card-footer text-center">
                    {{ $contacts->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
    // $("#table").DataTable(window.tableOptions);

</script>
@stop
