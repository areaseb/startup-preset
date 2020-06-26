@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}contacs">Contatti</a></li>
@stop

@include('layouts.elements.title', ['title' => $contact->fullname])

@section('content')

    <div class="row">

        <div class="col-md-3">

            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        {!!$contact->avatar!!}
                        <h3 class="profile-username text-center">{{$contact->fullname}}</h3>
                        @if($contact->tipo)
                            <p class="text-muted text-center">{{$contact->tipo}}</p>
                        @endif
                        <a href="{{$contact->url}}/edit" class="btn btn-sm btn-primary btn-block"><b> <i class="fa fa-edit"></i> Modifica</b></a>
                    </div>
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Dettagli</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Indirizzo</strong>
                    <p class="text-muted">{{$contact->indirizzo}} {{$contact->cap}}, {{$contact->citta}}<br>
                        {{$contact->provincia}} {{$contact->nazione}}
                    </p>
                    <hr>

                    <strong><i class="fas fa-at mr-1"></i> Contatti</strong>
                    @if($contact->cellulare)<p class="text-muted"><b>Tel:</b> {{$contact->cellulare}}</p>@endif
                    @if($contact->email)<p class="text-muted"><b>Email:</b> <small>{{$contact->email}}</small></p>@endif
                </div>
            </div>
        </div>


        <div class="col-md-9">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Activity</a></li>
                        <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Timeline</a></li>
                        <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Settings</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="activity">
                            @if($contact->reports()->exists())
                                <div class="row">
                                    <div class="col-md-6">
                                        summery
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Aperta</th>
                                                    <th>Statistiche</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($contact->reports as $report)
                                                    @if($report->opened)
                                                    <tr>
                                                        <td>
                                                            Sì
                                                        </td>
                                                        <td>
                                                            <a href="#" class="btn btn-sm">Vedi stats</a>
                                                        </td>
                                                    </tr>
                                                    @else
                                                        <tr><td colspan="2"> non aperta </td></tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            @endif
                        </div>
                        <div class="tab-pane" id="timeline">
                            Timeline
                        </div>
                        <div class="tab-pane" id="settings">
                            Settings
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

@stop
