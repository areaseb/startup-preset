@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}contacts">Contatti</a></li>
    <li class="breadcrumb-item"><a href="{{config('app.url')}}contacts/lists">Liste</a></li>
@stop

@include('layouts.elements.title', ['title' => $list->nome])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{$list->nome}}</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group mb-1">
                        <button type="button" class="btn btn-default">Azioni</button>
                        <button id="actions" type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" data-display="static">
                            <span class="sr-only">Azioni</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" id="select-all" value="option1">
                                <label for="select-all" class="custom-control-label ml-3 font-weight-normal">Seleziona</label>
                            </div>
                            <div class="dropdown-divider"></div>
                            <button class="dropdown-item action-btn" id="removeFromList" disabled>Rimuovi dalla lista</button>
                            <button class="dropdown-item action-btn" id="copyToList" disabled>Copia in un'altra lista</button>
                            <button class="dropdown-item action-btn" id="moveToList" disabled>Sposta in un'altra lista</button>
                        </div>
                    </div>
                    <table class="table table-sm table-bordered table-striped table-php">
                        <thead>
                            <tr>
                                <th>
                                </th>
                                <th data-field="nome" data-order="asc">Nome <i class="fas fa-sort"></i></th>
                                <th data-field="email" data-order="asc">Email <i class="fas fa-sort"></i></th>
                                <th data-field="tipo" data-order="asc">Tipo <i class="fas fa-sort"></i></th>
                                <th data-field="statistiche" data-order="asc">Statistiche <i class="fas fa-sort"></i></th>
                                <th data-field="updated_at" data-order="asc">Modificato <i class="fas fa-sort"></i></th>
                                <th data-field="created_at" data-order="asc">Creato <i class="fas fa-sort"></i></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contacts as $contact)
                                <tr id="row-{{$contact->id}}">
                                    <td>
                                        <div class="custom-control custom-checkbox pl-5">
                                            <input class="custom-control-input" type="checkbox" id="select-item-{{$contact->id}}" value="{{$contact->id}}">
                                            <label for="select-item-{{$contact->id}}" class="custom-control-label"></label>
                                        </div>
                                    </td>
                                    <td>{{$contact->fullname}}</td>
                                    <td>{{$contact->email}}</td>
                                    <td>{{$contact->tipo}}</td>
                                    <td>
                                        @include('models.contacts.components.contact-stats')
                                    </td>
                                    <td>{{$contact->updated_at->format('d/m/Y')}}</td>
                                    <td>{{$contact->created_at->format('d/m/Y')}}</td>
                                    <td class="text-center">
                                        {!! Form::open(['method' => 'delete', 'url' => $list->url.'/contact/'.$contact->id, 'id' => "form-".$contact->id]) !!}
                                            <a href="{{$contact->url}}/edit" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-edit"></i></a>
                                            <button type="submit" id="{{$contact->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
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
$('#select-all').on('change', function(){
    if($(this).prop('checked') === true)
    {
        $('*[id*=select-item-]').each(function() {
            $(this).prop('checked', true);
        });
    }
    else
    {
        $('*[id*=select-item-]').each(function() {
            $(this).prop('checked', false);
        });
    }
});

$('input[type="checkbox"]').on('change', function(){
    let actionBtn = $('button.action-btn');
    if($(this).prop('checked') === true)
    {
        actionBtn.prop('disabled',false);
    }
    else
    {
        actionBtn.prop('disabled',true);
    }
});

$('button#removeFromList').on('click', function(){
    $.post("{{$list->url.'/update'}}", getData('remove')).done(function(data){
        if(data === 'done')
        {
            let count = 0;
            $.each(getSelectedContacts(), function(k,v){
                $('tr#row-'+v).remove();
                count++;
            });
            new Noty({
                type: 'success',
                timeout: 3000,
                text: count+' contatti eliminati dalla lista',
            }).show();
        }
    });
});


$('button#copyToList').on('click', function(){
    let html = '<p class="text-center">Scelgli la lista</p>';
    html += '<select name="list_id" class="form-control">{!!$options!!}</select>';

    var noty = new Noty({
        text: html,
        timeout: false,
        modal: true,
        layout: 'center',
        closeWith: 'button',
        theme: 'bootstrap-v4',
        buttons: [
            Noty.button('No', 'btn btn-light w-50', function () {
                noty.close();
            }),
            Noty.button('Copia', 'btn btn-success w-50', function () {
                let list_id = $('select[name="list_id"]').children("option:selected").val();
                $.post("{{$list->url.'/update'}}", getData('copy', list_id)).done(function(data){
                    if(data === 'done')
                    {
                        new Noty({
                            type: 'success',
                            timeout: 3000,
                            text: 'contatti copiati',
                        }).show();
                    }
                    console.log(data);
                });
                    noty.close();
                },
                {id: 'button1', 'data-status': 'ok'}
            )
        ]
    }).show();
});


$('button#moveToList').on('click', function(){
    let html = '<p class="text-center">Scelgli la lista</p>';
    html += '<select name="list_id" class="form-control">{!!$options!!}</select>';

    var noty = new Noty({
        text: html,
        timeout: false,
        modal: true,
        layout: 'center',
        closeWith: 'button',
        theme: 'bootstrap-v4',
        buttons: [
            Noty.button('No', 'btn btn-light w-50', function () {
                noty.close();
            }),
            Noty.button('Sposta', 'btn btn-success w-50', function () {
                    let list_id = $('select[name="list_id"]').children("option:selected").val();
                    $.post("{{$list->url.'/update'}}", getData('move', list_id)).done(function(data){
                        if(data === 'done')
                        {

                            let count = 0;
                            $.each(getSelectedContacts(), function(k,v){
                                $('tr#row-'+v).remove();
                                count++;
                            });

                            new Noty({
                                type: 'success',
                                timeout: 3000,
                                text: count+' contatti spostati',
                            }).show();
                        }
                    });
                    noty.close();
                },
                {id: 'button1', 'data-status': 'ok'}
            )
        ]
    }).show();
});


function getData(action, list_id = null)
{
    return data = {
        _token: "{{csrf_token()}}",
        contact_id: getSelectedContacts(),
        action: action,
        target_list_id: list_id
    };
}

function getSelectedContacts()
{
    let contactsIds = [];
    $('*[id*=select-item-]').each(function() {
        if( $(this).prop('checked') === true )
        {
            contactsIds.push($(this).val());
        }
    });
    return contactsIds;
}

</script>
@stop
