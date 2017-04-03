/**
 * Manages the client/server communication. Handles humhub json api responses and
 * pjax requests.
 */
humhub.module('file', function (module, require, $) {

    var Widget = require('ui.widget').Widget;
    var Progress = require('ui.progress').Progress;
    var client = require('client');
    var util = require('util');
    var object = util.object;
    var string = util.string;
    var action = require('action');
    var event = require('event');
    
    var view = require('ui.view');

    var Upload = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Upload, Widget);

    Upload.component = 'humhub-file-upload';

    Upload.prototype.init = function () {
        this.fileCount = this.options.fileCount || 0;
        this.options.name = this.options.name || 'fileList[]';
        this.$form = (this.$.data('upload-form')) ? $(this.$.data('upload-form')) : this.$.closest('form');
        this.initProgress();
        this.initPreview();
        this.initFileUpload();
        
        if(!this.canUploadMore()) {
            this.disable(this.$.data('max-number-of-files-message'));
        }

        var that = this;
        this.on('upload', function () {
            that.$.trigger('click');
        });
    };

    Upload.prototype.validate = function () {
        return this.$.is('[type="file"]');
    };

    Upload.prototype.getDefaultOptions = function () {
        var that = this;
        var data = {
            objectModel: this.$.data('upload-model'),
            objectId: this.$.data('upload-model-id')
        };
        return {
            url: this.$.data('url') || this.$.data('upload-url') || module.config.upload.url,
            dropZone: this.getDropZone(),
            dataType: 'json',
            formData: data,
            autoUpload: false,
            singleFileUploads: false,
            add: function (e, data) {
                if (that.options.maxNumberOfFiles && (that.getFileCount() + data.files.length > that.options.maxNumberOfFiles)) {
                    that.handleMaxFileReached();
                } else {
                    data.process().done(function () {
                        data.submit();
                    });
                }

            }
        };
    };

    Upload.prototype.handleMaxFileReached = function () {
        module.log.warn(this.$.data('max-number-of-files-message'), true);
        this.$ = $(this.getIdSelector());
        if (!this.canUploadMore()) {
            this.disable(this.$.data('max-number-of-files-message'));
        }
    };

    Upload.prototype.disable = function (message) {
        var $trigger = this.getTrigger();
        if ($trigger.length) {
            $trigger.addClass('disabled');
            this.originalTriggerTitle = $trigger.data('original-title');
            message = message || 'disabled';
            if(message && $trigger.data('bs.tooltip')) {
                $trigger.attr('data-original-title', message)
                        .tooltip('fixTitle');
            }
        }

        this.$.prop('disabled', true);
    };
    
    Upload.prototype.enable = function () {
        var $trigger = this.getTrigger();
        if ($trigger.length) {
            $trigger.removeClass('disabled');
        }
        
        if($trigger.data('bs.tooltip')) {
            $trigger.attr('data-original-title', this.originalTriggerTitle)
                        .tooltip('fixTitle');
        }

        this.$.prop('disabled', false);
    };

    Upload.prototype.getDropZone = function () {
        var dropZone = $(this.$.data('upload-drop-zone'));
        dropZone = (dropZone.length) ? dropZone : this.getTrigger();
        return dropZone;
    };

    Upload.prototype.getTrigger = function () {
        return $('[data-action-target="' + this.getIdSelector() + '"]');
    };

    Upload.prototype.reset = function () {
        this.fileCount = 0;
        this.$form.find('input[name="' + this.options.name + '"]').remove();
        if (this.preview) {
            this.preview.reset();
        }
    };

    Upload.prototype.getIdSelector = function () {
        return '#' + this.$.attr('id');
    };

    Upload.prototype.initPreview = function () {
        if (this.$.data('upload-preview')) {
            this.preview = Preview.instance(this.$.data('upload-preview'));
            if (this.preview.setSource) {
                this.preview.setSource(this);
            } else {
                this.preview.source = this;
            }
            
            // Get current file count form preview component.
            if(object.isFunction(this.preview.getFileCount)) {
                this.fileCount = this.preview.getFileCount();
            }
        }
    };
    
    Upload.prototype.getFileCount = function () {
        if(this.preview && object.isFunction(this.preview.getFileCount)) {
            return this.preview.getFileCount();
        }
        return this.fileCount;
    };

    Upload.prototype.initProgress = function () {
        this.progress = Progress.instance(this.$.data('upload-progress'));
    };

    Upload.prototype.initFileUpload = function () {

        this.$.on('click', function (evt) {
            evt.stopPropagation();
        });

        // Save option callbacks
        this.callbacks = {
            start: this.options.start,
            progressall: this.options.progressall,
            done: this.options.done,
            error: this.options.error,
            stop: this.options.stop
        };

        $.extend(this.options, {
            start: $.proxy(this.start, this),
            progressall: $.proxy(this.updateProgress, this),
            done: $.proxy(this.done, this),
            error: $.proxy(this.error, this),
            stop: $.proxy(this.finish, this)
        });

        this.$.fileupload(this.options);
    };

    Upload.prototype.error = function (e) {
        module.log.error(e, true);
    };

    Upload.prototype.start = function (e, data) {
        if (this.progress) {
            this.progress.fadeIn();
        }

        if (this.callbacks.start) {
            this.callbacks.start(e, data);
        }
        
        this.fire('humhub:file:uploadStart', [data]);
    };

    Upload.prototype.updateProgress = function (e, data) {
        if (this.progress) {
            this.progress.update(data.loaded, data.total);
        }

        if (this.callbacks.processall) {
            this.callbacks.processall(e, data);
        }
    };

    Upload.prototype.done = function (e, response) {
        var that = this;
        
        if(!response.result.files || !response.result.files.length) {
            module.log.error('error.unknown', true);
        }
        
        $.each(response.result.files, function (index, file) {
            that.handleFileResponse(file);
        });

        if (this.callbacks.done) {
            this.callbacks.done(e, response);
        }

        this.fire('humhub:file:uploadEnd', [response]);
    };

    Upload.prototype.handleFileResponse = function (file) {
        if (file.error) {
            this.errors.push(file.name + ':');
            this.errors.push(file.errors);
            this.errors.push('&nbsp;');
        } else if (this.$form && this.$form.length) {
            var name = this.options.uploadSubmitName || 'fileList[]';
            
            if(this.options.uploadSingle) {
                this.$form.find('input[name="'+name+'"]').remove();
                this.fileCount = 1;
            } else {
                this.fileCount++;
            }
            
            this.$form.append('<input type="hidden" name="' + name + '" value="' + file.guid + '">');
            if (this.preview) {
                this.preview.show();
                this.preview.add(file);
            }
        }
    };

    Upload.prototype.delete = function (file) {
        var that = this;
        return new Promise(function (resolve, reject) {
            _delete(file).then(function (response) {
                that.$form.find('[value="' + file.guid + '"]').remove();
                module.log.success('success.delete', true);
                that.enable();
                resolve();
            }).catch(function (err) {
                module.log.error(err, true);
                reject(err);
            });
        });
    };

    Upload.prototype.finish = function (e) {
        if (this.progress) {
            this.progress.fadeOut().then(function (progress) {
                progress.reset();
            });
        }

        if (this.errors.length) {
            this.statusError(module.text('error.upload'));
            this.errors = [];
        }

        if (this.callbacks.stop) {
            this.callbacks.stop(e);
        }

        // We have to reselect the input node since it was replaced by blueimp
        // https://github.com/blueimp/jQuery-File-Upload/wiki/Frequently-Asked-Questions#why-is-the-file-input-field-cloned-and-replaced-after-each-selection
        this.$ = $(this.getIdSelector());

        if (!this.canUploadMore()) {
            this.disable(this.$.data('max-number-of-files-message'));
        }
    };

    Upload.prototype.canUploadMore = function () {
        return !this.options.maxNumberOfFiles || (this.getFileCount() < this.options.maxNumberOfFiles);
    };

    var Preview = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Preview, Widget);

    Preview.component = 'humhub-file-preview';

    Preview.prototype.init = function (files) {
        this.$list = $(Preview.template.root);
        this.$.append(this.$list);

        if (!files) {
            this.$.hide();
            return;
        } else {
            this.$.show();
        }

        var that = this;
        $.each(files, function (i, file) {
            that.add(file);
        });
    };
    
    Preview.prototype.getFileCount = function () {
        return this.$.find('.file-preview-item').length;
    };

    Preview.prototype.add = function (file) {
        file.galleryId = this.$.attr('id') + '_file_preview_gallery';
        var template = this.getTemplate(file);
        var $file = $(string.template(template, file));
        
        if(this.source && this.source.options.uploadSingle) {
            this.$list.find('li').remove();
        }
        
        this.$list.append($file);

        if (file.thumbnailUrl && !this.options.preventPopover) {
            // Preload image
            new Image().src = file.thumbnailUrl;
            if(!view.isSmall()) {
                $file.find('.file-preview-content').popover({
                    html: true,
                    trigger: 'hover',
                    animation: 'fade',
                    delay: 100,
                    placement: this.options.popoverPosition || 'right',
                    container: 'body',
                    content: function () {
                        return string.template(Preview.template.popover, file);
                    }
                });
            }
        };

        var that = this;
        $file.find('.file_upload_remove_link').on('click', function () {
            that.delete(file);
        });
        
        if(!(this.isImage(file) && this.options.hideImageFileInfo)) {
            $file.fadeIn();
        }
    };
    
    Preview.prototype.isImage = function (file) {
        return file.mimeIcon === 'mime-image';
    };
    
    Preview.prototype.getTemplate = function (file) {
        if (this.options.fileEdit) {
            return Preview.template.file_edit;
        } else if (file.thumbnailUrl) {
            return Preview.template.file_image;
        } else {
            return Preview.template.file;
        }
    };

    Preview.prototype.delete = function (file) {
        var that = this;

        var promise = (this.source) ? this.source.delete(file) : _delete(file);
        promise.then(function (response) {
            that.remove(file).then(function () {
                if (!that.source) {
                    module.log.success('success.delete', true);
                }
                if (!that.hasFiles()) {
                    that.hide();
                }
            });
        }).catch(function (err) {
            if (!that.source) {
                module.log.error(err, true);
            }
        });
    };

    Preview.prototype.hasFiles = function () {
        return this.$list.find('li').length > 0;
    };

    Preview.prototype.reset = function () {
        this.$list.find('li').remove();
        this.$.hide();
    };

    Preview.prototype.remove = function (file) {
        var that = this;
        return new Promise(function (resolve, reject) {
            that.$.find('[data-preview-guid="' + file.guid + '"]').fadeOut('fast', function () {
                $(this).remove();
                if (!that.$.find('.file-preview-item').length) {
                    that.hide();
                }
                resolve();
            });
        });
    };

    var _delete = function (file) {
        var options = {
            url: module.config.upload.deleteUrl,
            data: {
                guid: file.guid
            }
        };

        return client.post(options);
    };

    Preview.template = {
        root: '<ul class="files" style="list-style:none; margin:0;padding:0px;"></ul>',
        file_edit: '<li class="file-preview-item mime {mimeIcon}" data-preview-guid="{guid}" style="padding-left:24px;display:none;"><span class="file-preview-content">{name}<span class="file_upload_remove_link" data-ui-loader> <i class="fa fa-times-circle"></i>&nbsp;</span></li>',
        file: '<li class="file-preview-item mime {mimeIcon}" data-preview-guid="{guid}" style="padding-left:24px;display:none;"><span class="file-preview-content">{openLink}<span class="time file-fileInfo" style="padding-right: 20px;"> - {size_format}</span></li>',
        file_image: '<li class="file-preview-item mime {mimeIcon}" data-preview-guid="{guid}" style="padding-left:24px;display:none;"><span class="file-preview-content">{openLink}<span class="time file-fileInfo" style="padding-right: 20px;"> - {size_format}</span></li>',
        popover: '<img alt="{name}" src="{thumbnailUrl}" />'
    };

    var init = function () {
        event.on('humhub:file:created', function (evt, files) {
            
            if (!object.isArray(files)) {
                files = [files];
            }

            var $processTrigger = action.getProcessTrigger('file-handler');
            var upload = Widget.instance($processTrigger.closest('.btn-group').find('[data-ui-widget]'));

            $.each(files, function (index, file) {
                upload.handleFileResponse(file);
            });

            upload.finish();
        });
    };

    var upload = function (evt) {
        Upload.instance(evt.$target).trigger('upload');
    };

    module.export({
        init: init,
        actionUpload: upload,
        Upload: Upload,
        Preview: Preview
    });
});