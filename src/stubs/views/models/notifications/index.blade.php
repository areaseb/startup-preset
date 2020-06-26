@extends('layouts.app')

@include('layouts.elements.title', ['title' => 'Notifiche'])


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Notifiche</h3>
                </div>
                <div class="card-body">

                    <table id="table" class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Modello</th>
                                <th>Data</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notifications as $notification)
                                <tr id="row-{{$notification->id}}">
                                    <td>{{$notification->name}}</td>
                                    <td><a class="defaultColor" href="{{$notification->notificationable->url}}">{{$notification->notificationable->class}}</a></td>
                                    <td>{{$notification->created_at->diffForHumans()}}</td>
                                    <td class="text-center">
                                        {!! Form::open(['method' => 'delete', 'url' => $notification->url, 'id' => "form-".$notification->id]) !!}
                                            @if($notification->read)
                                                <a href="#" class="btn btn-secondary btn-icon btn-sm" title="letta"><i class="fa fa-check"></i></a>
                                            @else
                                                <a href="{{$notification->url}}" class="btn btn-primary btn-icon btn-sm markAsRead" title="segna come letta"><i class="fa fa-check"></i></a>
                                            @endif
                                            <button type="submit" id="{{$notification->id}}" class="btn btn-danger btn-icon btn-sm delete"><i class="fa fa-trash"></i></button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>


                </div>
                <div class="card-footer text-center">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
    $('a.markAsRead').on('click', function(e){
        e.preventDefault();
        let btn = $(this);
        let url = btn.attr('href');
        let data = {_token: "{{csrf_token()}}"};
        $.post(url, data).done(function(response){
            if(response == 'done')
            {
                btn.removeClass('btn-primary');
                btn.addClass('btn-secondary');

                new Noty({
                    text: "Notifica Letta",
                    type: 'success',
                    theme: 'bootstrap-v4',
                    timeout: 2500,
                    layout: 'topRight'
                }).show();
            }
        })
    })

</script>
@stop
