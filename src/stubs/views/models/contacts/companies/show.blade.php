@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}companies">Aziende</a></li>
@stop

@include('layouts.elements.title', ['title' => $company->rag_soc])

@section('content')

    <div class="row">

        <div class="col-md-3">

            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        {!!$company->avatar!!}
                        <h3 class="profile-username text-center">{{$company->rag_soc}}</h3>
                        @if($company->tipo)
                            <p class="text-muted text-center">{{$company->tipo}}</p>
                        @else
                            @if($company->partner)
                                <p class="text-muted text-center">Partner</p>
                            @endif
                            @if($company->fornitore)
                                <p class="text-muted text-center">Fornitore</p>
                            @endif
                        @endif
                    </div>
                    <ul class="list-group list-group-unbordered mb-3">
                        @if($company->contacts()->exists())
                            @foreach($company->contacts as $contact)
                                <li class="list-group-item">
                                    <a href="{{$contact->url}}"> {{$contact->fullname}}</b> </a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                    <a href="{{$company->url}}/edit" class="btn btn-sm btn-primary btn-block"><b> <i class="fa fa-edit"></i> Modifica</b></a>

                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Dettagli</h3>
                </div>
                <div class="card-body">
                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Indirizzo</strong>
                    <p class="text-muted">{{$company->indirizzo}} {{$company->cap}}, {{$company->citta}}<br>
                        {{$company->provincia}} {{$company->nazione}}
                    </p>
                    <hr>
                    <strong><i class="fas fa-euro-sign mr-1"></i> Fatturazione</strong>
                    @if($company->pec)<p class="text-muted"><b>PEC:</b> {{$company->pec}}</p>@endif
                    @if($company->piva)<p class="text-muted"><b>P.IVA:</b> {{$company->piva}}</p>@endif
                    @if($company->cf)<p class="text-muted"><b>CF:</b> {{$company->cf}}</p>@endif
                    @if($company->sdi)<p class="text-muted"><b>SDI:</b> {{$company->sdi}}</p>@endif
                    <hr>
                    <strong><i class="fas fa-at mr-1"></i> Contatti</strong>
                    @if($company->telefono)<p class="text-muted"><b>Tel:</b> {{$company->telefono}}</p>@endif
                    @if($company->email)<p class="text-muted"><b>Email:</b> <small>{{$company->email}}</small></p>@endif
                    @if($company->email_ordini)<p class="text-muted"><b>Email Ord.:</b> <small>{{$company->email_ordini}}</small></p>@endif
                    @if($company->email_fatture)<p class="text-muted"><b>Email Fatt.:</b> <small>{{$company->email_fatture}}</small></p>@endif
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
                            Activity
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
