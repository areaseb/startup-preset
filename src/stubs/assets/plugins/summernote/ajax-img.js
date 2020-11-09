/*
	Created by Tomaac (https://github.com/tomaac)
    2019.

    Updated by Axel Hardy (https://axelhardy.com/en)
    March 2020.
*/

var php_upload_path = $('meta[name=baseURL]').attr("content")+"admin/upload-editor-img";

(function(factory) {
    /* global define */
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node/CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals
        factory(window.jQuery);
    }
}(function($) {
    // Extends plugins for adding ajaxfileupload.
    //  - plugin is external module for customizing.
    $.extend($.summernote.plugins, {
        /**
         * @param {Object} context - context object has status of editor.
         */
        'ajaximageupload': function(context) {
            var self = this;

            // ui has renders to build ui elements.
            //  - you can create a button with `ui.button`
            var ui = $.summernote.ui;
            var uploadedFile = '';

            // add ajaxfileupload button
            context.memo('button.ajaximageupload', function() {
                // create button
                var button = ui.button({
                    contents: '<i class="note-icon-picture"/>',
                    tooltip: 'Upload Image',
                    click: function() {
                        self.$panel.show();

						$("body").addClass('ajaxfileupload-overlay');

                        var $saveBtn = self.$panel.find('#ajaxFileUploadSubmit'); // upload btn
                        var $closeBtn = self.$panel.find('#ajaxPanelClose'); // close btn (x)


                        // on close btn press
                        $closeBtn.click(function() {
                            self.$panel.hide();
                            $("body").removeClass('ajaxfileupload-overlay');
                        }); // close click


                        // on save btn press
                        $saveBtn.click(function() {
                            // send file by ajax
                            var formData = new FormData();
                            formData.append('image', $('#file')[0].files[0]);
                            formData.append('_token', $('meta[name=token]').attr("content") );

                            $saveBtn.prop("disabled", true);
							$saveBtn.html("Uploading...");

                            $.ajax({
                                url: php_upload_path, // php file location to upload files
                                type: 'POST',
                                data: formData,
                                dataType: 'json',
                                processData: false,
                                contentType: false,
                                success: function(data) {

									$saveBtn.prop("disabled", false);
									$saveBtn.html("Upload");

                                    if (data.message == 'ok') {

                                        uploadedFile = data.response;
                                        console.log(data.response);

                                        context.invoke('editor.pasteHTML', "<img src='" + uploadedFile + "' style='width: 20%; margin: 10px;' alt='uploaded picture' />");
                                        self.$panel.hide();
										$("body").removeClass('ajaxfileupload-overlay');

                                    }
                                    else {
                                        alert(data.message);
                                    }
                                }
                            });
                        });


                    }
                });

                // create jQuery object from button instance.
                var $ajaxfileupload = button.render();
                return $ajaxfileupload;
            });


            // This events will be attached when editor is initialized.
            this.events = {
                // This will be called after modules are initialized.
                'summernote.init': function(we, e) {},
                // This will be called when user releases a key on editable.
                'summernote.keyup': function(we, e) {}
            };



            // Creates dialog box with upload buttons
            // some basic styling for this is in attached css file.
            this.initialize = function() {
                this.$panel = $('<div class="ajaxfileupload-panel"><div id="ajaxFileUploadInner"><div id="ajaxPanelClose">+</div><div id="fileUploadGroup"><h4>Nuova IMG :</h4><br /><input type="file" id="file" name="file"  /></div><div id="ajaxFileUploadSubmit">Carica</div></div></div>').css({
                    position: 'absolute',
                    width: 400,
                    height: 175,
                    left: '50%',
                    top: '30%',
                    background: 'white'
                }).hide();

                this.$panel.appendTo('body');
            };


            this.destroy = function() {
                this.$panel.remove();
                this.$panel = null;
                $("body").removeClass('ajaxfileupload-overlay');
            };
        }
    });
}));
