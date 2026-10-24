import { afterEach, describe, expect, it } from 'vitest';

await import('../../resources/js/humhub/humhub.url.js');

const urlModule = globalThis.humhub.modules.url;

describe('humhub.url', () => {
    afterEach(() => {
        delete urlModule.config.template;
        globalThis.humhubStubs.logCalls.error.length = 0;
    });

    it('fills a pretty-URL template and appends params', () => {
        urlModule.config.template = '/__route__';
        expect(urlModule.to('/like/like/like', { recordId: 7 })).toBe('/like/like/like?recordId=7');
    });

    it('encodes the route and appends params to a query-string template', () => {
        urlModule.config.template = '/index.php?r=__route__';
        expect(urlModule.to('like/like/unlike', { recordId: 7 })).toBe('/index.php?r=like%2Flike%2Funlike&recordId=7');
    });

    it('omits the trailing separator when there are no params', () => {
        urlModule.config.template = '/__route__';
        expect(urlModule.to('/like/like/like')).toBe('/like/like/like');
    });

    it('falls back to a root-relative URL and logs once when no template is configured', () => {
        delete urlModule.config.template;
        globalThis.humhubStubs.logCalls.error.length = 0;

        expect(urlModule.to('/a/b', { x: 1 })).toBe('/a/b?x=1');
        expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
    });
});
