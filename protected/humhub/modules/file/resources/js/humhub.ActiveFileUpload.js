humhub.module('ActiveFileUpload', function (module, require, $) {
    const Widget = require('ui.widget').Widget;
    const object = require('util').object;
    const string = require('util').string;
    const BasePreview = require('file').Preview

    const Upload = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Upload, Widget);

    Upload.uploadWidget = null;
    Upload.previewWidget = null;

    Upload.prototype.init = function () {
        this.uploadWidget = Widget.instance(this.$.find('input[type="file"]'));
        this.previewWidget = Widget.instance(this.$.find('.img-uploader-preview'));

        const removeButton = this.$.find('.img-uploader-remove');

        if (this.previewWidget.hasFiles()) {
            removeButton.show();
        }

        this.uploadWidget.on('fileDeleted', () => {
            removeButton.hide();
        });

        this.uploadWidget.on('uploadFinish', () => {
            removeButton.show();
        });
    };

    Upload.prototype.upload = function () {
        this.uploadWidget.trigger('upload')
    };

    Upload.prototype.delete = function (event) {
        event.preventDefault()

        this.uploadWidget.delete({guid: this.previewWidget.$.find('.file-preview-item').first().attr('data-guid')});
        this.previewWidget.reset();
    };

    const Preview = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Preview, BasePreview);

    Preview.prototype.add = function (file) {
        file.name = string.encode(file.name);
        file.galleryId = this.$.attr('id') + '_file_preview_gallery';

        if (file.highlight) {
            file.highlight = 'highlight';
        } else {
            file.highlight = '';
        }

        this.$list.html(
            $('<li></li>')
                .addClass('file-preview-item')
                .attr('data-guid', file.guid)
                .html(string.template(Preview.template.popover, file))
        );
    };

    module.export({
        Upload: Upload,
        Preview: Preview,
    });
});
