@extends('extra.editor.layout')

@section('script')

<script>

    @include('extra.editor.js.load-images')


    $('.bal-editor-demo').on('click','.goBack', function(){
        window.location.href = "{{url('templates')}}";
    });

    var _templateListItems;

    var _emailBuilder = $('.bal-editor-demo').emailBuilder({
        lang: 'it',
        elementJsonUrl: "{{$elements}}",
        langJsonUrl: "{{asset( 'public/editor/lang-1.json?ree' )}}",
        loading_color1: 'red',
        loading_color2: 'green',
        showLoading: true,

        blankPageHtmlUrl: "{{asset( 'public/editor/template-blank-page.html' )}}",
        loadPageHtmlUrl: "{{$page}}",

        //left menu
        showElementsTab: true,
        showPropertyTab: true,
        showCollapseMenu: true,
        showBlankPageButton: true,
        showCollapseMenuinBottom: true,

        //setting items
        showSettingsBar: true,
        showSettingsPreview: true,
        showSettingsExport: true,
        showSettingsSendMail: true,
        showSettingsSave: true,
        showSettingsLoadTemplate: false,

        //show context menu
        showContextMenu: true,
        showContextMenu_FontFamily: true,
        showContextMenu_FontSize: true,
        showContextMenu_Bold: true,
        showContextMenu_Italic: true,
        showContextMenu_Underline: true,
        showContextMenu_Strikethrough: true,
        showContextMenu_Hyperlink: true,

        //show or hide elements actions
        showRowMoveButton: true,
        showRowRemoveButton: true,
        showRowDuplicateButton: true,
        showRowCodeEditorButton: true,
        onElementDragStart: function(e) {
            console.log('onElementDragStart html');
        },
        onElementDragFinished: function(e,contentHtml) {
            console.log('onElementDragFinished html');
            //console.log(contentHtml);

        },

        onBeforeRowRemoveButtonClick: function(e) {
            console.log('onBeforeRemoveButtonClick html');

            /*
              if you want do not work code in plugin ,
              you must use e.preventDefault();
            */
            //e.preventDefault();
        },
        onAfterRowRemoveButtonClick: function(e) {
            console.log('onAfterRemoveButtonClick html');
        },
        onBeforeRowDuplicateButtonClick: function(e) {
            console.log('onBeforeRowDuplicateButtonClick html');
            //e.preventDefault();
        },
        onAfterRowDuplicateButtonClick: function(e) {
            console.log('onAfterRowDuplicateButtonClick html');
        },
        onBeforeRowEditorButtonClick: function(e) {
            console.log('onBeforeRowEditorButtonClick html');
            //e.preventDefault();
        },
        onAfterRowEditorButtonClick: function(e) {
            console.log('onAfterRowDuplicateButtonClick html');
        },
        onBeforeShowingEditorPopup: function(e) {
            console.log('onBeforeShowingEditorPopup html');
            //e.preventDefault();
        },
        onBeforeSettingsSaveButtonClick: function(e) {
            console.log('onBeforeSaveButtonClick html');
            @if($update)
                console.log('just save');
                e.preventDefault();
                $.ajax({
                    url: "{{$template->url}}",
                    type: 'POST',
                    data: {
                        contenuto_html: $('.bal-content-wrapper').html(),
                        _token: "{{csrf_token()}}"
                    },
                    success: function(data) {
                        if(data === 'done')
                        {
                            window.location.href = "{{request()->url()}}";
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            @endif
        },
        onPopupUploadImageButtonClick: function() {
            console.log('onPopupUploadImageButtonClick html');
            var file_data = $('.input-file').prop('files')[0];
            var form_data = new FormData();
            form_data.append('file', file_data);
            form_data.append('_token', '{{csrf_token()}}');
            form_data.append('mediable_type', "App\\Classes\\Editor");
            form_data.append('directory', "editor");
            $.ajax({
                url: "{{url('editor/upload')}}",
                dataType: 'text',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(response) {
                    loadImages();
                }
            });

        },
        onSettingsPreviewButtonClick: function(e, getHtml) {
            console.log('onPreviewButtonClick html');
            @if($update)
                e.preventDefault();
                $.ajax({
                    url: "{{$template->url}}",
                    type: 'POST',
                    data: {
                        contenuto_html: getHtml,
                        _token: "{{csrf_token()}}"
                    },
                    success: function(data) {
                        if(data === 'done')
                        {
                            var goTo = "{{$template->url}}";
                            var win = window.open(goTo, '_blank');
                            win.focus();
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            @else

                $.ajax({
                    url: "{{url('templates')}}",
                    type: 'POST',
                    //dataType: 'json',
                    data: {
                        nome: "Nuovo template",
                        contenuto_html: getHtml,
                        _token: "{{csrf_token()}}"
                    },
                    success: function(data) {
                          console.log(data);
                        if (data) {
                            var goTo = "{{url('templates')}}/"+data;
                            var win = window.open(goTo, '_blank');
                            win.focus();
                        } else {
                            $('.input-error').text('Problem in server');
                        }
                    },
                    error: function(error) {
                        $('.input-error').text('Internal error');
                    }
                });

            @endif
        },

        onSettingsExportButtonClick: function(e, getHtml) {
            console.log('onSettingsExportButtonClick html');
            console.log('go back to templates');
            e.preventDefault();
        },
        onBeforeSettingsLoadTemplateButtonClick: function(e) {

            $('.template-list').html('<div style="text-align:center">Loading...</div>');
        },
        onSettingsSendMailButtonClick: function(e) {
            console.log('onSettingsSendMailButtonClick html');
            //e.preventDefault();
        },
        onPopupSendMailButtonClick: function(e, _html) {
            console.log('onPopupSendMailButtonClick html');
        },
        onBeforeChangeImageClick: function(e) {
            console.log('onBeforeChangeImageClick html');
            loadImages();
        },
        onBeforePopupSelectTemplateButtonClick: function(e) {
            console.log('onBeforePopupSelectTemplateButtonClick html');

        },
        onBeforePopupSelectImageButtonClick: function(e) {
            console.log('onBeforePopupSelectImageButtonClick html');

        },
        onPopupSaveButtonClick: function() {
            console.log('onPopupSaveButtonClick html');
            console.log($('.bal-content-wrapper').html());

            $.ajax({
                url: "{{url('templates')}}",
                type: 'POST',
                //dataType: 'json',
                data: {
                    nome: $('.template-name').val(),
                    contenuto_html: $('.bal-content-wrapper').html(),
                    _token: "{{csrf_token()}}"
                },
                success: function(data) {
                      console.log(data);
                    if (data) {
                        window.location.href = "{{url('template-builder')}}/"+data;
                    } else {
                        $('.input-error').text('Problem in server');
                    }
                },
                error: function(error) {
                    $('.input-error').text('Internal error');
                }
            });
        }
    });

    _emailBuilder.setAfterLoad(function(e) {
          console.log('onAfterLoad html');

          _emailBuilder.makeSortable();

          setTimeout(function(){
              _emailBuilder.makeSortable();
              _emailBuilder.makeRowElements();
              console.log('finito');
          },5000);

     });




</script>


@stop
