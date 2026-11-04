/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {CheckboxField, openPrompt, TextField, SelectField} from "../../prompt";
import {validateHref} from "../../util/linkUtil";

const BOOLEAN_FIELDS = ['controls', 'autoplay', 'muted', 'loop'];

const editNode = (node, context, view) => {
    const validateSource = function (val) {
        if (!validateHref(val)) {
            return context.translate('Invalid audio source.');
        }
    };

    openPrompt({
        title: context.translate("Edit audio"),
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
            nextAttrs.float = parseInt(attrs.float);
            BOOLEAN_FIELDS.forEach((field) => {
                nextAttrs[field] = !!attrs[field];
            });

            view.dispatch(view.state.tr.replaceSelectionWith(context.schema.nodes.audio.create(nextAttrs)));
            view.focus();
        }
    });
};

export {editNode};
