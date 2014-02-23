/*
 * Userpicker
 * Version 1.0.0
 * Written by: Andreas Strobel
 *
 * @property String $inputId is the ID of the input HTML Element
 * @property Int $maxUsers the maximum of users in this dropdown
 * @property String $userSearchUrl the url of the search, to find the users
 * @property String $currentValue is the current value of the parent field.
 * @property String $templates.inputStructure is the HTML structure to replace the original input element.
 *
 * License: MIT License - http://www.opensource.org/licenses/mit-license.php
 */


var chosen = "";
var userCount = 0;

$.fn.userpicker = function (options) {

    // set standard options
    options = $.extend({
        inputId: "",
        maxUsers: 0,
        searchUrl: "",
        currentValue: "",
        renderType: "normal", // possible values are "normal", "partial"
        focus: false,
        templates: {
            inputStructure: '<div class="user_picker_container"><ul class="tag_input" id="invite_tags"><li id="tag_input"><input type="text" id="tag_input_field" class="tag_input_field" value="" autocomplete="off" placeholder="Add an user"></li></ul><ul class="dropdown-menu" id="userpicker" role="menu" aria-labelledby="dropdownMenu"></ul></div>'
        }

    }, options);


    init();


    function init() {

        // remove picker if existing
        $('.user_picker_container').remove();

        // insert the new input structure after the original input element
        $(options.inputId).after(options.templates.inputStructure);

        // hide original input element
        $(options.inputId).hide();

        if (options.currentValue != "") {

            // restore data from database
            restoreUserTags(options.currentValue);
        }

        if (options.focus == true) {
            // set focus to input
            $('#tag_input_field').focus();
            $('#invite_tags').addClass('focus');
        }

        // simulate focus in
        $('#tag_input_field').focusin(function() {
            $('#invite_tags').addClass('focus');
        })

        // simulate focus out
        $('#tag_input_field').focusout(function() {
            $('#invite_tags').removeClass('focus');
        })

    }

    function restoreUserTags(html) {

        // add html structure for input element
        $('#invite_tags').prepend(html);

        // create function for every user tag to remove the element
        $('#invite_tags .userInput i').each(function () {

            $(this).click(function () {

                // remove user tag
                $(this).parent().remove();

                // reduce the count of added user
                userCount--;

            })

            // raise the count of added user
            userCount++;

        })


    }


    // Set focus on the input field, by clicking the <ul> construct
    jQuery('#invite_tags').click(function () {

        // set focus
        $('#tag_input_field').focus();
    })

    $('#tag_input_field').keydown(function (event) {

        // by pressing the tab key an the input is empty
        if ($(this).val() == "" && event.keyCode == 9) {

            //do nothing

            // by pressing enter, tab, up or down arrow
        } else if (event.keyCode == 40 || event.keyCode == 38 || event.keyCode == 13 || event.keyCode == 9) {

            // ... disable the default behavior to hold the cursor at the end of the string
            event.preventDefault();

        }

        // if there is a user limit and the user didn't press the tab key
        if (options.maxUsers != 0 && event.keyCode != 9) {

            // if the max user count is reached
            if (userCount == options.maxUsers) {

                // show hint
                showHintUser();

                // block input events
                event.preventDefault();
            }
        }

    })

    $('#tag_input_field').keyup(function (event) {

        // start search after a specific count of characters
        if ($('#tag_input_field').val().length >= 3) {

            // set userpicker position in bottom of the user input
            $('#userpicker').css({
                position: "absolute",
                top: $('#tag_input_field').position().top + 30,
                left: $('#tag_input_field').position().left + 0
            })

            if (event.keyCode == 40) {

                // select next <li> element
                if (chosen === "") {
                    chosen = 1;
                } else if ((chosen + 1) < $('#userpicker li').length) {
                    chosen++;
                }
                $('#userpicker li').removeClass('selected');
                $('#userpicker li:eq(' + chosen + ')').addClass('selected');
                return false;

            } else if (event.keyCode == 38) {

                // select previous <li> element
                if (chosen === "") {
                    chosen = 1;
                } else if (chosen > 0) {
                    chosen--;
                }
                $('#userpicker li').removeClass('selected');
                $('#userpicker li:eq(' + chosen + ')').addClass('selected');
                return false;

            } else if (event.keyCode == 13 || event.keyCode == 9) {

                // simulate click event
                window.location.href = $('#userpicker .selected a').attr('href');

            } else {

                // save the search string to variable
                var str = $('#tag_input_field').val();

                // show userpicker with the results
                $('#userpicker').show();

                // load users
                loadUser(str);
            }
        } else {

            // hide userpicker
            $('#userpicker').hide();
        }


    })


    $('#tag_input_field').focusout(function () {

        // set the plain text including user guids to the original input or textarea element
        $(options.inputId).val(parseUserInput());
    })


    function loadUser(string) {

        // remove existings entries
        $('#userpicker li').remove();

        // show loader while loading
        $('#userpicker').html('<li><div class="loader"></div></li>');

        jQuery.getJSON(options.searchUrl.replace('-keywordPlaceholder-', string), function (json) {

            // remove existings entries
            $('#userpicker li').remove();


            if (json.length > 0) {


                for (var i = 0; i < json.length; i++) {

                    // build <li> entry
                    var str = '<li><a tabindex="-1" href="javascript:addUserTag(\'' + json[i].guid + '\', \'' + json[i].image + '\', \'' + json[i].displayName + '\');"><img class="img-rounded" src="' + json[i].image + '" height="20" width="20" alt=""/> ' + json[i].displayName + '</a></li>';

                    // append the entry to the <ul> list
                    $('#userpicker').append(str);

                }

                // reset the variable for arrows keys
                chosen = "";

            } else {

                // hide userpicker, if no user was found
                $('#userpicker').hide();
            }


            // remove hightlight
            $("#userpicker li").removeHighlight();

            // add new highlight matching strings
            $("#userpicker li").highlight(string);

            // add selection to the first space entry
            $('#userpicker li:eq(0)').addClass('selected');

        })
    }

    function showHintUser() {

        // remove hint, if exists
        $('#maxUsersHint').remove();

        // build html structure
        var _html = '<div id="maxUsersHint" style="display: none;" class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">x</button><strong>Sorry!</strong> You can add a maximum of ' + options.maxUsers + ' users as admin for this group.</div>';

        // add hint to DOM
        $('#invite_tags').after(_html);

        // fadein hint
        $('#maxUsersHint').fadeIn('fast');
    }

}

