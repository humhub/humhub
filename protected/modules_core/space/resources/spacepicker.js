/*
 * SpacePicker
 * Version 1.0.0
 * Written by: Andreas Strobel
 *
 * @property String $inputId is the ID of the input HTML Element
 * @property Int $maxSpaces the maximum of users in this dropdown
 * @property String $spaceSearchUrl the url of the search, to find the spaces
 * @property String $currentValue is the current value of the parent field.
 * @property String $templates.inputStructure is the HTML structure to replace the original input element.
 *
 * License: MIT License - http://www.opensource.org/licenses/mit-license.php
 */


var chosen = "";
var spaceCount = 0;

$.fn.spacepicker = function(options) {

    // set standard options
    options = $.extend({
        inputId: "",
        maxSpaces: 0,
        searchUrl: "",
        currentValue: "",
        templates: {
            inputStructure: '<div class="space_picker_container"><ul class="tag_input" id="space_tags"><li id="space_tag_input"><input type="text" id="space_input_field" class="tag_input_field" value="" autocomplete="off" placeholder="Add a space"></li></ul><ul class="dropdown-menu" id="spacepicker" role="menu" aria-labelledby="dropdownMenu"></ul></div>'
        }

    }, options);


    init();


    function init() {

        // remove picker if existing
        $('.space_picker_container').remove();

        // insert the new input structure after the original input element
        $(options.inputId).after(options.templates.inputStructure);

        // hide original input element
        $(options.inputId).hide();

        if (options.currentValue != "") {

            // restore data from database
            restoreSpaceTags(options.currentValue);
        }

        // simulate focus in
        $('#space_input_field').focusin(function() {
            $('#space_tags').addClass('focus');
        })

        // simulate focus out
        $('#space_input_field').focusout(function() {
            $('#space_tags').removeClass('focus');
        })
    }

    function restoreSpaceTags(html) {

        // add html structure for input element
        $('#space_tags').prepend(html);

        // create function for every space tag to remove the element
        $('#space_tags .spaceInput i').each(function() {

            $(this).click(function() {

                // remove user tag
                $(this).parent().remove();

                // reduce the count of added spaces
                spaceCount--;

            })

            // raise the count of added spaces
            spaceCount++;

        })


    }


    // Set focus on the input field, by clicking the <ul> construct
    jQuery('#space_tags').click(function() {

        // set focus
        $('#space_input_field').focus();
    })

    $('#space_input_field').keydown(function(event) {

        // by pressing the tab key an the input is empty
        if ($(this).val() == "" && event.keyCode == 9) {

            //do nothing

            // by pressing enter, tab, up or down arrow
        } else if (event.keyCode == 40 || event.keyCode == 38 || event.keyCode == 13 || event.keyCode == 9) {

            // ... disable the default behavior to hold the cursor at the end of the string
            event.preventDefault();

        }

        // if there is a space limit and the user didn't press the tab key
        if (options.maxSpaces != 0 && event.keyCode != 9) {

            // if the max space count is reached
            if (spaceCount == options.maxSpaces) {

                // show hint
                showHintSpaces();

                // block input events
                event.preventDefault();
            }
        }

    })

    $('#space_input_field').keyup(function(event) {

        // start search after a specific count of characters
        if ($('#space_input_field').val().length >= 3) {

            // set spacepicker position in bottom of the space input
            $('#spacepicker').css({
                position: "fixed",
                top: $('#space_input_field').offset().top + 30,
                left: $('#space_input_field').offset().left + 2
            })

            if (event.keyCode == 40) {

                // select next <li> element
                if (chosen === "") {
                    chosen = 1;
                } else if ((chosen + 1) < $('#spacepicker li').length) {
                    chosen++;
                }
                $('#spacepicker li').removeClass('selected');
                $('#spacepicker li:eq(' + chosen + ')').addClass('selected');
                return false;

            } else if (event.keyCode == 38) {

                // select previous <li> element
                if (chosen === "") {
                    chosen = 1;
                } else if (chosen > 0) {
                    chosen--;
                }
                $('#spacepicker li').removeClass('selected');
                $('#spacepicker li:eq(' + chosen + ')').addClass('selected');
                return false;

            } else if (event.keyCode == 13 || event.keyCode == 9) {

                var href = $('#spacepicker .selected a').attr('href');
                // simulate click event when href is not undefined.
                if (href !== undefined) {
                    window.location.href = href;
                }

            } else {

                // save the search string to variable
                var str = $('#space_input_field').val();

                // show spacepicker with the results
                $('#spacepicker').show();

                // load spaces
                loadSpaces(str);
            }
        } else {

            // hide spacepicker
            $('#spacepicker').hide();
        }


    })


    $('#space_input_field').focusout(function() {

        // set the plain text including user guids to the original input or textarea element
        $(options.inputId).val(parseSpaceInput());
    })


    function loadSpaces(string) {

        // remove existings entries
        $('#spacepicker li').remove();

        // show loader while loading
        $('#spacepicker').html('<li><div class="loader"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></li>');

        jQuery.getJSON(options.searchUrl.replace('-keywordPlaceholder-', string), function(json) {

            // remove existings entries
            $('#spacepicker li').remove();


            if (json.length > 0) {


                for (var i = 0; i < json.length; i++) {
                    // build <li> entry
                    var str = '<li><a tabindex="-1" href="javascript:addSpaceTag(\'' + json[i].guid + '\', \'' + json[i].image + '\', \'' + addslashes(htmlDecode(json[i].title)) + '\');"><img class="img-rounded" src="' + json[i].image + '" height="20" width="20" alt=""/> ' + json[i].title + '</a></li>';

                    // append the entry to the <ul> list
                    $('#spacepicker').append(str);

                }

                // reset the variable for arrows keys
                chosen = "";

            } else {

                // hide spacepicker, if no space was found
                $('#spacepicker').hide();
            }


            // remove hightlight
            $("#spacepicker li").removeHighlight();

            // add new highlight matching strings
            $("#spacepicker li").highlight(string);

            // add selection to the first space entry
            $('#spacepicker li:eq(0)').addClass('selected');

        })
    }

    function showHintSpaces() {

        // remove hint, if exists
        $('#maxSpaceHint').remove();

        // build html structure
        var _html = '<div id="maxSpaceHint" style="display: none;" class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><strong>Sorry!</strong> You can add a maximum of ' + options.maxSpaces + ' default spaces for this group.</div>';

        // add hint to DOM
        $('#space_tags').after(_html);

        // fadein hint
        $('#maxSpaceHint').fadeIn('fast');
    }

}

