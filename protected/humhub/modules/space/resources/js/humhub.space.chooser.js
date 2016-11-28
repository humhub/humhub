/**
 * This module provides an api for handling content objects e.g. Posts, Polls...
 *
 * @type undefined|Function
 */

humhub.module('space.chooser', function(module, require, $) {
    var event = require('event');
    var space = require('space');
    var client = require('client');
    var ui = require('ui');
    var Widget = ui.widget.Widget;
    var object = require('util').object;

    var SELECTOR_ITEM = '[data-space-chooser-item]';
    var SELECTOR_ITEM_REMOTE = '[data-space-none]';

    module.initOnPjaxLoad = false;

    var SpaceChooser = function() {};

    object.inherits(SpaceChooser, Widget);

    SpaceChooser.prototype.init = function() {
        this.$menu = $('#space-menu');
        this.$chooser = $('#space-menu-spaces');
        this.$search = $('#space-menu-search');
        this.$remoteSearch = $('#space-menu-remote-search');
        // set niceScroll to SpaceChooser menu
        this.$chooser.niceScroll({
            cursorwidth: "7",
            cursorborder: "",
            cursorcolor: "#555",
            cursoropacitymax: "0.2",
            nativeparentscrolling: false,
            railpadding: {top: 0, right: 3, left: 0, bottom: 0}
        });

        this.$.on('click', SELECTOR_ITEM, function(evt) {
            if (this === evt.target) {
                $(this).find('a')[0].click();
            }
        });

        this.initEvents();
        this.initSpaceSearch();
    };

    SpaceChooser.prototype.initEvents = function() {
        var that = this;

        // Set no space icon for non space views and set space icon for space views.
        event.on('humhub:ready', function() {
            if (!space.isSpacePage()) {
                that.setNoSpace();
            }
        }).on('humhub:modules:space:changed', function(evt, options) {
            that.setSpace(options);
        });

        // Focus on search on open and clear item selection when closed
        this.$menu.parent().on('shown.bs.dropdown', function() {
            that.$search.focus();
        }).on('hidden.bs.dropdown', function() {
            that.clearSelection();
        });
    };

    SpaceChooser.prototype.initSpaceSearch = function() {
        var that = this;

        $('#space-search-reset').click(function() {
            that.resetSearch();
        });

        $('#space-directory-link').on('click', function() {
            that.$menu.trigger('click');
        });

        this.$search.on('keyup', function(event) {
            var $selection = that.getSelectedItem();
            switch (event.keyCode) {
                case 40: // Down -> select next
                    if (!$selection.length) {
                        SpaceChooser.selectItem(that.getFirstItem());
                    } else if ($selection.nextAll(SELECTOR_ITEM + ':visible').length) {
                        SpaceChooser.deselectItem($selection)
                                .selectItem($selection.nextAll(SELECTOR_ITEM + ':visible').first());
                    }
                    break;
                case 38: // Up -> select previous
                    if ($selection.prevAll(SELECTOR_ITEM + ':visible').length) {
                        SpaceChooser.deselectItem($selection)
                                .selectItem($selection.prevAll(SELECTOR_ITEM + ':visible').first());
                    }
                    break;
                case 13: // Enter
                    if ($selection.length) {
                        $selection.find('a')[0].click();
                    }
                    break;
                default:
                    that.triggerSearch();
                    break;
            }
        }).on('keydown', function(event) {
            if (event.keyCode === 13) {
                event.preventDefault();
            }
        }).on('focus', function() {
            $('#space-directory-link').addClass('focus');
        }).on('blur', function() {
            $('#space-directory-link').removeClass('focus');
        });
    };

    SpaceChooser.prototype.triggerSearch = function() {
        var input = this.$search.val().toLowerCase();

        // Don't repeat the search querys
        if (this.$search.data('last-search') === input) {
            return;
        }

        // Reset search if no input is given, else fade in search reset
        if (!input.length) {
            this.resetSearch();
            return;
        } else {
            $('#space-search-reset').fadeIn('fast');
        }

        // Filter all existing items and highlight text
        this.filterItems(input);
        this.highlight(input);

        this.triggerRemoteSearch(input);
    };

    SpaceChooser.prototype.filterItems = function(input) {
        this.clearSelection();
        this.$search.data('last-search', input);

        // remove max-height property to hide the nicescroll scrollbar in case of search input
        this.$chooser.css('max-height', ((input) ? 'none' : '400px'));

        this.getItems().each(function() {
            var $item = $(this);
            var itemText = $item.text().toLowerCase();

            // Select the first item if search was successful
            if (itemText.search(input) >= 0) {
                $item.show();
            } else {
                $item.hide();
            }
        });

        SpaceChooser.selectItem(this.getFirstItem());
    };

    SpaceChooser.prototype.highlight = function(input, selector) {
        selector = selector || SELECTOR_ITEM;
        this.$chooser.find(SELECTOR_ITEM).removeHighlight().highlight(input);
    };

    SpaceChooser.prototype.triggerRemoteSearch = function(input) {
        var that = this;
        this.remoteSearch(input).then(function(data) {
            if (!data) {
                that.onChange(input);
                return;
            }

            $.each(data, function(index, result) {
                if (!that.$.find('[data-space-guid="' + result.guid + '"]').length) {
                    that.$chooser.append(result.output);
                }
            });

            that.highlight(input, SELECTOR_ITEM_REMOTE);
            that.onChange(input);
        }).catch(function(e) {
            module.log.error(e, true);
        });
    };

    SpaceChooser.prototype.remoteSearch = function(input) {
        var that = this;
        return new Promise(function(resolve, reject) {
            // Clear all current remote results not matching the current search
            that.clearRemoteSearch(input);
            var url = module.config.remoteSearchUrl;

            if (!url) {
                module.log.warn('Could not execute space remote search, set data-space-search-url in your space search input');
                resolve();
                return;
            } else if (input.length < 2) {
                resolve();
                return;
            }

            var searchTs = Date.now();
            var options = {data: {keyword: input, target: 'chooser'}};
            
            ui.loader.set(that.$remoteSearch, {'wrapper': '<li>', 'css': {padding: '5px'}});
            client.get(url, options).then(function(response) {
                that.$remoteSearch.empty();
                var lastSearchTs = that.$remoteSearch.data('last-search-ts');
                var isOutDated = lastSearchTs && lastSearchTs > searchTs;
                var hastData = response.data && response.data.length;

                // If we got no result we return
                if (!hastData || isOutDated) {
                    resolve();
                } else {
                    that.$remoteSearch.data('last-search-ts', searchTs);
                    resolve(response.data);
                }
            }).catch(reject);
        });
    };

    /**
     * Clears all remote results which do not match with the input search.
     * If no input is given, all remote results will be removed.
     * 
     * @param {string} input search filter 
     * @returns {undefined}
     */
    SpaceChooser.prototype.clearRemoteSearch = function(input) {
        // Clear all non member and non following spaces
        this.$chooser.find('[data-space-none]').each(function() {
            var $this = $(this);
            if (!input || $this.find('.space-name').text().toLowerCase().search(input) < 0) {
                $this.remove();
            }
        });
    };

    SpaceChooser.prototype.resetSearch = function() {
        $('#space-search-reset').fadeOut('fast');
        this.clearRemoteSearch();

        this.$search.val('').focus();
        this.$search.removeData('last-search');
        this.getItems().show().removeHighlight().removeClass('selected');
        this.$chooser.css('max-height', '400px');
        this.$remoteSearch.empty();
    };

    SpaceChooser.prototype.onChange = function(input) {
        var emptyResult = !this.getFirstItem().length;
        var atLeastTwo = input && input.length > 1;

        if (emptyResult && atLeastTwo) {
            this.$remoteSearch.html('<li><div class="help-block">' + module.text('info.emptyResult') + '</div></li>');
        } else if (emptyResult) {
            this.$remoteSearch.html('<li><div class="help-block">' + module.text('info.emptyOwnResult') + '<br/>' + module.text('info.remoteAtLeastInput') + '</div></li>');
        } else if (!atLeastTwo) {
            this.$remoteSearch.html('<li><div class="help-block">' + module.text('info.remoteAtLeastInput') + '</div></li>');
        }
    };

    SpaceChooser.prototype.clearSelection = function() {
        return this.getSelectedItem().removeClass('selected');
    };

    SpaceChooser.prototype.getFirstItem = function() {
        return this.$chooser.find('[data-space-chooser-item]:visible').first();
    };

    SpaceChooser.selectItem = function($item) {
        $item.addClass('selected');
        return SpaceChooser;
    };

    SpaceChooser.deselectItem = function($item) {
        $item.removeClass('selected');
        return SpaceChooser;
    };

    /**
     * Resets the space chooser icon, if no space view is set.
     * 
     * @returns {undefined}
     */
    SpaceChooser.prototype.setNoSpace = function() {
        if (!this.$menu.find('.no-space').length) {
            this._changeMenuButton(module.config.noSpace);
        }
    };

    /**
     * Changes the space chooser icon, for the given space options.
     * 
     * @param {type} spaceOptions
     * @returns {undefined}
     */
    SpaceChooser.prototype.setSpace = function(spaceOptions) {
        this._changeMenuButton(spaceOptions.image + ' <b class="caret"></b>')
    };

    SpaceChooser.prototype._changeMenuButton = function(newButton) {
        var $newTitle = (newButton instanceof $) ? newButton : $(newButton);
        var $oldTitle = this.$menu.children();
        this.$menu.append($newTitle.hide());
        ui.additions.switchButtons($oldTitle, $newTitle, {remove: true});
    };

    SpaceChooser.prototype.getSelectedItem = function() {
        return this.$chooser.find('[data-space-chooser-item].selected');
    };

    SpaceChooser.prototype.getItems = function() {
        return this.$chooser.find('[data-space-chooser-item]');
    };

    module.export({
        SpaceChooser: SpaceChooser,
        init: function() {
            SpaceChooser.instane($('#space-menu-dropdown'));
        }
    });
});