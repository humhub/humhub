import { describe, expect, it } from 'vitest';
import { highlightParts } from '../../modules/marketplace/vue/components/highlight.js';
import { emphasis, strongParts } from '../../modules/marketplace/vue/components/messages.js';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const marked = (parts) => parts.filter((part) => part.mark).map((part) => part.text);
const joined = (parts) => parts.map((part) => part.text).join('');

describe('highlightParts', () => {
    it('marks every match, keeping the source text', () => {
        const parts = highlightParts('Meet, meet and MEET', 'meet');

        expect(marked(parts)).toEqual(['Meet', 'meet', 'MEET']);
        expect(joined(parts)).toBe('Meet, meet and MEET');
    });

    it('ignores accents on either side', () => {
        expect(marked(highlightParts('Café Crème', 'cafe'))).toEqual(['Café']);
        expect(marked(highlightParts('Cafe creme', 'crème'))).toEqual(['creme']);
        expect(marked(highlightParts('Umfräge', 'umfrage'))).toEqual(['Umfräge']);
    });

    it('keeps the source characters of a decomposed text', () => {
        const decomposed = 'Café au lait';
        const parts = highlightParts(decomposed, 'café');

        expect(marked(parts)).toEqual(['Café']);
        expect(joined(parts)).toBe(decomposed);
    });

    it('matches across spaces and trims the query', () => {
        expect(marked(highlightParts('Push Notifications (Firebase)', '  push notif '))).toEqual(['Push Notif']);
    });

    it('returns the text unmarked without a query or a match, nothing for no text', () => {
        expect(highlightParts('Calendar', '')).toEqual([{ text: 'Calendar', mark: false }]);
        expect(highlightParts('Calendar', '   ')).toEqual([{ text: 'Calendar', mark: false }]);
        expect(highlightParts('Calendar', 'wiki')).toEqual([{ text: 'Calendar', mark: false }]);
        expect(highlightParts(null, 'x')).toEqual([]);
    });

    it('treats markup and regular expression characters as text', () => {
        expect(highlightParts('a <b>c</b> (d.*)', '<b>')).toEqual([
            { text: 'a ', mark: false },
            { text: '<b>', mark: true },
            { text: 'c</b> (d.*)', mark: false },
        ]);
        expect(marked(highlightParts('a (d.*)', '(d.*)'))).toEqual(['(d.*)']);
    });
});

describe('strongParts', () => {
    it('splits a translated text at its emphasised placeholders', () => {
        const translated = globalThis.humhub.modules.vue.i18n.t('MarketplaceModule.base', 'To install {moduleName}, enter {what}.', emphasis('moduleName', 'what'));

        expect(strongParts(translated, { moduleName: '<b>X</b>', what: 'a key' })).toEqual([
            { text: 'To install ', strong: false },
            { text: '<b>X</b>', strong: true },
            { text: ', enter ', strong: false },
            { text: 'a key', strong: true },
            { text: '.', strong: false },
        ]);
    });

    it('drops empty parts', () => {
        expect(strongParts(`${emphasis('a').a} done`, { a: 'X' })).toEqual([{ text: 'X', strong: true }, { text: ' done', strong: false }]);
    });
});
