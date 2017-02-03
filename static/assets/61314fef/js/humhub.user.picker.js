humhub.module('user.picker', function(module, require, $) {
    var object = require('util').object;
    var Picker = require('ui.picker').Picker;

    var UserPicker = function(node, options) {
        Picker.call(this, node, options);
    };

    object.inherits(UserPicker, Picker);

    UserPicker.prototype.selectSelf = function() {
        var userConfig = require('config').get('user');
        if (userConfig && !userConfig.isGuest) {
            this.select(userConfig.guid, userConfig.text, userConfig.image);
        }
    };

    var actionSelectSelf = function(event) {
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
