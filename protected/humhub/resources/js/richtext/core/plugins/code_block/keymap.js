import {Selection} from 'prosemirror-state';

function arrowHandler(dir) {
    return (state, dispatch, view) => {
        if (state.selection.empty && view.endOfTextblock(dir)) {
            const side = dir === "left" || dir === "up" ? -1 : 1, $head = state.selection.$head;
            const pos = side > 0 ? $head.after() : $head.before();
            const nextPos = Selection.near(state.doc.resolve(pos), side);
            if (nextPos.$head && nextPos.$head.parent.type.name === "code_block") {
                dispatch(state.tr.setSelection(nextPos));
                return true;
            }
        }
        return false;
    };
}

const keymap = () => {
    return {
        ArrowLeft: arrowHandler("left"),
        ArrowRight: arrowHandler("right"),
        ArrowUp: arrowHandler("up"),
        ArrowDown: arrowHandler("down")
    };
};

export {keymap};
