/**
 * Collapsible panel
 *
 * @namespace humhub.modules.ui.panel
 **/

humhub.module('ui.panel', function(module, require, $) {

    let Widget = require('ui.widget').Widget;

    let PanelMenu = Widget.extend();

    let STATE_COLLAPSED = 'collapsed';

    PanelMenu.prototype.init = function() {
        this.$panel = this.$.closest('.panel');
        this.$body = this.$panel.find('.panel-body');

        var that = this;
        setTimeout(function() {
            if(!that.getToggleState()) {
                that.$body.css({'display': 'none'});
            }

            that.checkToggleLinkState();
        }, 1)
    };

    PanelMenu.prototype.getToggleState = function() {
        return localStorage.getItem(this.getKey()) !== STATE_COLLAPSED;
    };

    PanelMenu.prototype.getKey = function() {
        let panelId = this.$panel.attr('id');
        return (!panelId || !panelId.length) ? null : 'pm_'+this.$panel.attr('id');
    };

    PanelMenu.prototype.checkToggleLinkState = function() {
        let isCollapsed = this.$body.is(':visible');

        let icon = (isCollapsed)
            ? module.config.icon.up
            : module.config.icon.down;

        let text = (isCollapsed)
            ? module.text('collapse')
            : module.text('expand');

        let $collapseLink = this.$.find('.panel-collapse').html( icon + ' ' + text);

        if(isCollapsed) {
            $collapseLink.addClass('panel-collapsed');
        } else {
            $collapseLink.removeClass('panel-collapsed');
        }
    };

    PanelMenu.prototype.toggle = function(evt) {
        let that = this;
        if(this.$body.is(':visible')) {
            this.$body.slideUp("fast", function () {
                localStorage.setItem(that.getKey(), STATE_COLLAPSED);
                that.checkToggleLinkState();
            });

        } else {
            this.$body.slideDown("fast", function () {
                that.checkToggleLinkState();
                localStorage.removeItem(that.getKey());
            });
        }
    };

    module.export({
        PanelMenu: PanelMenu
    });
});
