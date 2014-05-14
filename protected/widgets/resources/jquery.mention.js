$.fn.mention = function (options) {

    var opts = $.extend({}, $.fn.mention.defaults, options);


    var mq = window.matchMedia("(min-width: 1023px)");

    if (mq.matches) {
        // window width is at least 500px


        // variable for userlist arrow navigation
        var chosen = "";

        // variables for text selection (username replacement)
        var searchStart = 0;
        var searchEnd = 0;

        $(this).each(function () {

            // create container arround textarea
            $(this).wrap('<div class="mention-container" style="height: 36px"></div>');

            // add mention overlay
            $(this).before('<div class="mention-overlay"></div>');

            // textarea for html content
            $(this).before('<textarea class="mention-html-content" name=""></textarea>');

            // add dropdown list for user results
            $(this).parent().append('<ul class="dropdown-menu mention-userlist" role="menu" aria-labelledby="dropdownMenu"><li><div class="loader"></div></li></ul>');

            // set the css properties from textarea to mention overlay
            $(this).parent().find('.mention-overlay').css({
                /*width: $textarea.css('width'),*/
                fontFamily: opts.fontFamily,
                fontWeight: opts.fontWeight,
                fontSize: opts.fontSize,
                border: opts.border,
                color: opts.color,
                padding: opts.padding,
                minHeight: opts.minHeight,
                lineHeight: opts.lineHeight,
                borderRadius: opts.borderRadius

            });

            // change textarea style
            $(this).css({
                position: 'absolute',
                resize: 'none',
                //height: '36px',
                backgroundColor: 'transparent'

            })

            // set name of original textarea to the new generated one
            $(this).parent().find('.mention-html-content').attr('name', $(this).attr('name'));

            // clear name for original textarea
            $(this).attr('name', '');

            //
            // Event for handle user input
            //
            $(this).keydown(function (event) {

                if (event.keyCode == 40 || event.keyCode == 38 || event.keyCode == 13 || event.keyCode == 9) {

                    // disable default behavior for arrow, enter and tab keys, when userlist is open
                    if ($.fn.mention.defaults.stateUserList == true) {
                        event.preventDefault();
                    }

                }
                // update mention overlay
                $.fn.mention.updateMentionOverlay($(this));

            })

            $(window).scroll(function () {

                // hide userlist
                $('.mention-userlist').hide();

            })

            // set textarea height to mention container
            $(this).on('change keyup paste', function () {
                $.fn.mention.updateMentionContainerSize($(this));
            })


            $(this).keyup(function (event) {

                // check if a @ char exists
                if ($(this).val().search(" @") == -1) {

                    // set userlist state to "close" if no @ char was found to deactivate the search
                    $.fn.mention.defaults.stateUserList = false;

                }

                // catch input from @ char for windows or mac
                if (event.altKey && event.keyCode == 76 || event.keyCode == 81) {

                    // check if there an space before @ to ignore email inputs
                    if ($(this).val().search(" @") != -1) {

                        // save the current cursor position
                        searchStart = $(this).textrange('get').start;

                        // set mention userlist to the right position
                        $.fn.mention.setPosition($(this));

                        // set userlist state to "open"
                        $.fn.mention.defaults.stateUserList = true;

                    }

                }

                // find userlist element
                var _obj = $(this).parent().find('.mention-userlist');

                // navigate with arrow keys
                if (event.keyCode == 40) {

                    // select next <li> element
                    if (chosen === "") {
                        chosen = 1;
                    } else if ((chosen + 1) < _obj.find('li').length) {
                        chosen++;
                    }
                    _obj.find('li').removeClass('selected');
                    _obj.find('li:eq(' + chosen + ')').addClass('selected');
                    return false;

                    // navigate with arrow keys
                } else if (event.keyCode == 38) {

                    // select previous <li> element
                    if (chosen === "") {
                        chosen = 1;
                    } else if (chosen > 0) {
                        chosen--;
                    }
                    _obj.find('li').removeClass('selected');
                    _obj.find('li:eq(' + chosen + ')').addClass('selected');
                    return false;

                } else if (event.keyCode == 13 || event.keyCode == 9) {

                    // simulate click event
                    if ($.fn.mention.defaults.stateUserList == true) {
                        window.location.href = _obj.find('.selected').children('a').attr('href');
                    }

                } else if ($.fn.mention.defaults.stateUserList == true) {

                    // set mention userlist to the right position
                    $.fn.mention.setPosition($(this));

                    // safe the current cursor position
                    searchEnd = $(this).textrange('get').start;

                    // select text from entered @ char until current cursor position
                    $(this).textrange('set', searchStart, searchEnd - searchStart);

                    // save selection
                    var _str = $(this).textrange('get');

                    if (_str.length >= 1) {

                        // search for user by string
                        loadUser($(this), _str.text);
                    } else {
                        $(this).parent().find('.mention-userlist').hide();
                    }

                    // set cursor the back to the last position
                    $(this).textrange('set', searchEnd, 0);

                }

            })


            //
            // Update the original textarea by losing focus
            //
            $(this).focusout(function () {

                // the focusout event will be also fired by clicking an userlist entry with mouse
                // so check, if the user selection is over, before updating the original textarea
                if ($.fn.mention.defaults.stateUserList == false) {

                    // hide mention userlist
                    $(this).parent().find('.mention-userlist').hide();

                    // save mention overlay object
                    var $element = $(this).parent().find('.mention-overlay');

                    // save unchanged content
                    var _html = $element.html();

                    // change <span> tags with just the user guids
                    for (var i = 0; i <= $element.html().split('</span>').length; i++) {
                        var _guid = $element.children('span').attr('data-guid');
                        $element.children('span').first().replaceWith('@' + _guid);
                    }

                    // add modified content to the original textarea
                    $(this).parent().find('.mention-html-content').val($element.html().split('&nbsp;').join(' '));

                    // put the original content back to the textarea
                    $element.html(_html);

                }

            })

        })


        function loadUser($obj, $string) {

            // get userlist element
            var _obj = $obj.parent().find('.mention-userlist');

            // show loader while loading
            //_obj.html('<li><div class="loader"></div></li>');

            // show userlist
            _obj.show();

            // start ajax request
            jQuery.getJSON(opts.searchUrl.replace('-keywordPlaceholder-', $string), function (json) {

                // remove existings entries
                _obj.find('li').remove();

                if (json.length > 0) {

                    for (var i = 0; i < json.length; i++) {

                        // build <li> entry
                        var str = '<li id="user_' + json[i].guid + '"><a tabindex="-1" href="javascript:$.fn.mention.addUser(\'' + $obj.attr('id') + '\',\'' + json[i].guid + '\', \'' + json[i].displayName + '\', ' + searchStart + ', ' + searchEnd + ');"><img class="img-rounded" src="' + json[i].image + '" height="20" width="20" alt=""/> ' + json[i].displayName + '</a></li>';

                        // append the entry to the <ul> list
                        _obj.append(str);

                    }

                    // check if the list is empty
                    if (_obj.children().length == 0) {
                        // hide userpicker, if it is
                        _obj.hide();
                    }

                    // reset the variable for arrows keys
                    chosen = "";

                } else {

                    // hide userpicker, if no user was found
                    _obj.hide();
                }

                // remove hightlight
                _obj.find('li').removeHighlight();

                // add new highlight matching strings
                _obj.find('li').highlight($string);

                // add selection to the first space entry
                _obj.find('li:eq(0)').addClass('selected');

            })
        }

    }


}

