humhub.module('user.PermissionGridModuleFilter', function (module, require, $) {
    var Widget = require('ui.widget.Widget');
    var util = require('util');

    var PermissionGridModuleFilter = Widget.extend();

    PermissionGridModuleFilter.prototype.init = function () {
        this.$permissions = $('.permission-grid-editor');
        this.initDropdown();

        var activeModule = util.url.getUrlParameter('module');

        if (activeModule && this.modules.indexOf(activeModule) > -1) {
            this.$.val(activeModule).trigger('change');
        }

    };

    PermissionGridModuleFilter.prototype.initDropdown = function () {
        this.modules = [];
        var that = this;
        this.$permissions.find('[data-module-id]').each(function () {
            var $this = $(this);
            var id = $this.attr('data-module-id');
            if (that.modules.indexOf(id) < 0) {
                that.$.append('<option value="' + id + '">' + $this.text() + '</option>');
                that.modules.push(id);
            }
        });
    };

    PermissionGridModuleFilter.prototype.filterModule = function (moduleId) {
        var showAll = moduleId === 'all';

        $('.permission-group-tabs').find('a').each(function () {
            var $this = $(this);
            var original = $this.data('originalUrl');

            if (!original) {
                original = $this.attr('href');
                $this.data('originalUrl', original);
            }

            $this.attr('href', !showAll ? original + '&module=' + moduleId : original);
        });

        if (showAll) {
            this.$permissions.find('tr').show();
            return;
        }

        this.$permissions.find('[data-module-id]').each(function () {
            var id = $(this).attr('data-module-id');
            var $row = $(this).closest('tr');
            if (id !== moduleId) {
                $row.hide();
            } else {
                $row.show();
            }

        });
    };

    PermissionGridModuleFilter.prototype.change = function (evt) {
        this.filterModule(this.$.val());
    };

    module.export = PermissionGridModuleFilter;
});
