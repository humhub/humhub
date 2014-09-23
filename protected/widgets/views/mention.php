<script type="text/javascript">

$(document).ready(function () {

    // Get placeholder
    var placeholder = $('#<?php echo $id; ?>').attr('placeholder');

    // hide original input element
    $('#<?php echo $id; ?>').hide();

    // add contenteditable div
    $('#<?php echo $id; ?>').after('<div id="<?php echo $id; ?>_contenteditable" class="atwho-input form-control atwho-placeholder" contenteditable="true">' + placeholder + '</div>');

    var emojis = ["Ambivalent", "Angry", "Confused", "Cool", "Frown", "Gasp", "Grin", "Heart", "Laughing", "Slant", "Smile", "Wink", "Yuck"];

    var emojis_list = $.map(emojis, function (value, i) {
        return {'id': i, 'name': value};
    });

    // init at plugin
    $('#<?php echo $id; ?>_contenteditable').atwho({
        at: "@",
        insert_tpl: "<span class='atwho-user' data-user-guid='@${guid}'>${atwho-data-value}</span>",
        tpl: "<li data-value='@${displayName}'><img class='img-rounded' src='${image}' height='20' width='20' alt=''> ${displayName}</li>",
        search_key: "displayName",
        limit: 10,
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
                $.getJSON("<?php echo $userSearchUrl; ?>", {keyword: query}, function (data) {
                    callback(data)
                });
            }
        }
    }).atwho({
        at: ":",
        insert_tpl: "<img class='atwho-emoji' data-emoji-name=':${name}:' src='<?php echo Yii::app()->baseUrl; ?>/img/emoji/${name}.png' />",
        tpl: "<li data-value=':${name}:'><img src='<?php echo Yii::app()->baseUrl; ?>/img/emoji/${name}.png' /> ${name}</li>",
        data: emojis_list,
        limit: 10
    }).atwho({
        at: "#",
        insert_tpl: "<span class='atwho-space' data-space-guid='#${guid}'>${atwho-data-value}</span>",
        tpl: "<li data-value='#${name}'><img class='img-rounded' src='${image}' height='20' width='20' alt=''> ${name}</li>",
        search_key: "name",
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
                $.getJSON("<?php echo Yii::app()->createAbsoluteUrl('//space/space/searchSpaceJson') ?>", {keyword: query}, function (data) {
                    callback(data)
                });
            }
        },
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
        if ($(this).html() == "" || $(this).html() == " ") {
            $(this).html(placeholder);
            $(this).addClass('atwho-placeholder');
        } else {
            $('#<?php echo $id; ?>').val(getCleanInput($(this).clone()));
        }
    })

    /*       $('#
    <?php //echo $id; ?>_contenteditable').summernote({
     airMode: true,
     airPopover: [
     ['font', ['bold', 'italic', 'underline']],
     ['para', ['ul', 'ol']],
     ['insert', ['link']]
     ]
     });*/


});


// TODO: Die Funktion wird jetzt ständig geladen, muss sie noch auslagern, damit sie nur einmal geladen wird!!!

function getCleanInput(element) {

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

    var boldCount = element.find('b').length;
    for (var i = 0; i <= boldCount; i++) {
        var boldContent = element.find('b:first').html();
        element.find('b:first').replaceWith('**' + boldContent + '**');
    }

    var italicCount = element.find('i').length;
    for (var i = 0; i <= italicCount; i++) {
        var italicContent = element.find('i:first').html();
        element.find('i:first').replaceWith('*' + italicContent + '*');
    }



    element.find('li').each(function () {
        $(this).html('- ' + $(this).html() + '\n');
    })




    var linkCount = element.find('a').length;
    for (var i = 0; i <= linkCount; i++) {
        var linkContent = element.find('a:first').text();
        var linkHref = element.find('a:first').attr('href');
        element.find('a:first').replaceWith('[' + linkContent + '](' + linkHref + ')');
    }



    // save html from contenteditable div
    var html = element.html();


    // replace all div tags with br tags (Chrome fix)
    html = html.replace(/\<div>/g, '<br>');

    // replace all <br> with new line break
    element.html(html.replace(/\<br\s*\>/g, '\n'));

    console.log(element.text());

    // return plain text without html tags
    return element.text();

}


</script>