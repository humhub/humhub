/*
 * FlatElements v0.2 by @andystrobel
 * Copyright 2014 HumHub
 *
 * Modified standard checkboxes and radio buttons
 */

//
// create closure
//
(function ($) {
    //
    // plugin definition
    //
    $.fn.flatelements = function (options) {

        // build main options before element iteration
        var opts = $.extend({}, $.fn.flatelements.defaults, options);

        //
        // check if an element follow the bootstrap html construction
        //
        function checkBootstrapStructure($obj) {

            //alert($obj.parent().parent().attr('class'));

            if ($obj.attr('type') == "checkbox" && $obj.parent().prop("tagName") == "LABEL") {
                return true;

            } else if ($obj.attr('type') == "radio" && $obj.parent().prop("tagName") == "LABEL") {
                return true
            } else {
                return false;
            }
        }

        // iterate and reformat each matched element
        return this.each(function () {

            // save object in a variable
            $this = $(this);

            // Only modify this element, if it follow the bootstrap html construction
            if (checkBootstrapStructure($this) == true) {

                // modify elements for checkboxes
                if ($this.attr('type') == "checkbox") {

                    // make checkbox to a slider element
                    if ($this.attr('data-view') == 'slider') {

                        // check if the standard bootstrap container <div> and <label> exists
                        if ($this.parent().parent().attr('class') == "checkbox" && $this.parent().prop("tagName") == "LABEL") {

                            // remove existing container class
                            $this.parent().parent().removeClass('checkbox');

                            // add new container class
                            $this.parent().parent().addClass('onoffswitch');

                            // add new input class
                            $this.addClass('onoffswitch-checkbox');

                            // save label text
                            var _label = $.trim($this.parent().parent().text());

                            // remove label text
                            $this.parent().html($this.parent().find('input'));

                            // build new slider construct
                            var _newHTML = '<label class="onoffswitch-label" for="' + $this.attr('id') + '">' +
                                '<div class="onoffswitch-inner"></div>' +
                                '<div class="onoffswitch-switch"></div>' +
                                '</label>';

                            // add new slider construckt
                            $this.parent().append(_newHTML);

                            // build closing slider construct
                            _newHTML = '<label class="onoffswitch-label" for="' + $this.attr('id') + '">' + _label + '</label>' +
                                '<div class="onoffswitch-clear"></div>';

                            // add closing slider contruct
                            $this.parent().parent().after(_newHTML);

                            // remove the enclosing label tag
                            $this.parent().replaceWith($this.parent().html());

                        }


                    } else {

                        // check if the standard bootstrap container <div> exists
                        if ($this.parent().parent().attr('class') == "checkbox") {

                            // add new class
                            $this.parent().parent().addClass('regular-checkbox-container');

                            // add a new <div> at the end to clear floats
                            $this.parent().parent().append('<div class="regular-checkbox-clear"></div>');
                        }

                        // check if the standard bootstrap <label> exists
                        if ($this.parent().prop("tagName") == "LABEL") {

                            // if there is no assignment
                            if ($this.parent().attr('for') == undefined) {

                                // assign label to checkbox
                                $this.parent().attr('for', $this.attr('id'));

                                // add new checkbox element
                                $this.parent().append('<div class="regular-checkbox-box"></div>');
                            }
                        }

                        // add new class to checkbox
                        $this.addClass('regular-checkbox');
                    }

                    // modify elements for radio buttons
                } else if ($this.attr('type') == "radio") {

                    // check if the standard bootstrap container <div> exists
                    if ($this.parent().parent().attr('class') == "radio") {

                        // add new class
                        $this.parent().parent().addClass('regular-radio-container');

                    }

                    // check if the standard bootstrap <label> exists
                    if ($this.parent().prop("tagName") == "LABEL") {

                        // if there is no assignment
                        if ($this.parent().attr('for') == undefined) {

                            // assign label to radio element
                            $this.parent().attr('for', $this.attr('id'));

                            // add new radio element
                            $this.parent().append('<div class="regular-radio-button"></div>');
                        }
                    }

                    // add new class to radio element
                    $this.addClass('regular-radio');

                }

            }
        });

    };


    //
    // plugin defaults
    //
    $.fn.flatelements.defaults = {};
//
// end of closure
//
})(jQuery);