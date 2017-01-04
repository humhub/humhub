<?php

use yii\helpers\Url;
?>

<script type="text/javascript">
    $(document).ready(function() {
        //The original form input element will be hidden
        var $formInput = $('#<?php echo $id; ?>').hide();
        var placeholder = $formInput.attr('placeholder');

        var $editableContent = $('#<?php echo $id; ?>_contenteditable');

        if(!$editableContent.length) {
            $formInput.after('<div id="<?php echo $id; ?>_contenteditable" autocomplete="off" class="atwho-input form-control atwho-placeholder" data-query="0" contenteditable="true">' + placeholder + '</div>');
            $editableContent = $('#<?php echo $id; ?>_contenteditable');
        }

        var emojis = [
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
        ];

        var emojis_list = $.map(emojis, function(value, i) {
            return {'id': i, 'name': value};
        });

        // Note we use &#x200b; to mark the end of a mentioning link
        $editableContent.atwho({
            at: "@",
            data: [{image: '', 'cssClass': 'hint', name: "<?= Yii::t('base', 'Please type at least 3 characters') ?>"}],
            insert_tpl: "<a href='${link}' class='atwho-user richtext-link' contenteditable='false' target='_blank' data-user-guid='${atwho-at}-${type}${guid}'>${atwho-data-value}&#x200b;</a>",
            tpl: "<li class='hint' data-value=''>${name}</li>",
            limit: 10,
            highlight_first: false,
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
                remote_filter: function(query, callback) {
                    this.setting.highlight_first = false;

                    // check the char length and data-query attribute for changing plugin settings for showing results
                    if(query.length >= 3 && $('#<?= $id; ?>_contenteditable').attr('data-query') == '1') {
                        // Render loading user feedback.
                        this.setting.tpl = "<li class='hint' data-value=''>${name}</li>";
                        this.view.render([{"type": "test", "cssClass": "hint", "name": "<?= Yii::t('base', 'Loading...') ?>", "image": "", "link": ""}]);

                        // set plugin settings for showing results
                        this.setting.highlight_first = true;
                        this.setting.tpl = '<li class="${cssClass}" data-value="@${name}">${image} ${name}</li>';
                        $.getJSON("<?php echo Url::to([$userSearchUrl]); ?>", {keyword: query}, function(data) {
                            callback(data);
                        });

                        // reset query count
                        query.length = 0;
                    }
                }
            }
        }).atwho({
            at: ":",
            insert_tpl: "<img data-emoji-name=';${name};' class='atwho-emoji' with='18' height='18' src='<?php echo Yii::getAlias('@web/img/emoji/${name}.svg'); ?>' />",
            tpl: "<li class='atwho-emoji-entry' data-value=';${name};'><img with='18' height='18' src='<?php echo Yii::getAlias('@web/img/emoji/${name}.svg'); ?>'/></li>",
            data: emojis_list,
            highlight_first: true,
            limit: 100
        });
        

        //it seems atwho detatches the original element so we have to do a requery
        $editableContent = $('#<?php echo $id; ?>_contenteditable');
        
        //This is a workaround for mobile browsers especially for Android Chrome which is not able to remove contenteditable="false" nodes.
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(navigator.userAgent)) {
            $editableContent.on('contextmenu', 'a, img', function() {
                if($(this).parent().is('span')) {
                    $(this).parent().remove();
                } else {
                    $(this).remove();
                }
                
                _checkForEmptySpans($editableContent);
                return false;
            });
        }

        // remove placeholder text
        $editableContent.on('focus', function() {
            if($(this).hasClass('atwho-placeholder')) {
                $(this).removeClass('atwho-placeholder');
                $(this).html('');
                $(this).focus();
            }
        }).on('focusout', function() {
            $('#<?php echo $id; ?>').val(getPlainInput($(this).clone()));
            // add placeholder text, if input is empty
            var html = $(this).html();
            if(html == "" || html == " " || html.trim() == "<br>") {
                $(this).attr('spellcheck', false);
                $(this).html(placeholder);
                $(this).addClass('atwho-placeholder');
            }
        }).on('paste', function(event) {

            // disable standard behavior
            event.preventDefault();
            event.stopImmediatePropagation();

            // create variable for clipboard content
            var text = "";

            if(event.originalEvent.clipboardData) {
                // get clipboard data (Firefox, Webkit)
                text = event.originalEvent.clipboardData.getData('text/plain');
            } else if(window.clipboardData) {
                // get clipboard data (IE)
                text = window.clipboardData.getData("Text");
            }

            // create jQuery object and paste content
            var $result = $('<div></div>').append(escapeHtml(text));
            // set plain text at current cursor position
            insertTextAtCursor($result.text());

        }).on('keydown', function(e) {
            _checkForEmptySpans($editableContent);
        }).on('keypress', function(e) {

            switch(e.which) {
                case 13: // Enter
                    // Insert a space after some delay to not interupt the browsers default new line insertion.
                    var $context = $(window.getSelection().getRangeAt(0).commonAncestorContainer);
                    setTimeout(function() {
                        if($context[0].nodeType === Node.TEXT_NODE) {
                            $context[0].textContent += '\u00a0';
                        }
                    }, 1000);
                    break;
                case 8: // Backspace
                    // Note chrome won't fire the backspace keypress event, but we don't need the
                    // workaround for chrome so its ok..
                    _checkRichTextLinkRemoval($editableContent);
                    break;
            }
            $(this).attr('spellcheck', true);
        }).on("shown.atwho", function(event) {
            // set attribute for showing search results
            $(this).attr('data-query', '1');
        }).on("inserted.atwho", function(event, $li) {
            $('.atwho-emoji').each(function() {
                if($(this).closest('.richtext-link').length) {
                    $(this).closest('.richtext-link').after(this);
                }
            });
            // set attribute for showing search hint
            $(this).attr('data-query', '0');
        }).on('clear', function(evt) {
            $(this).html(placeholder);
            $(this).addClass('atwho-placeholder');
        });
    });

    var _checkRichTextLinkRemoval = function($editableContent) {
        /**
         * This is a workaround for deleting links as a whole in firefox https://bugzilla.mozilla.org/show_bug.cgi?id=685445
         */
        var position = $editableContent.caret('offset');

        if(!position) {
            return;
        }

        // Check if the caret position is right before a link, if yes remove link and perhaps also the parent if empty.
        $('.richtext-link').each(function() {
            var $this = $(this);
            var offset = $this.offset();
            var right = offset.left + $this.outerWidth(true);

            // The caret top position seems a bit out in some cases...
            if(Math.abs(position.left - right) < 1 && Math.abs(position.top - offset.top) < 18) {
                $this.remove();

                // This is a workaround for a caret position issue in firefox https://bugzilla.mozilla.org/show_bug.cgi?id=904846
                _checkCaretPositionAfterRemove($editableContent);
                return false; // leave loop
            } 
        });
    };

    var _checkCaretPositionAfterRemove = function($editableContent) {
        if(!$editableContent.text().length) {
            var spaceText = document.createTextNode("\u00a0");
            $editableContent.prepend(spaceText);

            var sel = window.getSelection();
            var range = document.createRange();

            range.setStart(spaceText, 0);
            range.collapse(true);

            sel.removeAllRanges();
            sel.addRange(range);
        }
    };

    var _checkForEmptySpans = function($editableContent) {
        $editableContent.find('span').each(function() {
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

    var _getPreviousTextNode = function($node) {
        var $prev = $($node[0].previousSibling);
        var $parent = $node.parent();

        // As long we have not found a non empty text node.
        while(!$prev.length || ($prev[0].nodeType === Node.TEXT_NODE && !$prev[0].textContent.length)) {
            // If current prev is not defined or an empty text node test the next prev node
            if($prev.length && $prev[0].nodeType === Node.TEXT_NODE && !$prev[0].textContent.length) {
                $prev = $($prev[0].previousSibling);
            } else if($parent.is('[contenteditable]')) {
                // If our parent is the editable itself we stop searching for other parent siblings
                $prev = undefined;
                break;
            } else {
                // Else we traverse up the dom tree and search for a prev node.
                $prev = $($parent[0].previousSibling);
                $parent = $parent.parent();
            }
        }

        if($prev && $prev.length && $prev[0].nodeType === Node.TEXT_NODE) {
            return $prev;
        } else if($prev) { // Some other node (span/a) return its text.
            return $prev.contents().filter(function() {
                return this.nodeType === 3;
            });
        }

    };

    var entityMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;',
        '`': '&#x60;',
        '=': '&#x3D;'
    };

    function escapeHtml(string) {
        return String(string).replace(/[&<>"'`=\/]/g, function fromEntityMap(s) {
            return entityMap[s];
        });
    }

    /**
     * Convert contenteditable div content into plain text
     * @param element jQuery contenteditable div element
     * @returns plain text
     */
    function getPlainInput(element) {
        // GENERATE USER GUIDS
        var userCount = element.find('.atwho-user').length;

        for(var i = 0; i <= userCount; i++) {
            var userGuid = element.find('.atwho-user:first').attr('data-user-guid');
            element.find('.atwho-user:first').text(userGuid);
            element.find('.atwho-user:first').removeClass('atwho-user');
        }

        // GENERATE SPACE GUIDS
        var spaceCount = element.find('.atwho-space').length;

        for(var i = 0; i <= spaceCount; i++) {
            var spaceGuid = element.find('.atwho-space:first').attr('data-space-guid');
            element.find('.atwho-space:first').text(spaceGuid);
            element.find('.atwho-space:first').removeClass('atwho-space');
        }


        // GENERATE SMILEYS
        var emojiCount = element.find('.atwho-emoji').length;

        for(var i = 0; i <= emojiCount; i++) {
            var emojiName = element.find('.atwho-emoji:first').attr('data-emoji-name');
            element.find('.atwho-emoji:first').replaceWith(emojiName);
        }

        // save html from contenteditable div
        var html = element.html();

        // replace html space
        html = html.replace(/\&nbsp;/g, ' ');

        // rebuild tag structure for webkit browsers
        html = html.replace(/\<div>\s*<br\s*\\*>\<\/div>/g, '<div></div>');

        // replace all div tags with br tags (webkit)
        html = html.replace(/\<div>/g, '<br>');

        // replace all p tags with br tags (IE)
        html = html.replace(/\<p>\<br\s*\\*>\<\/p>/g, '<br>');
        html = html.replace(/\<\/p>/g, '<br>');

        // remove all line breaks
        html = html.replace(/(?:\r\n|\r|\n)/g, "");

        // replace all <br> with new line break
        element.html(html.replace(/\<br\s*\>/g, '\n'));

        // return plain text without html tags
        return element.text().trim();
    }

    /**
     * Insert a text at the current cursor position
     * @param text insert string
     */
    function insertTextAtCursor(text, prevTrim) {
        var lastNode;
        var sel = window.getSelection();

        var range = sel.getRangeAt(0);
        range.deleteContents();

        //Remove leading line-breaks and spaces
        text = text.replace(/^(?:\r\n|\r|\n)/g, '');

        if(!prevTrim) {
            text.trim();
        }

        //We insert the lines reversed since we don't have to align the range
        var lines = text.split(/(?:\r\n|\r|\n)/g).reverse();

        $.each(lines, function(i, line) {
            //Prevent break after last line
            if(i !== 0) {
                var br = document.createElement("br");
                range.insertNode(br);
            }

            //Insert new node
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

            //The last node is the first node since we insert reversed
            if(i === 0) {
                lastNode = newNode;
            }
        });

        //Align range
        range.setStartAfter(lastNode);
        range.setEndAfter(lastNode);
        sel.removeAllRanges();
        sel.addRange(range);
    }

</script>