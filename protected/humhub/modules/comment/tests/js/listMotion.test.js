import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { growIn, shrinkOut } from '../../vue/listMotion.js';

/**
 * jsdom neither lays out nor animates: the area's heights and `Element.animate()` are stand-ins,
 * the keyframes are what the browser would play.
 */
describe('growIn and shrinkOut ("Show more", "Show less")', () => {
    let area;
    let feed;
    let height;
    let animations;

    const addEntries = (ids, side) => {
        const html = ids.map((id) => `<hr class="comment-separator"><div id="comment_${id}" role="article"></div>`).join('');
        feed.insertAdjacentHTML(side === 'start' ? 'afterbegin' : 'beforeend', html);
    };
    const animationOf = (element) => animations.find((animation) => animation.element === element);

    beforeEach(() => {
        document.body.innerHTML = '<div class="comment-container"><div class="comment"></div></div>';
        area = document.querySelector('.comment-container');
        feed = area.querySelector('.comment');
        addEntries([5, 6], 'end');

        height = 200;
        vi.spyOn(area, 'getBoundingClientRect').mockImplementation(() => ({ height }));

        animations = [];
        Element.prototype.animate = vi.fn(function (keyframes, options) {
            const animation = { element: this, keyframes, options, finish: vi.fn(), onfinish: null, oncancel: null };
            animations.push(animation);
            return animation;
        });
    });

    afterEach(() => {
        delete Element.prototype.animate;
        document.documentElement.removeAttribute('data-hh-motion');
        vi.restoreAllMocks();
    });

    it('fades the loaded comments in and opens the list above them by the height they add', () => {
        const grow = growIn(feed);
        addEntries([3, 4], 'start');
        height = 350;
        grow([3, 4], 'start');

        const first = document.getElementById('comment_3');
        expect(animationOf(document.getElementById('comment_4')).keyframes).toEqual([{ opacity: 0 }, { opacity: 1 }]);
        expect(animations.filter((animation) => animation.element === first).map((animation) => animation.keyframes)).toContainEqual(
            [{ marginTop: '-150px' }, { marginTop: '0px' }],
        );
        expect(feed.style.overflow).toBe('hidden');

        animations.find((animation) => animation.keyframes[0].marginTop).onfinish();
        expect(feed.style.overflow).toBe('');
    });

    it('opens the list below the comments added at its end', () => {
        const grow = growIn(feed);
        addEntries([7, 8], 'end');
        height = 260;
        grow([7, 8], 'end');

        const last = document.getElementById('comment_8');
        expect(animations.filter((animation) => animation.element === last).map((animation) => animation.keyframes)).toContainEqual(
            [{ marginBottom: '-60px' }, { marginBottom: '0px' }],
        );
    });

    it('only fades comments added between others', () => {
        const grow = growIn(feed);
        document.getElementById('comment_6').insertAdjacentHTML('beforebegin', '<hr class="comment-separator"><div id="comment_7" role="article"></div>');
        height = 260;
        grow([7], 'end');

        expect(animations.map((animation) => animation.keyframes)).toEqual([[{ opacity: 0 }, { opacity: 1 }]]);
        expect(feed.style.overflow).toBe('');
    });

    it('does not move anything with reduced motion', () => {
        document.documentElement.setAttribute('data-hh-motion', 'reduce');
        const grow = growIn(feed);
        addEntries([3, 4], 'start');
        height = 350;
        grow([3, 4], 'start');

        expect(Element.prototype.animate).not.toHaveBeenCalled();
    });

    it('pulls the folded comments up under the top of the list and fades them, until they can go', async () => {
        addEntries([3, 4], 'start');
        const tops = { comment_3: 0, comment_4: 80, comment_5: 150 };
        Object.keys(tops).forEach((id) => vi.spyOn(document.getElementById(id), 'getBoundingClientRect').mockReturnValue({ top: tops[id] }));

        const folded = shrinkOut(feed, [3, 4]);

        const first = document.getElementById('comment_3');
        const pull = animations.find((animation) => animation.element === first && animation.keyframes[0].marginTop);
        expect(pull.keyframes).toEqual([{ marginTop: '0px' }, { marginTop: '-150px' }]);
        expect(animationOf(document.getElementById('comment_4')).keyframes).toEqual([{ opacity: 1 }, { opacity: 0 }]);
        expect(feed.style.overflow).toBe('hidden');

        let settled = false;
        folded.then(() => { settled = true; });
        await Promise.resolve();
        expect(settled).toBe(false);

        pull.onfinish();
        await folded;
        expect(feed.style.overflow).toBe('');
    });

    it('folds at once with reduced motion or not from the start of the list', async () => {
        addEntries([3, 4], 'start');
        await expect(shrinkOut(feed, [4])).resolves.toBeUndefined();
        document.documentElement.setAttribute('data-hh-motion', 'reduce');
        await expect(shrinkOut(feed, [3, 4])).resolves.toBeUndefined();

        expect(Element.prototype.animate).not.toHaveBeenCalled();
    });

    it('does nothing where the browser cannot animate', () => {
        delete Element.prototype.animate;
        const grow = growIn(feed);
        addEntries([3], 'start');

        expect(() => grow([3], 'start')).not.toThrow();
    });
});
