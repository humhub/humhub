import { describe, expect, it } from 'vitest';

await import('../../resources/js/humhub/humhub.util.js');

const { decodeEntities } = globalThis.humhub.modules.util.string;

describe('util.string.decodeEntities()', () => {
    it('decodes the entities Html::encode() writes, &nbsp; and numeric ones', () => {
        expect(decodeEntities('Tom &amp; Jerry&#039;s &quot;R&amp;D&quot; &lt;draft&gt; &apos;x&#39;'))
            .toBe('Tom & Jerry\'s "R&D" <draft> \'x\'');
        expect(decodeEntities('a&nbsp;b &#x2014; &#X2014; &#8212;')).toBe('a b — — —');
    });

    it('decodes in a single pass and parses nothing as markup', () => {
        expect(decodeEntities('&amp;lt;b&amp;gt;')).toBe('&lt;b&gt;');
        expect(decodeEntities('<img src=x onerror=alert(1)> &amp; more')).toBe('<img src=x onerror=alert(1)> & more');
    });

    it('keeps what is no entity it knows', () => {
        expect(decodeEntities('&copy; &#0; &#x110000; & &amp')).toBe('&copy; &#0; &#x110000; & &amp');
    });

    it('returns other values as they are', () => {
        const details = { message: '&amp;' };

        expect(decodeEntities(details)).toBe(details);
        expect(decodeEntities(undefined)).toBeUndefined();
        expect(decodeEntities(42)).toBe(42);
    });
});
