import {MenuItemGroup} from "./menugroup";
import {
    addMenuClass,
    buildMenuClass,
    initMenuItemTrigger,
    isMenuEvent,
    markMenuEvent,
    setClass,
    setTitle
} from "./menu-helper";
import crelt from "crelt";

// Holds currently opened dropdown item with close function
let opened = null;

// ::- A drop-down menu, displayed as a label with a downwards-pointing
// triangle to the right of it.
export class Dropdown extends MenuItemGroup {
    // :: ([MenuElement], ?Object)
    // Create a dropdown wrapping the elements. Options may include
    // the following properties:
    //
    // **`label`**`: string`
    //   : The label to show on the drop-down control.
    //
    // **`title`**`: string`
    //   : Sets the
    //     [`title`](https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/title)
    //     attribute given to the menu control.
    //
    // **`class`**`: string`
    //   : When given, adds an extra CSS class to the menu control.
    //
    // **`css`**`: string`
    //   : When given, adds an extra set of CSS styles to the menu control.
    constructor(content, options) {
        super(content, options);
        this.options.htmlNode = 'button';
    }

    // :: (EditorView) → {dom: dom.Node, update: (EditorState)}
    // Render the dropdown menu and sub-items.
    render(view, renderOptions) {
        this.view = view;
        this.getContentDom(view);
        this.initTrigger(view);
        this.initWrapper(view);
        this.initEvents(view);
        return this.dom;
    }

    initTrigger(view) {
        this.trigger = initMenuItemTrigger(view, this.options);

        if (!this.trigger) {
            throw new RangeError("Dropdown without icon or label property")
        }

        addMenuClass(this.trigger, 'dropdown');

        this.trigger.setAttribute('aria-haspopup', 'true');
        this.trigger.setAttribute('aria-expanded', 'false');

        setTitle(this.trigger, this.options, view);
    }

    initWrapper() {
        this.dom = crelt("div", {}, this.trigger);
        addMenuClass(this.dom, 'dropdown-wrapper');
        this.$ = $(this.dom);
    }

    initContent(content) {
        super.initContent(content);
        this.content.update = (state) => {
            let result = false;
            this.forEachItem((item) => {
                let updateResult = item.update(state);
                item.dom.style.display = updateResult ? "" : "none";
                result = result || updateResult;
            });
            return result;
        };
    }

    initEvents(view) {
        this.$.on('keydown', e => {
            let keyCode = e.keyCode || e.which;

            switch (keyCode) {
                case 9: // Enter
                    this.onTab(e);
                    break;
                case 13: // Enter
                    this.onEnter(e);
                    break;
                case 27: // Escape
                    this.onEscape(e);
                    break;
                case 40: // ArrowDown
                    this.onArrowDown(e);
                    break;
                case 38: // ArrowUp
                    this.onArrowUp(e);
                    break;
                case 37: // ArrowLeft
                    this.onArrowLeft(e);
                    break;
                case 39: // ArrowLeft
                    this.onArrowRight(e);
                    break;
            }
        });

        $(this.trigger).on('mousedown', e => {
            this.onClickTrigger(e);
        });
    }

    getContentDom(view) {
        if (!this.contentDom) {
            this.contentDom = this.renderItems(view);
        }

        return this.contentDom;
    }

    open() {
        opened = this.expand(this.dom);

        $(window).on('mousedown.richtextMenu', () => {
            if (!isMenuEvent(this.dom)) this.close()
        });

        closeSubMenues();

        this.trigger.setAttribute('aria-expanded', 'true');
    }

    close() {
        if (opened && opened.close()) {
            opened = null;
            $(window).off("mousedown.richtextMenu");
        }

        closeSubMenues();
        this.trigger.setAttribute('aria-expanded', 'false');
    }

    isOpen() {
        return this.$.find('.' + buildMenuClass('dropdown-menu')).is(':visible');
    }

    onTab(e) {
        if (this.isOpen()) {
            this.close();
        }
    }

