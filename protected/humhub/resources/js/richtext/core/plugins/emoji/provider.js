/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {getByCategory, getCharByName, getCharToDom} from "./util";
import {getUserLocale, isSmallView} from "../../humhub-bridge";

let userFlag = undefined;
let findUserFlag = () => {
    if (userFlag) {
        return userFlag;
    }

    const directMapping = {
        'en-us': 'us',
        'en': 'us',
        'en-gb': 'uk',
        'pt-br': 'portugal',
        'fa-ir': 'iran',
        'zh-cn': 'cn',
        'zh-tw': 'cn',
        'ja': 'jp',
        'ko': 'kr',
        'ar': 'united_arab_emirates',
        'uk': 'ukraine',
        'ru': 'ru',
        'vi': 'vietnam',
        'sv': 'sweden',
        'nb-no': 'norway',
        'it': 'it',
        'fr': 'fr',
        'es': 'es',
        'de': 'de',
        'da': 'denmark',
        'cs': 'czech_republic',
        'ca': 'es', // sorry for that ;)
        'an': 'es'
    };

    let result = '\uD83C\uDDE9\uD83C\uDDEA';

    try {
        let language = getUserLocale();

        if (language && directMapping[language]) {
            return getCharByName(directMapping[language]);
        }

        $.each(getByCategory('Flags'), (index, flag) => {
            if (flag && flag.keywords && flag.keywords.indexOf(language) >= 0) {
                result = flag.emoji;
                return false;
            }
        });
    } catch (e) {
        console.error('Error while determining user flag in emoji chooser');
        console.error(e);
    }

    return userFlag = result;
};

let chooser = undefined;

class EmojiChooser {
    constructor(provider) {
        this.provider = provider;
        this.categoryOrder = [
            'Smileys & Emotion',
            'People & Body',
            'Animals & Nature',
            'Food & Drink',
            'Travel & Places',
            'Activities',
            'Objects',
            'Symbols',
            'Flags',
            'Search'
        ];
        this.categories = {
            'Smileys & Emotion': {$icon: getCharToDom('\uD83D\uDE00')},
            'People & Body': {$icon: getCharToDom('\uD83D\uDC4D')},
            'Animals & Nature': {$icon: getCharToDom('\uD83D\uDC3B')},
            'Food & Drink': {$icon: getCharToDom('\uD83C\uDF82')},
            'Travel & Places': {$icon: getCharToDom('\u26BD')},
            'Activities': {$icon: getCharToDom('\u2708\uFE0F')},
            'Objects': {$icon: getCharToDom('\uD83D\uDDA5')},
            'Symbols': {$icon: getCharToDom('\u2764\uFE0F')},
            'Flags': {$icon: getCharToDom(findUserFlag())},
            'Search': {$icon: getCharToDom('\uD83D\uDD0D')}
        };
    }

    update(provider, focus) {
        this.provider = provider;
        let position = provider.$node.offset();

        if (!this.$) {
            this.initDom();
            this.initCategory(this.categoryOrder[0]);
        }

        if (!isSmallView()) {
            this.$.css({
                top: position.top + provider.$node.outerHeight() - 5,
                left: position.left,
            }).show();
        } else {
            this.$.css({
                top: 5,
                position: 'fixed',
                left: 0,
                right: 0,
                margin: 'auto'
            }).show();
        }

        if (focus) {
            this.$.find('.humhub-emoji-chooser-search').focus();
        }
    }

