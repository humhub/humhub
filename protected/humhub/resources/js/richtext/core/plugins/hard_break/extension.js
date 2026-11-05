
import MarkdownIt from "markdown-it"

let inst = new MarkdownIt();
let isSpace = inst.utils.isSpace;

function htmlBreak(state, silent) {
    const max = state.posMax;
    const start  = state.pos;
    let pos = state.pos;

    if (silent) { return false; } // don't run any pairs in validation mode
    //if (state.src.charCodeAt(start) !== 0x3c/* < */) { return false; }
    if (state.src.substr(start, 4) !== '<br>') { return false; }

    state.push('hardbreak', 'br', 0);

    pos = pos + 4;

    // skip heading spaces for next line
    while (pos < max && isSpace(state.src.charCodeAt(pos))) {
        pos++;
    }

    state.pos = pos;
    return true;
}

export {htmlBreak}
