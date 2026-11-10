import { describe, it, expect, afterEach } from 'vitest';

import '../../testEditor.js';
import MentionProvider from '../../../../../resources/js/richtext/core/plugins/mention/provider';
import {Autocomplete} from '../../../../../resources/js/richtext/core/plugins/emoji/autocomplete';

/**
 * A list of suggestions is a named listbox; the editor points to its current option, so a screen reader reads it while
 * the focus stays in the text
 */
describe("Plugin:suggestions", () => {
    const fakeView = () => ({
        dom: $('<div contenteditable="true">').appendTo('body')[0],
        _props: {translate: (text) => (text === 'Suggestions' ? 'Vorschläge' : text)},
        coordsAtPos: () => ({bottom: 0, left: 0}),
    });

    afterEach(() => {
        $('body').empty();
    });

    it("lists the mentions as options and points the editor to the current one", () => {
        const view = fakeView();
        const provider = new MentionProvider({minInput: 0});
        provider.state = {view};
        provider.$node = $('<span>').appendTo('body');
        provider.result = [{name: 'Alice', image: '<span><a href="/u/alice/"><img src="a.jpg" alt="Profile picture of Alice"></a></span>'}, {name: 'Bob'}];
        provider.update();

        const $listbox = provider.$container.find('[role="listbox"]');
        const $options = $listbox.children('[role="option"]');
        expect($listbox.attr('aria-label')).toBe('Vorschläge');
        expect($options.length).toBe(2);
        expect($options.eq(0).find('a').length).toBe(0);
        expect($options.eq(0).find('img').attr('alt')).toBe('');
        expect($options.eq(0).attr('aria-selected')).toBe('true');
        expect(view.dom.getAttribute('aria-activedescendant')).toBe($options.eq(0).attr('id'));
        expect(view.dom.getAttribute('aria-controls')).toBe($listbox.attr('id'));

        provider.next();
        expect($options.eq(0).attr('aria-selected')).toBe('false');
        expect($options.eq(1).attr('aria-selected')).toBe('true');
        expect(view.dom.getAttribute('aria-activedescendant')).toBe($options.eq(1).attr('id'));

        provider.reset();
        expect(view.dom.hasAttribute('aria-activedescendant')).toBe(false);
        expect(view.dom.hasAttribute('aria-controls')).toBe(false);
    });

    it("lists the emojis as options of the listbox itself and points the editor to the current one", () => {
        const view = fakeView();
        const autocomplete = new Autocomplete(view);
        autocomplete.query = {from: 1, to: 4, query: 'gr'};
        autocomplete.items = [{emoji: '😀', label: 'grinning face'}, {emoji: '😁', label: 'beaming face'}];
        autocomplete.render();

        const $listbox = autocomplete.$container.find('[role="listbox"]');
        const $options = $listbox.children('[role="option"]');
        expect($listbox.prop('tagName')).toBe('UL');
        expect($listbox.attr('aria-label')).toBe('Vorschläge');
        expect(view.dom.getAttribute('aria-activedescendant')).toBe($options.eq(0).attr('id'));

        autocomplete.move(1);
        expect(view.dom.getAttribute('aria-activedescendant')).toBe(autocomplete.$container.find('[role="option"]').eq(1).attr('id'));

        autocomplete.close();
        expect(view.dom.hasAttribute('aria-activedescendant')).toBe(false);
    });
});
