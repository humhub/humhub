import { beforeEach, describe, expect, it, vi } from 'vitest';

// `oembed.load()` is called by the richtext editor's own oembed plugin and by the plugins of modules
// (e.g. a link preview) for the same text, so an url must not be requested twice.

globalThis.humhub.module('ui.status', function (module) {
    module.export({warn: () => {}});
});
globalThis.humhub.module('util', function (module) {
    module.export({});
});
await import('../../resources/js/humhub/humhub.oembed.js');

const oembed = globalThis.humhub.modules.oembed;
const client = globalThis.humhubStubs.client;
const status = globalThis.humhub.modules['ui.status'];

let responders;
let post;

const deferred = () => {
    let resolve;
    const promise = new Promise((res) => { resolve = res; });
    return {promise, resolve};
};

describe('oembed.load', () => {
    beforeEach(() => {
        oembed.config.loadUrl = '/oembed/load';
        oembed.text = (key) => key === 'brokenUrl' ? 'Cannot load {urls}' : key;
        responders = [];
        post = vi.spyOn(client, 'post').mockImplementation(() => {
            const request = deferred();
            responders.push(request);
            return request.promise;
        });
        vi.spyOn(status, 'warn');
    });

    it('loads the urls from the server once and then from the cache', async () => {
        const first = oembed.load(['https://a.example/1']);
        responders.at(-1).resolve({data: {'https://a.example/1': '<div>one</div>'}});
        expect(await first).toEqual({'https://a.example/1': '<div>one</div>'});

        expect(await oembed.load(['https://a.example/1'])).toEqual({'https://a.example/1': '<div>one</div>'});
        expect(post).toHaveBeenCalledTimes(1);
    });

    it('does not request an url again while its request is in flight', async () => {
        const first = oembed.load(['https://b.example/1']);
        const second = oembed.load(['https://b.example/1'], {silent: true});

        expect(post).toHaveBeenCalledTimes(1);

        responders.at(-1).resolve({data: {'https://b.example/1': '<div>two</div>'}});

        expect(await first).toEqual({'https://b.example/1': '<div>two</div>'});
        expect(await second).toEqual({'https://b.example/1': '<div>two</div>'});
    });

    it('requests only the urls that are not requested yet and waits for the others', async () => {
        const first = oembed.load(['https://c.example/1']);
        const second = oembed.load(['https://c.example/1', 'https://c.example/2']);

        expect(post).toHaveBeenCalledTimes(2);
        expect(post.mock.calls[1][1].data.urls).toEqual(['https://c.example/2']);

        // The second call is answered when both requests are
        responders[1].resolve({data: {'https://c.example/2': '<div>c2</div>'}});
        let secondDone = false;
        second.then(() => { secondDone = true; });
        await new Promise((resolve) => setTimeout(resolve, 0));
        expect(secondDone).toBe(false);

        responders[0].resolve({data: {'https://c.example/1': '<div>c1</div>'}});
        expect(await first).toEqual({'https://c.example/1': '<div>c1</div>'});
        expect(await second).toEqual({'https://c.example/1': '<div>c1</div>', 'https://c.example/2': '<div>c2</div>'});
    });

    it('does not share a request between the embed and the consent mode', () => {
        oembed.load(['https://d.example/1']);
        oembed.load(['https://d.example/1'], {consent: true});

        expect(post).toHaveBeenCalledTimes(2);
        expect(post.mock.calls[1][1].data.consent).toBe(1);
    });

    it('requests an url again after its request failed', async () => {
        const first = oembed.load(['https://e.example/1']);
        responders.at(-1).resolve(Promise.reject(new Error('network')));
        await expect(first).rejects.toThrow('network');

        oembed.load(['https://e.example/1']);
        expect(post).toHaveBeenCalledTimes(2);
    });

    it('leaves an unreachable url out and warns once for the call that requested it', async () => {
        const first = oembed.load(['https://f.example/1']);
        const second = oembed.load(['https://f.example/1'], {silent: true});

        responders.at(-1).resolve({data: {'https://f.example/1': null}});

        expect(await first).toEqual({});
        expect(await second).toEqual({});
        expect(status.warn).toHaveBeenCalledTimes(1);
    });
});
