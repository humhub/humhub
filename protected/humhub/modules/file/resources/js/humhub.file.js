/**
 * Manages the client/server communication. Handles humhub json api responses and
 * pjax requests.
 */
humhub.module('file', function (module, require, $) {

    var Widget = require('ui.widget').Widget;
    var client = require('client');
    var Progress = require('ui.progress').Progress;
    var util = require('util');
    var object = util.object;
    var action = require('action');
    var event = require('event');
    var i18n = require('i18n');

    module.requiredI18nCategories = ['base', 'FileModule.base'];

    // The events between `file.Preview` and its `<upload-list>` island. The same names as `UPLOAD_EVENTS` in
    // `protected/humhub/vue/upload/uploadBridge.js`, which describes them
    var UPLOAD_EVENTS = {
        ITEMS: 'upload:items',
        ADD: 'upload:add',
        PROGRESS: 'upload:progress',
        DONE: 'upload:done',
        FAIL: 'upload:fail',
        RESET: 'upload:reset',
        READY: 'upload:ready',
        REMOVE: 'upload:remove',
    };

    // Keys of the files being uploaded; the prefix keeps them apart from the guids of uploaded files
    var uploadKeys = 0;
    var nextUploadKey = function () {
        return 'upload-' + (++uploadKeys);
    };

    var uploadFailedMessage = function () {
        return i18n.t('FileModule.base', 'The upload failed.');
    };

    var SIZE_UNITS = ['B', 'KB', 'MB', 'GB', 'TB'];

    // A size as the file rows show it (`formatFileSize()` in `protected/humhub/vue/upload/fileSize.js`)
    var formatFileSize = function (bytes) {
        var value = Number(bytes) || 0;
        var unit = 0;

        while (value >= 1024 && unit < SIZE_UNITS.length - 1) {
            value /= 1024;
            unit++;
        }

        return value.toLocaleString(undefined, {maximumFractionDigits: unit === 0 ? 0 : 1}) + ' ' + SIZE_UNITS[unit];
    };

    // The largest file in bytes the server takes, 0 if unknown
    var maxFileSize = function () {
        return Number((module.config.upload || {}).maxFileSize) || 0;
    };

    var tooLargeMessage = function (limit) {
        return limit > 0
            ? i18n.t('FileModule.base', 'The file is too large. Maximum: {size}', {size: formatFileSize(limit)})
            : i18n.t('FileModule.base', 'The file is too large.');
    };

    // The reason of a refused request: the server's (`message`, or `errors` as a list or by field), else one by the
    // status. A cancelled request is none
    var requestFailMessage = function (jqXHR) {
        var response = (jqXHR && jqXHR.responseJSON) || {};
        var errors = response.errors && typeof response.errors === 'object' ? response.errors : {};
        var messages = Object.values(errors).flat().filter(function (message) {
            return typeof message === 'string' && message;
        });

        if (messages.length) {
            return messages.join(' ');
        }

        if (typeof response.message === 'string' && response.message) {
            return response.message;
        }

        var status = (jqXHR && jqXHR.status) || 0;
        if (status === 413) {
            // A limit in front of PHP (e.g. the web server's), lower than the one checked before uploading: no number
            return tooLargeMessage(0);
        }

        if (status === 429) {
            return i18n.t('FileModule.base', 'Too many uploads. Please try again in a moment.');
        }

        if (status === 0) {
            return i18n.t('FileModule.base', 'The connection was interrupted.');
        }

        if (status >= 500) {
            return i18n.t('FileModule.base', 'The server could not process the file.');
        }

        return uploadFailedMessage();
    };

    // Whether the files go into a richtext instead of the rows
    var richtextFiles = function () {
        return typeof humhub.prosemirrorFileHandler !== 'undefined' && humhub.prosemirrorFileHandler !== false;
    };

    // Numbers the transports of inputs without an id
    var transports = 0;

    /**
     * Sends the files of an upload widget (`file.Upload`) to the server: the files picked in its file input, dropped on
     * its drop zone, pasted into its paste zone or given to `add()`. The requests are XMLHttpRequests with FormData,
     * sent through jQuery's ajax for the CSRF header and the global ajax events, as the Vue `uploadClient.js` does.
     *
     * It reports through the callbacks in `options`, each with an event and the upload's `data`:
     * - `add(e, data)` for each request to be, with `data.files`; `data.submit()` sends it, `data.abort()` cancels it
     * - `start(e)` when a request is submitted while none runs or waits, `stop(e)` when the last one ended
     * - `progress(e, data)` per request and `progressall(e, {loaded, total})` for all of them, in bytes of the files
     * - `done(e, data)` with `data.result` (also in `data._response`), `fail(e, data)` with `data.textStatus` and
     *   `data.errorThrown` ('abort' when cancelled), and before it `error(jqXHR, textStatus, errorThrown)` for a request
     *   that was sent, as jQuery's ajax setting
     *
     * Further options: `url`, `formData` (an object), `singleFileUploads` (one request per file, else one for all
     * files added at once), `limitConcurrentUploads` (the others wait), `dropZone` and `pasteZone` (jQuery objects).
     * The files go in the field named as the input, `files[]` without a name.
     *
     * @param {jQuery} $input the file input
     * @param {Object} options the zones are taken at once, the rest when files are added
     */
    var UploadTransport = function ($input, options) {
        this.$input = $input;
        this.options = options;
        this.waiting = [];
        this.running = [];
        this.progressAll = {loaded: 0, total: 0};
        // One per input: a widget created again for it replaces the handlers of the one before
        this.namespace = '.humhubUpload-' + String($input.attr('id') || ++transports).replace(/[^\w-]/g, '_');
        this.bind();
    };

    UploadTransport.prototype.bind = function () {
        var that = this;
        var ns = this.namespace;

        // Unbound first, all of them: the drop and the paste zone may be the same element
        this.$input.off(ns);
        $(this.options.dropZone).off(ns);
        $(this.options.pasteZone).off(ns);

        this.$input.on('change' + ns, function (evt) {
            var files = Array.from(this.files || []);
            // Lets the same file be picked again
            this.value = '';
            that.add(files, evt);
        });

        // Only files dragged over the drop zone are taken, as a copy
        $(this.options.dropZone).on('dragover' + ns + ' dragenter' + ns + ' dragleave' + ns, function (evt) {
            var dataTransfer = evt.originalEvent && evt.originalEvent.dataTransfer;
            if (that.isBound() && dataTransfer && Array.prototype.indexOf.call(dataTransfer.types || [], 'Files') !== -1) {
                evt.preventDefault();
                if (evt.type === 'dragover') {
                    dataTransfer.dropEffect = 'copy';
                }
            }
        }).on('drop' + ns, function (evt) {
            var dataTransfer = evt.originalEvent && evt.originalEvent.dataTransfer;
            if (that.isBound() && dataTransfer && dataTransfer.files && dataTransfer.files.length) {
                evt.preventDefault();
                var files = droppedFiles(dataTransfer);
                if (object.isArray(files)) {
                    that.add(files, evt);
                } else {
                    files.then(function (found) {
                        that.add(found, evt);
                    });
                }
            }
        });

        $(this.options.pasteZone).on('paste' + ns, function (evt) {
            var clipboardData = evt.originalEvent && evt.originalEvent.clipboardData;
            if (that.isBound() && clipboardData) {
                that.add(Array.from(clipboardData.items || []).map(function (item) {
                    return item.getAsFile ? item.getAsFile() : null;
                }).filter(Boolean), evt);
            }
        });
    };

    // A zone may outlive the input (e.g. the body as paste zone, across pjax): then its handlers go
    UploadTransport.prototype.isBound = function () {
        if (this.$input[0].isConnected) {
            return true;
        }

        $(this.options.dropZone).off(this.namespace);
        $(this.options.pasteZone).off(this.namespace);
        return false;
    };

    /**
     * Adds files as one request each or as one request for all, see `singleFileUploads`.
     *
     * @param {File[]|FileList} files
     * @param {Event} [e] the event that brought them
     */
    UploadTransport.prototype.add = function (files, e) {
        files = Array.from(files || []);
        if (!files.length) {
            return;
        }

        var that = this;
        var sets = this.options.singleFileUploads ? files.map(function (file) {
            return [file];
        }) : [files];

        sets.forEach(function (set) {
            var data = that.createData(set, files);
            if (object.isFunction(that.options.add)) {
                that.options.add.call(that.$input[0], e || null, data);
            } else {
                data.submit();
            }
        });
    };

    // The data of a request; `_state` is internal: undefined until submitted, then 'waiting', 'running' and 'done'
    UploadTransport.prototype.createData = function (files, originalFiles) {
        var that = this;
        var data = {
            files: files,
            originalFiles: originalFiles,
            loaded: 0,
            total: 0,
            _response: {},
            process: function () {
                return $.Deferred().resolveWith(data, [data]).promise();
            },
            submit: function () {
                that.submit(data);
            },
            abort: function () {
                that.abort(data);
            }
        };

        return data;
    };

    // The number of requests that run or wait
    UploadTransport.prototype.active = function () {
        return this.running.length + this.waiting.length;
    };

    UploadTransport.prototype.submit = function (data) {
        if (data._state) {
            return;
        }

        var first = !this.active();
        if (first) {
            this.progressAll = {loaded: 0, total: 0};
        }

        // The size of the files that are left, e.g. after refusing those that are too large; at least 1 byte each
        data.total = data.files.reduce(function (total, file) {
            return total + (file.size || 1);
        }, 0);
        data.loaded = 0;
        this.progressAll.total += data.total;
        data._state = 'waiting';
        this.waiting.push(data);

        // A failing `start` handler must not leave the request queued
        try {
            if (first) {
                this.callback('start');
            }
        } finally {
            this.next();
        }
    };

    UploadTransport.prototype.next = function () {
        var limit = this.options.limitConcurrentUploads;
        while (this.waiting.length && (!limit || this.running.length < limit)) {
            this.send(this.waiting.shift());
        }
    };

    UploadTransport.prototype.send = function (data) {
        var that = this;
        var formData = new FormData();
        $.each(this.options.formData || {}, function (name, value) {
            if (value !== undefined) {
                formData.append(name, value);
            }
        });

        var field = this.$input.attr('name') || 'files[]';
        data.files.forEach(function (file) {
            formData.append(field, file);
        });

        data._state = 'running';
        this.running.push(data);
        data.jqXHR = $.ajax({
            url: this.options.url,
            type: 'POST',
            data: formData,
            // The FormData goes as it is: jQuery must neither serialize it nor set a Content-Type
            processData: false,
            contentType: false,
            cache: false,
            dataType: 'json',
            // Upload progress is an event of the XMLHttpRequest that jQuery does not pass on
            xhr: function () {
                var xhr = $.ajaxSettings.xhr();
                if (xhr.upload) {
                    xhr.upload.addEventListener('progress', function (evt) {
                        if (evt.lengthComputable && evt.total && data._state === 'running') {
                            that.progress(data, Math.floor(evt.loaded / evt.total * data.total));
                        }
                    });
                }
                return xhr;
            }
        }).done(function (result, textStatus, jqXHR) {
            that.end(data, function () {
                // The files are sent completely
                if (data.loaded < data.total) {
                    that.progress(data, data.total);
                }

                data.result = data._response.result = result;
                data.textStatus = data._response.textStatus = textStatus;
                data.jqXHR = data._response.jqXHR = jqXHR;
                that.callback('done', data);
            });
        }).fail(function (jqXHR, textStatus, errorThrown) {
            that.end(data, function () {
                if (object.isFunction(that.options.error)) {
                    that.options.error.call(that.$input[0], jqXHR, textStatus, errorThrown);
                }
                that.failed(data, jqXHR, textStatus, errorThrown);
            });
        });
    };

    UploadTransport.prototype.progress = function (data, loaded) {
        this.progressAll.loaded += loaded - data.loaded;
        data.loaded = loaded;
        this.callback('progress', data);
        this.callback('progressall', {loaded: this.progressAll.loaded, total: this.progressAll.total});
    };

    // A failed or cancelled request no longer counts for the progress of all
    UploadTransport.prototype.failed = function (data, jqXHR, textStatus, errorThrown) {
        this.progressAll.loaded -= data.loaded;
        this.progressAll.total -= data.total;
        data.jqXHR = data._response.jqXHR = jqXHR;
        data.textStatus = data._response.textStatus = textStatus;
        data.errorThrown = data._response.errorThrown = errorThrown;
        this.callback('fail', data);
    };

    // Reports the end of a request, then frees its place for the next one; after the last one the uploads stop
    UploadTransport.prototype.end = function (data, report) {
        try {
            report();
        } finally {
            data._state = 'done';
            this.running = this.running.filter(function (other) {
                return other !== data;
            });
            this.waiting = this.waiting.filter(function (other) {
                return other !== data;
            });
            this.next();

            if (!this.active()) {
                this.callback('stop');
            }
        }
    };

    UploadTransport.prototype.abort = function (data) {
        var that = this;

        if (data._state === 'running') {
            // Ends through the request's failure, as 'abort'
            data.jqXHR.abort();
        } else if (data._state === 'waiting') {
            this.end(data, function () {
                that.failed(data, undefined, 'abort', 'abort');
            });
        } else if (!data._state) {
            data._state = 'done';
            data.errorThrown = 'abort';
            this.callback('fail', data);
        }
    };

    UploadTransport.prototype.callback = function (name, data) {
        if (object.isFunction(this.options[name])) {
            this.options[name].call(this.$input[0], $.Event(name, {target: this.$input[0]}), data);
        }
    };

    /**
     * The files of a drop. A dropped folder gives the files in it and in its folders, later: then the result is a
     * promise.
     *
     * @returns {File[]|Promise<File[]>}
     */
    var droppedFiles = function (dataTransfer) {
        var items = Array.from(dataTransfer.items || []);
        var entries = items.map(function (item) {
            return item.webkitGetAsEntry ? item.webkitGetAsEntry() : null;
        });

        if (!entries.some(function (entry) {
            return entry && entry.isDirectory;
        })) {
            return Array.from(dataTransfer.files);
        }

        // The items are only readable during the drop event
        var files = items.map(function (item) {
            return item.getAsFile ? item.getAsFile() : null;
        });

        return Promise.all(entries.map(function (entry, i) {
            if (entry && entry.isDirectory) {
                return readDirectory(entry);
            }
            return files[i] ? [files[i]] : [];
        })).then(function (lists) {
            return lists.flat();
        });
    };

    // The files in a folder of a drop and in its folders; what can't be read is left out
    var readDirectory = function (directory) {
        return new Promise(function (resolve) {
            var reader = directory.createReader();
            var entries = [];
            var readAll = function () {
                resolve(Promise.all(entries.map(function (entry) {
                    return entry.isDirectory ? readDirectory(entry) : new Promise(function (resolveFile) {
                        entry.file(function (file) {
                            resolveFile([file]);
                        }, function () {
                            resolveFile([]);
                        });
                    });
                })).then(function (lists) {
                    return lists.flat();
                }));
            };
            // The entries come in parts, the last one is empty
            var readPart = function () {
                reader.readEntries(function (part) {
                    if (part.length) {
                        entries = entries.concat(part);
                        readPart();
                    } else {
                        readAll();
                    }
                }, readAll);
            };
            readPart();
        });
    };

    /**
     * The upload of a file input (`file\widgets\UploadInput`, e.g. behind `UploadButton`): sends the files the user
     * picks, drops or pastes (`UploadTransport`) and hands the uploaded files to the form and its preview.
     */
    var Upload = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Upload, Widget);

    Upload.component = 'humhub-file-upload';

    Upload.prototype.init = function () {
        this.fileCount = this.options.fileCount || 0;
        this.options.name = this.options.name || 'fileList[]';
        this.$form = (this.$.data('upload-form')) ? $(this.$.data('upload-form')) : this.$.closest('form');
        // The uploads of the rows, by key, for cancelling them
        this.uploads = {};
        this.initProgress();
        this.initPreview();

        if (this.hasUploadRows()) {
            // One request per file, three at a time, each file in its own row
            this.options.singleFileUploads = true;
            this.options.limitConcurrentUploads = 3;
            this.preview.abortHandler = $.proxy(this.abort, this);
        }

        this.initFileUpload();

        if (!this.canUploadMore()) {
            this.disable(this.$.data('max-number-of-files-message'));
        }

        var that = this;
        this.on('upload', function () {
            that.run();
        });
    };

    Upload.prototype.run = function () {
        this.$.trigger('click');
    };

    /**
     * Uploads files as if the user had picked them, e.g. files dropped onto or pasted into a richtext.
     *
     * @param {File[]|FileList} files
     * @since 1.20
     */
    Upload.prototype.addFiles = function (files) {
        this.transport.add(files);
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

        if (this.$.data('upload-hide-in-stream')) {
            data['hideInStream'] = 1;
        }

        return {
            url: this.$.data('url') || this.$.data('upload-url') || module.config.upload.url,
            dropZone: this.getDropZone(),
            pasteZone: this.getPasteZone(),
            formData: data,
            singleFileUploads: !this.$.prop('multiple'),
            add: function (e, data) {
                if (that.options.maxNumberOfFiles && (that.getFileCount() + data.files.length > that.options.maxNumberOfFiles)) {
                    // With one request per file this comes for each file over the maximum: warn once per selection
                    if (!data.originalFiles || data.originalFiles !== that.refusedFiles) {
                        that.refusedFiles = data.originalFiles;
                        that.handleMaxFileReached(that.options.maxNumberOfFilesMessage);
                    }
                } else if (that.options.phpMaxFileUploads && data.files.length > that.options.phpMaxFileUploads) {
                    that.handleMaxFileReached(that.options.phpMaxFileUploadsMessage);
                } else {
                    that.refuseTooLarge(data);
                    if (data.files.length) {
                        that.addUploadRow(data);
                        data.process().done(function () {
                            data.submit();
                        });
                    }
                }

            }
        };
    };

    /**
     * Whether the preview shows a row per file (`file.Preview`), and this widget uploads one file per request.
     * Other previews (e.g. a module's own list) keep one request for all files.
     */
    Upload.prototype.hasUploadRows = function () {
        return !!this.preview && object.isFunction(this.preview.addPending);
    };

    /**
     * Shows a file the user added as a row being uploaded; the key of the row stays on the upload's data
     * (`uploadKey`). Not for files that go into a richtext instead.
     */
    Upload.prototype.addUploadRow = function (data) {
        if (!this.hasUploadRows() || richtextFiles() || data.files.length !== 1) {
            return;
        }

        var key = data.uploadKey = nextUploadKey();
        this.uploads[key] = data;
        this.preview.addPending(key, data.files[0]);
    };

    /**
     * Takes the files larger than the server takes (the core JS config's `file.upload.maxFileSize`) out of the
     * upload before it is sent: each shows as a refused row, or in the status bar where files get no row.
     */
    Upload.prototype.refuseTooLarge = function (data) {
        var limit = maxFileSize();
        if (!limit) {
            return;
        }

        var that = this;
        data.files = data.files.filter(function (file) {
            if (!(file.size > limit)) {
                return true;
            }

            var message = tooLargeMessage(limit);
            if (that.hasUploadRows() && !richtextFiles()) {
                var key = nextUploadKey();
                that.preview.addPending(key, file);
                that.preview.fail(key, message);
            } else {
                module.log.error(file.name + ': ' + message, true);
            }

            return false;
        });
    };

    /**
     * Cancels the upload of a row (the user removed it); it neither ends as done nor as failed.
     */
    Upload.prototype.abort = function (key) {
        var data = this.uploads[key];
        delete this.uploads[key];
        if (data) {
            data.abort();
        }

        this.enableBelowMax();
    };

    // A row's file was refused: it no longer counts against the maximum. The row shows the reason as text: the server
    // encodes its messages for HTML (`Html::encode()`), e.g. the file name in "File {fileName} could not be uploaded!"
    Upload.prototype.failRow = function (key, message) {
        this.preview.fail(key, util.string.decodeEntities(message));
        this.enableBelowMax();
    };

    // Enables the upload again once there is room for more files; uploads still running count against the maximum
    Upload.prototype.enableBelowMax = function () {
        if (this.$.prop('disabled') && this.canUploadMore()) {
            this.enable();
        }
    };

    Upload.prototype.handleMaxFileReached = function (message) {
        module.log.warn(message, true);
        if (!this.canUploadMore()) {
            this.disable(this.$.data('max-number-of-files-message'));
        }
    };

    Upload.prototype.disable = function (message) {
        var $trigger = this.getTrigger();
        if ($trigger.length) {
            $trigger.addClass('disabled');
            this.originalTriggerTitle = $trigger.data('bs-title');
            message = message || 'disabled';
            if (message && $trigger.data('bs.tooltip')) {
                $trigger.attr('data-bs-title', message)
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

        if ($trigger.data('bs.tooltip')) {
            $trigger.attr('data-bs-title', this.originalTriggerTitle)
                .tooltip('fixTitle');
        }

        this.$.prop('disabled', false);
    };

    Upload.prototype.getDropZone = function () {
        var dropZone = $(this.$.data('upload-drop-zone'));
        dropZone = (dropZone.length) ? dropZone : this.getTrigger();
        return dropZone;
    };

    Upload.prototype.getPasteZone = function () {
        var pasteZone = $(this.$.data('upload-paste-zone'));
        pasteZone = pasteZone.length ? pasteZone : undefined;
        return pasteZone;
    };

    Upload.prototype.getTrigger = function () {
        return $('[data-action-target="' + this.getIdSelector() + '"]');
    };

    Upload.prototype.reset = function () {
        this.fileCount = 0;
        this.$form.find('input[name="' + this.options.name + '"]').remove();
        this.$form.find('input[name="' + this.options.uploadSubmitName + '"]').remove();
        if (this.preview) {
            this.preview.reset();
        }
    };

    Upload.prototype.getIdSelector = function () {
        return '#' + this.$.attr('id');
    };

    Upload.prototype.initPreview = function () {
        var $preview;

        if (this.$.data('upload-preview')) {
            $preview = $(this.$.data('upload-preview'));
        } else {
            $preview = $('#' + this.$.attr('id') + '_preview');
        }

        if ($preview.length) {
            this.preview = Widget.instance($preview);
            if (this.preview.setSource) {
                this.preview.setSource(this);
            } else {
                this.preview.source = this;
            }

            // Get current file count form preview component.
            if (object.isFunction(this.preview.getFileCount)) {
                this.fileCount = this.preview.getFileCount();
            }
        }
    };

    Upload.prototype.getFileCount = function () {
        if (this.preview && object.isFunction(this.preview.getFileCount)) {
            return this.preview.getFileCount();
        }
        return this.fileCount;
    };

    Upload.prototype.initProgress = function () {
        var $progress;

        if (this.$.data('upload-progress')) {
            $progress = $(this.$.data('upload-progress'));
        } else {
            $progress = $('#' + this.$.attr('id') + '_progress');
        }

        this.progress = Progress.instance($progress);
    };

    Upload.prototype.initFileUpload = function () {

        this.$.on('click', function (evt) {
            evt.stopPropagation();
        });

        // Save option callbacks
        this.callbacks = {
            start: this.options.start,
            progress: this.options.progress,
            progressall: this.options.progressall,
            done: this.options.done,
            fail: this.options.fail,
            error: this.options.error,
            stop: this.options.stop
        };

        $.extend(this.options, {
            start: $.proxy(this.start, this),
            progress: $.proxy(this.updateFileProgress, this),
            progressall: $.proxy(this.updateProgress, this),
            done: $.proxy(this.done, this),
            fail: $.proxy(this.fail, this),
            error: $.proxy(this.error, this),
            stop: $.proxy(this.finish, this)
        });

        this.transport = new UploadTransport(this.$, this.options);
    };

    // The request's error; a cancelled upload is none, and with rows the row tells the user
    Upload.prototype.error = function (e, textStatus) {
        if (textStatus === 'abort') {
            return;
        }

        module.log.error(e, !this.hasUploadRows());
    };

    // A failed upload of a row; a cancelled one has no row anymore
    Upload.prototype.fail = function (e, data) {
        var key = data.uploadKey;
        if (key !== undefined) {
            delete this.uploads[key];
            if (this.hasUploadRows() && data.errorThrown !== 'abort' && data.textStatus !== 'abort') {
                this.failRow(key, requestFailMessage(data.jqXHR));
            }
        }

        if (this.callbacks.fail) {
            this.callbacks.fail(e, data);
        }
    };

    Upload.prototype.start = function (e, data) {
        if (this.progress) {
            this.progress.fadeIn();
        }

        if (this.callbacks.start) {
            this.callbacks.start(e, data);
        }

        this.fire('humhub:file:uploadStart', [data]);
        this.fire('uploadStart', [data]);
    };

    Upload.prototype.updateProgress = function (e, data) {
        if (this.progress) {
            this.progress.update(data.loaded, data.total);
        }

        if (this.callbacks.progressall) {
            this.callbacks.progressall(e, data);
        }
    };

    // The progress of one request, i.e. of a row
    Upload.prototype.updateFileProgress = function (e, data) {
        if (data.uploadKey !== undefined && this.hasUploadRows() && data.total) {
            this.preview.progress(data.uploadKey, Math.floor(data.loaded / data.total * 100));
        }

        if (this.callbacks.progress) {
            this.callbacks.progress(e, data);
        }
    };

    /**
     * Handles the response of an upload request; with rows the response of one file. A module may call it with a
     * response of several files, which then go into the preview as uploaded files.
     */
    Upload.prototype.done = function (e, response) {
        var that = this;
        var key = response.uploadKey;
        var rows = key !== undefined && this.hasUploadRows();

        if (key !== undefined) {
            delete this.uploads[key];
        }

        if (!response.result.files || !response.result.files.length) {
            if (rows) {
                this.failRow(key, uploadFailedMessage());
            } else {
                module.log.error('error.unknown', true);
            }
        }

        $.each(response.result.files, function (index, file) {
            that.handleFileResponse(file, rows ? key : undefined);
        });

        if (this.callbacks.done) {
            this.callbacks.done(e, response);
        }

        //deprecated event use uploadEnd
        this.fire('humhub:file:uploadEnd', [response]);
        this.fire('uploadEnd', [response]);
    };

    /**
     * Takes an uploaded or refused file into the form and the preview.
     *
     * @param {Object} file a file of the upload response (`FileHelper::getFileInfos()`)
     * @param {string} [key] the row of the file; without one an uploaded file is added to the preview
     */
    Upload.prototype.handleFileResponse = function (file, key) {
        var row = key !== undefined && this.hasUploadRows();
        if (row && !this.preview.hasItem(key)) {
            // The row was removed meanwhile
            return;
        }

        if (file.error) {
            if (row) {
                this.failRow(key, fileErrorMessage(file));
            } else {
                this.errors.push(file.name + ':');
                this.errors.push(file.errors);
                this.errors.push('&nbsp;');
            }
            return;
        }

        if (this.$form && this.$form.length) {
            var name = this.options.uploadSubmitName || 'fileList[]';

            if (this.options.uploadSingle) {
                this.$form.find('input[name="' + name + '"]').remove();
                this.fileCount = 1;
            } else {
                this.fileCount++;
            }

            this.$form.append('<input type="hidden" name="' + name + '" value="' + file.guid + '">');
            if (!row && this.preview && (typeof humhub.prosemirrorFileHandler === 'undefined' || humhub.prosemirrorFileHandler === false)) {
                this.preview.show();
                this.preview.add(file);
            }
        }

        if (row) {
            this.preview.done(key, file);
        }
    };

    // The reason a file was refused, from the upload response (one message or a list)
    var fileErrorMessage = function (file) {
        var errors = object.isArray(file.errors) ? file.errors : [file.errors];
        var message = errors.filter(function (error) {
            return typeof error === 'string' && error;
        }).join(' ');

        return message || uploadFailedMessage();
    };

    Upload.prototype.delete = function (file) {
        var that = this;
        return new Promise(function (resolve, reject) {
            _delete(file).then(function (response) {
                that.$form.find('[value="' + file.guid + '"]').remove();
                module.log.success(i18n.t('base', 'The file has been deleted.'), true);
                that.enable();
                that.fire('fileDeleted', [file]);
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
            this.statusError(i18n.t('base', 'Some files could not be uploaded:'));
            this.errors = [];
        }

        if (this.callbacks.stop) {
            this.callbacks.stop(e);
        }

        if (!this.canUploadMore()) {
            this.disable(this.$.data('max-number-of-files-message'));
        }

        this.fire('uploadFinish', [this]);
    };

    Upload.prototype.canUploadMore = function () {
        return !this.options.maxNumberOfFiles || (this.getFileCount() < this.options.maxNumberOfFiles);
    };

    /**
     * Keeps the submit button of a form disabled while files upload, from `uploadStart` until no upload of this widget
     * runs or waits anymore: the form is not sent before its files are uploaded. Several upload widgets may lock the
     * same button (e.g. the attachments and the editor's own upload); it waits for all of them. A second call replaces
     * the button.
     *
     * @param {jQuery|string} button
     * @since 1.20
     */
    Upload.prototype.lockWhileUploading = function (button) {
        var that = this;
        var toggle = function (uploading) {
            var $button = $(button);
            var running = ($button.data('humhub-file-uploads') || []).filter(function (upload) {
                return upload !== that;
            });
            if (uploading) {
                running.push(that);
            }
            $button.data('humhub-file-uploads', running).prop('disabled', running.length > 0);
        };

        this.off('.humhubUploadLock')
            .on('uploadStart.humhubUploadLock', function () {
                toggle(true);
            })
            // Modules and the `humhub:file:created` handler call finish() while requests may still run
            .on('uploadFinish.humhubUploadLock', function () {
                toggle(that.isActive());
            });
    };

    /**
     * Whether requests of this widget run or wait.
     *
     * @returns {boolean}
     * @since 1.20
     */
    Upload.prototype.isActive = function () {
        return !!this.transport && this.transport.active() > 0;
    };

    /**
     * Keeps the submit button of a form disabled while one of the upload widgets in it uploads, e.g. the attachments
     * and the richtext's own upload (`Upload.lockWhileUploading()`).
     *
     * @param {jQuery} $container the form, or an element around it
     * @param {jQuery|string} button
     * @since 1.20
     */
    var lockFormWhileUploading = function ($container, button) {
        $container.find('input[type="file"][data-ui-widget]').each(function () {
            var upload = Widget.instance($(this));
            if (upload && object.isFunction(upload.lockWhileUploading)) {
                upload.lockWhileUploading(button);
            }
        });
    };

    /**
     * The files of a form (docs/ui/file-upload.md): the facade of the `<upload-list>` island that
     * `file\widgets\FilePreview` renders into this container. It keeps the rows - files being uploaded, refused files
     * and uploaded files - and sends them to the island through DOM events on the island's tag (`UPLOAD_EVENTS`).
     * The island starts with the files of its props and, once mounted, asks for the rows (`upload:ready`).
     *
     * The upload widget (`file.Upload`) feeds it per file: `addPending()`, `progress()`, then `done()` or `fail()`.
     * Modules add uploaded files with `add()`. When the user removes a row, its upload is cancelled
     * (`abortHandler`) or its file deleted (`delete()`, the row shows it is busy meanwhile); a removed file fires
     * `fileRemoved`.
     */
    var Preview = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Preview, Widget);

    Preview.component = 'humhub-file-preview';

    /**
     * Cancels the upload of a row, `function (key)`; set by the upload widget.
     */
    Preview.prototype.abortHandler = null;

    Preview.prototype.init = function () {
        var $island = this.$.find('upload-list[data-upload-bridge]').first();
        this.island = $island[0] || null;
        this.ready = false;
        // The keys of the rows whose file is being deleted
        this.removing = {};
        // The rows, `[{key, file, progress, error}]`; `file` in the API's shape (`file\serializers\FileSerializer::file()`)
        this.items = readIslandItems($island).map(function (item) {
            return {key: String(item.key), file: item.file || {}, progress: null, error: null};
        });

        var that = this;
        this.$.off('.humhubFilePreview')
            .on(UPLOAD_EVENTS.READY + '.humhubFilePreview', function (evt) {
                if (evt.target === that.island) {
                    that.ready = true;
                    that.sync();
                }
            })
            .on(UPLOAD_EVENTS.REMOVE + '.humhubFilePreview', function (evt) {
                var detail = evt.originalEvent && evt.originalEvent.detail;
                if (evt.target === that.island && detail) {
                    that.removeItem(detail.key);
                }
            });

        // The island was mounted before this widget
        if (this.island && this.island.hasAttribute('data-upload-ready')) {
            this.ready = true;
            this.sync();
        }
    };

    /**
     * The uploaded files and those still uploading; refused files don't count.
     */
    Preview.prototype.getFileCount = function () {
        return this.items.filter(function (item) {
            return !item.error;
        }).length;
    };

    Preview.prototype.hasFiles = function () {
        return this.getFileCount() > 0;
    };

    Preview.prototype.hasItem = function (key) {
        return !!this.getItem(key);
    };

    Preview.prototype.getItem = function (key) {
        return this.items.find(function (item) {
            return item.key === key;
        });
    };

    /**
     * A file the user added, while it uploads.
     *
     * @param {string} key the row's key, for progress(), done() and fail()
     * @param {{name: string, size: number}} file the picked file
     */
    Preview.prototype.addPending = function (key, file) {
        var item = {key: String(key), file: {fileName: file.name, size: file.size}, progress: 0, error: null};
        var index = this.items.findIndex(function (other) {
            return other.key === item.key;
        });

        if (index === -1) {
            this.items.push(item);
        } else {
            this.items[index] = item;
        }

        this.send(UPLOAD_EVENTS.ADD, copyItem(item, false));
        this.toggle();
    };

    /**
     * @param {string} key
     * @param {number} percent 0-100; the row takes whole percents, each once
     */
    Preview.prototype.progress = function (key, percent) {
        var item = this.getItem(key);
        var progress = Math.floor(Math.max(0, Math.min(100, Number(percent) || 0)));
        if (item && isUploading(item) && item.progress !== progress) {
            item.progress = progress;
            this.send(UPLOAD_EVENTS.PROGRESS, {key: item.key, percent: progress});
        }
    };

    /**
     * @param {string} key
     * @param {Object} file the uploaded file, as the upload response has it (`FileHelper::getFileInfos()`)
     */
    Preview.prototype.done = function (key, file) {
        var item = this.getItem(key);
        if (!item) {
            return;
        }

        item.file = toFileShape(file);
        item.progress = null;
        item.error = null;

        if (this.isSingle()) {
            // A new file replaces the one before
            this.items = this.items.filter(function (other) {
                return other === item || isUploading(other);
            });
            this.sync();
        } else {
            this.send(UPLOAD_EVENTS.DONE, {key: item.key, file: item.file});
        }
    };

    /**
     * The file was refused; its row keeps the reason until the user removes it.
     *
     * @param {string} key
     * @param {string} message
     */
    Preview.prototype.fail = function (key, message) {
        var item = this.getItem(key);
        if (!item) {
            return;
        }

        item.progress = null;
        item.error = message || uploadFailedMessage();
        this.send(UPLOAD_EVENTS.FAIL, {key: item.key, message: item.error});
    };

    /**
     * Adds an uploaded file, e.g. a file a module uploaded.
     *
     * @param {Object} file as the upload response has it (`FileHelper::getFileInfos()`), or in the API's shape
     */
    Preview.prototype.add = function (file) {
        var shape = toFileShape(file);
        if (!this.options.fileEdit && this.options.excludeMediaFilesPreview && this.isMedia(shape)) {
            return;
        }

        var item = {key: shape.guid ? String(shape.guid) : nextUploadKey(), file: shape, progress: null, error: null};
        var single = this.isSingle();
        this.items = this.items.filter(function (other) {
            return other.key !== item.key && (!single || isUploading(other));
        });
        this.items.push(item);
        this.sync();
    };

    Preview.prototype.isImage = function (file) {
        return file.mimeIcon === 'mime-image';
    };

    Preview.prototype.isMedia = function (file) {
        return ['mime-image', 'mime-video', 'mime-audio'].includes(file.mimeIcon);
    };

    Preview.prototype.isSingle = function () {
        return !!(this.source && this.source.options && this.source.options.uploadSingle);
    };

    /**
     * Deletes an uploaded file and removes its row.
     *
     * @returns {Promise}
     */
    Preview.prototype.delete = function (file) {
        var that = this;
        var item = this.findFile(file);
        var key = item ? item.key : null;

        if (key !== null) {
            if (this.removing[key]) {
                return Promise.resolve();
            }
            // The row shows it is busy meanwhile
            this.removing[key] = true;
            this.sync();
        }

        var promise = (this.source && object.isFunction(this.source.delete)) ? this.source.delete(file) : _delete(file);
        return Promise.resolve(promise).then(function () {
            return that.remove(file).then(function () {
                if (!that.source) {
                    module.log.success(i18n.t('base', 'The file has been deleted.'), true);
                }
            });
        }).catch(function (err) {
            if (!that.source) {
                module.log.error(err, true);
            }
        }).finally(function () {
            if (key !== null && that.removing[key]) {
                delete that.removing[key];
                // The file could not be deleted: its row stays
                if (that.hasItem(key)) {
                    that.sync();
                }
            }
        });
    };

    /**
     * Removes the row of a file without deleting the file.
     *
     * @returns {Promise}
     */
    Preview.prototype.remove = function (file) {
        var item = this.findFile(file);
        if (item) {
            this.drop(item.key);
            this.fire('fileRemoved', [file]);
        }

        return Promise.resolve();
    };

    /**
     * Clears the rows; uploads still running are cancelled.
     */
    Preview.prototype.reset = function () {
        var that = this;
        var uploading = this.items.filter(isUploading);

        this.items = [];
        this.removing = {};
        uploading.forEach(function (item) {
            that.abort(item.key);
        });

        this.send(UPLOAD_EVENTS.RESET);
        this.$.hide();
    };

    // The user removed a row in the island: a refused file goes, an upload is cancelled, an uploaded file deleted
    Preview.prototype.removeItem = function (key) {
        var item = this.getItem(key);
        if (!item || this.removing[item.key]) {
            return;
        }

        if (item.error) {
            this.drop(item.key);
        } else if (isUploading(item)) {
            this.drop(item.key);
            this.abort(item.key);
        } else {
            this.delete(item.file);
        }
    };

    Preview.prototype.abort = function (key) {
        if (object.isFunction(this.abortHandler)) {
            this.abortHandler(key);
        }
    };

    Preview.prototype.findFile = function (file) {
        return file && file.guid ? this.items.find(function (item) {
            return item.file && item.file.guid === file.guid;
        }) : undefined;
    };

    // Takes a row out. The island hands the focus of a removed row on to another row; after the last one it goes to
    // the upload button (docs/ui/keyboard.md, "Moving the focus")
    Preview.prototype.drop = function (key) {
        this.items = this.items.filter(function (item) {
            return item.key !== key;
        });

        if (!this.items.length) {
            this.focusUploadButton();
        }

        this.sync();
    };

    Preview.prototype.focusUploadButton = function () {
        if (!this.island || !this.island.contains(document.activeElement)) {
            return;
        }

        var $trigger = (this.source && object.isFunction(this.source.getTrigger)) ? this.source.getTrigger() : $();
        if ($trigger.length) {
            $trigger[0].focus();
        }
    };

    // Sends all rows to the island
    Preview.prototype.sync = function () {
        var removing = this.removing;
        this.send(UPLOAD_EVENTS.ITEMS, this.items.map(function (item) {
            return copyItem(item, !!removing[item.key]);
        }));
        this.toggle();
    };

    // Events to the island must be native: jQuery's trigger() does not reach its listeners
    Preview.prototype.send = function (type, detail) {
        if (this.ready && this.island) {
            this.island.dispatchEvent(new CustomEvent(type, {detail: detail}));
        }
    };

    // The container shows while there are rows; the last row fades out with it
    Preview.prototype.toggle = function () {
        if (this.items.length) {
            this.$.stop(true, true).show();
        } else {
            this.$.fadeOut('fast');
        }
    };

    var isUploading = function (item) {
        return item.progress !== null && item.progress !== undefined && !item.error;
    };

    // A row for the island; `busy` while its file is being deleted
    var copyItem = function (item, busy) {
        return {key: item.key, file: item.file, progress: item.progress, error: item.error, busy: busy};
    };

    // The rows the island starts with: the `items` of its props (`file\widgets\FilePreview`)
    var readIslandItems = function ($island) {
        try {
            var props = JSON.parse($island.attr('props') || '{}');
            return object.isArray(props.items) ? props.items : [];
        } catch (e) {
            module.log.error(e);
            return [];
        }
    };

    /**
     * A file as the rows show it, in the API's shape (`file\serializers\FileSerializer::file()`), from the file of an
     * upload response (`FileHelper::getFileInfos()`). Without `icon` (a module's own response) the row shows the
     * generic icon, without `downloadUrl` its download goes to `url`.
     */
    var toFileShape = function (file) {
        if (file.fileName !== undefined) {
            return $.extend({}, file);
        }

        return {
            guid: file.guid,
            fileName: file.name,
            size: Number(file.size) || 0,
            mimeType: file.mimeType,
            mimeIcon: file.mimeIcon,
            icon: file.icon || null,
            url: file.url,
            downloadUrl: file.downloadUrl || null,
            previewUrl: file.thumbnailUrl || null,
            highlight: !!file.highlight,
        };
    };

    var uploadByType = function (evt) {
        const selectedType = evt.$trigger;
        const type = evt.params.type;
        const btnGroup = selectedType.closest('.btn-group');
        const inputBtn = btnGroup.children('.fileinput-button');
        const inputField = $(inputBtn.data('action-target'));

        inputField.attr('accept', type);
        inputBtn.click();
        inputField.removeAttr('accept');
    }

    var getFileUrl = function (guid, mode) {
        var url = module.config.url.load;

        if (typeof mode !== 'undefined') {
            if (mode === 'download' || mode === true || mode === 1) {
                url = module.config.url.download
            } else if (mode === 'view') {
                url = module.config.url.view;
            } else if (mode === 'display' && module.config.url.display) {
                // What the media viewer shows of an image (the display version)
                url = module.config.url.display;
            }
        }

        return url.replace('-guid-', guid);
    };

    var filterFileUrl = function (url, mode) {
        var result = {
            url: url,
            guid: null
        };

        if (url.indexOf('file-guid:') === 0 || url.indexOf('file-guid-') === 0) {
            result.guid = url.substr(10, url.length);
            result.url = humhub.modules.file.getFileUrl(result.guid, mode);
        }

        return result;
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

    var init = function () {
        event.on('humhub:file:created', function (evt, files) {

            if (!object.isArray(files)) {
                files = [files];
            }

            // Only a handler offered next to a legacy upload widget is handled here. A handler
            // offered elsewhere - e.g. in a Vue island, which attaches the created files itself -
            // has no widget to push them into.
            var $processTrigger = action.getProcessTrigger('file-handler');
            var upload = $processTrigger
                ? Widget.instance($processTrigger.closest('.btn-group').find('[data-ui-widget]'))
                : null;

            if (!upload) {
                return;
            }

            $.each(files, function (index, file) {
                upload.handleFileResponse(file);
            });

            upload.finish();
        });

        // The upload trigger (e.g. "Attach Files") is a <span data-action-click>
        // with tabindex, not a native <button> - so unlike a real button, the
        // browser does not turn Enter/Space into a click on its own. Do that here.
        $(document).on('keydown', '[data-action-click="file.upload"]', function (evt) {
            if (evt.which === 13 /* Enter */ || evt.which === 32 /* Space */) {
                evt.preventDefault();
                $(this).trigger('click');
            }
        });

        // Bootstrap 3 drop-down menu auto dropup according to screen position
        // Must be removed when Humhub uses Bootstrap 4+
        var dropdownButtonSelector = '.richtext-create-buttons > .btn-group';
        $(document).on("shown.bs.dropdown", dropdownButtonSelector, function () {
            var $ul = $(this).children(".dropdown-menu");
            var $button = $(this).children(".dropdown-toggle");
            var ulOffset = $ul.offset();
            var spaceUp = (ulOffset.top - $button.height() - $ul.height()) - $(window).scrollTop();
            var spaceDown = $(window).scrollTop() + $(window).height() - (ulOffset.top + $ul.height());
            if (spaceDown < 0 && (spaceUp >= 0 || spaceUp > spaceDown))
                $(this).addClass("dropup");
        }).on("hidden.bs.dropdown", dropdownButtonSelector, function () {
            $(this).removeClass("dropup");
        });
    };

    var upload = function (evt) {
        Upload.instance(evt.$target).trigger('upload');
    };

    module.export({
        init: init,
        actionUpload: upload,
        Upload: Upload,
        uploadByType: uploadByType,
        Preview: Preview,
        lockFormWhileUploading: lockFormWhileUploading,
        getFileUrl: getFileUrl,
        filterFileUrl: filterFileUrl
    });
});
