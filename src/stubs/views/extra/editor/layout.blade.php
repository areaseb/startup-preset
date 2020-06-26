<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Template Builder</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{asset('public/editor/assets/css/email-editor.bundle.min.css')}}" rel="stylesheet" />
    <link href="{{asset('public/editor/assets/css/colorpicker.css')}}" rel="stylesheet" />
    <link href="{{asset('public/editor/assets/css/editor-color.css')}}" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <div class="bal-editor-demo"></div>
    <div id="previewModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Anteprima</h4>
                </div>
                <div class="modal-body">
                    <div class="">
                        <label for="">URL : </label> <span class="preview_url"></span>
                    </div>
                    <iframe id="previewModalFrame" width="100%" height="400px"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
    <script src="{{asset('public/editor/assets/vendor/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{asset('public/editor/assets/vendor/jquery-ui/jquery-ui.min.js')}}"></script>
    <script src="{{asset('public/editor/assets/vendor/jquery-nicescroll/dist/jquery.nicescroll.min.js')}}"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/ace/1.1.01/ace.js" type="text/javascript"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/ace/1.1.01/theme-monokai.js" type="text/javascript"></script>
    <script src="{{asset('public/editor/assets/vendor/tinymce/js/tinymce/tinymce.min.js')}}"></script>
    <script src="{{asset('public/editor/assets/vendor/tinymce/js/tinymce/themes/inlite/theme.min.js')}}"></script>

    <script src="{{asset('public/editor/assets/vendor/sweetalert2/dist/sweetalert2.min.js')}}"></script>
    <script src="{{asset('public/editor/assets/js/colorpicker.js')}}"></script>
    <script src="{{asset('public/editor/assets/js/bal-email-editor-plugin.js')}}"></script>
    @yield('script')
</body>
</html>
