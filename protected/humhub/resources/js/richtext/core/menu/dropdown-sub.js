import {Dropdown} from "./dropdown";
import {buildMenuClass, setAttributesFromOptions, translate} from "./menu-helper";
import crelt from "crelt";

// ::- Represents a submenu wrapping a group of elements that start
// hidden and expand to the right when hovered over or tapped.
export class DropdownSubmenu extends Dropdown {
    // :: ([MenuElement], ?Object)
    // Creates a submenu for the given group of menu elements. The
    // following options are recognized:
    //
    // **`label`**`: string`
    //   : The label to show on the submenu.
    constructor(content, options) {
        super(content, options);
        this.options.bubbleActive = true;
    }

    initTrigger(view) {
        this.trigger = $('<a tabindex="0" aria-haspopup="listbox" aria-expanded="false"></a>')
            .text(translate(view, this.options.label))[0];
        setAttributesFromOptions(this.trigger, this.options);
    }

    /**
     * <div class="ProseMirror-menu-submenu-wrap">
     *     <div class="ProseMirror-menu-submenu-label">
     *         <a tabindex="0">Label</a>
     *     </div>
     *     <div class="ProseMirror-menu-submenu">
     *         <div class="ProseMirror-menu-dropdown-item"></div>
     *         <div class="ProseMirror-menu-dropdown-item"></div>
     *     </div>>
     * </div>
     */
    initWrapper(view) {
        let label = $('<div>').addClass(buildMenuClass('submenu-label')).html(this.trigger)[0];

        this.menu = crelt("div", {class: buildMenuClass("submenu")},  this.getContentDom(view));

        this.dom = crelt("div", {class: buildMenuClass("submenu-wrap")}, label, this.menu);

        this.$ = $(this.dom);
    }

    onEnter(e) {
        if(this.getFocused().length) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        this.getSubMenu().show();

        this.getFirstLink().focus();
    }

    onArrowLeft(e) {
        if(this.isOpen()) {
            e.preventDefault();
            e.stopPropagation();
            this.close();
            $(this.trigger).focus();
        }
    }

    onArrowRight(e) {
        e.preventDefault();
        e.stopPropagation();
        this.getSubMenu().show();
        this.getFirstLink().focus();
    }

    onArrowUp(e) {
        if(!this.isOpen()) {
            // Let parent dropdown handle
            return;
        }

        super.onArrowUp(e);
    }

    onArrowDown(e) {
        if(!this.isOpen()) {
            // Let parent dropdown handle
            return;
        }

        super.onArrowDown(e);
    }

    open() {
        this.getSubMenu().show();
        this.trigger.setAttribute('aria-expanded', 'true');
    }

    close() {
        this.getSubMenu().css('display', '');
        this.trigger.setAttribute('aria-expanded', 'false');
    }

    getSubMenu() {
        return this.$.find('.ProseMirror-menu-submenu');
    }

    focusNext() {
        let $focused = this.getSubMenu().find('a:focus');
        let $parent = $focused.parent('.'+buildMenuClass('dropdown-item'));
        let $next = $parent.next();
        $next = $next.length ? $next.find('a:visible:first') : this.getFirstLink();
        $next.focus();
    }

    isOpen() {
        return this.getSubMenu().is(':visible');
    }
}