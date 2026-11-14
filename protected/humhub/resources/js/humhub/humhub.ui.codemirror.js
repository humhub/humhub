/**
 * This module is used to initialize CodeMirror
 *
 * @namespace humhub.modules.ui.codemirror
 */
humhub.module('ui.codemirror', function(module, require, $) {
    var event = require('event');

    var init = function () {
        event.on('humhub:ready', function (evt) {
            var initCodeMirrorInterval = setInterval(function () {

                if(!$('textarea[data-codemirror]').length) {
                    clearInterval(initCodeMirrorInterval);
                }

                if (typeof CodeMirror !== 'undefined') {
                    $('textarea[data-codemirror]').each(function() {
                        if(typeof $(this).data('codemirror-instance') === 'object') {
                            $(this).data('codemirror-instance').toTextArea();
                        }

                        // CodeMirror types into its own textarea: it takes the name and description
                        // of the original one (WCAG 4.1.2), and the label focuses it
                        var labels = this.labels ? $(this.labels) : $();
                        var name = labels.length ? labels.first().text().trim() : this.getAttribute('aria-label');

                        var codeMirrorInstance = CodeMirror.fromTextArea(this, {
                            mode: $(this).data('codemirror'),
                            screenReaderLabel: name || undefined,
                            lineNumbers: true,
                            autoRefresh: true,
                            extraKeys: {
                                'Ctrl-Space': 'autocomplete',
                                'Ctrl-F': 'find',
                                'Cmd-F': 'find',
                                'Ctrl-G': 'findNext',
                                'Cmd-G': 'findNext',
                                'Shift-Ctrl-G': 'findPrev',
                                'Shift-Cmd-G': 'findPrev',
                                'Shift-Ctrl-F': 'replace',
                                'Shift-Cmd-F': 'replace',
                                'Alt-F': 'replace'
                            }
                        });
                        $(this).data('codemirror-instance', codeMirrorInstance);

                        var describedBy = this.getAttribute('aria-describedby');
                        if (describedBy) {
                            codeMirrorInstance.getInputField().setAttribute('aria-describedby', describedBy);
                        }
                        labels.off('click.codemirror').on('click.codemirror', function (evt) {
                            evt.preventDefault();
                            codeMirrorInstance.focus();
                        });
                    });

                    clearInterval(initCodeMirrorInterval);
                }

            }, 200);
        });
    }

    module.export({
        init
    });
});
