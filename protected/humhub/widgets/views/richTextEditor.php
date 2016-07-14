<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>

<script type="text/javascript">

    $(document).ready(function () {
        //The original form input element will be hidden
        var $formInput = $('#<?php echo $id; ?>').hide();
        var placeholder = $formInput.attr('placeholder');
        
        var $editableContent = $('#<?php echo $id; ?>_contenteditable');

        if (!$editableContent.length) {
            $formInput.after('<div id="<?php echo $id; ?>_contenteditable" class="atwho-input form-control atwho-placeholder" data-query="0" contenteditable="true">' + placeholder + '</div>');
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

        var emojis_list = $.map(emojis, function (value, i) {
            return {'id': i, 'name': value};
        });

        // init at plugin
        $editableContent.atwho({
            at: "@",
            data: ["<?php echo Yii::t('base', 'Please type at least 3 characters') ?>"],
            insert_tpl: "<a href='<?php echo Url::to(['/user/profile']); ?>/&uguid=${guid}' target='_blank' class='atwho-user' data-user-guid='@-${type}${guid}'>${atwho-data-value}</a>",
            tpl: "<li class='hint' data-value=''>${name}</li>",
            search_key: "name",
            limit: 10,
            highlight_first: false,
            callbacks: {
                matcher: function (flag, subtext, should_start_with_space) {
                    var match, regexp;
                    regexp = new RegExp(/(\s+|^)@([\u00C0-\u1FFF\u2C00-\uD7FF\w\s\-\']*$)/); 
                    match = regexp.exec(subtext);
                    
                    if (match && typeof match[2] !== 'undefined') {
                        return match[2];
                    }
                    
                    return null;
                },
                remote_filter: function (query, callback) {

                    // set plugin settings for showing hint
                    this.setting.highlight_first = false;
                    this.setting.tpl = "<li data-value=''><?php echo Yii::t('base', 'Please type at least 3 characters') ?></li>";
                    //this.setting.tpl = "<li class='hint' data-value=''>${name}</li>";

                    // check the char length and data-query attribute for changing plugin settings for showing results
                    if (query.length >= 3 && $('#<?php echo $id; ?>_contenteditable').attr('data-query') == '1') {

                        // set plugin settings for showing results
                        this.setting.highlight_first = true;
                        this.setting.tpl = "<li data-value='@${name}'>${image} ${name}</li>",
                            // load data
                            $.getJSON("<?php echo Url::to([$userSearchUrl]); ?>", {keyword: query}, function (data) {
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
        
        // remove placeholder text
        $editableContent.on('focus', function () {
            if ($(this).hasClass('atwho-placeholder')) {
                $(this).removeClass('atwho-placeholder');
                $(this).html('');
                $(this).focus();
            }
        }).on('focusout', function () {
            $('#<?php echo $id; ?>').val(getPlainInput($(this).clone()));
            // add placeholder text, if input is empty
            if ($(this).html() == "" || $(this).html() == " " || $(this).html() == " <br>") {
                $(this).html(placeholder);
                $(this).addClass('atwho-placeholder');
            }
        }).on('paste', function (event) {

            // disable standard behavior
            event.preventDefault();

            // create variable for clipboard content
            var text = "";

            if (event.originalEvent.clipboardData) {
                // get clipboard data (Firefox, Webkit)
                var text = event.originalEvent.clipboardData.getData('text/plain');
            } else if (window.clipboardData) {
                // get clipboard data (IE)
                var text = window.clipboardData.getData("Text");
            }

            // create jQuery object and paste content
            var $result = $('<div></div>').append(text);

            // set plain text at current cursor position
            insertTextAtCursor($result.text());

        }).on('keypress', function (e) {
            if (e.which == 13) {
                // insert a space by hitting enter for a clean convert of user guids
                insertTextAtCursor(' ');
            }
        }).on("shown.atwho", function (event) {
            // set attribute for showing search results
            $(this).attr('data-query', '1');
        }).on("inserted.atwho", function (event, $li) {
            // set attribute for showing search hint
            $(this).attr('data-query', '0');
        }).on('clear', function(evt) {
             $(this).html(placeholder);
             $(this).addClass('atwho-placeholder');
        });
    });

    /**
     * Convert contenteditable div content into plain text
     * @param element jQuery contenteditable div element
     * @returns plain text
     */
    function getPlainInput(element) {

        // GENERATE USER GUIDS
        var userCount = element.find('.atwho-user').length;

        for (var i = 0; i <= userCount; i++) {
            var userGuid = element.find('.atwho-user:first').attr('data-user-guid');
            element.find('.atwho-user:first').text(userGuid);
            element.find('.atwho-user:first').removeClass('atwho-user');
        }


        // GENERATE SPACE GUIDS
        var spaceCount = element.find('.atwho-space').length;

        for (var i = 0; i <= spaceCount; i++) {
            var spaceGuid = element.find('.atwho-space:first').attr('data-space-guid');
            element.find('.atwho-space:first').text(spaceGuid);
            element.find('.atwho-space:first').removeClass('atwho-space');
        }


        // GENERATE SMILEYS
        var emojiCount = element.find('.atwho-emoji').length;

        for (var i = 0; i <= emojiCount; i++) {
            var emojiName = element.find('.atwho-emoji:first').attr('data-emoji-name');
            element.find('.atwho-emoji:first').replaceWith(emojiName);
        }

        // save html from contenteditable div
        var html = element.html();

        // rebuild tag structure for webkit browsers
        html = html.replace('<div> <br></div>', '<div></div>');

        // replace all div tags with br tags (webkit)
        html = html.replace(/\<div>/g, '<br>');

        // replace all p tags with br tags (IE)
        html = html.replace(/\<p>/g, '<br>');

        // replace html space
        html = html.replace(/\&nbsp;/g, ' ');

        // remove all line breaks
        html = html.replace(/(?:\r\n|\r|\n)/g, "");

        // replace all <br> with new line break
        element.html(html.replace(/\<br\s*\>/g, '\n'));

        // return plain text without html tags
        return element.text();

    }

    /**
     * Insert a text at the current cursor position
     * @param text insert string
     */
    function insertTextAtCursor(text) {
        var sel, range, html;
        sel = window.getSelection();
        range = sel.getRangeAt(0);
        range.deleteContents();
        var newNode = document.createTextNode(text);
        range.insertNode(newNode);
        range.setStartAfter(newNode);
        range.setEndAfter(newNode);
        sel.removeAllRanges();
        sel.addRange(range);

    }

</script>