import {Plugin, PluginKey} from "prosemirror-state";

export const sourcePluginKey = new PluginKey('source');

export const EDIT_MODE_SOURCE = 'source';
export const EDIT_MODE_RICHTEXT = 'richtext';

export function isSourceMode(state) {
    return sourcePluginKey.getState(state) === EDIT_MODE_SOURCE;
}

export function sourcePlugin(context) {
    return new Plugin({
        key: sourcePluginKey,
        state: {
            init(config, state) {
                return EDIT_MODE_RICHTEXT;
            },
            apply(tr, prevPluginState, oldState, newState) {
                return tr.getMeta(sourcePluginKey) || prevPluginState;
            }
        },
    })
}

const textareaHandler = (event, context) => {
    const isFullscreen = context.editor.$.is('.fullscreen');

    if (!isFullscreen) {
        setTimeout(() => {
            const elem = event.target;
            const paddingTop = window.getComputedStyle(elem, null).getPropertyValue('padding-top');
            const paddingTopValue = parseFloat(paddingTop.replace('px', ''));

            // Resetting height to 'auto' collapses the textarea, and the
            // browser re-scrolls to keep the focused caret in view - on a
            // scrolled page this snapped to the top on every keystroke.
            // Save/restore the scroll position around the resize, forcing
            // scroll-behavior to 'auto' during the restore so a page-wide
            // `scroll-behavior: smooth` doesn't turn it into a visible slide
            // back down. `.style.height` (not `.style.cssText`) also avoids
            // wiping other inline styles (e.g. `width`) set on this element.
            const scroller = document.scrollingElement || document.documentElement;
            const scrollTop = scroller.scrollTop;
            const prevScrollBehavior = scroller.style.scrollBehavior;

            elem.style.height = 'auto';
            elem.style.height = (elem.scrollHeight + paddingTopValue) + 'px';

            scroller.style.scrollBehavior = 'auto';
            scroller.scrollTop = scrollTop;
            scroller.style.scrollBehavior = prevScrollBehavior;
        }, 0);
    }
};

export function switchToSourceMode(context, focus = true) {
    const $editor = context.editor.$;
    const $wrapper = $editor.find('.ProseMirror-menubar-wrapper');
    const $menubar = $editor.find('.ProseMirror-menubar');
    const $stage = $editor.find('.ProseMirror');
    let $textarea = $editor.find('textarea');

    if ($editor.is('.fullscreen')) {
        $wrapper.css({ height: 'auto' });
    }

    if (!$textarea.length) {
        $textarea = $('<textarea class="ProseMirror-editor-source"></textarea>');
        $editor.append($textarea);
        context.$source = $textarea;
    }

    $textarea.on('keydown', (event) => textareaHandler(event, context));

    $textarea.css({
        height: $editor.is('.fullscreen') ? 'calc(100% - ' + $menubar.outerHeight() + 'px)' : $stage.outerHeight(),
        width: '100%',
    });

    $stage.addClass('d-none');
    $textarea.val(context.editor.serialize());

    if (focus) {
        context.editor.focus();
    }

    return EDIT_MODE_SOURCE;
}

export function switchToRichtextMode(context) {
    const $editor = context.editor.$;
    const $wrapper = $editor.find('.ProseMirror-menubar-wrapper');
    const $stage = $editor.find('.ProseMirror');
    let $textarea = $editor.find('textarea');

    if ($editor.is('.fullscreen')) {
        $wrapper.css({ height: '100%' });
    }

    context.editor.init($textarea.val());
    $textarea.off('keydown', (event) => textareaHandler(event, context));
    $textarea.remove();

    $stage.removeClass('d-none');
    context.menu.update();
    context.editor.focus();

    return EDIT_MODE_RICHTEXT;
}
