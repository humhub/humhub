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

    var emojis = ["Ambivalent", "Angry", "Confused", "Cool", "Frown", "Gasp", "Grin", "Heart", "Hearteyes", "Laughing", "Slant", "Smile", "Wink", "Yuck"];

    var emojis_list = $.map(emojis, function (value, i) {
        return {'id': i, 'name': value};
    });

    // init at plugin
    $('#<?php echo $id; ?>_contenteditable').atwho({
        at: "@",
        data: ["Please type at least 3 characters"],
        insert_tpl: "<a href='<?php echo Yii::app()->createAbsoluteUrl('user/profile'); ?>/&uguid=${guid}' target='_blank' class='atwho-user' data-user-guid='@-${type}${guid}'>${atwho-data-value}</a>",
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
                        $.getJSON("<?php echo Yii::app()->createAbsoluteUrl($userSearchUrl); ?>", {keyword: query}, function (data) {
                            callback(data)
                        });

                }
            }
        }
    }).atwho({
        at: ":",
        insert_tpl: "<img class='atwho-emoji' data-emoji-name=';${name};' src='<?php echo Yii::app()->baseUrl; ?>/img/emoji/${name}.png' />",
        tpl: "<li data-value=';${name};'><img src='<?php echo Yii::app()->baseUrl; ?>/img/emoji/${name}.png' /> ${name}</li>",
        data: emojis_list,
        limit: 10
    });


    // remove placeholder text
    $('#<?php echo $id; ?>_contenteditable').focus(function () {
        $(this).removeClass('atwho-placeholder');

        if ($(this).html() == placeholder) {
            $(this).html(' ');
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