    onEnter(e) {
        e.preventDefault();
        if (!this.isOpen()) {
            if (opened) {
                this.close();
            }

            this.open();
            return;
        }

        if (this.getSubMenu().find('a:focus').length) {
            this.getMenuBar().context.editor.focus();
        }

        this.close();
    }

    getSubMenu() {
        return $(this.menu);
    }

    onEscape(e) {
        e.preventDefault();
        e.stopPropagation();

        let wasOpen = this.isOpen();

        this.close();

        if (wasOpen) {
            $(this.trigger).focus();
        }
    }

    onArrowDown(e) {
        e.preventDefault();
        e.stopPropagation();

        if (this.isOpen()) {
            this.focusNext();
        } else {
            if (opened) {
                this.close();
            }

            $(this.trigger).trigger('mousedown');
        }
    }

    focusNext() {
        let $focused = this.getFocused();
        let $parent = $focused.parent('.' + buildMenuClass('dropdown-item'));
        let $next = $parent.next();
        $next = $next.length ? $next.find('a:visible:first') : this.getFirstLink();
        $next.focus();
    }

    getFocused() {
        return this.getSubMenu().find('a:focus');
    }

    getFirstLink() {
        return $(this.menu).find('.' + buildMenuClass('dropdown-item') + ':first').find('a:first');
    }

    onArrowUp(e) {
        e.preventDefault();
        e.stopPropagation();

        if (this.isOpen()) {
            this.focusPrev();
        } else {
            if (opened) {
                this.close();
            }

            this.open();
        }
    }

    onArrowLeft(e) {
        // Prevent main nav switch
        if (this.isOpen()) {
            e.preventDefault();
            e.stopPropagation();
        }
    }

    onArrowRight(e) {
        // Prevent main nav switch
        if (this.isOpen()) {
            e.preventDefault();
            e.stopPropagation();
        }
    }

    focusPrev() {
        let $focused = this.getFocused();
        let $parent = $focused.closest('.' + buildMenuClass('dropdown-item'));
        let $prev = $parent.prev();
        $prev = $prev.length ? $prev.find('a:visible:first') : this.getLastLink();
        $prev.focus();
    }

    getLastLink() {
        return $(this.menu).children('.' + buildMenuClass('dropdown-item:last')).find('a:first');
    }

    onClickTrigger(e) {
        e.preventDefault();
        if (!this.selected || !this.enabled) return;
        markMenuEvent(e);

        let wasOpen = this.isOpen();

        if (opened) {
            this.close();
        }

        if (!wasOpen) {
            this.open()
        }
    }

    renderItems(view) {
        let rendered = [];
        this.content.items.forEach((item) => {
            let dom = item.render(view, {htmlNode: 'a', tabindex: 0});
            let itemDom = crelt("div", {}, dom);
            addMenuClass(itemDom, 'dropdown-item');
            rendered.push(itemDom);
        });

        return rendered;
    }

    update(state) {
        let contentUpdateResult = this.content.update(state);

        let forceEnable = false;
        let forceActive = false;

        this.content.items.forEach((item) => {
            forceEnable = forceEnable || item.enabled;
            forceActive = forceActive || item.active;
        });

        this.adoptItemState(state, forceEnable, (forceActive && this.options.bubbleActive));
        return contentUpdateResult;
    }

    expand(dom) {

        let menuDOM = crelt("div", {}, this.getContentDom());
        addMenuClass(menuDOM, 'dropdown-menu');

        let done = false;

        function close() {
            closeSubMenues();
            if (done) return;
            done = true;
            dom.removeChild(menuDOM);
            return true;
        }

        dom.appendChild(menuDOM);

        let $menuDom = $(menuDOM);
        let right = $menuDom.offset().left + $menuDom.width();
        let rightAlignClass = buildMenuClass('dropdown-right');

        setClass($menuDom[0], rightAlignClass, (right > $(window).width() / 2));

        this.menu = menuDOM;
        return {close, node: menuDOM};
    }
}

function closeSubMenues() {
    $('.' + buildMenuClass('submenu')).css('display', '');
}
