@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{config('app.url')}}settings">Settings</a></li>
@stop

@include('layouts.elements.title', ['title' => 'Modifica Settings'])


@section('content')

    {!! Form::model($setting, ['url' => $setting->url, 'autocomplete' => 'off', 'method' => 'PATCH', 'id' => 'settingForm']) !!}
        <div class="row">
            @include('components.errors')
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Modello</label>
                            {!!Form::text('model', null, ['class' => 'form-control', 'required', 'disabled'])!!}
                        </div>
                        <div class="row">
                            @if($setting->fields)
                                @foreach ($setting->fields as $key => $value)
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {!!Form::text('key[]', $key, ['class' => 'form-control'])!!}
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group">
                                            {!!Form::text('value[]', $value, ['class' => 'form-control'])!!}
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                @for($i=0;$i<5;$i++)
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            {!!Form::text('key[]', null, ['class' => 'form-control'])!!}
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group">
                                            {!!Form::text('value[]', null, ['class' => 'form-control'])!!}
                                        </div>
                                    </div>
                                @endfor
                            @endif
                            <div class="col-12 text-center" id="target">
                                <div class="form-group">
                                    <a href="#" id="addRow" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Aggiungi campo vuoto</a>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-block btn-primary btn-lg" id="submitForm"><i class="fa fa-save"></i> Salva</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}

@stop

@section('scripts')
<script>
const html = '<div class="col-md-3"><div class="form-group"><input class="form-control" name="key[]" type="text"></div></div><div class="col-md-9"><div class="form-group"><input class="form-control" name="value[]" type="text"></div></div>';
$('a#addRow').on('click', function(e){
    e.preventDefault();
    $(html).insertBefore('div#target');
});
</script>
@stop
