/**
 * Collapse panel
 */
function togglePanelUp($id) {
    $('#' + $id + ' .panel-body').slideUp("fast", function () {
        // Animation complete.
        $('#' + $id + ' .panel-collapse').hide();
        $('#' + $id + ' .panel-expand').show();
        $('#' + $id).addClass('panel-collapsed');

        localStorage.setItem('pm_' + $id, 'collapsed');
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
        $('#' + $id).removeClass('panel-collapsed');

        localStorage.removeItem('pm_' + $id);
    });
}

/**
 * Check and change current panel state, if necessary
 */
function checkPanelMenuCookie($id) {
    // checks if panel's saved state in LocalStorage is collapsed
    if (localStorage.getItem('pm_' + $id) === 'collapsed') {
       $('#' + $id + ' .panel-body').css({
            display: 'none'
        });

        // change menu to 'collapsed' state
        $('#' + $id + ' .panel-collapse').hide();
        $('#' + $id + ' .panel-expand').show();
        $('#' + $id).addClass('panel-collapsed');
    }
}
