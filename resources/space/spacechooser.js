/**
 * Handling space chooser user input
 */

$(document).ready(function () {

    var chosen = []; // Array for visible space menu entries
    var arrPosition = ""; // Save the current position inside the chosen array



    /**
     * Show and navigate through spaces depends on user input
     */
    $('#space-menu-search').keyup(function (event) {

        if (event.keyCode == 40) {

            // set current array position
            if (arrPosition === "") {
                arrPosition = 1;
            } else if ((arrPosition) < chosen.length - 1) {
                arrPosition++;
            }

            // remove selection from last space entry
            $('#space-menu-dropdown li ul li').removeClass('selected');

            // add selection to the current space entry
            $('#space-menu-dropdown li ul li:eq(' + chosen[arrPosition] + ')').addClass('selected');

            return false;

        } else if (event.keyCode == 38) {

            // set current array position
            if (arrPosition === "") {
                arrPosition = 1;
            } else if ((arrPosition) > 0) {
                arrPosition--;
            }

            $('#space-menu-dropdown li ul li').removeClass('selected');

            // add selection to the current space entry
            $('#space-menu-dropdown li ul li:eq(' + chosen[arrPosition] + ')').addClass('selected');

            return false;

        } else if (event.keyCode == 13) {

            // check if one space is selected
            if ($('#space-menu-spaces li').hasClass("selected")) {

                // move to selected space, by hitting enter
                window.location.href = $('#space-menu-dropdown li ul li.selected a').attr('href');
            }

        } else {

            // lowercase and save entered string in variable
            var input = $(this).val().toLowerCase();

            if (input > 0) {
                // remove max-height property to hide the nicescroll scrollbar
                $('#space-menu-spaces').css({'max-height': 'none'});
            } else {
                // set max-height property to show the nicescroll scrollbar
                $('#space-menu-spaces').css({'max-height': '400px'});
            }

            // empty variable and array
            chosen = [];
            arrPosition = "";

            $("#space-menu-dropdown li ul li").each(function (index) {

                // remove selected classes from all space entries
                $('#space-menu-dropdown li ul li').removeClass('selected');


                // lowercase and save space strings in variable
                var str = $(this).text().toLowerCase();

                if (str.search(input) == -1) {
                    // hide elements when not matched
                    $(this).css('display', 'none');
                } else {
                    // show elements when matched
                    $(this).css('display', 'block');

                    // update array with the right li element
                    chosen.push(index);
                }

            });


            // add selection to the first space entry
            $('#space-menu-dropdown li ul li:eq(' + chosen[0] + ')').addClass('selected');

            // check if entered string is empty or not
            if (input.length == 0) {
                // reset inputs
                resetSpaceSearch();
            } else {
                // show search reset icon
                $('#space-search-reset').fadeIn('fast');
            }

            // remove hightlight
            $("#space-menu-dropdown li ul li").removeHighlight();

            // add new highlight matching strings
            $("#space-menu-dropdown li ul li").highlight(input);


        }

        //return event.returnValue;

    });

    /**
     * Disable enter key
     */
    $('#space-menu-search').keypress(function (event) {
        if (event.keyCode == 13) {
            // deactivate the standard press event
            event.preventDefault();
            return false;
        }
    });


    /**
     * Click handler to reset user input
     */
    $('#space-search-reset').click(function () {
        resetSpaceSearch();
    });

    /**
     * Reset user input
     */
    function resetSpaceSearch() {

        // fade out the cross icon
        $('#space-search-reset').fadeOut('fast');

        // empty input field
        $('#space-menu-search').val('');

        // set focus to input field
        $('#space-menu-search').focus();

        $("#space-menu-dropdown li ul li").each(function () {

            // show all space entries
            $(this).css('display', 'block');

            // remove search result highlighting
            $("#space-menu-dropdown li ul li").removeHighlight();

            // remove the curren tspace entry selection
            $('#space-menu-dropdown li ul li').removeClass('selected');

        });

        // set max-height property to show the nicescroll scrollbar
        $('#space-menu-spaces').css({'max-height': '400px'});
    }

});