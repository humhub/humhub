/*
 * Userpicker
 * Version 1.0.0
 * Written by: Andreas Strobel
 *
 * @property String $inputId is the ID of the input HTML Element
 * @property Int $maxUsers the maximum of users in this dropdown
 * @property String $userSearchUrl the url of the search, to find the users
 * @property String $currentValue is the current value of the parent field.
 *
 */

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
        userGuid: "",
        data: {},
        placeholderText: 'Add an user'
    }, options);

    var chosen = "";
    var uniqueID = "";


    init();


    function init() {

        uniqueID = options.inputId.substr(1);

        var _template = '<div class="' + uniqueID + '_user_picker_container"><ul class="tag_input" id="' + uniqueID + '_invite_tags"><li id="' + uniqueID + '_tag_input"><input type="text" id="' + uniqueID + '_tag_input_field" class="tag_input_field" value="" autocomplete="off"></li></ul><ul class="dropdown-menu" id="' + uniqueID + '_userpicker" role="menu" aria-labelledby="dropdownMenu"></ul></div>';

        // remove picker if existing
        $('.'+uniqueID+'_user_picker_container').remove();


        if ($('.' + uniqueID + '_user_picker_container').length == 0) {

            // insert the new input structure after the original input element
            $(options.inputId).after(_template);
        }


        // hide original input element
        $(options.inputId).hide();

        if (options.currentValue != "") {

            // restore data from database
            restoreUserTags(options.currentValue);
        }

        // add placeholder text to input field
        $('#' + uniqueID + '_tag_input_field').attr('placeholder', options.placeholderText);

        if (options.focus == true) {
            // set focus to input
            $('#' + uniqueID + '_tag_input_field').focus();
            $('#' + uniqueID + '_invite_tags').addClass('focus');
        }

        // simulate focus in
        $('#' + uniqueID + '_tag_input_field').focusin(function () {
            $('#' + uniqueID + '_invite_tags').addClass('focus');
        })

        // simulate focus out
        $('#' + uniqueID + '_tag_input_field').focusout(function () {
            $('#' + uniqueID + '_invite_tags').removeClass('focus');
        })

    }

    function restoreUserTags(html) {

        // add html structure for input element
        $('#' + uniqueID + '_invite_tags .userInput').remove();
        $('#' + uniqueID + '_invite_tags').prepend(html);

        // create function for every user tag to remove the element
        $('#' + uniqueID + '_invite_tags .userInput i').each(function () {

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
    jQuery('#' + uniqueID + '_invite_tags').click(function () {

        // set focus
        $('#' + uniqueID + '_tag_input_field').focus();
    });

    $('#' + uniqueID + '_tag_input_field').keydown(function (event) {

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

    });

    $('#' + uniqueID + '_tag_input_field').keyup(function (event) {

        // start search after a specific count of characters
        if ($('#' + uniqueID + '_tag_input_field').val().length >= 3) {

            // set userpicker position in bottom of the user input
            $('#' + uniqueID + '_userpicker').css({
                position: "absolute",
                top: $('#' + uniqueID + '_tag_input_field').position().top + 30,
                left: $('#' + uniqueID + '_tag_input_field').position().left + 0
            })

            if (event.keyCode == 40) {

                // select next <li> element
                if (chosen === "") {
                    chosen = 1;
                } else if ((chosen + 1) < $('#' + uniqueID + '_userpicker li').length) {
                    chosen++;
                }
                $('#' + uniqueID + '_userpicker li').removeClass('selected');
                $('#' + uniqueID + '_userpicker li:eq(' + chosen + ')').addClass('selected');
                return false;

            } else if (event.keyCode == 38) {

                // select previous <li> element
                if (chosen === "") {
                    chosen = 1;
                } else if (chosen > 0) {
                    chosen--;
                }
                $('#' + uniqueID + '_userpicker li').removeClass('selected');
                $('#' + uniqueID + '_userpicker li:eq(' + chosen + ')').addClass('selected');
                return false;

            } else if (event.keyCode == 13 || event.keyCode == 9) {

                var href = $('#' + uniqueID + '_userpicker .selected a').attr('href');
                // simulate click event when href is not undefined.
                if (href !== undefined) {
                    window.location.href = href;
                }

            } else {

                // save the search string to variable
                var str = $('#' + uniqueID + '_tag_input_field').val();

                // show userpicker with the results
                $('#' + uniqueID + '_userpicker').show();

                // load users
                loadUser(str);
            }
        } else {

            // hide userpicker
            $('#' + uniqueID + '_userpicker').hide();
        }


    });


    $('#' + uniqueID + '_tag_input_field').focusout(function () {

        // set the plain text including user guids to the original input or textarea element
        $(options.inputId).val($.fn.userpicker.parseUserInput(uniqueID));
    });


    function loadUser(keyword) {

        // remove existings entries
        $('#' + uniqueID + '_userpicker li').remove();

        // show loader while loading
        $('#' + uniqueID + '_userpicker').html('<li><div class="loader"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></li>');

        // build data object
        var data = options['data'] || {};
        
        //This is the preferred way of adding the keyword
        if(options['searchUrl'].indexOf('-keywordPlaceholder-') < 0) {
            data['keyword'] = keyword;
        }
        
        //Set the user role filter
        if(options['userRole']) {
            data['userRole'] = options['userRole'];
        }

        jQuery.getJSON(options.searchUrl.replace('-keywordPlaceholder-', keyword), data, function (json) {

            // remove existings entries
            $('#' + uniqueID + '_userpicker li').remove();

            // sort by disabled/enabled and contains keyword
            json.sort(function(a,b) {
                if(a.disabled !== b.disabled) {
                    return (a.disabled < b.disabled) ? -1 : 1;
                } else if(a.priority !== b.priority) {
                    return (a.priority > b.priority) ? -1 : 1;
                }  else if(a.displayName.indexOf(keyword) >= 0 && b.displayName.indexOf(keyword) < 0) {
                    return -1;
                } else if(a.displayName.indexOf(keyword) < 0 && b.displayName.indexOf(keyword) >= 0) {
                      return 1;
                }
  
                return 0;
            });


            if (json.length > 0) {

                for (var i = 0; i < json.length; i++) {

                    var _takenStyle = "";
                    var _takenData = false;
                   
                    // set options to link, that this entry is already taken or not available
                    if (json[i].disabled == true || $('#' + uniqueID + '_' + json[i].guid).length || $('#'+json[i].guid).length || json[i].isMember == true || json[i].guid == options.userGuid) {
                        _takenStyle = "opacity: 0.4;"
                        _takenData = true;
                    }

                    // build <li> entry
                    var str = '<li id="user_' + json[i].guid + '"><a style="' + _takenStyle + '" data-taken="' + _takenData + '" tabindex="-1" href="javascript:$.fn.userpicker.addUserTag(\'' + json[i].guid + '\', \'' + json[i].image + '\', \'' + json[i].displayName.replace(/&#039;/g, "\\'") + '\', \'' + uniqueID + '\');"><img class="img-rounded" src="' + json[i].image + '" height="20" width="20" alt=""/> ' + json[i].displayName + '</a></li>';

                    // append the entry to the <ul> list
                    $('#' + uniqueID + '_userpicker').append(str);


                }

                // check if the list is empty
                if ($('#' + uniqueID + '_userpicker').children().length == 0) {
                    // hide userpicker, if it is
                    $('#' + uniqueID + '_userpicker').hide();
                }

                // reset the variable for arrows keys
                chosen = "";

            } else {

                // hide userpicker, if no user was found
                $('#' + uniqueID + '_userpicker').hide();
            }


            // remove hightlight
            $('#' + uniqueID + '_userpicker li').removeHighlight();

            // add new highlight matching strings
            $('#' + uniqueID + '_userpicker li').highlight(keyword);

            // add selection to the first space entry
            $('#' + uniqueID + '_userpicker li:eq(0)').addClass('selected');

        })
    }

    function showHintUser() {

        // remove hint, if exists
        $('#maxUsersHint').remove();

        // build html structure
        var _html = '<div id="maxUsersHint" style="display: none;" class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">x</button><strong>Sorry!</strong> You can add a maximum of ' + options.maxUsers + ' users as admin for this group.</div>';

        // add hint to DOM
        $('#' + uniqueID + '_invite_tags').after(_html);

        // fadein hint
        $('#maxUsersHint').fadeIn('fast');
    }


}


// Add an usertag for invitation
$.fn.userpicker.addUserTag = function (guid, image_url, name, id) {
    
    if ($('#user_' + guid + ' a').attr('data-taken') != "true") {
      
        // Building a new <li> entry
        var _tagcode = '<li class="userInput" id="' + id + '_' + guid + '"><img class="img-rounded" alt="24x24" data-src="holder.js/24x24" style="width: 24px; height: 24px;" src="' + image_url + '" alt="' + name + '" width="24" height="24" />' + name + '<i class="fa fa-times-circle"></i></li>';


        // insert the new created <li> entry into the <ul> construct
        $('#' + id + '_tag_input').before(_tagcode);

        // remove tag, by clicking the close icon
        $('#' + id + '_' + guid + " i").click(function () {

            // remove user tag
            $('#' + id + '_' + guid).remove();

            // reduce the count of added user
            userCount--;

        })

        // hide user results
        $('#' + id + '_userpicker').hide();

        // set focus to the input element
        $('#' + id + '_tag_input_field').focus();

        // Clear the textinput
        $('#' + id + '_tag_input_field').val('');

    }


}

$.fn.userpicker.parseUserInput = function (id) {

    // create and insert a dummy <div> element to work with
    $('#' + id + '_invite_tags').after('<div id="' + id + '_inputResult"></div>')

    // set html form input element to the new <div> element
    $('#' + id + '_inputResult').html($('#' + id + '_invite_tags').html());


    $('#' + id + '_inputResult .userInput').each(function () {


        // get user guid without unique userpicker id
        var pureID = this.id.replace(id + '_', '');

        // add the user guid as plain text
        $(this).after(pureID + ",");

        // remove the link
        $(this).remove();
    })

    // save the plain text
    var result = $('#' + id + '_inputResult').text();

    // remove the dummy <div> element
    $('#' + id + '_inputResult').remove();

    // return the plain text
    return result;

}

