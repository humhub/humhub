/**
 * Module for creating an manipulating modal dialoges.
 * Normal layout of a dialog:
 * 
 * <div class="modal">
 *     <div class="modal-dialog">
 *         <div class="modal-content">
 *             <div class="modal-header"></div>
 *             <div class="modal-body"></div>
 *             <div class="modal-footer"></div>
 *         </div>
 *     </div>
 * </div>
 *  
 * @param {type} param1
 * @param {type} param2
 */
humhub.module('media.Jplayer', function (module, require, $) {

    var Widget = require('ui.widget').Widget;
    var object = require('util').object;

    var Jplayer = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Jplayer, Widget);

    Jplayer.prototype.init = function (playlist) {
        var cssSlectors = {
            jPlayer: '#' + this.$.attr('id'),
            cssSelectorAncestor: this.options.containerId
        };
        this.playlist = new jPlayerPlaylist(cssSlectors, playlist, this.options);
    };

    Jplayer.prototype.getDefaultOptions = function () {
        return {
            playlistOptions: {
                enableRemoveControls: false,
            },
            useStateClassSkin: true,
            supplied: "mp3",
            smoothPlayBar: true,
            keyEnabled: false,
            audioFullScreen: false
        };
    };


    module.export = Jplayer;
});