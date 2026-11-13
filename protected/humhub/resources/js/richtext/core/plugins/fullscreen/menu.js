/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {icons, MenuItem} from "../../menu/menu";

// A toggle button: it tells whether the editor is in full screen (WCAG 4.1.2)
class FullScreenMenuItem extends MenuItem {
    render(view, renderOptions) {
        const dom = super.render(view, renderOptions);
        this.setPressed(this.options.isFullScreen());
        return dom;
    }

    // Menu wrappers give every item an `active` state, which drops `aria-pressed` while it is off
    setActiveItemState() {
        this.active = false;
        this.setPressed(this.options.isFullScreen());
    }

    setPressed(pressed) {
        if (this.dom) {
            this.dom.setAttribute('aria-pressed', pressed ? 'true' : 'false');
        }
    }
}

// In full screen the editor covers the page: Tab and Shift+Tab go round its toolbar and text, Escape leaves the
// full screen and gives the focus back to the button (WCAG 2.4.3, 2.1.2)
function onFullScreenKeydown(context, e) {
    if (e.isDefaultPrevented()) {
        return;
    }

    if (e.key === 'Escape') {
        if (leaveFullScreen(context)) {
            e.preventDefault();
        }
        return;
    }

    if (e.key !== 'Tab' || e.altKey || e.ctrlKey || e.metaKey) {
        return;
    }

    const controls = context.editor.$
        .find('button, a[href], input, select, textarea, [tabindex], [contenteditable="true"]')
        .filter((i, el) => el.getAttribute('tabindex') !== '-1' && !el.disabled && $(el).is(':visible'))
        .toArray();
    if (!controls.length) {
        return;
    }

    const first = controls[0];
    const last = controls[controls.length - 1];
    if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
    }
}

/**
 * Leaves the full screen and focuses its button, unless a list of suggestions is open (Escape closes that first).
 *
 * @returns {boolean} whether the editor left the full screen
 */
export function leaveFullScreen(context) {
    if (!context.editor.$.is('.fullscreen') || $('.humhub-richtext-provider:visible').length) {
        return false;
    }

    minimize(context);
    const button = context.fullScreenMenuItem && context.fullScreenMenuItem.dom;
    if (button && $(button).is(':visible')) {
        button.focus();
    } else {
        context.editor.view.focus();
    }
    return true;
}

function fullScreen(context) {
    return new FullScreenMenuItem({
        id: 'fullscreen',
        title: "Fullscreen",
        icon: icons.enlarge,
        isFullScreen: () => context.editor.$.is('.fullscreen'),
        sortOrder: 300,
        hideOnCollapse: true,
        run: () => {
            const $editor = context.editor.$;
            let $textarea = $editor.find('.ProseMirror-editor-source');

            if ($editor.is('.fullscreen')) {
                minimize(context);

                if ($textarea.length) {
                    const elem = $textarea.get(0);
                    const paddingTop = window.getComputedStyle(elem, null).getPropertyValue('padding-top');
                    const paddingTopValue = parseFloat(paddingTop.replace('px', ''));

                    $textarea.css({height: elem.scrollHeight + paddingTopValue});
                }
            } else {
                maximize(context);

                $editor.find('.ProseMirror-menubar-wrapper').css({height: $textarea.length ? 'auto' : '100%'});
                if ($textarea.length) {
                    $textarea.css({
                        height: 'calc(100% - ' + $editor.find('.ProseMirror-menubar').outerHeight() + 'px)',
                    });
                }
            }
        }
    });
}

export function minimize(context, menuItem) {
    let $editor = context.editor.$;

    if ($editor.is('.fullscreen')) {
        $('body').removeClass('modal-open');
        $editor.removeClass('fullscreen');
        $editor.off('keydown.fullscreen');
        $editor.find('.Prosemirror').blur();

        context.fullScreenMenuItem.switchIcon(icons.enlarge);
        context.fullScreenMenuItem.setPressed(false);
    }
}

export function maximize(context, menuItem) {
    let $editor = context.editor.$;

    if (!$editor.is('.fullscreen')) {
        // Fixes a bug in ios safari when displaying a position:fixed element with input focus...
        document.activeElement.blur();
        setTimeout(() => {
            context.editor.view.focus();
        }, 200);

        $('body').addClass('modal-open');
        $editor.addClass('fullscreen');
        $editor.off('keydown.fullscreen').on('keydown.fullscreen', (e) => onFullScreenKeydown(context, e));

        context.fullScreenMenuItem.switchIcon(icons.shrink);
        context.fullScreenMenuItem.setPressed(true);
    }
}

export function menu(context) {
    return [
        {
            id: 'fullScreen',
            group: 'resize',
            item: fullScreen(context)
        },
    ];
}
