import {NodeType, MarkType, Mark} from 'prosemirror-model'
import {TextSelection} from 'prosemirror-state'

class NodePos {
    constructor(node, pos = 0, parent) {
        this.node = node;
        this.pos = pos;
        this.children = [];
        this.content = new NodePosFragment(this);
    }

    push(childNodePos) {
        if (!this.hasChild(childNodePos.pos)) {
            this.children.push(childNodePos);
        }
    }

    hasChild(pos) {
        for (let i = 0; i < this.children.length; i++) {
            if (this.children[i].pos === pos) {
                return true;
            }
        }
    }

    removeMark(mark) {
        let markInstance = this.getMark(mark);
        let index = this.node.marks.indexOf(markInstance);

        if (index > -1) {
            this.node.marks.splice(index, 1);
        }
    }

    hasMark(mark) {
        return this.getMark(mark) != null;
    }

    getMark(mark) {
        let result = null;

        if (mark instanceof MarkType) {
            mark = mark.name;
        }

        this.node.marks.forEach((activeMark) => {
            if (activeMark.type.name === mark) {
                result = activeMark;
            }
        });

        return result;
    }

    isPlain() {
        return !this.node.marks.length;
    }

    addMarks(marks) {
        if (!marks || !marks.length) {
            return;
        }

        marks.forEach(mark => {
            this.node.marks = mark.addToSet(this.node.marks);
        });
    }

    nodesBetween(from = 0, to, f, pos = 0, level = 1) {
        this.content.nodesBetween(from, to, (childNode, childPos, parent, i, level) => {
            f(childNode, childPos , parent, i, level);
        }, pos, this.node, level);
    }

    start() {
        return this.pos;
    }

    end() {
        return this.pos + this.node.nodeSize;
    }
}

class NodePosFragment {
    constructor(nodePos) {
        this.nodePos = nodePos;
        this.fragment = nodePos.node.content;
        this.size = this.fragment.size;
        this.content = this.fragment.content;
    }

    nodesBetween(from, to, f, nodeStart = 0, parent, level) {
        for (let i = 0, pos = 0; pos < to; i++) {
            let child = this.content[i], end = pos + child.nodeSize
            if (end > from && f(child, nodeStart + pos, parent, i, level) !== false && child.content.size) {
                let start = pos + 1;
                let childNodePos = new NodePos(child, start);

                childNodePos.nodesBetween(Math.max(0, from - start),
                                        Math.min(child.content.size, to - start),
                                        f, nodeStart + start, level + 1);
            }
            pos = end
        }
    }
}

let $node = function (node, pos = 0) {
    if (!(this instanceof $node)) {
        return new $node(node,pos);
    }

    this.tree = [];
    this.flat = [];
    this.filters = [];
    this.findFlag = false;

    if (node) {
        this.push(new NodePos(node, pos))
    }
};

$node.prototype.push = function (nodePos, parentPos) {
    if (this._hasNodePos(nodePos.pos)) {
        return;
    }

    this.flat.push(nodePos);

    if (parentPos) {
        parentPos.push(nodePos);
    } else {
        this.tree.push(nodePos);
    }
};

$node.prototype.find = function (selector) {
    this.filters = [];

    if (!selector) {
        this.findFlag = true;
        return this;
    }

    return this.type(selector, false);
};

$node.prototype._hasNodePos = function(pos) {
    for (let i = 0; i < this.flat.length; i++) {
        if (this.flat[i].pos === pos) {
            return true;
        }
    }
};

$node.prototype.size = function() {
    return this.flat.length;
};

$node.prototype.type = function (selector, includeSelf) {
    const typeFilter = (node, filter) => {
        let result = false;
        if (Array.isArray(filter)) {
            filter.forEach((type) => {
                if (typeFilter((node, type))) {
                    result = true;
                }
            });
        } else if (filter instanceof NodeType) {
            result = node.type === filter
        } else if (typeof filter === 'string') {
            result = node.type.name === filter;
        }
        return result;
    };

    return this.where((node) => {
        return typeFilter(node, selector);
    }, includeSelf);
};

$node.prototype.between = function (from, to) {
    return this.where((node, pos) => {
        let $pos = node.resolve(pos);
        return from <= $pos.start && to >= $pos.end;
    });
};

$node.prototype.from = function (from) {
    return this.where((node, pos) => {
        return from <= node.resolve(pos).start;
    });
};

$node.prototype.to = function (from, to) {
    return this.where((node, pos) => {
        return to >= node.resolve(pos).end;
    });
};

$node.prototype.mark = function (filterMark, attributes) {
    if (!filterMark) {
        this.where((node) => {
            return !node.marks.length;
        })
    }

    const markFilter = (node, attributes, filter) => {
        let result = false;
        if (Array.isArray(filter)) {
            result = true;
            filter.forEach((type) => {
                result = result && markFilter(node, attributes, type);
            });
        } else {
            result = hasMark(node, filter);
        }

        return result;
    };

    return this.where((node) => {
        return markFilter(node, attributes, filterMark);
    });
};


$node.prototype.markup = function (type, attrs, marks) {
    return this.where((node) => {
        return node.hasMarkup(type, attrs, marks);
    });
};

