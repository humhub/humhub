// Newly uploaded file
var newFile = "";

function initMarkdownEditor(elementId) {
    $("#" + elementId).markdown({
        iconlibrary: 'fa',
        additionalButtons: [
            [{
                    name: "groupCustom",
                    data: [{
                            name: "cmdLinkWiki",
                            title: "Add link",
                            icon: {glyph: 'glyphicon glyphicon-link', fa: 'fa fa-link', 'fa-3': 'icon-link'},
                            callback: function(e) {
                                newFile = "";
                                $('#addLinkModal').modal('show');
                                $('#addLinkModalUploadForm').show();
                                $('#addLinkTitle').val(e.getSelection().text);

                                // FIXME @Struppi
                                if ($('#addLinkTitle').val() == "") {
                                    $('#addLinkTitle').focus();
                                } else {
                                    $('#addLinkTarget').focus();
                                }

                                $('#addLinkButton').off('click');
                                $('#addLinkButton').on('click', function() {
                                    chunk = "[" + $('#addLinkTitle').val() + "](" + $('#addLinkTarget').val() + ")";
                                    selected = e.getSelection(), content = e.getContent(),
                                            e.replaceSelection(chunk);
                                    cursor = selected.start;
                                    e.setSelection(cursor, cursor + chunk.length);
                                    $('#addLinkModal').modal('hide')
                                });

                                $('#addLinkModal').on('hide.bs.modal', function(ee) {
                                    $('#addLinkTitle').val("");
                                    $('#addLinkTarget').val("");
                                })
                            }
                        },
                        {
                            name: "cmdImgWiki",
                            title: "Add image/file",
                            icon: {glyph: 'glyphicon glyphicon-picture', fa: 'fa fa-picture-o', 'fa-3': 'icon-picture'},
                            callback: function(e) {
                                newFile = "";
                                $('#addImageModal').modal('show');
                                $('#addImageModalUploadForm').show();
                                $('#addImageModalProgress').hide();
                                $('#addImageModal').on('hide.bs.modal', function(ee) {
                                    if (newFile != "") {
                                        if (newFile.mimeBaseType == "image") {
                                            chunk = "![" + newFile.name + "](file-guid-" + newFile.guid + ")";
                                        } else {
                                            chunk = "[" + newFile.name + "](file-guid-" + newFile.guid + ")";
                                        }
                                        selected = e.getSelection(), content = e.getContent(),
                                                e.replaceSelection(chunk);
                                        cursor = selected.start;
                                        e.setSelection(cursor, cursor + chunk.length);
                                    }
                                })
                            }
                        },
                    ]
                }]
        ],
        reorderButtonGroups: ["groupFont", "groupCustom", "groupMisc", "groupUtil"],
        onPreview: function(e) {
            $.ajax({
                type: "POST",
                url: markdownPreviewUrl,
                data: {
                    markdown: e.getContent(),
                }
            }).done(function(previewHtml) {
                $('#markdownpreview').html(previewHtml);
            });
            var previewContent = "<div id='markdownpreview'><div class='loader'></div></div>";
            return previewContent;
        }
    });

    $('#fileUploadProgress').hide();
    $('#fileUploaderButton').fileupload({
        dataType: 'json',
        done: function(e, data) {
            $.each(data.result.files, function(index, file) {
                if (!file.error) {
                    newFile = file;

                    hiddenValueField = $('#fileUploaderHiddenGuidField');
                    hiddenValueField.val(hiddenValueField.val() + "," + file.guid);

                    $('#addImageModal').modal('hide');
                } else {
                    alert("file upload error");
                }
            });
        },
        progressall: function(e, data) {
            newFile = "";
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#addImageModalUploadForm').hide();
            $('#addImageModalProgress').show();
            if (progress == 100) {
                $('#addImageModalProgress').hide();
                $('#addImageModalUploadForm').hide();
            }
        }
    }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

}