    initDom() {
        let that = this;
        this.$ = $('<div class="atwho-view humhub-richtext-provider humhub-emoji-chooser"><div>' +
            '<input type="text" placeholder="' + this.translate('Search') + '" class="form-control humhub-emoji-chooser-search">' +
            '</div></div>')
            .hide().appendTo($('body'))
            .on('hidden', () => {
                if (that.provider) {
                    that.provider.reset();
                }
            });

        this.$.find('.humhub-emoji-chooser-search').on('keydown', function (e) {
            switch (e.which) {
                case 9:
                    e.preventDefault();
                    that.nextCategory();
                    break;
                case 27:
                    that.provider.reset();
                    break;
                case 13:
                    e.preventDefault();
                    that.provider.select();
                    break;
                case 37:
                    that.prev();
                    break;
                case 38:
                    that.up();
                    break;
                case 39:
                    that.next();
                    break;
                case 40:
                    that.down();
                    break;
            }
        }).on('keyup', function (e) {
            let keyCode = e.keyCode || e.which;
            // This line should prevent processing in case user presses down/up on desktop, android chrome does not send
            // always send keyCode 229 so we can skip this check in this case
            if (keyCode !== 229 && keyCode !== 8 && !/[a-z0-9\d]/i.test(String.fromCharCode(keyCode))) {
                return;
            }

            let val = $(this).val();
            if (!val.length && that.lastActiveCategory) {
                that.openCategory(that.lastActiveCategory);
                return;
            }

            let currentlyActive = that.getActiveCategoryMenuItem().attr('data-emoji-nav-item');
            if (currentlyActive !== 'Search') {
                that.lastActiveCategory = currentlyActive;
            }

            that.updateSearch(val);
        });

        this.initNav();
    }

    initNav() {
        let $nav = $('<div class="emoji-nav">').appendTo(this.$);

        this.categoryOrder.forEach((categoryName, index) => {
            let categoryDef = this.categories[categoryName];
            let $item = $('<span class="emoji-nav-item" title="' + this.translate(categoryName) + '">').attr('data-emoji-nav-item', categoryName).append(categoryDef.$icon).on('click', () => {
                this.openCategory(categoryName);
                this.provider.event.trigger('focus');
            });

            if (index === 0) {
                $item.addClass('cur');
            }

            $nav.append($item);
        });

        $nav.find('[data-emoji-nav-item="Search"]').hide();
    }

    clearSearch() {
        this.$.find('[data-emoji-nav-item="Search"]').hide();
        this.$.find('.humhub-emoji-chooser-search').val('');
    }

    updateSearch(searchStr) {
        this.$.find('[data-emoji-nav-item="Search"]').show();
        let result = [];
        let length = searchStr.length;
        this.categoryOrder.forEach((categoryName, index) => {
            $.each(getByCategory(categoryName), (index, emoji) => {
                if (emoji && emoji.keywords) {
                    $.each(emoji.keywords, (index, keyword) => {
                        if (length < 3) {
                            if (keyword.lastIndexOf(searchStr, 0) === 0) {
                                result.push(emoji);
                                return false;
                            }
                        } else if (keyword.includes(searchStr)) {
                            result.push(emoji);
                            return false;
                        }
                    });
                }
            });
        });

        this.openCategory('Search');
        this.setCategoryItems('Search', result);
    }

    openCategory(categoryName) {
        let categoryDef = this.categories[categoryName];

        if (!this.$.find('[data-emoji-category="' + categoryName + '"]').length) {
            this.initCategory(categoryName);
        }

        if (categoryName !== 'Search') {
            this.clearSearch();
        }

        this.$.find('[data-emoji-nav-item]').removeClass('cur');
        this.$.find('[data-emoji-nav-item="' + categoryName + '"]').addClass('cur');
        this.$.find('[data-emoji-category]').hide();
        this.$.find('[data-emoji-category="' + categoryName + '"]').show();
    }

    initCategory(categoryName) {
        let that = this;
        let $category = $('<div>').attr('data-emoji-category', categoryName).on('click', '.atwho-emoji-entry', function () {
            that.getSelectionNode().removeClass('cur');
            $(this).addClass('cur');
            that.provider.select();
        }).prependTo(this.$);

        $('<ul class="atwo-view-ul humhub-emoji-chooser-item-list">').appendTo($category);
        this.categories[categoryName].$ = $category;
        this.setCategoryItems(categoryName);
    }

    setCategoryItems(categoryName, items) {
        if (!items && categoryName !== 'Search') {
            items = getByCategory(categoryName);
        }

        if (!items) {
            items = [];
        }

        const $list = this.categories[categoryName].$.find('.humhub-emoji-chooser-item-list').empty();

        items.forEach((emojiDef) => {
            const $img = getCharToDom(emojiDef.emoji, emojiDef.name);

            if ($img && $img !== '' && $img.length) {
                const $li = $('<li class="atwho-emoji-entry">').append($img);

                if (categoryName === 'Flags' && emojiDef.emoji === findUserFlag()) {
                    $list.prepend($li);
                } else {
                    $list.append($li);
                }
            }
        });

        $list.children().first().addClass('cur');
    }

