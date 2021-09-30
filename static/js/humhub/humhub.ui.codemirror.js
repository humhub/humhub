/**
 * This module is used to initialize CodeMirror
 *
 * @namespace humhub.modules.ui.codemirror
 */
humhub.module('ui.codemirror', function(module, require, $) {
    var event = require('event');

    var init = function () {
        event.on('humhub:ready', function (evt) {
            if (typeof CodeMirror === 'undefined') {
                return;
            }
            $('textarea[data-codemirror]').each(function() {
                var codeMirrorInstance = CodeMirror.fromTextArea(this, {
                    mode: $(this).data('codemirror'),
                    lineNumbers: true,
                    extraKeys: {'Ctrl-Space': 'autocomplete'}
                });
                $(this).data('codemirror-instance', codeMirrorInstance);
            });
        });
    }

    module.export({
        init
    });
});