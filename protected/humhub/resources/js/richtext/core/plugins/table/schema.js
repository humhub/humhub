import {tableNodes} from 'prosemirror-tables';

let nodes = tableNodes({
    tableGroup: "block",
    cellContent: "paragraph+",
    cellAttributes: {
        style: {
            default: null,
            getFromDOM(dom) {
                return dom.style;
            },
            setDOMAttr(value, attrs) {
                if (value) {
                    attrs.style = value
                }
            }
        }
    }
});

nodes.table_row.parseMarkdown = {tr: {block: "table_row"}};
nodes.table_header.parseMarkdown = {th: {block: "table_header"}};
nodes.table_cell.parseMarkdown = {td: {block: "table_cell"}};

nodes = Object.assign(nodes, {
    table: {
        content: "(table_row+ | table_head | table_body | table_foot)",
        tableRole: "table",
        isolating: false,
        group: "block",
        parseDOM: [{tag: "table"}],
        toDOM: () => {
            return ["table", ["tbody", 0]]
        },
        toMarkdown: (state, node) => {
            renderTable(state,node);
        },
        parseMarkdown: {block: "table"}
    },
    table_head: {
        content: "table_row*",
        tableRole: "head",
        parseDOM: [{tag: "thead"}],
        toDOM: () => {
            return ["thead", 0]
        },
        parseMarkdown: {thead: {block: "table_head"}}
    },
    table_body: {
        content: "table_row*",
        tableRole: "body",
        parseDOM: [{tag: "tbody"}],
        toDOM: () => {
            return ["tbody", 0]
        },
        parseMarkdown: {tbody: {block: "table_body"}}
    },
    table_foot: {
        content: "table_row*",
        tableRole: "foot",
        parseDOM: [{tag: "tfoot"}],
        toDOM: () => {
            return ["tfoot", 0]
        },
        parseMarkdown: {tfoot: {block: "table_foot"}}
    }
});

let renderTable = function(state, node, withHead) {
    state.table = true;

    if(typeof withHead === 'undefined') {
        withHead = true;
    }

    node.forEach(function (child, _, i) {
        if(child.type.name === 'table_body' || child.type.name === 'table_head') {
            renderTable(state, child, i === 0);
        } else if(withHead && i === 0) {
            renderHeadRow(state,child);
        } else {
            renderRow(state, child);
        }

        if(i !== (node.childCount -1)) {
            state.write("\n");
        }
    });

    state.table = false;
    state.closeBlock(node);
};

let renderHeadRow = function(state, node) {
    renderRow(state,node);
    state.write("\n");
    renderRow(state,node, true);
};

let renderRow = function(state, node, headMarker) {
    state.write('|');
    node.forEach(function (child, _, i) {
        renderCell(state, child, headMarker);
    });
};

let renderCell = function(state, node, headMarker) {
    state.write(' ');
    if(headMarker) {
        (node.textContent.length) ? state.write(state.repeat('-', node.textContent.length)) : state.write('---');
       /* if(node.attrs.style && node.attrs.style.indexOf("text-align:right") >= 0) {
            state.write(':');
        } else {
            state.write(' ');
        }*/
        state.write(' ');
    } else {
        state.renderContent(node);

        state.write(' ');
    }
    state.write('|');
};


const schema = {
    nodes: nodes
};

export {schema};
