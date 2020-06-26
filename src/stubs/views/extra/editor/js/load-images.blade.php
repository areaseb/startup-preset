function loadImages() {
    $.ajax({
        url: "{{url('editor/images')}}",
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.code == 0) {
                _output = '';
                for (var k in data.files) {
                    if (typeof data.files[k] !== 'function') {
                        _output += "<div class='col-sm-3 text-center center'>" +
                            "<img class='upload-image-item' src='" +
                            data.directory + data.files[k] + "' alt='" + data.files[k] +
                            "' data-url='" + data.directory + data.files[k] + "'>" +
                            "<small>"+data.captions[k]+"</small></div>";
                    }
                }
                $('.upload-images').html(_output);
            }
        },
        error: function() {}
    });
}
