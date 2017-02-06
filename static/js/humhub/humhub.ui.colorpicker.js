/**
 * Module for creating an manipulating modal dialoges.
 * Normal layout of a dialog:
 * 
 * <div class="modal">
 *     <div class="modal-dialog">
 *         <div class="modal-content">
 *             <div class="modal-header"></div>
 *             <div class="modal-body"></div>
 *             <div class="modal-footer"></div>
 *         </div>
 *     </div>
 * </div>
 *  
 * @param {type} param1
 * @param {type} param2
 */
humhub.module('ui.colorpicker', function (module, require, $) {

    module.initOnPjaxLoad = false;

    var apply = function (container, input, color) {
        var $container = $(container);
        $container.colorpicker({
            format: 'hex',
            color: color,
            'align': 'left',
            horizontal: false,
            component: '.input-group-addon',
            input: input
        });
        
        // Add hex input field to color picker
        $container.on('create', function () {
            var picker = $(this).data('colorpicker');
            picker.picker.css('z-index', '3000');
            if (!picker.picker.find('.hexInput').length) {

                var $colorPickerHexInput = $('<input type="text" class="hexInput" style="border:0px;outline: none;width:120px;" value="' + picker.color.toHex() + '"></input>');
                picker.picker.append($colorPickerHexInput);
                
                $colorPickerHexInput.on('change', function () {
                    picker.color.setColor($(this).val());
                    picker.update();
                }).on('click', function (event) {
                    $colorPickerHexInput.focus();
                    event.stopPropagation();
                    event.preventDefault();
                }).on('keydown', function (e) {
                    var keyCode = e.keyCode || e.which;
                    // Close On Tab
                    if (keyCode === 9) {
                        e.preventDefault();
                        picker.hide();
                        $('#space-name').focus();
                    }
                });
            }
        });

        $container.on('showPicker', function () {
            $(this).data('colorpicker').picker.find('.hexInput').select();
        });

        $container.on('changeColor', function () {
            var picker = $(this).data('colorpicker');
            picker.picker.find('.hexInput').val(picker.color.toHex());
        });
    };

    module.export({
        apply: apply
    });
});