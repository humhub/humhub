/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {TextSelection} from 'prosemirror-state';
import {toggleMark} from "prosemirror-commands";

import {MenuItem, icons, markActive, canInsertLink} from "../../menu/menu";
import {openPrompt, TextField} from "../../prompt";
import {validateHref, validateRelative} from "../../util/linkUtil";

function linkItem(context) {
    let mark = context.schema.marks.link;
    return new MenuItem({
        title: context.translate("Add or remove link"),
        sortOrder: 500,
        icon: icons.link,
        active(state) {
            return markActive(state, mark)
        },
        enable(state) {
            if (state.selection.empty) {
                return false;
            }

            return canInsertLink(state);
        },
        run(state, dispatch, view) {
            if (markActive(state, mark)) {
                toggleMark(mark)(state, dispatch);
                return true;
            }

            const { $from, $to } = state.selection;
            const node = $from.node($from.depth);
            const nodeText = node.textBetween($from.parentOffset, $to.parentOffset, "\n");
            const attrs = {text: nodeText, title: nodeText, href: validateHref(nodeText, {anchor: '#'}) ? nodeText : ''};

            promt(context.translate("Create a link"), context, $.extend({}, mark.attrs, attrs));
        }
    })
}

export function editNode(dom, context) {
    const doc = context.editor.view.state.doc;
    const view = context.editor.view;

    const nodePos = view.posAtDOM(dom);
    const node = doc.nodeAt(nodePos);

    if (node.type !== context.schema.nodes.text) {
        return;
    }

    const $pos = doc.resolve(nodePos);
    const $end = $pos.node(0).resolve($pos.pos + node.nodeSize);
    view.dispatch(view.state.tr.setSelection(new TextSelection($pos, $end)).scrollIntoView());

    const mark = getLinkMark(node, context);

    promt(context.translate("Edit link"), context, $.extend({}, mark.attrs, {text: node.text}), node, mark);
}

export function promt(title, context, attrs, node, mark) {
    const view = context.editor.view;

    const fields = {
        text: new TextField({label: "Text", value: attrs && attrs.text}),
        href: new TextField({
            label: context.translate("Link target"),
            value: attrs && attrs.href,
            required: true,
            clean: (val) => {
                if (!validateHref(val, {anchor: '#'}) && !validateRelative(val))  {
                    return 'https://' + val;
                }

                return val;
            }
        }),
        title: new TextField({label: "Title", value: attrs && attrs.title})
    };

    if (!node) {
        delete fields['text'];
    }

    openPrompt({
        title,
        fields,
        callback(attrs) {
            if (node) {
                if (mark.attrs.href === attrs.href) {
                    attrs.fileGuid = mark.attrs.fileGuid;
                }
                const newLinkMark = context.schema.marks.link.create(attrs);
                const newLinkNode = context.schema.text(attrs.text).mark([newLinkMark]);

                view.dispatch(view.state.tr.replaceSelectionWith(newLinkNode, false));
            } else {
                toggleMark(context.schema.marks.link, attrs)(view.state, view.dispatch);
            }
            view.focus();
        }
    });
}

function getLinkMark(node, context) {
    let result = null;
    node.marks.forEach((mark) => {
        if (mark.type === context.schema.marks.link) {
            result = mark;
        }
    });

    return result;
}

export function menu(context) {
    return [{
        id: 'linkItem',
        mark: 'link',
        group: 'marks',
        item: linkItem(context)
    }];
}
