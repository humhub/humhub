/**
 * Motion of "Show more" and "Show less" in a comment list: the comments that appear grow in, the ones
 * folded away shrink out, so what is below the list follows smoothly instead of jumping. Nothing moves
 * with reduced motion (docs/ui/principles.md).
 */

const GROW_MS = 200;
const GROW_MAX_MS = 400;

// The comments' list (feed) -> its running animation
const running = new WeakMap();

// The user's accessibility option (`<html data-hh-motion>`), else the system setting
const reducedMotion = () => {
    const motion = document.documentElement.getAttribute('data-hh-motion');
    return motion ? motion === 'reduce' : typeof window.matchMedia === 'function'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
};

// The time for a change of `height` px: a long batch takes a little longer, so it does not read as a jump
const durationFor = (height) => Math.min(GROW_MAX_MS, GROW_MS + height / 10);

const finishRunning = (feed) => {
    const previous = running.get(feed);
    if (previous) {
        previous.finish();
    }
};

/**
 * Lets the comments that "Show more" added grow in: the list opens over their height from the side they
 * were added on, so the older comments slide down from under the button and the newer ones unfold
 * downwards, while they fade in.
 *
 * Measures the comment area before the comments are rendered.
 *
 * @param {HTMLElement|undefined} feed the list the comments are added to
 * @returns {function(number[], string): void} call it once they are rendered, with their ids and the
 * side they were added on (`start`, `end`)
 */
export function growIn(feed) {
    // The `.comment-container`: its padding keeps the list's margins in, its height is all that moves
    const area = feed ? feed.parentElement : null;
    if (!area || typeof feed.animate !== 'function' || reducedMotion()) {
        return () => {};
    }

    finishRunning(feed);
    const before = area.getBoundingClientRect().height;

    return (ids, side) => {
        const entries = ids.map((id) => document.getElementById('comment_' + id)).filter((entry) => entry && feed.contains(entry));
        if (!entries.length) {
            return;
        }

        entries.forEach((entry) => entry.animate([{ opacity: 0 }, { opacity: 1 }], { duration: GROW_MS, easing: 'ease' }));

        // Opened at an end of the list only: a negative margin in between would draw the comments
        // over each other
        const articles = feed.querySelectorAll(':scope > [role="article"]');
        const edge = side === 'start' ? entries[0] : entries[entries.length - 1];
        const delta = area.getBoundingClientRect().height - before;
        if (delta <= 0 || edge !== (side === 'start' ? articles[0] : articles[articles.length - 1])) {
            return;
        }

        // The edge entry starts pulled in by the height that was added, so nothing around the area
        // has moved yet; the list clips what is still pulled in
        const property = side === 'start' ? 'marginTop' : 'marginBottom';
        const rest = parseFloat(getComputedStyle(edge)[property]) || 0;
        const frames = (height) => [{ [property]: `${rest - height}px` }, { [property]: `${rest}px` }];
        let added = delta;
        feed.style.overflow = 'hidden';
        const motion = edge.animate(frames(added), { duration: durationFor(delta), easing: 'ease-out' });
        running.set(feed, motion);

        // What renders a moment later (the like links, a formatted text) is added to the height, before
        // it is painted
        const resized = typeof ResizeObserver === 'function' ? new ResizeObserver(() => {
            const pulled = rest - (parseFloat(getComputedStyle(edge)[property]) || 0);
            const now = area.getBoundingClientRect().height - before + pulled;
            if (Math.abs(now - added) >= 1 && motion.effect && typeof motion.effect.setKeyframes === 'function') {
                added = now;
                motion.effect.setKeyframes(frames(added));
            }
        }) : null;
        if (resized) {
            resized.observe(feed);
        }

        const done = () => {
            if (resized) {
                resized.disconnect();
            }
            if (running.get(feed) === motion) {
                running.delete(feed);
                feed.style.overflow = '';
            }
        };
        motion.onfinish = done;
        motion.oncancel = done;
    };
}

/**
 * Lets the comments at the start of the list that "Show less" folds away shrink out: they slide up under
 * the top of the list and fade, while the rest of the list and what is below it move up.
 *
 * @param {HTMLElement|undefined} feed the list
 * @param {number[]} ids the comments to fold away, the first ones of the list
 * @returns {Promise<void>} resolves when they can be removed; they stay pulled in until then
 */
export function shrinkOut(feed, ids) {
    const area = feed ? feed.parentElement : null;
    if (!area || typeof feed.animate !== 'function' || reducedMotion()) {
        return Promise.resolve();
    }

    finishRunning(feed);
    const entries = ids.map((id) => document.getElementById('comment_' + id)).filter((entry) => entry && feed.contains(entry));
    const articles = feed.querySelectorAll(':scope > [role="article"]');
    const kept = articles[entries.length];
    if (!entries.length || entries[0] !== articles[0] || !kept) {
        return Promise.resolve();
    }

    const first = entries[0];
    const height = kept.getBoundingClientRect().top - first.getBoundingClientRect().top;
    const rest = parseFloat(getComputedStyle(first).marginTop) || 0;
    const duration = durationFor(height);

    entries.forEach((entry) => entry.animate([{ opacity: 1 }, { opacity: 0 }], { duration, easing: 'ease', fill: 'forwards' }));
    feed.style.overflow = 'hidden';
    const motion = first.animate(
        [{ marginTop: `${rest}px` }, { marginTop: `${rest - height}px` }],
        { duration, easing: 'ease-out', fill: 'forwards' },
    );
    running.set(feed, motion);

    // The caller removes them before the next frame is painted (the promise settles in a microtask)
    return new Promise((resolve) => {
        const done = () => {
            if (running.get(feed) === motion) {
                running.delete(feed);
                feed.style.overflow = '';
            }
            resolve();
        };
        motion.onfinish = done;
        motion.oncancel = done;
    });
}
