<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Dettagli</h3>
        </div>
        <div class="card-body">

            <div class="form-group">
                <label>Nome</label>
                {!! Form::text('nome', null, ['class' => 'form-control', 'required', 'placeholder' => 'Per referenza interna']) !!}
            </div>
            <div class="form-group">
                <label>Oggetto</label>
                {!! Form::text('oggetto', null, ['class' => 'form-control', 'placeholder' => "Oggetto dell'email"]) !!}
            </div>
            <div class="form-group">
                <label>Descrizione</label>
                {!!Form::textarea('descrizione', null, ['class' => 'form-control', 'placeholder' => "testo per l'anteprima"])!!}
            </div>


        </div>
    </div>


</div>

<div class="col-md-6">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Associazioni</h3>
        </div>
        <div class="card-body">

            <div class="form-group">
                <label>Liste</label>

                @isset($newsletter)
                    {!! Form::select('list_id[]', $lists, $newsletter->lists, [
                        'class' => 'form-control select2bs4',
                        'multiple' => 'multiple',
                        'data-placeholder' => 'Seleziona almeno una lista',
                        'style' => 'width:100%',
                        'required']) !!}
                @else
                    {!! Form::select('list_id[]', $lists, old('list_id') ?? null, [
                        'class' => 'form-control select2bs4',
                        'multiple' => 'multiple',
                        'data-placeholder' => 'Seleziona almeno una lista',
                        'style' => 'width:100%',
                        'required']) !!}
                @endisset
                <small class="form-text text-muted">Seleziona i destinatari della newsletter, <a target="_BLANK" href="{{url('contacts/lists')}}">Vedi liste</a></small>
            </div>

            <div class="form-group">
                <label>Template</label>
                {!! Form::select('template_id', $templates, null, ['class' => 'custom-select', 'id' => 'template_id', 'required']) !!}
                <small class="form-text text-muted">Seleziona un template, <a target="_BLANK" href="{{url('templates')}}">Vedi templates</a></small>
            </div>

        </div>
    </div>



    <div class="card">

        <button type="submit" class="btn btn-block btn-primary btn-lg" id="submitForm"><i class="fa fa-save"></i> Salva</button>

    </div>

</div>


@section('scripts')
<script>

    // $('button#submitForm').on('click', function(e){
    //     e.preventDefault();
    //     $('form#newsletterForm').submit();
    // })

</script>
@stop