//
// Update the height
//
$.fn.mention.updateMentionContainerSize = function ($obj) {

    // set mention overlay height to textarea height
    $obj.parent().css({
        height: $obj.outerHeight() + "px"
    })
}

//
// Update mention overlay with modified textarea content
//
$.fn.mention.updateMentionOverlay = function ($obj) {

    // create a delay to get the newest content after the keydown event
    var _updateInterval = setInterval(function () {

        // replace textfield spaces and line breaks with html codes
        var _content = $obj.val().split('  ').join('&nbsp;&nbsp;').replace(/\n/g, "<br>");

        // update mention overlay with modified html content
        $obj.parent().find('.mention-overlay').html(_content);

        // check if username is there
        for (var i = 0; i < $.fn.mention.defaults.arrUser.length; i++) {


            if ($obj.val().search($.fn.mention.defaults.arrUser[i]['name']) > -1) {

                // save current textarea content
                var _value = $obj.parent().find('.mention-overlay').html();

                // replace name through a <span> tag with user details
                _value = _value.replace($.fn.mention.defaults.arrUser[i]['name'], '<span data-guid="' + $.fn.mention.defaults.arrUser[i]['guid'] + '">' + $.fn.mention.defaults.arrUser[i]['name'] + '</span>');

                // add modified content to mention overlay
                $obj.parent().find('.mention-overlay').html(_value);

            } else {
                // remove user from array, if he didn't exists in the textarea anymore
                $.fn.mention.defaults.arrUser.splice(i, 1);
            }
        }

        // set textarea height to mention container
        $.fn.mention.updateMentionContainerSize($obj);

        // delete interval
        clearInterval(_updateInterval);

    }, 1);

}

