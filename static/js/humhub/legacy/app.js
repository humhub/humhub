
/**
 * @deprecated since v1.2
 */
function setModalLoader() {
    $(".modal-footer .btn").hide();
    $(".modal-footer .loader").removeClass("hidden");
}

/**
 * USED in  fileuploader.js
 */
function htmlEncode(value) {
    //create a in-memory div, set it's inner text(which jQuery automatically encodes)
    //then grab the encoded contents back out.  The div never exists on the page.
    return $('<div/>').text(value).html();
}

/**
 * Used in spacepicker.js
 */
function htmlDecode(value) {
    return $('<div/>').html(value).text();
}


/**
 * Dummy method for compatibility reasons (prio 1.2)
 *
 * @deprecated since 1.2
 * @returns string
 */
function parseHtml(htmlString) {
    return htmlString;
}


/**
 * Used previously to format/fix time fields on focus out
 * 
 * @deprecated 1.2
 */
$.fn.format = function (options) {;}