    reset() {
        this.provder = undefined;
        this.$.remove();
        this.$ = undefined;
    }

    getSelection() {
        let $selection = this.getSelectionNode().find('img');
        return {
            name: $selection.data('name'),
            alt: $selection.attr('alt'),
            src: $selection.attr('src'),
        }
    }

    translate(key) {
        return this.provider.context.translate(key);
    }

    getSelectionNode() {
        return this.getActiveCategoryTab().find('.cur');
    }

    getActiveCategoryTab() {
        return this.$.find('[data-emoji-category]:visible');
    }

    getActiveCategoryMenuItem() {
        return this.$.find('[data-emoji-nav-item].cur');
    }

    nextCategory() {
        let $next = this.getActiveCategoryMenuItem().next('[data-emoji-nav-item]:not([data-emoji-nav-item="Search"])');
        if (!$next.length) {
            $next = this.$.find('[data-emoji-nav-item]:first');
        }

        this.openCategory($next.attr('data-emoji-nav-item'));
    }

    prev() {
        let $cur = this.getSelectionNode();
        let $prev = $cur.prev();
        if ($prev.length) {
            $prev.addClass('cur');
            $cur.removeClass('cur');
            this.alignScroll();
        }
    }

    next() {
        let $cur = this.getSelectionNode();
        let $next = $cur.next();
        if ($next.length) {
            $next.addClass('cur');
            $cur.removeClass('cur');
            this.alignScroll();
        }
    }

    up() {
        let $cur = this.getSelectionNode();
        let curPosition = $cur.position();

        for (let $prev = $cur.prev(); $prev.length; $prev = $prev.prev()) {
            let nextPosition = $prev.position();

            if (nextPosition.top < curPosition.top && nextPosition.left === curPosition.left) {
                $prev.addClass('cur');
                $cur.removeClass('cur');
                this.alignScroll();
                return;
            }
        }
    }

    down() {
        let $cur = this.getSelectionNode();
        let curPosition = $cur.position();

        for (let $next = $cur.next(); $next.length; $next = $next.next()) {
            let nextPosition = $next.position();
            if (nextPosition.top > curPosition.top && nextPosition.left === curPosition.left) {
                $next.addClass('cur');
                $cur.removeClass('cur');
                this.alignScroll();
                return;
            }
        }

        // If we did not find a match the line below is probably the last line.
        let $last = this.getActiveCategoryTab().find('.atwho-emoji-entry:last');
        if ($last.position().top !== curPosition.top) {
            $last.addClass('cur');
            $cur.removeClass('cur');
            this.alignScroll();
        }
    }

    alignScroll() {
        let $cur = this.getSelectionNode();
        let $tab = this.getActiveCategoryTab();
        let scrollTop = $tab.scrollTop();
        let scrollBottom = scrollTop + $tab.height();

        let offsetTop = $cur[0].offsetTop;
        let offsetBottom = offsetTop + $cur.height();

        if (offsetTop > scrollBottom || offsetTop < scrollTop || offsetBottom > scrollBottom || offsetBottom < scrollTop) {
            $tab[0].scrollTop = $cur[0].offsetTop;
        }
    }
}

export class EmojiProvider {
    constructor(context) {
        this.event = $({});
        this.context = context;
    }

    query(state, node, focus) {
        this.state = state;
        this.$node = $(node);
        this.update(focus);
    }

    reset(query, node) {
        if (this.$node) {
            this.$node = undefined;
            this.getChooser().reset();
            this.event.trigger('closed');
        }
    }

    next() {
        this.getChooser().next();
    }

    prev() {
        this.getChooser().prev();
    }

    down() {
        this.getChooser().down();
    }

    up() {
        this.getChooser().up();
    }

    select() {
        this.state.addEmoji(this.getChooser().getSelection());
    }

    update(focus) {
        this.getChooser().update(this, focus);
    }

    getChooser() {
        if (!chooser) {
            chooser = new EmojiChooser(this);
        }

        return chooser;
    }
}


export function getProvider(context) {
    return (context.options.emoji && context.options.emoji.provider)
        ? context.options.emoji.provider : new EmojiProvider(context);
}
