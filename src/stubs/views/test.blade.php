@extends('layouts.app')

@include('layouts.elements.title', ['title' => 'Test'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Test Card</h3>
                </div>
                <div class="card-body">
                    {!! Form::open(['url' => url('upload'), 'files' => true, 'id' => 'csvPeeker']) !!}
                        <input type="text" name="header" class="d-none">
                        <div class="form-group">
                            <label for="csv">File input</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input name="file" type="file" class="custom-file-input" id="csv" accept=".csv">
                                    <label class="custom-file-label" for="csv" >Scegli il file</label>
                                </div>
                                <div class="input-group-append">
                                    <button class="btn btn-default" id="upload" disabled>Carica</button>
                                </div>
                            </div>
                        </div>
                    {!!Form::close()!!}

                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Test Card</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                        foreach($fields as $field)
                        {
                            $arr[$field] = $field;
                        }
                        @endphp

                        @foreach($fields as $key => $field)
                            <div class="col">
                                {!! Form::select($field, $arr, $field, ['class' => 'custom-select', 'placeholder' => '-- Vuoto -- ']) !!}
                                <div style="font-size:70%;text-align:center;" class="result-{{$loop->index}}"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer d-none" id="hiddenFooter">
                    {!! Form::open(['url' => url('imports/contacts'), 'files' => true, 'id' => 'finalImport']) !!}
                        <button class="btn btn-primary" id="final">Importa Contatti</button>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script type="text/javascript">



    bsCustomFileInput.init();
    $('input#csv').on('change', function(){
        $('button#upload').prop('disabled', false);
        $('button#upload').removeClass('btn-default');
        $('button#upload').addClass('btn-success');
    })



    $("form#csvPeeker").submit(function(e) {
        e.preventDefault();
        var form = $(this)[0];
        var formData = new FormData(form);

        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: $(form).attr('action'),
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 600000,
            success: function (data) {
                $.each(data, function(key, value){
                    $.each(value, function(k,v){
                        $('.result-'+k).append(v+'<br>');
                    })
                });
                $('#hiddenFooter').removeClass('d-none');
            }
        });
    });

    $("form#finalImport").submit(function(e) {
        e.preventDefault();

        $(document).Toasts('create', {
            title: 'Importazione in corso',
            autohide: true,
            class: 'bg-info',
            delay: 4000,
            body: 'Stiamo importando i contatti. Ti notificheremo ad operazione conclusa'
        });


        let headers = '';
        $.each($('select[class="custom-select"]'), function(){
            headers += $(this).find('option:selected').text()+',';
        })
        headers = headers.slice(0,-1);
        $('input[name="header"]').val(headers);

        var form = $("form#csvPeeker")[0];
        var formData = new FormData(form);


        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: $(this).attr('action'),
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 600000,
            success: function (data) {
                if(data === 'done')
                {
                    $(document).Toasts('create', {
                        title: 'Importazione completata',
                        autohide: true,
                        class: 'bg-success',
                        delay: 3000,
                        body: 'Tutti i nuovi contatti sono stati importati'
                    });
                    $('button#upload').prop('disabled', true);
                    $('button#final').prop('disabled', true);
                }
            }
        });


    });


    </script>
@stop
