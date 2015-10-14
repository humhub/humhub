<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>

<script type="text/javascript">

    $(document).ready(function () {

        // Get placeholder
        var placeholder = $('#<?php echo $id; ?>').attr('placeholder');

        // hide original input element
        $('#<?php echo $id; ?>').hide();

        // check if contenteditable div already exists
        if ($('#<?php echo $id; ?>_contenteditable').length == 0) {

            // add contenteditable div
            $('#<?php echo $id; ?>').after('<div id="<?php echo $id; ?>_contenteditable" class="atwho-input form-control atwho-placeholder" data-query="0" contenteditable="true">' + placeholder + '</div>');

        }

        var emojis = [
            "1F601", "1F602", "1F603", "1F604", "1F605", "1F606",
            "1F607", "1F608", "1F609", "1F610", "1F611", "1F612",
            "1F613", "1F614", "1F615", "1F616", "1F617", "1F618",
            "1F619", "1F620", "1F621", "1F622", "1F623", "1F624",
            "1F625", "1F626", "1F627", "1F628", "1F629", "1F631",
            "1F632", "1F633", "1F634", "1F635", "1F636", "1F637",
            "1F641", "1F642", "1F60A", "1F60B", "1F60C", "1F60D",
            "1F60E", "1F60F", "1F61A", "1F61B", "1F61C", "1F61D",
            "1F61E", "1F61F", "1F62A", "1F62B", "1F62C", "1F62D",
            "1F62E", "1F62F", "1F44A", "1F592", "1F593", "2764",
            "1F389", "1F525", "1F37B", "1F382", "1F354", "1F355",
            "1F357", "1F56B", "1F575", "1F31E"
        ];

        var emojis_list = $.map(emojis, function (value, i) {
            return {'id': i, 'name': value};
        });

        // init at plugin
        $('#<?php echo $id; ?>_contenteditable').atwho({
            at: "@",
            data: ["Please type at least 3 characters"],
            insert_tpl: "<a href='<?php echo Url::to(['/user/profile']); ?>/&uguid=${guid}' target='_blank' class='atwho-user' data-user-guid='@-${type}${guid}'>${atwho-data-value}</a>",
            //tpl: "<li data-value='@${name}'><img class='img-rounded' src='${image}' height='20' width='20' alt=''> ${name}</li>",
            tpl: "<li class='hint' data-value=''>${name}</li>",
            search_key: "name",
            limit: 10,
            highlight_first: false,
            callbacks: {
                matcher: function (flag, subtext, should_start_with_space) {
                    var match, regexp;

                    flag = flag.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");

                    if (should_start_with_space) {
                        flag = '(?:^|\\s)' + flag;
                    }

                    regexp = new RegExp(flag + '([A-Za-z0-9_\\s\+\-\]*)$', 'gi');
                    match = regexp.exec(subtext.replace(/\s/g, " "));
                    if (match) {
                        return match[2] || match[1];
                    } else {
                        return null;
                    }
                },
                remote_filter: function (query, callback) {

                    // set plugin settings for showing hint
                    this.setting.highlight_first = false;
                    this.setting.tpl = "<li class='hint' data-value=''>${name}</li>";

                    // check the char length and data-query attribute for changing plugin settings for showing results
                    if (query.length >= 3 && $('#<?php echo $id; ?>_contenteditable').attr('data-query') == '1') {

                        // set plugin settings for showing results
                        this.setting.highlight_first = true;
                        this.setting.tpl = "<li data-value='@${name}'><img class='img-rounded' src='${image}' height='20' width='20' alt=''> ${name}</li>",
                            // load data
                            $.getJSON("<?php echo Url::to([$userSearchUrl]); ?>", {keyword: query}, function (data) {
                                callback(data)
                            });

                    }
                }
            }
        }).atwho({
            at: ":",
            insert_tpl: "<img data-emoji-name=';${name};' class='atwho-emoji' with='18' height='18' src='<?php echo Yii::getAlias('@web/img/emoji/${name}.svg'); ?>' />",
            tpl: "<li class='atwho-emoji-entry' data-value=';${name};'><img with='18' height='18' src='<?php echo Yii::getAlias('@web/img/emoji/${name}.svg'); ?>'/></li>",
            data: emojis_list,
            highlight_first: false,
            limit: 100
        });


        // remove placeholder text
        $('#<?php echo $id; ?>_contenteditable').focus(function () {
            $(this).removeClass('atwho-placeholder');

            if ($(this).html() == placeholder) {
                $(this).html('');
                $(this).focus();
            }
        })
        // add placeholder text, if input is empty
        $('#<?php echo $id; ?>_contenteditable').focusout(function () {
            if ($(this).html() == "" || $(this).html() == " " || $(this).html() == " <br>") {
                $(this).html(placeholder);
                $(this).addClass('atwho-placeholder');
            } else {
                $('#<?php echo $id; ?>').val(getPlainInput($(this).clone()));
            }
        })

        $('#<?php echo $id; ?>_contenteditable').on('paste', function (event) {


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

        });


        $('#<?php echo $id; ?>_contenteditable').keypress(function (e) {
            if (e.which == 13) {
                // insert a space by hitting enter for a clean convert of user guids
                insertTextAtCursor(' ');
            }
        });

        $('#<?php echo $id; ?>_contenteditable').on("shown.atwho", function (event) {
            // set attribute for showing search results
            $(this).attr('data-query', '1');
        });

        $('#<?php echo $id; ?>_contenteditable').on("inserted.atwho", function (event, $li) {
            // set attribute for showing search hint
            $(this).attr('data-query', '0');
        });

    })
    ;


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