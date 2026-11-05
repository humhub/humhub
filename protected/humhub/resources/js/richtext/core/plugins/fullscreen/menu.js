/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {icons, MenuItem} from "../../menu/menu";

function fullScreen(context) {
    return new MenuItem({
        id: 'fullscreen',
        title: "Fullscreen",
        icon: icons.enlarge,
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
        $editor.find('.Prosemirror').blur();

        context.fullScreenMenuItem.switchIcon(icons.enlarge);
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

        context.fullScreenMenuItem.switchIcon(icons.shrink);
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
