/**
 * Lets the user choose a section of an image with Cropper.js, e.g. for the profile image or the banner.
 *
 * The options keep the names of the Jcrop plugin used before (`aspectRatio`, `minSize`, `setSelect`,
 * `bgColor`, `bgOpacity`, `boxWidth`, `boxHeight`, `onChange`, `keySupport`), all sizes and coordinates
 * are pixels of the image itself. With the keyboard the section is a control of its own: the arrow keys
 * move it (Shift: ten steps), + and - change its size (docs/ui/keyboard.md).
 *
 * @since 1.20
 */
humhub.module('ui.cropImage', function (module, require, $) {
    var TEMPLATE = '<cropper-canvas>'
        + '<cropper-image></cropper-image>'
        + '<cropper-shade hidden></cropper-shade>'
        + '<cropper-selection movable resizable>'
        + '<cropper-grid bordered covered aria-hidden="true"></cropper-grid>'
        + '<cropper-crosshair centered aria-hidden="true"></cropper-crosshair>'
        + '<cropper-handle action="move" theme-color="transparent" aria-hidden="true"></cropper-handle>'
        + ['n', 'e', 's', 'w', 'ne', 'nw', 'se', 'sw'].map(function (direction) {
            return '<cropper-handle action="' + direction + '-resize" aria-hidden="true"></cropper-handle>';
        }).join('')
        + '</cropper-selection>'
        + '</cropper-canvas>';

    var MOVE_KEYS = {ArrowLeft: [-1, 0], ArrowRight: [1, 0], ArrowUp: [0, -1], ArrowDown: [0, 1]};
    var RESIZE_KEYS = {'+': 1.1, '-': 1 / 1.1};

    var clamp = function (value, min, max) {
        return Math.min(Math.max(value, min), max);
    };

    /**
     * @returns {number} the factor that fits an image into the box, never above 1
     */
    var displayScale = function (width, height, boxWidth, boxHeight) {
        var scale = 1;
        if (boxWidth > 0) {
            scale = Math.min(scale, boxWidth / width);
        }
        if (boxHeight > 0) {
            scale = Math.min(scale, boxHeight / height);
        }
        return scale;
    };

    /**
     * @param {number[]} setSelect the rectangle `[x1, y1, x2, y2]` to start with
     * @returns {{x: number, y: number, width: number, height: number}} the rectangle inside the image,
     * narrowed to the aspect ratio around its center
     */
    var initialSelection = function (setSelect, aspectRatio, width, height) {
        var rect = (setSelect && setSelect.length === 4) ? setSelect.map(Number) : [0, 0, width, height];
        var x = clamp(Math.min(rect[0], rect[2]), 0, width);
        var y = clamp(Math.min(rect[1], rect[3]), 0, height);
        var w = clamp(Math.max(rect[0], rect[2]), 0, width) - x;
        var h = clamp(Math.max(rect[1], rect[3]), 0, height) - y;

        if (!(w > 0 && h > 0)) {
            x = 0;
            y = 0;
            w = width;
            h = height;
        }

        if (aspectRatio > 0) {
            if (w / h > aspectRatio) {
                x += (w - h * aspectRatio) / 2;
                w = h * aspectRatio;
            } else {
                y += (h - w / aspectRatio) / 2;
                h = w / aspectRatio;
            }
        }

        return {x: x, y: y, width: w, height: h};
    };

    /**
     * @param {{x: number, y: number, width: number, height: number}} selection on the shown image
     * @returns {{x: number, y: number, w: number, h: number, x2: number, y2: number}} the selection in
     * pixels of the image, as Jcrop reported it
     */
    var imageCoords = function (selection, scale, width, height) {
        var x = clamp(Math.round(selection.x / scale), 0, width);
        var y = clamp(Math.round(selection.y / scale), 0, height);
        var w = clamp(Math.round(selection.width / scale), 0, width - x);
        var h = clamp(Math.round(selection.height / scale), 0, height - y);
        return {x: x, y: y, w: w, h: h, x2: x + w, y2: y + h};
    };

    var loaded = function (image) {
        return new Promise(function (resolve) {
            if (image.complete && image.naturalWidth) {
                resolve();
            } else {
                image.addEventListener('load', resolve, {once: true});
            }
        });
    };

    var shadeColor = function (options) {
        var opacity = (options.bgOpacity === undefined) ? 0.6 : Number(options.bgOpacity);
        return 'color-mix(in srgb, ' + options.bgColor + ' ' + Math.round(opacity * 100) + '%, transparent)';
    };

    var inside = function (rect, bounds) {
        return rect.x >= 0 && rect.y >= 0 && rect.x + rect.width <= bounds.width && rect.y + rect.height <= bounds.height;
    };

    /**
     * Places the selection inside the shown image: a step beyond the edge stops at the edge
     */
    var place = function (selection, x, y, width, height, bounds) {
        if (width > bounds.width || height > bounds.height) {
            return;
        }
        selection.$change(clamp(x, 0, bounds.width - width), clamp(y, 0, bounds.height - height), width, height);
    };

    var addKeyboard = function (selection, options, bounds) {
        selection.setAttribute('tabindex', '0');
        selection.setAttribute('role', 'application');
        if (options.label) {
            selection.setAttribute('aria-label', options.label);
        }
        if (options.describedBy) {
            selection.setAttribute('aria-describedby', options.describedBy);
        }

        selection.addEventListener('keydown', function (event) {
            var step = event.shiftKey ? 10 : 1;
            if (MOVE_KEYS[event.key]) {
                place(
                    selection,
                    selection.x + MOVE_KEYS[event.key][0] * step,
                    selection.y + MOVE_KEYS[event.key][1] * step,
                    selection.width,
                    selection.height,
                    bounds
                );
            } else if (RESIZE_KEYS[event.key]) {
                var factor = Math.min(RESIZE_KEYS[event.key], bounds.width / selection.width, bounds.height / selection.height);
                var width = Math.round(selection.width * factor);
                var height = Math.round(selection.height * factor);
                place(
                    selection,
                    selection.x - (width - selection.width) / 2,
                    selection.y - (height - selection.height) / 2,
                    width,
                    height,
                    bounds
                );
            } else {
                return;
            }
            event.preventDefault();
        });
    };

    /**
     * @param {string|Element|jQuery} image the `<img>` to crop
     * @param {object} options see the module's description
     * @returns {Promise<Cropper>} resolves when the image is loaded and the crop area shown
     */
    var create = function (image, options) {
        image = $(image)[0];
        options = options || {};

        return loaded(image).then(function () {
            var width = image.naturalWidth;
            var height = image.naturalHeight;
            var scale = displayScale(width, height, Number(options.boxWidth), Number(options.boxHeight));
            var aspectRatio = Number(options.aspectRatio) || 0;
            var minSize = options.minSize || [0, 0];
            var onChange = (typeof options.onChange === 'function') ? options.onChange : $.noop;

            var cropper = new window.Cropper.Cropper(image, {template: TEMPLATE});
            var canvas = cropper.getCropperCanvas();
            var selection = cropper.getCropperSelection();
            var bounds = {width: Math.round(width * scale), height: Math.round(height * scale)};

            canvas.style.width = bounds.width + 'px';
            canvas.style.height = bounds.height + 'px';
            canvas.style.margin = '0 auto';

            if (options.bgColor) {
                cropper.container.querySelector('cropper-shade').setAttribute('theme-color', shadeColor(options));
            }

            selection.addEventListener('change', function (event) {
                var rect = event.detail;
                if (!inside(rect, bounds)) {
                    // A drag beyond the edge stops at the edge, a size beyond it is refused
                    event.preventDefault();
                    if (rect.width === selection.width && rect.height === selection.height) {
                        place(selection, rect.x, rect.y, rect.width, rect.height, bounds);
                    }
                    return;
                }

                var coords = imageCoords(rect, scale, width, height);
                if (coords.w < minSize[0] || coords.h < minSize[1]) {
                    event.preventDefault();
                    return;
                }
                onChange(coords);
            });

            if (options.keySupport !== false) {
                addKeyboard(selection, options, bounds);
            }

            var start = initialSelection(options.setSelect, aspectRatio, width, height);
            if (aspectRatio) {
                selection.setAttribute('aspect-ratio', String(aspectRatio));
            }
            selection.setAttribute('x', String(Math.round(start.x * scale)));
            selection.setAttribute('y', String(Math.round(start.y * scale)));
            selection.setAttribute('width', String(Math.round(start.width * scale)));
            selection.setAttribute('height', String(Math.round(start.height * scale)));
            onChange(imageCoords({x: start.x * scale, y: start.y * scale, width: start.width * scale, height: start.height * scale}, scale, width, height));

            return cropper;
        });
    };

    module.export({
        create: create,
        displayScale: displayScale,
        initialSelection: initialSelection,
        imageCoords: imageCoords,
    });
});
