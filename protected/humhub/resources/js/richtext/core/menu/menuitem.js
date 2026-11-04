// ::- An icon or label that, when clicked, executes a command.
import {getIcon} from "./icons";
import {
    buildMenuClass,
    initMenuItemTrigger,
    setAttribute,
    setAttributesFromOptions,
    setClass,
    setTabindex,
    setTitle
} from "./menu-helper";

// MenuItemSpec:: interface
// The configuration object passed to the `MenuItem` constructor.
//
//   run:: (EditorState, (Transaction), EditorView, dom.Event)
//   The function to execute when the menu item is activated.
//
//   select:: ?(EditorState) → bool
//   Optional function that is used to determine whether the item is
//   appropriate at the moment. Deselected items will be hidden.
//
//   enable:: ?(EditorState) → bool
//   Function that is used to determine if the item is enabled. If
//   given and returning false, the item will be given a disabled
//   styling.
//
//   active:: ?(EditorState) → bool
//   A predicate function to determine whether the item is 'active' (for
//   example, the item for toggling the strong mark might be active then
//   the cursor is in strong text).
//
//   render:: ?(EditorView) → dom.Node
//   A function that renders the item. You must provide either this,
//   [`icon`](#menu.MenuItemSpec.icon), or [`label`](#MenuItemSpec.label).
//
//   icon:: ?Object
//   Describes an icon to show for this item. The object may specify
//   an SVG icon, in which case its `path` property should be an [SVG
//   path
//   spec](https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/d),
//   and `width` and `height` should provide the viewbox in which that
//   path exists. Alternatively, it may have a `text` property
//   specifying a string of text that makes up the icon, with an
//   optional `css` property giving additional CSS styling for the
//   text. _Or_ it may contain `dom` property containing a DOM node.
//
//   label:: ?string
//   Makes the item show up as a text label. Mostly useful for items
//   wrapped in a [drop-down](#menu.Dropdown) or similar menu. The object
//   should have a `label` property providing the text to display.
//
//   title:: ?union<string, (EditorState) → string>
//   Defines DOM title (mouseover) text for the item.
//
//   class:: string
//   Optionally adds a CSS class to the item's DOM representation.
//
//   css:: string
//   Optionally adds a string of inline CSS to the item's DOM
//   representation.
//
//   execEvent:: string
//   Defines which event on the command's DOM representation should
//   trigger the execution of the command. Defaults to mousedown.
export class MenuItem {
    // :: (MenuItemSpec)
    constructor(options) {
        // :: MenuItemSpec
        // The options used to create the menu item.
        this.options = options || {};
        this.sortOrder = this.options.sortOrder;
        this.options.htmlNode = 'button';
    }

    // :: (EditorView) → {dom: dom.Node, update: (EditorState) → bool}
    // Renders the icon according to its [display
    // options](#menu.MenuItemSpec.display), and adds an event handler which
    // executes the command when the representation is clicked.
    render(view, renderOptions = {}) {
        this.options = $.extend(this.options, renderOptions);

        if (typeof this.options.render === 'function') {
            return this.options.render.apply(this, [this.options]);
        }

        this.dom = initMenuItemTrigger(view, this.options);

        if (!this.dom) {
            throw new RangeError("MenuItem without icon or label property");
        }

        this.$ = $(this.dom);

        setAttributesFromOptions(this.dom, this.options);
        setTabindex(this.dom, renderOptions);
        setTitle(this.dom, this.options, view);
        this.initEvents(view);

        return this.dom;
    }

    initEvents(view) {
        const runHandler = e => {
            e.preventDefault();
            if (!this.$.hasClass(buildMenuClass('disabled'))) {
                this.options.run.call(this, view.state, view.dispatch, view, e);
            }
        };

        $(this.dom).on("mousedown", runHandler);
        $(this.dom).on("keydown", e => {
            const keyCode = e.keyCode || e.which;

            switch (keyCode) {
                case 13: // Enter
                    e.preventDefault();
                    runHandler(e);
                    break;
            }
        });
    }

    getMenuBar() {
        if (!this.menuBar) {
            this.menuBar = $(this.dom).closest('.ProseMirror-menubar').data('menuBarInstance');
        }

        return this.menuBar;
    }

    switchIcon(icon, title) {
        if (title) {
            $(this.dom).attr('title', title);
        }
        $(this.dom).find('svg').replaceWith($(getIcon(icon, this.options.htmlNode)).find('svg'));
    }

    update(state) {
        this.adoptItemState(state);
        return this.selected;
    }

    adoptItemState(state, forceEnable, forceActive) {
        this.setEnabledItemState(state, forceEnable);
        this.setActiveItemState(state, forceActive);
        this.setSelectedItemState(state, forceEnable);
    }

    setActiveItemState(state, forceActive) {
        this.active = false;
        if (this.options.active) {
            this.active = (this.options.active(state) || forceActive) || false;
            setClass(this.dom, buildMenuClass('active'), this.active);
            setAttribute(this.dom, 'aria-pressed', 'true', this.active);
        }
    }

    setEnabledItemState(state, forceEnable) {
        this.enabled = true;
        if (this.options.enable) {
            this.enabled = this.options.enable(state) || forceEnable || false;
            setClass(this.dom, buildMenuClass('disabled'), !this.enabled);
            setAttribute(this.dom, 'aria-disabled', 'true', !this.enabled);
        }
    }

    setSelectedItemState(state, forceEnable) {
        this.selected = true;
        if (this.options.select) {
            this.selected = this.options.select(state);
            this.dom.style.display = this.selected || forceEnable ? "" : "none";
            this.setHidden(!this.selected);
        }
    }

    setHidden(isHidden) {
        setAttribute(this.dom, 'aria-hidden', 'true', isHidden);
        setClass(this.dom, 'd-none', isHidden);

        if (this.isFocusable() && isHidden) {
            setAttribute(this.dom, 'tabindex', '-1', isHidden);
        }
    }

    isFocusable() {
        return ['a', 'button', 'select', 'input'].includes(this.dom.tagName.toLocaleLowerCase());
    }
}
