/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Module for creating an manipulating modal dialoges.
 * Normal layout of a dialog:
 *
 * <div class="modal">
 *     <div class="modal-dialog">
 *         <div class="modal-content">
 *             <div class="modal-header"></div>
 *             <div class="modal-body"></div>
 *             <div class="modal-footer"></div>
 *         </div>
 *     </div>
 * </div>
 *
 * @param {type} param1
 * @param {type} param2
 */
humhub.module('ui.markdown', function (module, require, $) {
    var util = require('util');
    var object = util.object;
    var additions = require('ui.additions');
    var richtext = require('ui.richtext');
    var Widget = require('ui.widget').Widget;
    var client = require('client');
    var modal = require('ui.modal');

    var init = function () {
        initAddition();
    };

    /**
     * Initializes the [data-ui-markdown] addition used to translate the content of e.g a div into markdown syntax used
     * in posts/comments etc.
     */
    var initAddition = function () {
        additions.register('markdown', '[data-ui-markdown]', function ($match) {
            var converter = new Markdown.getSanitizingConverter();
            Markdown.Extra.init(converter);
            $match.each(function () {
                var $this = $(this);

                if ($this.data('markdownProcessed')) {
                    return;
                }

                // Export all richtext features
                var features = {};
                $this.find('[data-richtext-feature], .oembed_snippet').each(function () {
                    var $this = $(this);

                    var featureKey = $this.data('guid') || '@-' + $this.attr('id');

                    // old oembeds
                    if ($this.is('.oembed_snippted') && !$this.data('guid')) {
                        featureKey = '@-oembed-' + $this.data('url');
                    }

                    features[featureKey] = $this.clone();
                    // We add a space to make sure our placeholder is not appended to any link or something.
                    $this.replaceWith(' ' + featureKey);
                });

                var text = richtext.Richtext.plainText($this.clone());
                var result = converter.makeHtml(text);

                // Rewrite richtext feature
                $.each(features, function (featureKey, $element) {
                    result = result.replace(new RegExp('( )?' + featureKey.trim(), 'g'), $('<div></div>').html($element).html());
                });


                $this.html(result).data('markdownProcessed', true);

                // Make sure to add noopener to all links
                $this.find('a').attr('rel', 'noopener noreferrer');
            });
        });
    };

    var MarkdownField = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(MarkdownField, Widget);

    MarkdownField.prototype.insert = function(e, chunk) {
        var selected = e.getSelection();
        var content = e.getContent();

        e.replaceSelection(chunk);

        var cursor = selected.start;
        e.setSelection(cursor, cursor + chunk.length);
    };

    MarkdownField.prototype.getUploadButtonWidget = function() {
        var uploadWidget = Widget.instance('#markdown-file-upload');
        uploadWidget.$form = $(this.$.closest('form'));

        if(this.options.filesInputName) {
            uploadWidget.options.uploadSubmitName = this.options.filesInputName;
        } else {
            uploadWidget.options.uploadSubmitName = uploadWidget.data('upload-submit-name');
        }
        return uploadWidget;
    };

    MarkdownField.prototype.init = function () {
        var that = this;
        this.$.markdown({
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
                            var linkModal = modal.get('#markdown-modal-add-link');
                            $titleInput = linkModal.$.find('.linkTitle');
                            $urlInput = linkModal.$.find('.linkTarget');

                            linkModal.show();

                            $titleInput.val(e.getSelection().text);
                            if ($titleInput.val() == "") {
                                $titleInput.focus();
                            } else {
                                $urlInput.focus();
                            }

                            linkModal.$.find('.addLinkButton').off('click').on('click', function () {
                                that.insert(e, "[" + $titleInput.val() + "](" + $urlInput.val() + ")");
                                linkModal.close();
                            });

                            linkModal.$.on('hide.bs.modal', function (e) {
                                $titleInput.val("");
                                $urlInput.val("");
                            })
                        }
                    },
                    {
                        name: "cmdImgWiki",
                        title: "Image/File",
                        icon: {glyph: 'glyphicon glyphicon-picture', fa: 'fa fa-picture-o', 'fa-3': 'icon-picture'},
                        callback: function (e) {

                            var fileModal = modal.get('#markdown-modal-file-upload');
                            fileModal.show();

                            that.getUploadButtonWidget().off('uploadEnd').on('uploadEnd', function(evt, response) {
                                fileModal.close();
                                $.each(response.result.files, function(i, file) {
                                    var chunk = (file.mimeType.substring(0, 6) == "image/") ? '!' : '';
                                    chunk += "[" + file.name + "](file-guid-" + file.guid + ")";
                                    that.insert(e, chunk);
                                    e.setSelection(e.end, 0);
                                });
                            });
                        }
                    },
                    ]
                }]
            ],
            reorderButtonGroups: ["groupFont", "groupCustom", "groupMisc", "groupUtil"],
            onPreview: function (e) {
                var options = {
                    dataType: 'html',
                    data : {
                        markdown: e.getContent()
                    }
                };

                client.post(that.options.previewUrl, options).then(function(response) {
                    that.$.siblings('.md-preview').html(response.html);
                });

                return "<div><div class='loader'></div></div>";
            }
        });

        /*$('#addFileModal_' + elementId).find(".uploadProgress").hide();
        $('#addFileModal_' + elementId).find('.fileUploadButton').fileupload({
            dataType: 'json',
            done: function (e, data) {
                debugger;
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
        }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');*/
    };


    module.export({
        init: init,
        MarkdownField: MarkdownField
    });
});