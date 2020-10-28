@section('scripts')
    <script src="{{asset('plugins/dropzone/min.js')}}"></script>
    <script src="{{asset('plugins/popup/min.js')}}"></script>
<script>
    (function(){


        let text = "<strong>Clicca per caricare immagini e documenti. (jpg, png, pdf, doc, xls, ...)</strong><br> Max upload size 8MB";

        Dropzone.options.dropzoneForm = {
            paramName: "file",
            maxFilesize: 200,
            dictDefaultMessage: text,
            sending: function(file, xhr, formData) {
                formData.append("mediable_id", "{{$model->id}}");
                formData.append("mediable_type", "{{str_replace("\\","\\\\", $model->full_class)}}");
                formData.append("directory", "{{$model->directory}}");
            },
            init: function () {
                this.on('queuecomplete', function () {
                    location.reload();
                });
            },
        };

        $('.popup-gallery tr td').magnificPopup({delegate: 'a',type: 'image',tLoading: 'Loading image #%curr%...',mainClass: 'mfp-img-mobile',gallery: {enabled: true,navigateByImgClick: true,preload: [0,1] },image: {tError: '<a href="%url%">The image #%curr%</a> errorrrrrr',titleSrc: function(item) {return item.el.attr('title') + '<small>{{ config('app.name') }}</small>';}}});

        $('.form-description button').on('click', function(e){
            e.preventDefault();
            var arr = {
                '_token': '{{csrf_token()}}',
                'id': $(this).attr('id'),
                'description': $(this).siblings('input[name=description]').val()
            }
            $.post( "{{url('api/media/update')}}", arr, function(data){
                new Noty({
                    text: data,
                    type: 'success',
                    theme: 'bootstrap-v4',
                    timeout: 2500,
                    layout: 'topRight'
                }).show();
            });
        });

        // $('.form-check').on('change', function(){
        //     var arr = {
        //         '_token': '//csrf_token()}}',
        //         'id': $(this).find('input:checked').attr('id'),
        //         'type': $(this).find('input:checked').val()
        //     }
        //     $.post( "//url('api/media/type')}}", arr, function(data){
        //         new Noty({
        //             text: data,
        //             type: 'success',
        //             theme: 'bootstrap-v4',
        //             timeout: 2500,
        //             layout: 'topRight'
        //         }).show();
        //     });
        // });

        $('.image-table').sortable({
            containerSelector: 'table',
            itemPath: '> tbody',
            itemSelector: 'tr',
            placeholder: '<tr class="placeholder"/>',
            handle: 'td.handler',

            onDrop: function ($item, container, _super) {
                var tableData = $('.table tbody tr');
                var arr = {
                    '_token': '{{csrf_token()}}',
                    'media_order': []
                }
                $.each(tableData, function(index, value){
                    id = $(value).children('td:last-child').text();
                    arr.media_order.push(id);
                });
                $.post( "{{url('api/media/order')}}", arr, function( data ) {
                    new Noty({
                        text: data,
                        type: 'success',
                        theme: 'bootstrap-v4',
                        timeout: 2500,
                        layout: 'topRight'
                    }).show();
                });
                _super($item, container);
            }
        });

    })(jQuery);
</script>
@stop
