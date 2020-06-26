@extends('layouts.app')

@include('layouts.elements.title', ['title' => 'Newsletters'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Newsletters</h3>

                    <div class="card-tools">
                        <div class="btn-group" role="group">

                            {{-- <div class="btn-group" role="group">
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
                            </div> --}}

                            <div class="btn-group" role="group">
                                <button id="create" type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" data-display="static">
                                    Crea
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="create">
                                    <a class="dropdown-item" href="{{url('newsletters/create')}}">Crea Newsletter</a>
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
                                <th>Inviata</th>
                                <th>Liste</th>
                                <th>Creata</th>
                                <th>Modificata</th>
                                <th data-orderable="false"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($newsletters as $newsletter)
                                <tr id="row-{{$newsletter->id}}">
                                    <td>{{$newsletter->nome}}</td>
                                    <td>@if($newsletter->inviata) Sì @else No @endif</td>
                                    <td>
                                        @foreach($newsletter->lists as $list)
                                            @if($loop->last)
                                                {{$list->nome}} ({{$list->count_contacts}})
                                            @else
                                                {{$list->nome.' ('.$list->count_contacts.'), '}}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td data-sort="{{$newsletter->created_at->timestamp}}">{{$newsletter->created_at->format('d/m/Y')}}</td>
                                    <td data-sort="{{$newsletter->updated_at->timestamp}}">{{$newsletter->updated_at->format('d/m/Y')}}</td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            @if($newsletter->inviata)
                                                <a href="{{$newsletter->url}}/reports" class="btn-sm btn btn-default"><i class="fa fa-eye"></i> Report</a>
                                            @endif
                                            <button id="actions" type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" data-display="static">
                                                Azioni
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right" >
                                                @if($newsletter->template_id)
                                                    <a href="{{$newsletter->template->url}}" target="_blank" class="dropdown-item"><i class="fa fa-eye"></i> Anteprima</a>
                                                    <a href="{{$newsletter->url}}/send-test" class="dropdown-item btn-modal" data-toggle="modal" data-target="#modal" data-save="Invia"><i class="fa fa-eye"></i> Invia test email</a>
                                                @endif

                                                <a href="{{$newsletter->url}}/edit" class="dropdown-item"><i class="fa fa-edit"></i> Modifica</a>
                                                {!! Form::open(['method' => 'delete', 'url' => $newsletter->url, 'id' => "form-".$newsletter->id]) !!}
                                                    <button type="submit" id="{{$newsletter->id}}" class="dropdown-item delete"><i class="fa fa-trash"></i> Elimina</button>
                                                {!! Form::close() !!}
                                                <div class="dropdown-divider"></div>
                                                <a href="{{$newsletter->url}}/send" class="dropdown-item btn-modal" data-toggle="modal" data-target="#modal" data-save="Invia Newsletter"><i class="fas fa-paper-plane"></i> Invia Newsletter</a>
                                            </div>
                                        </div>
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
