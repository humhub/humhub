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

        // Get Menu Link to "Collapse" / "Expand"
        this.$collapseLink = this.$.find('a.panel-collapse');
        if (!this.$collapseLink.length) {
            return;
        }

        // Get collapse ID
        const collapseId = this.$collapseLink.attr('href').substring(1); // Remove #
        if (!collapseId) {
            throw new Error('Collapse ID ' + collapseId + ' not found.');
        }

        // Get parent element
        const $parent = this.$.closest('.panel');
        if (!$parent.length) {
            throw new Error('Panel for ' + collapseId + ' not found.');
        }

        // Get HTML element to collapse (next if it is not a heading)
        let $collapseElement = $parent.find('.collapse');
        if (!$collapseElement.length) {
            $collapseElement = this.$.next();
            if ($collapseElement.hasClass('panel-heading')) {
                // Use next element (usually it is .panel-body)
                $collapseElement = $collapseElement.next();
            }
        }
        if (!$collapseElement.length) {
            throw new Error('Collapse element for ' + collapseId + ' not found.');
        }
        $collapseElement.addClass('collapse');

        // Set ID to collapse element and get vanilla JS collapse element
        $collapseElement.attr('id', collapseId);
        const collapseElement = $collapseElement[0];

        // Instantiate BS collapse
        const bsCollapse = new bootstrap.Collapse(collapseElement, {
            toggle: false, // Don't toggle on instantiation
            parent: null // Avoid parent lookup because the Link is in a dropdown menu
        });

        const localStorageKey = 'pm_' + collapseId;

        // Expand or Collapse depending on the local storage state
        if (localStorage.getItem(localStorageKey) !== STATE_COLLAPSED) {
            bsCollapse.show();
            that.setToggleLinkTitle(true);
        } else {
            bsCollapse.hide();
            that.setToggleLinkTitle(false);
        }

        // When the Link is clicked, update the local storage state
        collapseElement.addEventListener('show.bs.collapse', function () {
            localStorage.removeItem(localStorageKey);
        });
        collapseElement.addEventListener('hide.bs.collapse', function () {
            localStorage.setItem(localStorageKey, STATE_COLLAPSED);
        });

        // When the collapse is toggled, update the Link title
        collapseElement.addEventListener('shown.bs.collapse', function () {
            that.setToggleLinkTitle(true);
        });
        collapseElement.addEventListener('hidden.bs.collapse', function () {
            that.setToggleLinkTitle(false);
        });
    };

    PanelMenu.prototype.setToggleLinkTitle = function (isExpanded) {
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
