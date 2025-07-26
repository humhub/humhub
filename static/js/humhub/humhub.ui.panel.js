/**
 * Collapsible panel
 *
 * @namespace humhub.modules.ui.panel
 **/

humhub.module('ui.panel', function (module, require, $) {

    const Widget = require('ui.widget').Widget;
    const PanelMenu = Widget.extend();
    const STATE_COLLAPSED = 'collapsed';

    PanelMenu.prototype.init = function () {
        const that = this;

        this.$collapseLink = this.$.find('a.panel-collapse');
        if (!this.$collapseLink.length) {
            return; // No toggle Link
        }

        const collapseId = this.$collapseLink.attr('href').substring(1); // Remove #
        if (!collapseId) {
            throw new Error('Collapse ID ' + collapseId + ' not found.');
        }

        const collapseElement = document.getElementById(collapseId);
        if (!collapseElement) {
            throw new Error('Collapse element ' + collapseId + ' not found.');
        }

        const bsCollapse = new bootstrap.Collapse(collapseElement, {
            toggle: false, // Don't toggle on instantiation
            parent: null // Avoid parent lookup because the Link is in a dropdown menu
        });

        const localStorageKey = 'pm_' + collapseId;

        if (localStorage.getItem(localStorageKey) !== STATE_COLLAPSED) {
            bsCollapse.show();
            that.setToggleLinkState(true);
        } else {
            bsCollapse.hide();
            that.setToggleLinkState(false);
        }

        collapseElement.addEventListener('show.bs.collapse', function () {
            localStorage.removeItem(localStorageKey);
        });

        collapseElement.addEventListener('hide.bs.collapse', function () {
            localStorage.setItem(localStorageKey, STATE_COLLAPSED);
        });

        collapseElement.addEventListener('shown.bs.collapse', function () {
            that.setToggleLinkState(true);
        });

        collapseElement.addEventListener('hidden.bs.collapse', function () {
            that.setToggleLinkState(false);
        });
    };

    PanelMenu.prototype.setToggleLinkState = function (isExpanded) {
        const icon = isExpanded
            ? module.config.icon.up
            : module.config.icon.down;

        const text = isExpanded
            ? module.text('collapse')
            : module.text('expand');

        this.$collapseLink.html(icon + ' ' + text).removeClass('disabled').removeAttr('disabled');
    };

    module.export({
        PanelMenu: PanelMenu
    });
});
