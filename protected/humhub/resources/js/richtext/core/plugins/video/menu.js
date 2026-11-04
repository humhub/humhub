/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {CheckboxField, openPrompt, TextField, SelectField} from "../../prompt";
import {validateHref} from "../../util/linkUtil";

const BOOLEAN_FIELDS = ['controls', 'autoplay', 'muted', 'loop'];
const isDefined = function (obj) {
    if (arguments.length > 1) {
        let result = true;
        this.each(arguments, function (index, value) {
            if (!isDefined(value)) {
                return false;
            }
        });

        return result;
    }
    return typeof obj !== 'undefined';
};

const endsWith = function (val, suffix) {
    if (!isDefined(val) || !isDefined(suffix)) {
        return false;
    }
    return val.indexOf(suffix, val.length - suffix.length) !== -1;
};

const editNode = (node, context, view) => {
    const cleanDimension = function (val) {
        val = val.trim();
        if (endsWith(val, 'px')) {
            val = val.substring(0, val.length - 2);
        }
        return val;
    };

    const validateDimension = function (val) {
        val = cleanDimension(val);

        if (val.length && !/^[0-9]+%?$/.test(val)) {
            return context.translate('Invalid dimension format used.');
        }
    };

    const validateSource = function (val) {
        if (!validateHref(val)) {
            return context.translate('Invalid video source.');
        }
    };

    openPrompt({
        title: context.translate("Edit video"),
        fields: {
            src: new TextField({
                label: context.translate("Location"),
                required: true,
                value: node.attrs.src,
                validate: validateSource
            }),
            title: new TextField({
                label: context.translate("Title"),
                value: node.attrs.title
            }),
            width: new TextField({
                label: context.translate("Width"),
                value: node.attrs.width,
                clean: cleanDimension,
                validate: validateDimension
            }),
            height: new TextField({
                label: context.translate("Height"),
                value: node.attrs.height,
                clean: cleanDimension,
                validate: validateDimension
            }),
            float: new SelectField({
                label: context.translate("Position"),
                value: node.attrs.float,
                options: [
                    {label: context.translate("Normal"), value: 0},
                    {label: context.translate("Left"), value: 1},
                    {label: context.translate("Center"), value: 2},
                    {label: context.translate("Right"), value: 3}
                ]
            }),
            controls: new CheckboxField({
                label: context.translate("Show controls"),
                value: node.attrs.controls
            }),
            autoplay: new CheckboxField({
                label: context.translate("Autoplay"),
                value: node.attrs.autoplay
            }),
            muted: new CheckboxField({
                label: context.translate("Muted"),
                value: node.attrs.muted
            }),
            loop: new CheckboxField({
                label: context.translate("Loop"),
                value: node.attrs.loop
            }),
        },
        callback(attrs) {
            const nextAttrs = Object.assign({}, node.attrs);
            nextAttrs.src = attrs.src;
            nextAttrs.title = attrs.title;
            nextAttrs.width = attrs.width;
            nextAttrs.height = attrs.height;
            nextAttrs.float = parseInt(attrs.float);
            BOOLEAN_FIELDS.forEach((field) => {
                nextAttrs[field] = !!attrs[field];
            });

            view.dispatch(view.state.tr.replaceSelectionWith(context.schema.nodes.video.create(nextAttrs)));
            view.focus();
        }
    });
};

export {editNode};
