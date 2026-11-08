/**
 * Moves the keyboard focus out of a comment that is being removed, to a neighbouring comment or a
 * fallback, so it does not fall to <body> (docs/ui/keyboard.md, "Moving the focus"). Acts only when
 * the focus was inside the removed entry or is already lost.
 *
 * @param {Element|null} removed the entry root that left (`#comment_<id>`), grabbed before the removal
 * @param {number|null} neighbourId the comment to focus instead
 * @param {Element|null} fallback the element to focus when there is no neighbour
 */
export function handOverFocus(removed, neighbourId, fallback) {
    const active = document.activeElement;
    const lost = !active || active === document.body || !active.isConnected
        || (removed && removed.contains(active));
    if (!lost) {
        return;
    }

    const neighbour = neighbourId === null ? null : document.getElementById('comment_' + neighbourId);
    const target = (neighbour && (neighbour.querySelector('.comment-heading a') || neighbour)) || fallback;
    if (!target || !target.isConnected) {
        return;
    }

    if (!target.matches('a[href], button, input, select, textarea, [tabindex]')) {
        target.setAttribute('tabindex', '-1');
    }
    target.focus({ preventScroll: true });
}
