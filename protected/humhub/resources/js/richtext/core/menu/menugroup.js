import {MenuItem} from "./menuitem";
import {addMenuClass, PREFIX, setAttributesFromOptions} from "./menu-helper";
import {buildMenuClass} from "./menu-helper";
import {Dropdown} from "./dropdown";
import crelt from "crelt";

const MENU_SUFFIX_GROUP = 'group';

export class MenuItemGroup extends MenuItem {
    constructor(content, options) {
        super(options);
        this.options.htmlNode = 'div';
        this.initContent(content);
    }

    initContent(content) {
        this.content = {
            items: sortItems(Array.isArray(content) ? content : [content]),
            update: (state) => {
                let result = false;

                sortItems(this.content.items).forEach((item, i) => {
                    let updateResult = item.update(state);
                    let $item = $(item.dom);

                    if (!updateResult) {
                        $item.hide();
                    } else {
                        $item.show();
                    }

                    // Mark the last item in the group
                    if ((i === this.content.items.length - 1)) {
                        $item.addClass('last');
                    }

                    // If one item is visible the whole group is visible
                    result = result || updateResult;
                });

                return result;
            }
        };
    }

    forEachItem(callable) {
        this.content.items.forEach(callable);
    }

    render(view, renderOptions) {
        this.$ = $('<' + this.options.htmlNode + '>').addClass(buildMenuClass(MENU_SUFFIX_GROUP));
        this.dom = this.$[0];

        setAttributesFromOptions(this.dom, this.options);

        this.renderItems(view).forEach((itemDom) => {
            this.$.append(itemDom);
        });

        return this.dom;
    }

    update(state) {
        let result = this.content.update(state);
        return result && super.update(state);
    }

    setHidden(isHidden) {
        super.setHidden(isHidden);

        if (isHidden) {
            this.forEachItem((item) => {
                item.setHidden(isHidden);
            })
        }
    }

    renderItems(view) {
        let rendered = [];

        this.forEachItem((item) => {
            let dom = item.render(view);

            // Note the PREFIX + item is here for compatibility reasons
            let itemDom = crelt("div", {class: PREFIX + 'item'}, dom);
            addMenuClass(itemDom, 'item');
            rendered.push(itemDom);
        });

        return rendered;
    }
}

export function sortItems(items) {
    let result = [];
    items.forEach((item) => {
        if (item && item.type && item.type === 'dropdown') {
            result.push(new Dropdown(sortItems(item.items), item));
        } else if (item && item.type && item.type === 'group') {
            result.push(new MenuItemGroup(sortItems(item.items), item));
        } else if (item) {
            result.push(item);
        }
    });

    return result.sort(function (a, b) {
        if (typeof a.sortOrder === 'undefined') {
            return 1;
        }
        if (typeof b.sortOrder === 'undefined') {
            return -1;
        }
        return a.sortOrder - b.sortOrder;
    });
}
