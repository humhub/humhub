/**
 * Manages the client/server communication. Handles humhub json api responses and
 * pjax requests.
 */
humhub.module('ui.richtext', function(module, require, $) {

    var Widget = require('ui.widget').Widget;
    var util = require('util');
    var object = util.object;
    var string = util.string;

    var Richtext = function(node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Richtext, Widget);

    Richtext.component = 'humhub-ui-richtext';

    Richtext.prototype.init = function() {
        this.$input = $('#' + this.$.attr('id') + '_input').hide();
        this.features = [];
        this.emptyMentionings = [];
        this.checkPlaceholder();
        this.initEvents();
        if(this.options.disabled) {
            this.disable();
        }
    };

    Richtext.prototype.initEvents = function() {
        var that = this;
        this.$.on('focus', function() {
            that.checkPlaceholder(true);
            // Initialize features on first focus.
            if(!that.featuresInitialized) {
                that.initFeatures();
            }
        }).on('focusout', function() {
            that.update();
            that.checkPlaceholder();
        }).on('paste', function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            var text = "";
            if(event.originalEvent.clipboardData) { //Forefox, Webkit
                text = event.originalEvent.clipboardData.getData('text/plain');
            } else if(window.clipboardData) { // IE
                text = window.clipboardData.getData("Text");
            }

            that.insertTextAtCursor(text);
            that.fire('richtextPaste');
        }).on('keydown', function(e) {
            that.checkForEmptySpans();
        }).on('keypress', function(e) {
            switch(e.which) {
                case 8: // Backspace
                    // Note chrome won't fire the backspace keypress event, but we don't need the workaround for chrome so its ok..
                    that.checkLinkRemoval();
                    break;
            }
        }).on('clear', function(e) {
            that.clear();
        }).on('disable', function(e) {
            that.disable();
        });
    };

    /**
     * This is a workaround for deleting links as a whole in firefox https://bugzilla.mozilla.org/show_bug.cgi?id=685445
     */
    Richtext.prototype.checkLinkRemoval = function() {
        var position = this.$.caret('offset');

        if(!position) {
            return;
        }

        var that = this;
        // Check if the caret position is right before a link, if yes remove link and perhaps also the parent if empty.
        this.$.find('.richtext-link, .atwho-emoji').each(function() {
            var $this = $(this);
            var offset = $this.offset();
            var right = offset.left + $this.outerWidth(true);

            // The caret top position seems a bit out in some cases...
            if(Math.abs(position.left - right) < 1 && Math.abs(position.top - offset.top) < 18) {
                $this.remove();

                // This is a workaround for a caret position issue in firefox
                _checkCaretPositionAfterRemove(that.$);
                return false; // leave loop
            }
        });
    };

    /**
     * This will prevent an caret glitch in firefox https://bugzilla.mozilla.org/show_bug.cgi?id=904846
     * @param {type} $node
     * @returns {undefined}
     */
    var _checkCaretPositionAfterRemove = function($node) {
        if(!$node.text().length) {
            var spaceText = document.createTextNode("\u00a0");
            $node.prepend(spaceText);

            var sel = window.getSelection();
            var range = document.createRange();

            range.setStart(spaceText, 0);
            range.collapse(true);

            sel.removeAllRanges();
            sel.addRange(range);
        }
    };

    /**
     * Empty spans prevent text deletions in some browsers, so we have to get sure there are no empty spans present.
     * @param {type} $node
     * @returns {undefined}
     */
    Richtext.prototype.checkForEmptySpans = function($node) {
        $node = $node || this.$;
        $node.find('span').each(function() {
            _checkEmptySpan($(this));
        });
    };

    var _checkEmptySpan = function($node) {
        if($node.is('span') && !$node.contents().length) {
            var $parent = $node.parent();
            $node.remove();
            _checkEmptySpan($parent);
        }
    };

    /**
     *  Inserts the given text at the current cursor position.
     *  This function gets sure not to insert unwanted html by escaping html chars.
     *  
     * @param {type} text
     * @returns {undefined}
     */
    Richtext.prototype.insertTextAtCursor = function(text) {
        // Get rid of unwanted html
        var text = $('<div></div>').append(string.escapeHtml(text)).text();

        var lastNode;
        var sel = window.getSelection();

        // Clear current selection, we'll overwrite selected text
        var range = sel.getRangeAt(0);
        range.deleteContents();

        // Remove leading line-breaks and spaces
        text = text.replace(/^(?:\r\n|\r|\n)/g, '').trim();

        // We insert the lines reversed since we don't have to align the range
        var lines = text.split(/(?:\r\n|\r|\n)/g).reverse();

        $.each(lines, function(i, line) {
            // Prevent break after last line
            if(i !== 0) {
                var br = document.createElement("br");
                range.insertNode(br);
            }

            // Insert actual text node
            var newNode = document.createTextNode(line.trim());
            range.insertNode(newNode);

            //Insert leading spaces as textnodes
            var leadingSpaces = line.match(/^\s+/);
            if(leadingSpaces) {
                var spaceCount = leadingSpaces[0].length;
                while(spaceCount > 0) {
                    var spaceNode = document.createTextNode("\u00a0");
                    range.insertNode(spaceNode);
                    spaceCount--;
                }
            }

            // The last node of the loop is the first node in dom since we insert reversed
            if(i === 0) {
                lastNode = newNode;
            }
        });

        // Align range after insertion
        range.setStartAfter(lastNode);
        range.setEndAfter(lastNode);
        sel.removeAllRanges();
        sel.addRange(range);
    };

    Richtext.prototype.update = function() {
        this.$input.val(this.getPlainText());
    };

    Richtext.prototype.checkPlaceholder = function(focus) {
        if(!focus && !this.$.text().trim().length && !this.$.find('[data-richtext-feature]').length) {
            this.$.addClass('atwho-placeholder');
            this.$.html(this.options.placeholder);
            this.$.attr('spellcheck', 'false');
        } else if(this.$.hasClass('atwho-placeholder')) {
            this.$.removeClass('atwho-placeholder');
            this.$.attr('spellcheck', 'true');
            this.$.html('');
        }
    };

    Richtext.prototype.initFeatures = function() {
        var that = this;
        $.each(Richtext.features, function(id, feature) {
            if(!that.isFeatureEnabled(id)) {
                return;
            }

            if(feature.atwho) {
                that.initAtwhoFeature(id, feature);
            }
        });

        // It seems atwho detatches the original element so we have to do a requery
        this.$ = $('#' + this.$.attr('id'));
        this.featuresInitialized = true;
    };

    Richtext.prototype.isFeatureEnabled = function(id) {
        if(this.options.excludes && this.options.excludes.indexOf(id) >= 0) {
            return false;
        }

        if(this.options.includes && this.options.includes.length && this.options.includes.indexOf(id) < 0) {
            return false;
        }

        return true;
    };

    Richtext.prototype.initAtwhoFeature = function(id, feature) {
        var options = (object.isFunction(feature.atwho))
                ? feature.atwho.call(this, feature)
                : $.extend({}, feature.atwho);

        if(object.isFunction(feature.init)) {
            feature.init.call(this, feature, options);
        }

        if(feature.atwho) {
            this.$.atwho(options);
        }

        this.features.push(id);
    };

    Richtext.prototype.disable = function(tooltip) {
        tooltip = tooltip || this.options.disabledText;
        this.$.removeAttr('contenteditable').attr({
            disabled: 'disabled',
            title: tooltip,
        }).tooltip({
            placement: 'bottom'
        });
    };

    Richtext.prototype.clear = function() {
        this.$.html('');
        this.checkPlaceholder();
    };

    Richtext.prototype.focus = function() {
        this.$.trigger('focus');
    };

    Richtext.prototype.getFeatures = function() {
        var result = [];
        $.each(this.features, function(i, id) {
            result.push(Richtext.features[id]);
        });

        return result;
    };

    Richtext.prototype.getPlainText = function() {
        // GENERATE USER GUIDS
        var that = this;

        var $clone = this.$.clone();
        $.each(this.getFeatures(), function(id, feature) {
            if(object.isFunction(feature.parse)) {
                feature.parse($clone, that, feature);
            }
        });

        return Richtext.plainText($clone);
    };

    Richtext.plainText = function(element, options) {
        options = options || {};
        var $element = element instanceof $ ? element : $(element);

        var html = $element.html();
        
        // remove all line breaks
        html = html.replace(/(?:\r\n|\r|\n)/g, "");

        // replace html space
        html = html.replace(/\&nbsp;/g, ' ');

        // rebuild tag structure for webkit browsers
        html = html.replace(/\<div>\s*<br\s*\\*>\<\/div>/g, '<div></div>');

        // replace all div tags with br tags (webkit)
        html = html.replace(/\<div>/g, '<br>');

        // replace all p tags with br tags (IE)
        html = html.replace(/\<p>\<br\s*\\*>\<\/p>/g, '<br>');
        html = html.replace(/\<\/p>/g, '<br>');

        // At.js adds a zwj at the end of each mentioning
        html = html.replace(/\u200d/g, '');

        // replace all <br> with new line break
        html = html.replace(/\<br\s*\>/g, '\n');

        // return plain text without html tags
        var $clone = (options.clone) ? $element.clone() : $element;
        $clone.html(html);
        return $clone.text().trim();
    };
    
    var _entityMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;',
        '=': '&#x3D;'
    };
    
    var _escapeHtml = function(string) {
            return String(string).replace(/[&<>"'=\/]/g, function(s) {
                return _entityMap[s];
            });
        }
    

    Richtext.features = {};

    Richtext.features.emoji = {
        'emojis': [
            "Relaxed", "Yum", "Relieved", "Hearteyes", "Cool", "Smirk",
            "KissingClosedEyes", "StuckOutTongue", "StuckOutTongueWinkingEye", "StuckOutTongueClosedEyes", "Disappointed", "Frown",
            "ColdSweat", "TiredFace", "Grin", "Sob", "Gasp", "Gasp2",
            "Laughing", "Joy", "Sweet", "Satisfied", "Innocent", "Wink",
            "Ambivalent", "Expressionless", "Sad", "Slant", "Worried", "Kissing",
            "KissingHeart", "Angry", "Naughty", "Furious", "Cry", "OpenMouth",
            "Fearful", "Confused", "Weary", "Scream", "Astonished", "Flushed",
            "Sleeping", "NoMouth", "Mask", "Worried", "Smile", "Muscle",
            "Facepunch", "ThumbsUp", "ThumbsDown", "Beers", "Cocktail", "Burger",
            "PoultryLeg", "Party", "Cake", "Sun", "Fire", "Heart"
        ],
        'atwho': {
            at: ":",
            highlightFirst: true,
            limit: 100
        },
        init: function(feature, options) {
            options.data = feature.emojis;
            options.insertTpl = "<img data-emoji-name=';${name};' class='atwho-emoji' with='18' height='18' src='" + module.config['emoji.url'] + "${name}.svg' />";
            options.displayTpl = "<li class='atwho-emoji-entry' data-value=';${name};'><img with='18' height='18' src='" + module.config['emoji.url'] + "${name}.svg' /></li>";
        },
        parse: function($clone) {
            $clone.find('.atwho-emoji').each(function() {
                $(this).replaceWith($(this).data('emoji-name'));
            });
        }
    };

    /**
     * Mentioning feature supports mentionings by typing @ the default mentioning calls an url after typing three digits.
     * Other mentionings can be registered by adding Richtext.features with the at option @:<prefix>
     */
    Richtext.features.mentioning = {};
    Richtext.features.mentioning.atwho = function() {
        // this is the widget instance.
        var that = this;
        return {
            at: "@",
            data: [{image: '', 'cssClass': 'hint', name: module.text('info.minInput')}],
            insertTpl: "<a href='${link}' class='atwho-user richtext-mention richtext-link' contenteditable='false' target='_blank' data-guid='${atwho-at}-${type}${guid}'>${name}</a>",
            displayTpl: "<li class='hint' data-value=''>${name}</li>",
            limit: 10,
            highlightFirst: false,
            callbacks: {
                matcher: function(flag, subtext, should_start_with_space) {
                    var match, regexp;
                    regexp = new RegExp(/(\s+|^)@([\u00C0-\u1FFF\u2C00-\uD7FF\w\s\-\']*$)/);
                    match = regexp.exec(subtext);

                    this.setting.tpl = "<li class='hint' data-value=''>${name}</li>";

                    if(match && typeof match[2] !== 'undefined') {
                        return match[2];
                    }

                    return null;
                },
                remoteFilter: function(query, callback) {
                    this.setting.highlight_first = false;

                    // check the char length and data-query attribute for changing plugin settings for showing results
                    if(query.length >= 3) {
                        if(that.isEmptyMentioning(query)) {
                           //return callback([]);
                        }
                        // Render loading user feedback.
                        this.setting.displayTpl = "<li class='hint' data-value=''>${name}</li>";
                        this.view.render([{"type": "test", "cssClass": "hint", "name": module.text('info.loading'), "image": "", "link": ""}]);

                        // set plugin settings for showing results
                        this.setting.highlightFirst = true;
                        this.setting.displayTpl = '<li class="${cssClass}" data-value="@${name}">${image} ${name}</li>';
                        $.getJSON(that.options.mentioningUrl, {keyword: query}, function(data, test) {
                            if(!data.length) {
                                that.emptyMentionings.push(query);
                            }
                            callback(data);
                        });

                        // reset query count
                        query.length = 0;
                    }
                }
            }
        };
    };

    Richtext.prototype.isEmptyMentioning = function(query) {
        var result = false;
        $.each(this.emptyMentionings, function(index, val) {
            if(string.startsWith(query, val)) {
                result = true;
            }
        });
        return result;
    }

    Richtext.features.mentioning.init = function(feature, options) {
        var widget = this;
        // This is a workaround for mobile browsers especially for Android Chrome which is not able to remove contenteditable="false" nodes.
        // This will enable mobile browsers to delete images and links by taphold/contextmenu event.
        if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(navigator.userAgent)) {
            this.$.on('contextmenu', 'a, img', function() {
                var $this = $(this);
                if($this.parent().is('span')) {
                    $this.parent().remove();
                } else {
                    $this.remove();
                }

                widget.checkForEmptySpans();
                return false;
            });
        }
    };

    /**
     * Used to parse the feature elements.
     * 
     * @param {type} $clone
     * @param {type} widget
     * @returns {undefined}
     */
    Richtext.features.mentioning.parse = function($clone, widget) {
        $clone.find('.atwho-user, .atwho-space').each(function() {
            $(this).text($(this).data('guid'));
        });
    };

    module.export({
        Richtext: Richtext
    });
});