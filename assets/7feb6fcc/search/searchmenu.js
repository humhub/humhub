/**
 * Handling search menu user input
 */

$(document).ready(function () {

    // declare variable for ajax request
    var _request;
    var chosenSearch = "";

    /**
     * Show and navigate through search resutls depends on user input
     */
    $('#search-menu-search').keyup(function (event) {


        if (event.keyCode == 40) {

            // select next <li> element
            if (chosenSearch === "") {
                chosenSearch = 1;
            } else if ((chosenSearch + 1) < $('#search-menu-dropdown li ul li').length) {
                chosenSearch++;
            }
            $('#search-menu-dropdown li ul li').removeClass('selected');
            $('#search-menu-dropdown li ul li:eq(' + chosenSearch + ')').addClass('selected');
            return false;

        } else if (event.keyCode == 38) {

            // select previous <li> element
            if (chosenSearch === "") {
                chosenSearch = 0;
            } else if (chosenSearch > 0) {
                chosenSearch--;
            }
            $('#search-menu-dropdown li ul li').removeClass('selected');
            $('#search-menu-dropdown li ul li:eq(' + chosenSearch + ')').addClass('selected');
            return false;

        } else if (event.keyCode == 13) {

            // checking if results existing
            if ($('#search-menu-dropdown li ul li').size() > 1) {
                // move to selected space, by hitting enter
                window.location.href = $('#search-menu-dropdown li ul li.selected a').attr('href');
            }

        } else {

            // show search reset icon
            $('#search-search-reset').fadeIn('fast');

            // empty variable
            chosenSearch = "";

            // create ajax object
            _request = jQuery.ajax();

            // get content form input field
            var _searchString = $(this).val();

            if (_searchString.length >= 3) {

                // cancel current ajax request
                _request.abort();

                $('#search-menu-dropdown li:not(:first)').remove();

                // DOM elements to show the loader
                var _menuStructure = '<li class="divider"></li><li><ul class="media-list"><li id="loader_search"><div class="loader"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></li></ul></li>';

                // add the DOM element to search dropdown menu
                $('#search-menu-dropdown').append(_menuStructure);

                _request = $.ajax({
                    'type': 'GET',
                    'url': searchAjaxUrl.replace('-searchKeyword-', _searchString),
                    'cache': false,
                    'data': jQuery(this).parents("form").serialize(),
                    'success': function (html) {
                        jQuery("#loader_search").replaceWith(html);

                        // add selection to the first space entry
                        $('#search-menu-dropdown li ul li:eq(0)').addClass('selected');

                        // add new highlight matching strings
                        $("#search-menu-dropdown li").highlight($('#search-menu-search').val());
                    }});

            } else {
                if (_searchString == 0) {
                    resetSearch();
                }
            }
        }

    })

    /**
     * Disable key events
     */
    $('#search-menu-search').keydown(function (event) {

        // deactivate the standard behavior for arrow keys
        if (event.keyCode == 40 || event.keyCode == 38 || event.keyCode == 13) {
            event.preventDefault();
        }
    })


    /**
     * Click handler to reset user input
     */
    $('#search-search-reset').click(function () {
        resetSearch();
    })


    /**
     * Reset user input
     */
    function resetSearch() {
        $('#search-search-reset').fadeOut('fast');
        $('#search-menu-search').val('');
        $('#search-menu-search').focus();

        $('#search-menu-dropdown li:not(:first)').remove();
    }
    
});