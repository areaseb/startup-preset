@extends('layouts.app')

@include('layouts.elements.title', ['title' => 'Settings'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Settings</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Modulo</th>
                                <th>Dati</th>
                                <th></th>
                                </tr>
                            </thead>
                        <tbody>
                            @foreach($settings as $setting)
                                <tr>
                                    <td>{{$setting->model}}</td>
                                    <td>{{$setting->count_fields}} campi</td>
                                    <td>
                                        <a href="{{$setting->url}}/edit" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-edit"></i></a>
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
