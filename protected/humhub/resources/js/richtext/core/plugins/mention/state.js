/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

import {isChromeWithSelectionBug} from "../index"

export class MentionState {
    constructor(state, options) {
        this.state = state;
        this.provider = options.provider;
        this.reset();
    }

    findQueryNode() {
        return $(this.view.dom).find('[data-mention-query]');
    }

    update(state, view) {
        this.view = view;
        this.state = state;
        const { mentionQuery } = state.schema.marks;
        const { doc, selection } = state;
        const { $from, from, to } = selection;

        this.active = doc.rangeHasMark(from - 1, to, mentionQuery);

        if (!this.active) {
            return this.reset();
        }

        let $query = this.findQueryNode();
        if(endsWith($query.text(), '  ')) {
            view.dispatch(this.state.tr.removeMark(0, this.state.doc.nodeSize -2, mentionQuery));
            return this.reset();
        }

        let $pos = doc.resolve(from - 1);

        this.queryMark = {
            start: $pos.path[$pos.path.length - 1],
            end: to
        };

        if(!$from.nodeBefore || !$from.nodeBefore.text) {
            return;
        }

        let query = $from.nodeBefore.text.substr(1);

        if(query != this.query) {
            this.query = query;
            this.provider.query(this, $query[0]);
        }
    }

    reset() {
        const { mentionQuery } = this.state.schema.marks;

        if(this.state.storedMarks && this.state.storedMarks.length) {
            this.state.storedMarks = mentionQuery.removeFromSet(this.state.storedMarks);
        }
        //this.state.storedMarks = [];
        this.active = false;
        this.query = null;
        this.provider.reset();
    }

    addMention(item) {
        if(!item || !item.name || !item.guid) {
            this.view.dispatch(this.state.tr.removeMark(0, this.state.doc.nodeSize -2, mentionQuery));
            this.reset();
            return;
        }
        const { mention } = this.state.schema.nodes;
        const { mentionQuery} = this.state.schema.marks;

        const nodes = [mention.create({
            name: item.name,
            guid: item.guid,
            href: item.link
        }, null), this.state.schema.text(' ')];


        let tr = this.state.tr
            .removeMark(0, this.state.doc.nodeSize -2, mentionQuery)
            .replaceWith(this.queryMark.start, this.queryMark.end, nodes);

        if(isChromeWithSelectionBug) {
            document.getSelection().empty();
        }

        this.view.dispatch(tr);
        this.view.focus();
    }
}

let endsWith = function(string, suffix) {
    return string.indexOf(suffix, string.length - suffix.length) !== -1;
};