$node.prototype.text = function (search) {
    return this.where((node) => {
        return node.isText && ((search) ? node.text === search : true)
    })
};

$node.prototype.contains = function (search) {
    return this.where((node) => {
        return node.textContent.indexOf(search) >= 0
    })
};

$node.prototype.textBlock = function () {
    return this.where((node) => {
        return node.isTextblock
    })
};

$node.prototype.block = function () {
    return this.where((node) => {
        return node.isBlock
    })
};

$node.prototype.inline = function () {
    return this.where((node) => {
        return node.isInline
    })
};

$node.prototype.leaf = function () {
    return this.where((node) => {
        return node.isLeaf
    })
};

$node.prototype.canAppend = function (node) {
    return this.where((node) => {
        return node.canAppend(node)
    })
};

$node.prototype.sameMarkup = function (node) {
    return this.where((node) => {
        return node.sameMarkup(node)
    })
};

$node.prototype.not = function () {
    this.notFlag = true;
    return this;
};

$node.prototype.delete = function (view) {
    let tr = view.state.tr;
    this.tree.reverse().forEach((nodePos) => {
        tr = tr.delete(nodePos.start(), nodePos.end());
    });
    view.dispatch(tr);
};

$node.prototype.get = function (index) {
    return this.tree[index];
};

$node.prototype.append = function (node, view) {
    let tr = view.state.tr;
    let doc = view.state.doc;

    this.flat.reverse().forEach((nodePos) => {
        tr = tr.setSelection(new TextSelection(doc.resolve(nodePos.end()))).replaceSelectionWith(node);
    });

    view.dispatch(tr);
};

$node.prototype.replaceWith = function (node, view, dispatch = true) {
    let tr = view.state.tr;
    let doc = view.state.doc;

    this.flat.reverse().forEach((nodePos) => {
        tr = tr.setSelection(new TextSelection(doc.resolve(nodePos.start()), doc.resolve(nodePos.end()))).replaceSelectionWith(node);
    });

    if (dispatch) {
        view.dispatch(tr);
    }
};

$node.prototype.removeMark = function (mark, state) {
    let tr = state.tr;
    let doc = state.doc;
    this.flat.forEach((nodePos) => {
        nodePos.removeMark(mark);
        tr = tr.setSelection(new TextSelection(doc.resolve(nodePos.start())), doc.resolve(nodePos.end())).replaceSelectionWith(nodePos.node, false)
    });
};

$node.prototype.getMark = function(mark) {
    if (!this.flat.length) {
        return;
    }

    return this.flat[0].getMark(mark);
};

$node.prototype.where = function (filter, includeSelf = true) {
    let addFilter = (this.notFlag)
                    ? (node, pos, parent, searchRoot) => {
                        return !filter(node, pos, parent, searchRoot)
                    }
                    : filter;

    this.filters.push(addFilter);

    let $result = new $node();
    $result.filters = this.filters;

    this.tree.forEach((rootNodePos) => {
        let branchMatch = [];

        if (!this.findFlag && includeSelf && checkFilter(this.filters, rootNodePos.node, rootNodePos.pos)) {
            branchMatch[0] = new NodePos(rootNodePos.node, rootNodePos.pos);
            $result.push(branchMatch[0]);
        }

        let lastLevel = 1;
        let startPos = rootNodePos.node.type.name === 'doc' ? 0 : rootNodePos.pos + 1;

        rootNodePos.nodesBetween(0, rootNodePos.content.size, (childNode, pos, parent, i, level) => {
            // We moved one tree level back or switched to another branch
            if (lastLevel >= level) {
                branchMatch = clearLevelBranch(branchMatch, level);
            }

            if (checkFilter(this.filters, childNode, pos, parent)) {
                let nodePos = new NodePos(childNode, pos);
                $result.push(nodePos, findBranchMatch(branchMatch, level));
                branchMatch[level] = nodePos;
            }

            lastLevel = level;
        }, startPos);

    });

    this.notFlag = false;
    this.findFlag = false;
    return $result;
};

let clearLevelBranch = function(branchMatches, level) {
    let result = [];
    branchMatches.forEach((val, index) => {
        result[index] = (index >= level) ? null : branchMatches[index];
    });
    return result;
};

let findBranchMatch = function(branchMatches, level) {
    for (let i = level - 1; i >= 0; i--) {
        if (branchMatches[i]) {
            return branchMatches[i];
        }
    }
};

let checkFilter = function (filters, node, pos, parent, searchRoot) {
    for (let i = 0; i < filters.length; i++) {
        if (!filters[i](node, pos, parent, searchRoot)) {
            return false;
        }
    }
    return true;
};

let hasMark = function (node, markType) {
    let result = false;

    if (!node) {
        return false;
    }

    node.marks.forEach((mark) => {
        if (markType instanceof Mark && mark.eq(markType)) {
            result = true;
        } else if (markType instanceof MarkType && ((mark.type.name === markType.name) || mark.eq(markType))) {
            result = true;
        } else if (typeof markType === 'string' && mark.type.name === markType) {
            result = true;
        }
    });

    return result;
};

export {$node, hasMark}
