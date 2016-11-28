humhub.module('user.picker', function(module, require, $) {
    var object = require('util').object;
    var Picker = require('ui.picker').Picker;

    module.initOnPjaxLoad = false;

    var UserPicker = function() {};

    object.inherits(UserPicker, Picker);

    UserPicker.prototype.selectSelf = function() {
        var userConfig = require('config').get('user');
        if (userConfig && !userConfig.isGuest) {
            this.select(userConfig.guid, userConfig.displayName, userConfig.image);
        }
    };

    var actionSelectSelf = function(event) {
        debugger;
        var picker = UserPicker.instance(event.$target);
        if (picker instanceof UserPicker) {
            picker.selectSelf();
        } else {
            module.log.error('Tried self select on non picker node!', true);
        }
    };

    module.export({
        UserPicker: UserPicker,
        actionSelectSelf: actionSelectSelf
    });
});
