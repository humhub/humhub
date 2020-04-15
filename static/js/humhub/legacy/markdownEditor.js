// Newly uploaded file
var newFile = "";

/**
 * @deprecated since 1.5
 */
function initMarkdownEditor(elementId) {

    if (!$('#addFileModal_' + elementId).length) {
        $("body").append($("#markdownEditor_dialogs_" + elementId).html());
    }

    $("#" + elementId).markdown({
        iconlibrary: 'fa',
        resize: 'vertical',
        additionalButtons: [
            [{
                    name: "groupCustom",
                    data: [{
                            name: "cmdLinkWiki",
                            title: "URL/Link",
                            icon: {glyph: 'glyphicon glyphicon-link', fa: 'fa fa-link', 'fa-3': 'icon-link'},
                            callback: function (e) {
                                addLinkModal = $('#addLinkModal_' + elementId);
                                linkTitleField = addLinkModal.find('.linkTitle');
                                linkTargetField = addLinkModal.find('.linkTarget');


                                addLinkModal.find(".close").off('click');


                                addLinkModal.modal('show');

                                linkTitleField.val(e.getSelection().text);
                                if (linkTitleField.val() == "") {
                                    linkTitleField.focus();
                                } else {
                                    linkTargetField.focus();
                                }

                                addLinkModal.find('.addLinkButton').off('click');
                                addLinkModal.find('.addLinkButton').on('click', function () {
                                    chunk = "[" + linkTitleField.val() + "](" + linkTargetField.val() + ")";
                                    selected = e.getSelection(), content = e.getContent(),
                                            e.replaceSelection(chunk);
                                    cursor = selected.start;
                                    e.setSelection(cursor, cursor + chunk.length);
                                    addLinkModal.modal('hide')
                                });

                                addLinkModal.on('hide.bs.modal', function (ee) {
                                    linkTitleField.val("");
                                    linkTargetField.val("");
                                })
                            }
                        },
                        {
                            name: "cmdImgWiki",
                            title: "Image/File",
                            icon: {glyph: 'glyphicon glyphicon-picture', fa: 'fa fa-picture-o', 'fa-3': 'icon-picture'},
                            callback: function (e) {

                                addFileModal = $('#addFileModal_' + elementId);
                                addFileModal.modal('show');
                                addFileModal.find(".uploadForm").show();
                                addFileModal.find(".uploadProgress").hide();

                                addFileModal.on('hide.bs.modal', function (ee) {
                                    if (newFile != "") {
                                        var chunk;
                                        if (newFile.mimeType.substring(0, 6) == "image/") {
                                            chunk = "![" + newFile.name + "](file-guid-" + newFile.guid + ") ";
                                        } else {
                                            chunk = "[" + newFile.name + "](file-guid-" + newFile.guid + ") ";
                                        }
                                        insertAtCaret(e.$textarea[0], chunk)
                                        newFile = "";
                                    }
                                })
                            }
                        },
                    ]
                }]
        ],
        reorderButtonGroups: ["groupFont", "groupCustom", "groupMisc", "groupUtil"],
        onPreview: function (e) {
            $.ajax({
                type: "POST",
                url: markdownPreviewUrl,
                data: {
                    markdown: e.getContent(),
                }
            }).done(function (previewHtml) {
                $('#markdownpreview_' + elementId).html(previewHtml);
            });
            var previewContent = "<div id='markdownpreview_" + elementId + "'><div class='loader'></div></div>";
            return previewContent;
        }
    });

    $('#addFileModal_' + elementId).find(".uploadProgress").hide();
    $('#addFileModal_' + elementId).find('.fileUploadButton').fileupload({
        dataType: 'json',
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                addFileModal = $('#addFileModal_' + elementId);
                if (!file.error) {
                    newFile = file;
                    hiddenValueField = $('#fileUploaderHiddenGuidField_' + elementId);
                    hiddenValueField.val(hiddenValueField.val() + "," + file.guid);
                    addFileModal.modal('hide');
                } else {
                    alert("file upload error");
                }
            });
        },
        progressall: function (e, data) {
            newFile = "";
            addFileModal = $('#addFileModal_' + elementId);

            var progress = parseInt(data.loaded / data.total * 100, 10);
            addFileModal.find(".uploadForm").hide();
            addFileModal.find(".uploadProgress").show();
            if (progress == 100) {
                addFileModal.find(".uploadProgress").hide();
                addFileModal.find(".uploadForm").hide();
            }
        }
    }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

}
function insertAtCaret(txtarea, text) {
    if (!txtarea) {
        return;
    }

    var scrollPos = txtarea.scrollTop;
    var strPos = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? "ff" : (document.selection ? "ie" : false));
    if (br == "ie") {
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart('character', -txtarea.value.length);
        strPos = range.text.length;
    } else if (br == "ff") {
        strPos = txtarea.selectionStart;
    }

    var front = (txtarea.value).substring(0, strPos);
    var back = (txtarea.value).substring(strPos, txtarea.value.length);
    txtarea.value = front + text + back;
    strPos = strPos + text.length;
    if (br == "ie") {
        txtarea.focus();
        var ieRange = document.selection.createRange();
        ieRange.moveStart('character', -txtarea.value.length);
        ieRange.moveStart('character', strPos);
        ieRange.moveEnd('character', 0);
        ieRange.select();
    } else if (br == "ff") {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
    }

    txtarea.scrollTop = scrollPos;
}
