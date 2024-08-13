/**
 * Handles conflicting jquery ui and bootstrap namespaces.
 */

$.widget.bridge('uibutton', $.ui.button);
$.widget.bridge('uitooltip', $.ui.tooltip);