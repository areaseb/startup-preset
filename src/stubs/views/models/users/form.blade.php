<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Credenziali</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Email address</label>
                {!!Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Indirizzo Email', 'required', 'autocomplete' => 'off'])!!}
                @include('components.add-invalid', ['element' => 'email'])
            </div>
            @empty($element)
                <div class="form-group">
                    <label>Password</label>
                    <input name="password" type="password" class="form-control" autocomplete="off">
                    @include('components.add-invalid', ['element' => 'password'])
                </div>
            @endempty
            <div class="form-group">
                <label>Ruolo</label>
                @isset($element)
                    {!! Form::select('role_id[]', $roles, $element->roles, [
                        'class' => 'form-control select2bs4',
                        'multiple' => 'multiple',
                        'data-placeholder' => 'Seleziona almeno un ruolo',
                        'style' => 'width:100%',
                        'required']) !!}
                @else
                    {!! Form::select('role_id[]', $roles, old('role_id') ?? null, [
                        'class' => 'form-control select2bs4',
                        'multiple' => 'multiple',
                        'data-placeholder' => 'Seleziona almeno un ruolo',
                        'style' => 'width:100%',
                        'required']) !!}
                @endisset

            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Azienda</h3>
        </div>
        <div class="card-body">

            <div class="form-group">
                <label>Associa ad un'azienda</label>
                {!! Form::select('company_id', $companies, $element->contact->company_id ?? null, [
                    'class' => 'form-control select2bs4',
                    'data-placeholder' => "Seleziona un'azienda",
                    'style' => 'width:100%']) !!}
                    <small><a href="{{url('companies/create')}}" target="_BLANK"><i class="fa fa-plus"></i> Crea una nuova azienda</a></small>
            </div>
        </div>
    </div>


    <div class="card">
        @empty($element)
            <div class="card-body">
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input name="sendEmail" value="1" class="custom-control-input" type="checkbox" id="sendEmail">
                        <label for="sendEmail" class="custom-control-label">Invia un'email con le credenziali all'utente</label>
                    </div>
                </div>
            </div>
        @endempty

        <button type="submit" class="btn btn-block btn-primary btn-lg" id="submitForm"><i class="fa fa-save"></i> Salva</button>

    </div>



</div>

<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Contatti</h3>
        </div>
        <div class="card-body">

            <div class="form-group">
                <label>Nome</label>
                {!! Form::text('nome', $element->contact->nome ?? null, ['class' => 'form-control', 'required']) !!}
                @include('components.add-invalid', ['element' => 'nome'])
            </div>
            <div class="form-group">
                <label>Cognome</label>
                {!! Form::text('cognome', $element->contact->cognome ?? null, ['class' => 'form-control', 'required']) !!}
                @include('components.add-invalid', ['element' => 'cognome'])
            </div>
            <div class="form-group">
                <label>Nazione</label>
                {!! Form::select('nazione', $countries, null, ['class' => 'custom-select', 'id' => 'country']) !!}
            </div>
            <div class="form-group">
                <label>Mobile</label>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="changePrefix"></span>
                    </div>
                    {!!Form::text('cellulare', $element->contact->cellulare ?? null, ['class' => 'form-control', 'placeholder' => 'Cellulare'])!!}
                </div>
            </div>
            <div class="form-group">
                <label>indirizzo</label>
                {!!Form::text('indirizzo', $element->contact->indirizzo ?? null, ['class' => 'form-control', 'placeholder' => 'Indirizzo'])!!}
            </div>
            <div class="form-group">
                <label>Città</label>
                {!!Form::text('citta', $element->contact->citta ?? null, ['class' => 'form-control', 'placeholder' => 'Città'])!!}
            </div>
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label>CAP</label>
                        {!!Form::text('cap', $element->contact->cap ?? null, ['class' => 'form-control', 'placeholder' => 'CAP'])!!}
                    </div>
                </div>
                <div class="col-8">
                    <div class="form-group">
                        <label>Provincia</label>

                        {!!Form::text('provincia', $element->contact->provincia ?? null, [
                            'class' => 'form-control',
                            'placeholder' =>'Regione Estera',
                            'id' => 'region'])!!}

                        {!! Form::select('provincia', $provinces, $element->contact->provincia ?? null, [
                            'class' => 'form-control select2bs4',
                            'data-placeholder' => 'Seleziona una provincia',
                            'style' => 'width:100%',
                            'id' => 'provincia']) !!}
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>


@section('scripts')
<script>
    function prefix()
    {
        $.post("{{url('countries')}}", {
            _token: $('input[name="_token"]').val(),
            iso: $('select#country').find(':selected').val()
        }).done(function(data){
            if(data !== null)
            {
                $('span#changePrefix').text('+'+data);
                if(data != '39')
                {
                    $('select#provincia').select2('destroy').hide();
                    $('input#region').show();
                }
                else
                {
                    $('select#provincia').select2().show();
                    $('input#region').hide();
                }
            }
        });
    }

    prefix();

    $('select#country').on('change', function(){
        prefix();
    });

    $('button#submitForm').on('click', function(e){
        e.preventDefault();
        let region = $('input#region');
        let province = $('select#provincia');

        if(region.val() == '')
        {
            region.val(province.val());
        }
        if(province.val() == '')
        {
            province.val(region.val());
        }

        $('form#userForm').submit();
    })

</script>
@stop
