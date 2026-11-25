/**
 * A card in the sidebar of a page (`humhub\widgets\SidebarCard`): restores and remembers whether
 * its body is collapsed. The ⋮ menu's entry toggles the body with Bootstrap's collapse, which
 * also keeps the entry's `aria-expanded` and `.collapsed` (its label) up to date.
 *
 * @since 1.20
 * @namespace humhub.modules.ui.sidebarCard
 **/
humhub.module('ui.sidebarCard', function (module, require, $) {

    const Widget = require('ui.widget').Widget;

    // The key `PanelMenu` used, so that a collapsed panel stays collapsed as a sidebar card
    const STORAGE_PREFIX = 'pm_';
    const STATE_COLLAPSED = 'collapsed';

    const SidebarCard = Widget.extend();

    SidebarCard.prototype.init = function () {
        const $body = this.$.children('.collapse');
        if (!$body.length) {
            return;
        }

        const body = $body[0];
        const storageKey = STORAGE_PREFIX + body.id;
        const $toggle = this.$.find('.sidebar-card-collapse');

        if (getState(storageKey) === STATE_COLLAPSED) {
            // At once, without the animation
            $body.removeClass('show');
            $toggle.addClass('collapsed').attr('aria-expanded', 'false');
        }

        body.addEventListener('show.bs.collapse', function (evt) {
            if (evt.target === body) {
                setState(storageKey, null);
            }
        });
        body.addEventListener('hide.bs.collapse', function (evt) {
            if (evt.target === body) {
                setState(storageKey, STATE_COLLAPSED);
            }
        });
    };

    // Storage may be unavailable (private mode, blocked site data): the card then stays open
    const getState = function (key) {
        try {
            return localStorage.getItem(key);
        } catch (e) {
            return null;
        }
    };

    const setState = function (key, state) {
        try {
            if (state === null) {
                localStorage.removeItem(key);
            } else {
                localStorage.setItem(key, state);
            }
        } catch (e) {
            // The state is not remembered
        }
    };

    module.export({
        SidebarCard: SidebarCard
    });
});
