<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Nominativo</h3>
        </div>
        <div class="card-body">

            <div class="form-group">
                <label>Ragione Sociale</label>
                {!! Form::text('rag_soc', null, ['class' => 'form-control', 'required']) !!}
                @include('components.add-invalid', ['element' => 'rag_soc'])

            </div>
            <div class="form-group">
                <label>Nazione</label>
                {!! Form::select('nazione', $countries, null, ['class' => 'custom-select', 'id' => 'country']) !!}
            </div>
            <div class="form-group">
                <label>indirizzo</label>
                {!!Form::text('indirizzo', null, ['class' => 'form-control', 'placeholder' => 'Indirizzo'])!!}
            </div>
            <div class="form-group">
                <label>Città</label>
                {!!Form::text('citta', null, ['class' => 'form-control', 'placeholder' => 'Città'])!!}
            </div>
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label>CAP</label>
                        {!!Form::text('cap', null, ['class' => 'form-control', 'placeholder' => 'CAP'])!!}
                    </div>
                </div>
                <div class="col-8">
                    <div class="form-group">
                        <label>Provincia</label>

                        {!!Form::text('provincia', null, [
                            'class' => 'form-control',
                            'placeholder' =>'Regione Estera',
                            'id' => 'region'])!!}

                        {!! Form::select('provincia', $provinces, null, [
                            'class' => 'form-control select2bs4',
                            'data-placeholder' => 'Seleziona una provincia',
                            'style' => 'width:100%',
                            'id' => 'provincia']) !!}
                        </div>

                </div>
            </div>

            <div class="form-group">
                <label>Email principale</label>
                {!!Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Indirizzo Email', 'required', 'autocomplete' => 'off', 'data-type' => 'email'])!!}
                @include('components.add-invalid', ['element' => 'email'])
            </div>
            <div class="form-group">
                <label>Email ordini</label>
                {!!Form::text('email_ordini', null, ['class' => 'form-control', 'placeholder' => 'Indirizzo email per ordini', 'autocomplete' => 'off', 'data-type' => 'email'])!!}
            </div>
            <div class="form-group">
                <label>Email fatturazione</label>
                {!!Form::text('email_fatture', null, ['class' => 'form-control', 'placeholder' => 'Indirizzo email per fatture', 'autocomplete' => 'off', 'data-type' => 'email'])!!}
            </div>


            <div class="form-group">
                <label>Telefono</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="changePrefix"></span>
                    </div>
                    {!!Form::text('telefono', null, ['class' => 'form-control', 'placeholder' => 'Telefono'])!!}
                </div>
            </div>

        </div>
    </div>


</div>

<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Fatturazione</h3>
        </div>
        <div class="card-body">

            <div class="form-group">
                <label>PEC</label>
                {!!Form::text('pec', null, ['class' => 'form-control', 'placeholder' => 'PEC', 'data-type' => 'email'])!!}
            </div>

            <div class="form-group">
                <label>P.IVA</label>
                {!!Form::text('piva', null, ['class' => 'form-control', 'placeholder' => 'Partita iva'])!!}
                @include('components.add-invalid', ['element' => 'piva'])
            </div>
            <div class="form-group">
                <label>CF</label>
                {!!Form::text('cf', null, ['class' => 'form-control', 'placeholder' => 'Codice fiscale'])!!}
            </div>
            <div class="form-group">
                <label>SDI</label>
                {!!Form::text('cf', null, ['class' => 'form-control', 'placeholder' => 'Identificativo e-fattura'])!!}
            </div>

        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Tipologia</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Tipo</label>
                        {!!Form::select('tipo',[''=> '', 'Lead' => 'Lead', 'Prospect' => 'Prospect', 'Client' => 'Client'] , null, ['class' => 'custom-select'])!!}
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Fornitore</label>
                        {!!Form::select('fornitore',[''=> '', '1' => 'Sì', '0' => 'No'] , null, ['class' => 'custom-select'])!!}
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>Partner</label>
                        {!!Form::select('partner',[''=> '', '1' => 'Sì', '0' => 'No'] , null, ['class' => 'custom-select'])!!}
                    </div>
                </div>
            </div>


        </div>
    </div>


    <div class="card">

        <button type="submit" class="btn btn-block btn-primary btn-lg" id="submitForm"><i class="fa fa-save"></i> Salva</button>

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

        $('form#companyForm').submit();
    })

</script>
@stop
