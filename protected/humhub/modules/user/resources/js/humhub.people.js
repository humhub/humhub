humhub.module('people', function(module, require, $) {

    var rotateCard = function(evt) {
        $(evt.$trigger).closest('.card > div').toggleClass('card-rotated');
    };

    module.export({
        rotateCard
    });
});
