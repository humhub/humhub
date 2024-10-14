humhub.module('content.highlight', function (module, require, $) {
    const Widget = require('ui.widget').Widget;
    const event = require('event');
    const highlightWords = require('ui.additions').highlightWords;

    const layout = $('.layout-content-container').length
        ? $('.layout-content-container')
        : $('#layout-content');

    const init = function () {
        $(document).ready(() => highlight());

        event.on('humhub:modules:content:highlight:afterInit', () => highlight());
        layout.find('[data-ui-widget="ui.richtext.prosemirror.RichText"]')
            .on('afterRender', (obj) => highlight(obj.target));

        const wallStream = Widget.instance('[data-ui-widget="stream.wall.WallStream"]');
        if (wallStream) {
            wallStream.on('humhub:stream:afterAddEntries', () => highlight());
        }
    }

    function highlight(object) {
        if (typeof module.config.keyword === 'string') {
            if (typeof object === 'undefined') {
                object = layout;
            }
            highlightWords(object, module.config.keyword);
        }
    }

    module.export({
        init,
        initOnPjaxLoad: true,
    })
})
