import {Plugin, Selection, TextSelection} from 'prosemirror-state';
import {redo, undo} from "prosemirror-history";
import {exitCode} from "prosemirror-commands";

import {EditorView as CodeMirror, keymap as cmKeymap, drawSelection, lineNumbers} from "@codemirror/view";
import {defaultKeymap, indentWithTab} from "@codemirror/commands";
import {defaultHighlightStyle, syntaxHighlighting} from "@codemirror/language";
import {javascript} from "@codemirror/lang-javascript";

class CodeBlockView {
    constructor(node, view, getPos) {
        // Store for later
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        // Create a CodeMirror instance
        this.cm = new CodeMirror({
            doc: this.node.textContent,
            extensions: [
                cmKeymap.of([
                    ...this.codeMirrorKeymap(),
                    indentWithTab,
                    ...defaultKeymap
                ]),
                drawSelection(),
                lineNumbers(),
                javascript(),
                syntaxHighlighting(defaultHighlightStyle),
                CodeMirror.updateListener.of(update => this.forwardUpdate(update))
            ]
        });

        // The editor's outer node is our DOM representation
        this.dom = this.cm.dom;

        // This flag is used to avoid an update loop between the outer and inner editor
        this.updating = false;
    }

    forwardUpdate(update) {
        if (this.updating || !this.cm.hasFocus) return;

        let codePos = this.getPos(), {main} = update.state.selection;
        let offset = codePos + 1;
        let selFrom = offset + main.from, selTo = offset + main.to;
        let pmSel = this.view.state.selection;

        if (update.docChanged || pmSel.from !== selFrom || pmSel.to !== selTo) {
            let tr = this.view.state.tr;

            update.changes.iterChanges((fromA, toA, fromB, toB, text) => {
                const from_A = offset + fromA;
                const to_A = offset + toA;

                if (text.length)
                    tr.replaceWith(from_A, to_A, this.view.state.schema.text(text.toString()));
                else
                    tr.delete(from_A, to_A);
                offset += (toB - fromB) - (toA - fromA);
            });

            tr.setSelection(TextSelection.create(tr.doc, selFrom, selTo));
            this.view.dispatch(tr);
        }
    }

    setSelection(anchor, head) {
        this.cm.focus();
        this.updating = true;
        this.cm.dispatch({selection: {anchor, head}});
        this.updating = false;
    }

    codeMirrorKeymap() {
        const view = this.view;
        return [
            {key: "ArrowUp", run: () => this.maybeEscape("line", -1)},
            {key: "ArrowLeft", run: () => this.maybeEscape("char", -1)},
            {key: "ArrowDown", run: () => this.maybeEscape("line", 1)},
            {key: "ArrowRight", run: () => this.maybeEscape("char", 1)},
            {key: "Ctrl-Enter", run: () => this.escapeBlock()},
            {key: "Ctrl-z", mac: "Cmd-z",
                run: () => undo(view.state, view.dispatch)},
            {key: "Shift-Ctrl-z", mac: "Shift-Cmd-z",
                run: () => redo(view.state, view.dispatch)},
            {key: "Ctrl-y", mac: "Cmd-y",
                run: () => redo(view.state, view.dispatch)}
        ];
    }

    escapeBlock() {
        if (!exitCode(this.view.state, this.view.dispatch)) return false;
        this.view.focus();
        return true;
    }

    maybeEscape(unit, dir) {
        let {state} = this.cm, {main} = state.selection;
        if (!main.empty) return false;
        if (unit === "line") main = state.doc.lineAt(main.head);
        if (dir < 0 ? main.from > 0 : main.to < state.doc.length) return false;
        const targetPos = this.getPos() + (dir < 0 ? 0 : this.node.nodeSize);
        if (dir > 0 && main.to === state.doc.length && targetPos === this.view.state.doc.content.size) {
            this.escapeBlock();
        }
        const selection = Selection.near(this.view.state.doc.resolve(targetPos), dir);
        const tr = this.view.state.tr.setSelection(selection).scrollIntoView();
        this.view.dispatch(tr);
        this.view.focus();
    }

    update(node) {
        if (node.type !== this.node.type) return false;
        this.node = node;
        if (this.updating) return true;
        let newText = node.textContent, curText = this.cm.state.doc.toString();

        if (newText !== curText) {
            let start = 0, curEnd = curText.length, newEnd = newText.length;
            while (start < curEnd && curText.charCodeAt(start) === newText.charCodeAt(start)) {
                ++start;
            }
            while (curEnd > start && newEnd > start && curText.charCodeAt(curEnd - 1) === newText.charCodeAt(newEnd - 1)) {
                curEnd--;
                newEnd--;
            }
            this.updating = true;
            this.cm.dispatch({
                changes: {
                    from: start, to: curEnd,
                    insert: newText.slice(start, newEnd)
                }
            });
            this.updating = false;
        }

        return true;
    }

    selectNode() {
        this.cm.focus();
    }

    stopEvent() {
        return true;
    }
}

const codeBlockPlugin = (context) => {
    return new Plugin({
        props: {
            nodeViews: {
                code_block: (node, view, getPos) => new CodeBlockView(node, view, getPos)
            }
        }
    });
};

export {codeBlockPlugin};
