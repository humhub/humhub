/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import Picker from 'emoji-picker-element/picker';
import {isSmallView} from '../../humhub-bridge';
import {getPickerI18n, getPickerOptions, getPickerStyle} from './data';

let current = null;

/**
 * Opens the emoji picker below (or above) the anchor element. Also usable outside of the editor, e.g. by modules:
 * `prosemirror.openEmojiPicker(button, {onSelect: (emoji) => ...})`
 *
 * @param {HTMLElement} anchor
 * @param {{onSelect: function(string), onClose: function()|undefined, translate: function(string)|undefined}} options
 * @returns {{close: function()}}
 */
export function openEmojiPicker(anchor, options = {}) {
    if (current) {
        current.close();
    }

    const container = document.createElement('div');
    container.className = 'humhub-richtext-provider humhub-emoji-picker';

    const picker = new Picker(Object.assign(getPickerOptions(), {
        i18n: getPickerI18n(options.translate),
    }));
    const style = getPickerStyle();
    Object.keys(style).forEach((name) => picker.style.setProperty(name, style[name]));
    container.appendChild(picker);

    let closed = false;
    const instance = {
        close(restoreFocus = false) {
            if (closed) {
                return;
            }
            closed = true;
            current = null;
            container.remove();
            document.removeEventListener('keydown', onKeyDown, true);
            if (options.onClose) {
                options.onClose(restoreFocus);
            }
        },
        // Called by the outside-click handler for `.humhub-richtext-provider` (richtext/index.js)
        reset() {
            instance.close();
        },
    };

    const onKeyDown = (evt) => {
        if (evt.key === 'Escape') {
            evt.preventDefault();
            evt.stopPropagation();
            instance.close(true);
        }
    };

    picker.addEventListener('emoji-click', (evt) => {
        const emoji = evt.detail.unicode;
        instance.close(true);
        if (emoji && options.onSelect) {
            options.onSelect(emoji);
        }
    });

    document.addEventListener('keydown', onKeyDown, true);
    document.body.appendChild(container);
    $(container).data('provider', instance);
    position(container, anchor);

    // The search field gets the focus as soon as the picker has rendered
    requestAnimationFrame(() => {
        const search = picker.shadowRoot && picker.shadowRoot.querySelector('input.search');
        if (search) {
            search.focus();
        }
    });

    current = instance;
    return instance;
}

function position(container, anchor) {
    if (isSmallView()) {
        container.classList.add('humhub-emoji-picker-small');
        return;
    }

    const rect = anchor.getBoundingClientRect();
    const width = container.offsetWidth;
    const height = container.offsetHeight;
    const viewportWidth = document.documentElement.clientWidth;
    const viewportHeight = document.documentElement.clientHeight;
    const gap = 4;

    let top = rect.bottom + gap;
    if (top + height > viewportHeight && rect.top - gap - height >= 0) {
        top = rect.top - gap - height;
    }

    const left = Math.max(gap, Math.min(rect.left, viewportWidth - width - gap));

    container.style.top = (top + window.scrollY) + 'px';
    container.style.left = (left + window.scrollX) + 'px';
}
