/**
 * Collapse panel
 */
function togglePanelUp($id) {
    $('#' + $id + ' .panel-body').slideUp("fast", function () {
        // Animation complete.
        $('#' + $id + ' .panel-collapse').hide();
        $('#' + $id + ' .panel-expand').show();

        $.cookie('pm_' + $id, 'collapsed', 5*365);
    });
}

/**
 * Expand panel
 */
function togglePanelDown($id) {
    $('#' + $id + ' .panel-body').slideDown("fast", function () {
        // Animation complete.
        $('#' + $id + ' .panel-expand').hide();
        $('#' + $id + ' .panel-collapse').show();

        $.cookie('pm_' + $id, 'expanded', 5*365);
    });
}

/**
 * Check and change current panel state, if necessary
 */
function checkPanelMenuCookie($id) {

    // check if cookie exists
    if ($.cookie('pm_' + $id) == undefined) {

        // if not, create new cookie with current panel state
        $.cookie('pm_' + $id, 'expanded', 5*365);
    } else if ($.cookie('pm_' + $id) == 'collapsed') {

       // collapse panel, if cookie is 'collapsed'
       $('#' + $id + ' .panel-body').css({
            overflow: 'hidden',
            display: 'none'
        });

        // change menu to 'collapsed' state
        $('#' + $id + ' .panel-collapse').hide();
        $('#' + $id + ' .panel-expand').show();

    }
}

