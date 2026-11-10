/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {Plugin, PluginKey} from 'prosemirror-state';
import {encode} from '../../humhub-bridge';
import {applySkinTone, getDatabase} from './data';
import {translate} from '../../menu/menu-helper';
import {setCurrentSuggestion, suggestionListId} from '../../util/suggestions';

const MAX_RESULTS = 8;

// `:` and at least two letters at the start of a word, e.g. `:smi`
const QUERY_REGEX = /(?:^|[\s([{"'])(:([\p{L}\p{N}_+-]{2,}))$/u;

export const autocompleteKey = new PluginKey('emojiAutocomplete');

const instances = new WeakMap();

/**
 * Inline autocomplete: typing `:smi` lists matching emojis (shortcodes and names in the user's language), Enter or
 * Tab replaces the query by the selected emoji.
 */
export function autocompletePlugin() {
    return new Plugin({
        key: autocompleteKey,
        view: (view) => {
            const autocomplete = new Autocomplete(view);
            instances.set(view, autocomplete);
            return autocomplete;
        },
    });
}

/**
 * The keys of the open list. A keymap, as plugin keymaps run before the editor's own (e.g. Enter).
 */
export function autocompleteKeymap() {
    const handle = (key) => (state, dispatch, view) => {
        const autocomplete = view && instances.get(view);
        return autocomplete ? autocomplete.handleKey(key) : false;
    };

    return ['ArrowDown', 'ArrowUp', 'Enter', 'Tab', 'Escape']
        .reduce((keymap, key) => Object.assign(keymap, {[key]: handle(key)}), {});
}

/**
 * @returns {{from: number, to: number, query: string}|null} the query before the cursor
 */
export function findQuery(state) {
    const {selection} = state;
    if (!selection.empty) {
        return null;
    }

    const $from = selection.$from;
    if (!$from.parent.isTextblock || $from.parent.type.spec.code
        || $from.marks().some((mark) => mark.type.spec.isCode)) {
        return null;
    }

    const textBefore = $from.parent.textBetween(Math.max(0, $from.parentOffset - 50), $from.parentOffset, null, '￼');
    const match = textBefore.match(QUERY_REGEX);
    if (!match) {
        return null;
    }

    return {
        from: $from.pos - match[1].length,
        to: $from.pos,
        query: match[2],
    };
}

/**
 * Emojis with a shortcode starting with the query first (shorter ones before longer ones), then names starting with
 * it, then the rest in the order of the database. The label is the matching shortcode, else the name.
 */
export function rankEmojis(emojis, query) {
    query = query.toLowerCase();

    return emojis
        .map((emoji, index) => {
            const shortcode = (emoji.shortcodes || [])
                .filter((code) => code.toLowerCase().startsWith(query))
                .sort((a, b) => a.length - b.length)[0];
            const rank = shortcode ? 0 : (String(emoji.annotation).toLowerCase().startsWith(query) ? 1 : 2);
            const label = shortcode || (emoji.shortcodes && emoji.shortcodes[0]);

            return {
                emoji,
                index,
                rank,
                length: shortcode ? shortcode.length : 0,
                label: label ? ':' + label + ':' : emoji.annotation,
            };
        })
        .sort((a, b) => a.rank - b.rank || a.length - b.length || a.index - b.index);
}

export class Autocomplete {
    constructor(view) {
        this.view = view;
        this.listId = suggestionListId();
        this.query = null;
        this.dismissed = null;
        this.items = [];
        this.index = 0;
        this.$container = null;
        this.request = 0;
    }

    update(view) {
        this.view = view;
        const query = findQuery(view.state);

        if (!query || (this.dismissed && this.dismissed.from === query.from && this.dismissed.query === query.query)) {
            this.close();
            return;
        }
        this.dismissed = null;

        const changed = !this.query || this.query.query !== query.query;
        this.query = query;

        if (changed) {
            this.search(query.query);
        } else if (this.isOpen()) {
            this.render();
        }
    }

    search(text) {
        const request = ++this.request;
        const database = getDatabase();

        Promise.all([database.getEmojiBySearchQuery(text), database.getPreferredSkinTone()])
            .then(([emojis, skinTone]) => {
                if (request !== this.request || !this.query) {
                    return;
                }
                this.items = rankEmojis(emojis, text).slice(0, MAX_RESULTS).map(({emoji, label}) => ({
                    emoji: applySkinTone(emoji, skinTone),
                    label,
                }));
                this.index = 0;
                this.render();
            })
            .catch((e) => {
                console.error('Emoji search failed', e);
                this.close();
            });
    }

    render() {
        if (!this.items.length) {
            this.removeContainer();
            return;
        }

        if (!this.$container) {
            this.$container = $('<div class="atwho-view humhub-richtext-provider humhub-emoji-autocomplete">')
                .data('provider', {reset: () => this.close()})
                .appendTo('body');
        }

        const $list = $('<ul role="listbox">').attr({id: this.listId, 'aria-label': translate(this.view, 'Suggestions')});
        this.items.forEach((item, index) => {
            $('<li role="option">')
                .attr('id', this.listId + '-' + index)
                .toggleClass('cur', index === this.index)
                .attr('aria-selected', index === this.index ? 'true' : 'false')
                .html('<span class="humhub-emoji-autocomplete-emoji">' + encode(item.emoji) + '</span> ' + encode(item.label))
                .on('mousedown', (evt) => {
                    evt.preventDefault();
                    this.index = index;
                    this.select();
                })
                .appendTo($list);
        });
        this.$container.empty().append($list);
        setCurrentSuggestion(this.view, this.listId, this.listId + '-' + this.index);

        const coords = this.view.coordsAtPos(this.query.from);
        this.$container.css({
            top: coords.bottom + window.scrollY + 2,
            left: coords.left + window.scrollX,
        }).show();
    }

    isOpen() {
        return !!(this.$container && this.items.length);
    }

    handleKey(key) {
        if (!this.isOpen()) {
            return false;
        }

        switch (key) {
            case 'ArrowDown':
                this.move(1);
                return true;
            case 'ArrowUp':
                this.move(-1);
                return true;
            case 'Enter':
            case 'Tab':
                this.select();
                return true;
            case 'Escape':
                // Keeps the list closed until the query changes
                this.dismissed = this.query;
                this.close();
                return true;
        }

        return false;
    }

    move(step) {
        this.index = (this.index + step + this.items.length) % this.items.length;
        this.render();
    }

    select() {
        const item = this.items[this.index];
        const query = this.query;
        this.close();

        if (item && query) {
            this.view.dispatch(this.view.state.tr.insertText(item.emoji, query.from, query.to).scrollIntoView());
            this.view.focus();
        }
    }

    close() {
        this.request++;
        this.query = null;
        this.items = [];
        this.removeContainer();
    }

    removeContainer() {
        if (this.$container) {
            this.$container.remove();
            this.$container = null;
        }
        setCurrentSuggestion(this.view, null, null);
    }

    destroy() {
        this.close();
        instances.delete(this.view);
    }
}
