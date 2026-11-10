/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {encode, getLoaderWidget} from "../../humhub-bridge";
import {translate} from "../../menu/menu-helper";
import {setCurrentSuggestion, suggestionListId} from "../../util/suggestions";

let MentionProvider = function (options) {
    this.event = $({});
    this.options = options;
    if (typeof this.options.minInput === 'undefined') {
        this.options.minInput = 2;
    }
    this.options.minInputText = this.options.minInputText || 'Please type at least ' + this.options.minInput + ' characters';
    this.listId = suggestionListId();
};

MentionProvider.prototype.getView = function () {
    return this.state ? this.state.view : null;
};

// The current suggestion: highlighted, selected and the one the editor points to
MentionProvider.prototype.setCurrent = function ($item) {
    this.$container.find('[role="option"]').removeClass('cur').attr('aria-selected', 'false');
    $item.addClass('cur').attr('aria-selected', 'true');
    setCurrentSuggestion(this.getView(), this.listId, $item.attr('id'));
};

MentionProvider.prototype.query = function (state, node) {
    this.state = state;
    this.$node = $(node);

    if (this.options.minInput > 0 && this.state.query.length < this.options.minInput) {
        this.result = {text: this.options.minInputText};
        this.update();
        return;
    }

    this.loading();
    let queryResult = this.find(this.state.query, node);

    if (queryResult.then) {
        queryResult.then((result) => {
            this.updateResult(result);
        });
    } else {
        this.updateResult(queryResult);
    }
};

MentionProvider.prototype.loading = function () {
    this.result = {loader: true};
    this.update();
};

MentionProvider.prototype.updateResult = function (result) {
    this.result = result;
    this.update();
};

MentionProvider.prototype.find = function (query, node) {
    // Abstract method has to be implemented by subclasses
};

MentionProvider.prototype.reset = function (query, node) {
    if (this.$container) {
        this.$container.remove();
        this.$container = null;
        setCurrentSuggestion(this.getView(), null, null);
        this.event.trigger('closed');
    }
};

MentionProvider.prototype.prev = function () {
    let $cur = this.$container.find('.cur');
    let $prev = $cur.prev();
    if ($prev.length) {
        this.setCurrent($prev);
    }
};

MentionProvider.prototype.next = function () {
    let $cur = this.$container.find('.cur');
    let $next = $cur.next();
    if ($next.length) {
        this.setCurrent($next);
    }
};

MentionProvider.prototype.select = function () {
    let $cur = this.$container.find('.cur');
    this.state.addMention($cur.data('item'));
    this.reset();
};

MentionProvider.prototype.update = function (loading) {
    if (!this.$container) {
        this.$container = $('<div class="atwho-view humhub-richtext-provider">').css({'margin-top': '5px'});
    } else {
        this.$container.empty();
    }

    let position = this.$node.offset();
    this.$container.css({
        top: position.top + this.$node.outerHeight() + 2,
        left: position.left,
    });


    const that = this;
    if (this.result && this.result.length) {
        let $list = $('<ul role="listbox" style="list-style-type: none;padding:0px;margin:0px;">').attr({
            id: this.listId,
            'aria-label': this.getView() ? translate(this.getView(), 'Suggestions') : 'Suggestions',
        });
        this.result.forEach(function (item, index) {
            const name = encode(item.name);
            const $li = (item.image) ? $('<li>' + item.image + ' ' + name + '</li>') : $('<li>' + name + '</li>');

            // An option holds no other control, and its name is the name: the image without its link and its alt text
            $li.find('a').contents().unwrap();
            $li.find('img').attr('alt', '');
            $li.attr({role: 'option', id: that.listId + '-' + index, 'aria-selected': 'false'});
            $li.data('item', item).on('click', () => {
                that.setCurrent($li);
                that.select();
            });

            $list.append($li);
        });

        this.$container.append($list);
        this.setCurrent($list.find('li').first());
    } else if (this.result.text) {
        setCurrentSuggestion(this.getView(), null, null);
        const name = encode(this.result.text);
        this.$container.append($('<span>' + name + '</span>'));
    } else if (this.result.loader) {
        setCurrentSuggestion(this.getView(), null, null);
        let $loader = getLoaderWidget();

        this.$container.append($('<div style="text-align:center;">').append($loader));
    } else {
        setCurrentSuggestion(this.getView(), null, null);
        this.$container.append($('<span>No Result</span>'));
    }

    $('body').append(this.$container.show());
};

export default MentionProvider
