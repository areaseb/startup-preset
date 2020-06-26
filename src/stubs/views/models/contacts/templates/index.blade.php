@extends('layouts.app')

@include('layouts.elements.title', ['title' => 'Templates'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Templates</h3>

                    <div class="card-tools">
                        <a href="{{url('template-builder')}}" class="btn btn-sm btn-primary">Nuovo Template</a>
                    </div>

                </div>
                <div class="card-body">


                    <table id="table" class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Modificato</th>
                                <th>Creato</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr id="row-{{$template->id}}" data-model="{{$template->class}}" data-id="{{$template->id}}">
                                    <td class="editable" data-field="nome">{{$template->nome}}</td>
                                    <td data-sort="{{$template->updated_at->timestamp}}">{{$template->updated_at->format('d/m/Y')}}</td>
                                    <td data-sort="{{$template->created_at->timestamp}}">{{$template->created_at->format('d/m/Y')}}</td>
                                    <td class="text-center">
                                        {!! Form::open(['method' => 'delete', 'url' => $template->url, 'id' => "form-".$template->id]) !!}
                                            <a href="{{$template->url}}" target="_BLANK" class="btn btn-success btn-icon btn-sm"><i class="fa fa-eye"></i></a>
                                            <a href="{{$template->builder}}" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                            <button type="submit" id="{{$template->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
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
    // $("#table").DataTable(window.tableOptions);

</script>
@stop
