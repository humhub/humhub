import { beforeEach, describe, expect, it } from 'vitest';
import { toImage } from '../../vue/picker/images.js';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

describe('picker images', () => {
    beforeEach(() => {
        globalThis.humhubStubs.logCalls.warn.length = 0;
    });

    it('reads nothing as no image', () => {
        expect(toImage(null)).toBeNull();
        expect(toImage(undefined)).toBeNull();
        expect(toImage('')).toBeNull();
        expect(toImage('   ')).toBeNull();
    });

    it('reads paths and URLs as url images', () => {
        expect(toImage('/uploads/a.jpg')).toEqual({ type: 'url', url: '/uploads/a.jpg' });
        expect(toImage('https://x.test/a.jpg')).toEqual({ type: 'url', url: 'https://x.test/a.jpg' });
    });

    it('reads #colors as color images', () => {
        expect(toImage('#ff0000')).toEqual({ type: 'color', color: '#ff0000' });
    });

    it('reads ti-/fa- names and bare words as icons', () => {
        expect(toImage('ti-star')).toEqual({ type: 'icon', name: 'star' });
        expect(toImage('fa-cog')).toEqual({ type: 'icon', name: 'fa-cog' });
        expect(toImage('users-group')).toEqual({ type: 'icon', name: 'users-group' });
    });

    it('refuses HTML and warns', () => {
        expect(toImage('<img src="/a.jpg">')).toBeNull();
        expect(globalThis.humhubStubs.logCalls.warn).toHaveLength(1);
    });

    it('takes descriptions as they are, and JSON strings of them', () => {
        const space = { type: 'space', name: 'Marketing', color: '#6fdbe8', imageUrl: null };
        expect(toImage(space)).toEqual(space);
        expect(toImage(JSON.stringify(space))).toEqual(space);
    });

    it('drops descriptions without a type', () => {
        expect(toImage({ url: '/a.jpg' })).toBeNull();
    });
});