// Add a usertag for invitation
function addUserTag(guid, image_url, name) {


    // Building a new <li> entry
    var _tagcode = '<li class="userInput" id="' + guid + '"><img class="img-rounded" alt="24x24" data-src="holder.js/24x24" style="width: 24px; height: 24px;" src="' + image_url + '" alt="' + name + '" width="24" height="24" />' + name + '<i class="icon-remove-sign"></i></li>';


    // insert the new created <li> entry into the <ul> contruct
    $('#tag_input').before(_tagcode);

    // remove tag, by clicking the close icon
    $('#' + guid + " i").click(function () {

        // remove user tag
        $('#' + guid).remove();

        // reduce the count of added user
        userCount--;

    })

    // hide user results
    $('#userpicker').hide();

    // set focus to the input element
    $('#tag_input_field').focus();

    // Clear the textinput
    $('#tag_input_field').val('');

    // raise the count of added user
    userCount++;


}

function parseUserInput() {

    // create and insert a dummy <div> element to work with
    $('#invite_tags').after('<div id="inputResult"></div>')

    // set html form input element to the new <div> element
    $('#inputResult').html($('#invite_tags').html());


    $('#inputResult .userInput').each(function () {

        // add the user guid as plain text
        $(this).after(this.id + ",");

        // remove the link
        $(this).remove();
    })

    // save the plain text
    var result = $('#inputResult').text();

    // remove the dummy <div> element
    $('#inputResult').remove();

// return the plain text
    return result;

}

$(document).ready(function () {

    // fire "loaded" event
    $(document).trigger("userpicker_loaded");

});

