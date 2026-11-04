/**
 * Test helpers of the richtext editor (protected/humhub/resources/js/richtext/): boots the editor from
 * its sources on a fixture page as the editor's own test page had it, and drives it.
 */
import { expect } from 'vitest';
import './standalone.js';
import '../../../resources/js/richtext/index.js';

document.body.innerHTML = '<div id="stage" class="ProsemirrorEditor"></div>'
    + '<div id="testContent" tabindex="0">Test content block</div>'
    + '<div id="result"></div>';

// jsdom lays nothing out, so jQuery's `:visible` (it checks the element's boxes) never
// matches: count an attached element as visible unless it or an ancestor is not displayed.
jQuery.expr.pseudos.visible = (element) => {
    if (!element.isConnected) {
        return false;
    }
    for (let node = element; node && node.nodeType === 1; node = node.parentNode) {
        if (getComputedStyle(node).display === 'none') {
            return false;
        }
    }
    return true;
};
jQuery.expr.pseudos.hidden = (element) => !jQuery.expr.pseudos.visible(element);

let editorInstance;
let richtextView;

export const initEditor = function (options, init) {
    localStorage.setItem("isExpandedToolbar", 'false');
    if (typeof options === 'string') {
        init = options;
        options = {};
    }

    options = options || {edit: true};

    editorInstance = new window.humhub.richtext.MarkdownEditor("#stage", options);

    editorInstance.init(init);

    return editorInstance;
};

export const initView = function (options, init) {
    if (typeof options === 'string') {
        init = options;
        options = {};
    }

    options = options || {edit: false};

    richtextView = new window.humhub.richtext.MarkdownView("#result", options);

    richtextView.init(init);

    return richtextView;
};

let getView = function (instance) {
    if (instance) {
        return instance;
    }

    if (!richtextView) {
        richtextView = initView();
    }

    return richtextView;
};

export { getView };

export const setViewText = function (s, view) {
    view = getView(view);
    view.$.html(htmlEncode(s));
}

export const viewToHtml = function (view) {
    view = getView(view);
    return view.$.html().replace(/(\r\n|\n|\r)/gm, "");
}

let htmlEncode = function (s) {
    return s.replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/'/g, '&#39;')
        .replace(/"/g, '&#quot;');
}

export const clearView = function (view) {
    view = getView(view);
    if (view) {
        view.clear();
    }
}

export const toHtml = function (selector = "#stage .ProseMirror") {
    return $(selector).html();
};

export const render = function (editor) {
    let result = getEditor(editor).render();
    $('#result').html(result);
    return result;
}

export const serialize = function (editor) {
    return getEditor(editor).serialize();
};

let getEditor = function (editor) {
    if (editor) {
        return editor;
    }

    if (editorInstance) {
        return editorInstance;
    }

    return initEditor();
};

export { getEditor };

export const type = function (word, editor, startPos) {
    // https://discuss.prosemirror.net/t/simulated-typing-for-integration-tests/807
    editor = getEditor(editor);

    let chars = word.split("");
    let transactions = [];
    let nextPos = startPos ? startPos : editor.view.state.doc.content.child(0).content.size + 1;

    if (editor.view.state.selection.from != nextPos) {
        setSelection(nextPos, null, editor);
    }

    for (let i = 0; i < word.length; i++) {
        let newTr = editor.view.state.tr.insertText(chars[i], nextPos);
        transactions.push(newTr);
        editor.view.dispatch(transactions[i]);

        nextPos = editor.view.state.doc.content.child(0).content.size + 1;
    }

    setSelection(nextPos, null, editor);
};

let insertText = function (text, from, to, editor) {
    editor = getEditor(editor);
    editor.view.dispatch(editor.view.state.tr.insertText(text, from, to));
};

export { insertText };

let setSelection = function (start, end, editor) {
    editor = getEditor(editor);

    if (!end) {
        end = start;
    }

    let selection = new prosemirror.state.TextSelection(editor.view.state.doc.resolve(start), editor.view.state.doc.resolve(end));
    editor.view.dispatch(editor.view.state.tr.setSelection(selection));
};

export { setSelection };

let clickMenuItem = function (id) {
    $('.ProseMirror-menu-' + id)[0].dispatchEvent(new Event('mousedown', {bubbles: true}));
};

let clickDropdownMenuItem = function (id, subId, subId2) {
    clickMenuItem(id);
    clickMenuItem(subId);
    if (subId2) {
        clickMenuItem(subId2);
    }
    // Manually close
    clickMenuItem(id);
};

let menuItemDisabled = function (id) {
    return $('.ProseMirror-menu-' + id).is('.ProseMirror-menu-disabled');
};

let pressKey = function (key, code, options) {
    options = options || {};
    options.key = key;
    options.code = code;
    $('.ProseMirror')[0].dispatchEvent(new KeyboardEvent('keydown', options));
}

let pressKeyArrowDown = function () {
    pressKey('ArrowDown', 40);
}

let pressKeyEnter = function () {
    pressKey('Enter', 13);
};

let pressKeyTab = function () {
    pressKey('Tab', 9);
};

let pressKeyShiftTab = function () {
    pressKey('Tab', 9, {shiftKey: true});
};

let pressKeyBackspace = function () {
    pressKey('Backspace', 8);
};

export { pressKey };
export { pressKeyEnter };
export { pressKeyTab };
export { pressKeyShiftTab };
export { pressKeyBackspace };
export { pressKeyArrowDown };
export { clickDropdownMenuItem };
export { clickMenuItem };
export { menuItemDisabled };

export const simulateInputRule = function (word, editor) {
    editor = getEditor(editor);
    let trigger = word.slice(-1);
    let input = word.substr(0, word.length - 1);

    type(input, editor);

    return editor.view.someProp("handleTextInput", function (f) {
        return f(editor.view, word.length, word.length, trigger);
    });
};

export const selectSource = function (start, end, direction, editor) {
    editor = getEditor(editor);
    editor.context.$source[0].setSelectionRange(start, end, direction);
}

export const expectMenuItemNotVisible = function (selector) {
    expect($('.ProseMirror-menu-' + selector).is(':visible')).to.be.false;
}

export const expectMenuItemVisible = function (selector) {
    expect($('.ProseMirror-menu-' + selector).is(':visible')).to.be.true;
}