$.fn.mention.addUser = function ($element_id, $guid, $name, $start, $end) {

    // replace current search input with username
    $('#' + $element_id).textrange('set', $start - 1, $end - ($start - 2));
    $('#' + $element_id).textrange('replace', $name + " ");

    // get cursor position after the new inserted username
    var _newEnd = $('#' + $element_id).textrange('get').end;

    // remove the username selection and set the cursor after the username
    $('#' + $element_id).textrange('set', _newEnd, 0);

    // update the array with a new user
    $.fn.mention.updateArray($name, $guid);

    // set textarea height to mention container
    $.fn.mention.updateMentionOverlay($('#' + $element_id));

    // reset userlist state
    $.fn.mention.defaults.stateUserList = false;

    // reset userlist elements
    $('#' + $element_id).parent().find('.mention-userlist').html('<li><div class="loader"></div></li>');
    $('#' + $element_id).parent().find('.mention-userlist').hide();

}

//
// Update array with new users
//
$.fn.mention.updateArray = function (username, userguid) {

    // create a new array element
    $.fn.mention.defaults.arrUser.push(new Array());

    // get the count of the created array element
    var count = $.fn.mention.defaults.arrUser.length - 1;

    // update properties
    $.fn.mention.defaults.arrUser[count]['name'] = username;
    $.fn.mention.defaults.arrUser[count]['guid'] = userguid;

};

//
// empty the elements (for example after ajax submits)
//
$.fn.mention.reset = function ($element) {

    // empty elements
    $($element).parent().find('.mention-html-content').val('');
    $($element).parent().find('.mention-overlay').empty();

    // change container size to one line
    $($element).parent().parent().find('.mention-container').css({height: $($element).outerHeight()});


};

//
// set the position of the userlist under the current textfield
//
$.fn.mention.setPosition = function ($obj) {

    // get the absolute position for the textarea inside the body
    var _top = $obj.offset().top - $(window).scrollTop();
    var _left = $obj.offset().left - $(window).scrollLeft();

    // set mention userlist to the right position
    $obj.parent().find('.mention-userlist').css({
        position: "fixed",
        top: _top + $obj.outerHeight() - 4,
        left: _left,
        width: $obj.outerWidth()
    })

}


// plugin defaults
$.fn.mention.defaults = {
    arrUser: new Array(),
    stateUserList: false,
    searchString: '',
    searchUrl: '',
    padding: '6px 12px',
    border: '2px solid transparent',
    color: '#ffffff',
    fontFamily: "'Open Sans', sans-serif",
    fontSize: '14px',
    fontWeight: '400',
    lineHeight: '20px',
    minHeight: '34px',
    borderRadius: '4px'
};