/**
 * The listbox of a select menu/picker, positioned `fixed` against its field (see
 * docs/develop/ui-js-vuejs-picker.md, "Behaviour"): an `overflow` of a dialog or card never
 * clips it. Below the field, or above when there is less room below than the list's height
 * and more above; its height limited to the room, the visible area (`visualViewport`, so the
 * on-screen keyboard counts) included.
 *
 * @since 1.20
 */

/**
 * The distance between field and list, in px (Bootstrap's `--bs-dropdown-spacer`, .125rem).
 */
export const DRAWER_GAP = 2;

/**
 * Positions `drawer` against `field`; `maxHeight` is the list's own limit (`$select-menu-max-height`).
 * Returns `{ dropUp }`.
 */
export const positionDrawer = (field, drawer, maxHeight) => {
    // Out of the page flow first: in it, the open list can lengthen the page, and the scrollbar that
    // brings shifts the field until the list is fixed
    drawer.style.position = 'fixed';
    const box = field.getBoundingClientRect();
    drawer.style.width = `${box.width}px`;
    const viewport = window.visualViewport;
    const viewTop = viewport ? viewport.offsetTop : 0;
    const viewBottom = viewTop + (viewport ? viewport.height : window.innerHeight);

    const below = Math.max(0, viewBottom - box.bottom - DRAWER_GAP);
    const above = Math.max(0, box.top - viewTop - DRAWER_GAP);
    // `max-height` holds the border (border-box), `scrollHeight` does not.
    const border = Math.max(0, (drawer.offsetHeight || 0) - (drawer.clientHeight || 0));
    const natural = Math.min(drawer.scrollHeight ? drawer.scrollHeight + border : maxHeight, maxHeight);
    const dropUp = below < natural && above > below;
    const height = Math.min(natural, dropUp ? above : below);

    Object.assign(drawer.style, {
        position: 'fixed',
        left: `${box.left}px`,
        width: `${box.width}px`,
        top: `${dropUp ? box.top - DRAWER_GAP - height : box.bottom + DRAWER_GAP}px`,
        bottom: 'auto',
        maxHeight: `${height}px`,
        margin: '0',
    });

    return { dropUp };
};

/**
 * Removes what positionDrawer() set.
 */
export const resetDrawer = (drawer) => {
    ['position', 'left', 'width', 'top', 'bottom', 'maxHeight', 'margin'].forEach((property) => {
        drawer.style[property] = '';
    });
};
