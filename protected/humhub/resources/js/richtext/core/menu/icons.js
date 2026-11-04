const SVG = "http://www.w3.org/2000/svg";
const XLINK = "http://www.w3.org/1999/xlink";

const prefix = "ProseMirror-icon";

const getIconSize = (name) => {
    const veryBigIcons = ['table', 'enlarge', 'shrink'];
    const biggerIcons = ['emoji'];
    const bigIcons = ['headline', 'join', 'image', 'undo', 'redo', 'markdown', 'angleDoubleRight', 'angleDoubleLeft'];
    const smallIcons = ['bulletList', 'orderedList', 'indent', 'outdent'];

    return smallIcons.includes(name)
        ? '18' : bigIcons.includes(name)
            ? '16' : biggerIcons.includes(name)
                ? '15' : veryBigIcons.includes(name) ? '14' : '17';
}

export function getIcon(icon, htmlNode) {
    htmlNode = htmlNode || 'div';
    let node = document.createElement(htmlNode);
    node.className = prefix;

    if (htmlNode === "button") {
        node.setAttribute("type", "button");
    }

    if (icon.tabler) {
        const i = node.appendChild(document.createElement("i"));
        i.className = "ti ti-" + icon.tabler;
        i.setAttribute("aria-hidden", "true");
    } else if (icon.path) {
        const name = "pm-icon-" + icon.name;
        if (!document.getElementById(name)) buildSVG(name, icon);
        const svg = node.appendChild(document.createElementNS(SVG, "svg"));
        const iconSize = getIconSize(icon.name);
        svg.setAttribute('height', iconSize /*icon.height*/);
        svg.setAttribute('width', iconSize /*icon.width*/);
        let use = svg.appendChild(document.createElementNS(SVG, "use"));
        use.setAttributeNS(XLINK, "href", /([^#]*)/.exec(document.location)[1] + "#" + name);
    } else if (icon.dom) {
        node.appendChild(icon.dom.cloneNode(true));
    } else {
        node.appendChild(document.createElement("span")).textContent = icon.text || '';
        if (icon.css) node.firstChild.style.cssText = icon.css;
    }
    return node;
}

function buildSVG(name, data) {
    let collection = document.getElementById(prefix + "-collection");
    if (!collection) {
        collection = document.createElementNS(SVG, "svg");
        collection.id = prefix + "-collection";
        collection.style.display = "none";
        document.body.insertBefore(collection, document.body.firstChild);
    }
    let sym = document.createElementNS(SVG, "symbol");
    sym.id = name;
    sym.setAttribute("viewBox", "0 0 " + data.width + " " + data.height);

    let pathData = Array.isArray(data.path) ? data.path : [data.path];

    pathData.forEach((path) => {
        let pathDom = sym.appendChild(document.createElementNS(SVG, "path"));
        pathDom.setAttribute("d", path);
        collection.appendChild(sym);
    });
}

// :: Object
// The editor's icons, each an object that can be used as the `icon` option to `MenuItem`:
// a Tabler icon (`tabler`, rendered as `<i class="ti ti-<name>">` - HumHub ships the font).
// A plugin may still pass an SVG icon (`path`, `width`, `height`) or a text one (`text`).
export const icons = {
    headline: {name: 'headline', tabler: 'heading'},
    plus: {name: 'plus', tabler: 'plus'},
    add: {name: 'add', tabler: 'square-plus'},
    join: {name: 'join', tabler: 'arrow-merge'},
    text: {name: 'text', tabler: 'typography'},
    strong: {name: 'strong', tabler: 'bold'},
    em: {name: 'em', tabler: 'italic'},
    strikethrough: {name: 'strikethrough', tabler: 'strikethrough'},
    code: {name: 'code', tabler: 'code'},
    link: {name: 'link', tabler: 'link'},
    bulletList: {name: 'bulletList', tabler: 'list'},
    orderedList: {name: 'orderedList', tabler: 'list-numbers'},
    indent: {name: 'indent', tabler: 'indent-increase'},
    outdent: {name: 'outdent', tabler: 'indent-decrease'},
    blockquote: {name: 'blockquote', tabler: 'blockquote'},
    table: {name: 'table', tabler: 'table'},
    emoji: {name: 'emoji', tabler: 'mood-smile'},
    upload: {name: 'upload', tabler: 'upload'},
    undo: {name: 'undo', tabler: 'arrow-back-up'},
    redo: {name: 'redo', tabler: 'arrow-forward-up'},
    enlarge: {name: 'enlarge', tabler: 'arrows-maximize'},
    shrink: {name: 'shrink', tabler: 'arrows-minimize'},
    angleDoubleRight: {name: 'angleDoubleRight', tabler: 'chevrons-right'},
    angleDoubleLeft: {name: 'angleDoubleLeft', tabler: 'chevrons-left'},
    markdown: {name: 'markdown', tabler: 'markdown'},
    horizontalRule: {name: 'horizontalRule', tabler: 'separator-horizontal'},
    selectParentNode: {name: 'selectParentNode', text: "\u2b1a", css: "font-weight: bold"}
};