// Add a space tag for invitation
function addSpaceTag(guid, image_url, name) {

    // Building a new <li> entry
    var _tagcode = '<li class="spaceInput" id="' + guid + '"><img class="img-rounded" src="' + image_url + '" alt="' + name + '" width="24" height="24" alt="24x24" data-src="holder.js/24x24" style="width: 24px; height: 24px;" />' + name + '<i class="fa fa-times-circle"></i></li>';


    // insert the new created <li> entry into the <ul> contruct
    $('#space_tag_input').before(_tagcode);


    // remove tag, by clicking the close icon
    $('#' + guid + " i").click(function() {

        // remove space tag
        $('#' + guid).remove();

        // reduce the count of added spaces
        spaceCount--;

    })

    // hide space results
    $('#spacepicker').hide();

    // set focus to the input element
    $('#space_input_field').focus();

    // Clear the textinput
    $('#space_input_field').val('');

    // raise the count of added spaces
    spaceCount++;


}

function parseSpaceInput() {

    // create and insert a dummy <div> element to work with
    $('#space_tags').after('<div id="spaceInputResult"></div>')

    // set html form input element to the new <div> element
    $('#spaceInputResult').html($('#space_tags').html());


    $('#spaceInputResult .spaceInput').each(function() {

        // add the space guid as plain text
        $(this).after(this.id + ",");

        // remove the link
        $(this).remove();
    })

    // save the plain text
    var result = $('#spaceInputResult').text();

    // remove the dummy <div> element
    $('#spaceInputResult').remove();

// return the plain text
    return result;

}

function addslashes(str) {

	return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

function htmlDecode(value) {
    return $("<textarea/>").html(value).text();
}