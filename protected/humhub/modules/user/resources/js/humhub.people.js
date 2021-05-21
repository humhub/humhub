humhub.module('people', function(module, require, $) {

    const applyFilters = function(evt) {
        $(evt.$trigger).closest('form').submit();
    }

    module.export({
        applyFilters,
    });
});
