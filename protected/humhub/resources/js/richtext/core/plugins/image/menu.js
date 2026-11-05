/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {MenuItem, canInsert, canInsertLink} from "../../menu/";
import {TextField, openPrompt, SelectField} from "../../prompt";
import {NodeSelection} from "prosemirror-state";
import {validateHref} from "../../util/linkUtil";

function insertImageItem(context) {
    return new MenuItem({
        title: context.translate("Insert image"),
        label: context.translate("Image"),
        sortOrder: 100,
        enable(state) {
            return canInsert(state, context.schema.nodes.image) && canInsertLink(state);
        },
        run(state, _, view) {
            if (state.selection instanceof NodeSelection && state.selection.node.type === context.schema.nodes.image) {
                editNode(state.selection.node, context, view);
            } else {
                promt(context.translate("Insert image"), context, null, view);
            }
        }
    })
}

export function editNode(node, context, view) {
    promt(context.translate("Edit image"), context, node.attrs, view, node);
}

let isDefined = function (obj) {
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

let endsWith = function (val, suffix) {
    if (!isDefined(val) || !isDefined(suffix)) {
        return false;
    }
    return val.indexOf(suffix, val.length - suffix.length) !== -1;
};

export function promt(title, context, attrs, view, node) {
    let state = view.state;
    let {from, to} = state.selection;

    let cleanDimension = function (val) {
        val = val.trim();
        if (endsWith(val, 'px')) {
            val = val.substring(0, val.length - 2);
        }
        return val;
    };

    let validateDimension = function (val) {
        val = cleanDimension(val);

        if (val.length && !/^[0-9]+%?$/.test(val)) {
            return context.translate('Invalid dimension format used.');
        }
    };

    let validateSource = function (val) {
        if (!validateHref(val)) {
            return context.translate('Invalid image source.');
        }
    };

    openPrompt({
        title: title,
        fields: {
            src: new TextField({
                label: context.translate("Location"),
                required: true,
                value: attrs && attrs.src,
                validate: validateSource
            }),
            title: new TextField({label: context.translate("Title"), value: attrs && attrs.title}),
            alt: new TextField({
                label: context.translate("Description"),
                value: attrs ? attrs.alt : state.doc.textBetween(from, to, " ")
            }),
            width: new TextField({
                label: context.translate("Width"),
                value: attrs && attrs.width,
                clean: cleanDimension,
                validate: validateDimension
            }),
            height: new TextField({
                label: context.translate("Height"),
                value: attrs && attrs.height,
                clean: cleanDimension,
                validate: validateDimension
            }),
            float: new SelectField({
                label: context.translate("Position"),
                value: attrs && attrs.float,
                options: [
                    {label: context.translate("Normal"), value: 0},
                    {label: context.translate("Left"), value: 1},
                    {label: context.translate("Center"), value: 2},
                    {label: context.translate("Right"), value: 3}
                ]
            })
        },
        callback(attrs) {
            if (node && node.attrs.src === attrs.src) {
                attrs.fileGuid = node.attrs.fileGuid;
            }

            attrs.float = parseInt(attrs.float);

            view.dispatch(view.state.tr.replaceSelectionWith(context.schema.nodes.image.createAndFill(attrs)));
            view.focus();
        }
    });
}

export function menu(context) {
    return [{
        id: 'insertImage',
        node: 'image',
        group: 'insert',
        item: insertImageItem(context)
    }];
}
