import crelt from "crelt";
import {Plugin} from "prosemirror-state";

import {icons, liftItem, MenuItem, MenuItemGroup} from "./menu";
import {buildMenuClass} from "./menu-helper";

const prefix = "ProseMirror-menubar";

function buildMenuItems(context) {
    let groups = {
        types: {
            type: 'dropdown',
            id: 'type',
            toggleSelect: false,
            sortOrder: 100,
            title: context.translate("Type"),
            seperator: true,
            icon: icons.text,
            items: []
        },
        marks: {type: 'group', id: 'marks-group', sortOrder: 200, items: []},
        format: {type: 'group', id: 'format-group', sortOrder: 300, items: [liftItem()]},
        insert: {
            type: 'dropdown',
            id: 'insert-dropdown',
            sortOrder: 400,
            title: context.translate("Insert"),
            seperator: true,
            icon: icons.upload,
            items: []
        },
        helper: {
            type: 'group',
            id: 'helper-group',
            hideOnCollapse: true,
            sortOrder: 500,
            items: []
        },
        resize: {type: 'group', id: 'resize-group', sortOrder: 600, items: []},
    };

    let definitions = [groups.types, groups.marks, groups.format, groups.insert, groups.helper, groups.mode, groups.resize];

    let menuGroupPlugins = [];
    let menuWrapperPlugins = [];

    context.plugins.forEach(function (plugin) {
        if (plugin.menu) {
            plugin.menu(context).forEach(function (menuDefinition) {
                if (checkMenuDefinition(context, menuDefinition)) {
                    if (menuDefinition.type && menuDefinition.type === 'group') {
                        definitions.push(menuDefinition);
                        return;
                    }

                    if (menuDefinition.item && menuDefinition.id) {
                        // transfer the id of the definition to the item itself
                        menuDefinition.item.options.id = menuDefinition.id;
                    }

                    if (menuDefinition.group && groups[menuDefinition.group]) {
                        groups[menuDefinition.group].items.push(menuDefinition.item);
                    } else if (menuDefinition.item && !menuDefinition.group) {
                        definitions.push(menuDefinition.item);
                    }
                }
            });
        }

        if (plugin.menuGroups) {
            menuGroupPlugins.push(plugin);
        }

        if (plugin.menuWrapper) {
            menuWrapperPlugins.push(plugin);
        }
    });

    // Execute after all menu items are assembled
    menuGroupPlugins.forEach(function (plugin) {
        definitions = plugin.menuGroups(definitions, context);
    });

    context.menuWrapperPlugins = menuWrapperPlugins;

    return filterOutExcludes(context, definitions);
}

function filterOutExcludes(context, definition) {
    if (!definition) {
        return false;
    }

    if (Array.isArray(definition)) {
        return definition.filter(item => {
            return filterOutExcludes(context, item);
        });
    }

    if (definition instanceof MenuItemGroup) {
        definition.content.items = definition.content.items.filter(item => {
            return filterOutExcludes(context, item);
        });
    }

    if (typeof definition === 'object' && definition.items) {
        definition.items = definition.items.filter(item => {
            return filterOutExcludes(context, item);
        });
    }

    let id = definition.id;
    if (!id && definition.options) {
        id = definition.options.id;
    }

    return definition && !isExcludedMenuItem(context, id);
}

function wrapMenuItem(plugin, context, menuItem) {
    if (!menuItem) {
        return;
    }

    if (!plugin.menuWrapper) {
        return;
    }

    if ($.isArray(menuItem)) {
        menuItem.forEach((item) => {
            wrapMenuItem(plugin, context, item);
        })
    }

    let wrapper = plugin.menuWrapper(context);

    if (menuItem instanceof MenuItem) {
        if (wrapper.run) {
            let origCallback = menuItem.options.run;
            menuItem.options.run = function (state, dispatch, view, evt) {
                let result = wrapper.run(menuItem, state, dispatch, view, evt);
                if (!result) {
                    origCallback.call(menuItem, state, dispatch, view, evt);
                }
            };
        }

        if (wrapper.active) {
            let origCallback = menuItem.options.active;
            menuItem.options.active = function (state) {
                let origValue = origCallback ? origCallback.call(menuItem, state) : false;
                return wrapper.active(menuItem, state, origValue);
            };
        }

        if (wrapper.enable) {
            let origCallback = menuItem.options.enable;
            menuItem.options.enable = function (state) {
                let origValue = origCallback ? origCallback.call(menuItem, state) : true;
                return wrapper.enable(menuItem, state, origValue);
            };
        }

        if (wrapper.select) {
            let origCallback = menuItem.options.select;
            menuItem.options.select = function (state) {
                let origValue = origCallback ? origCallback.call(menuItem, state) : true;
                return wrapper.select(menuItem, state, origValue);
            };
        }
    }

    if (menuItem.items) {
        wrapMenuItem(plugin, context, menuItem.items);
    }

    if (menuItem instanceof MenuItemGroup) {
        wrapMenuItem(plugin, context, menuItem.content.items);
    }
}

function checkMenuDefinition(context, menuDefinition) {
    if (!menuDefinition || menuDefinition.node && !context.schema.nodes[menuDefinition.node]) {
        return false;
    }

    return !(menuDefinition.mark && !context.schema.marks[menuDefinition.mark]);
}

function isExcludedMenuItem(context, id) {
    let presetOption = context.getPresetOption('menu', 'exclude', []);
    if (Array.isArray(presetOption) && presetOption.includes(id)) {
        return true;
    }

    let globalOption = context.getGlobalOption('menu', 'exclude', []);
    if (Array.isArray(globalOption) && globalOption.includes(id)) {
        return true;
    }

    let contextOption = context.getPluginOption('menu', 'exclude', []);
    return Array.isArray(contextOption) && contextOption.includes(id);
}

export function buildMenuBar(context) {
    context.menu = menuBar({
        content: buildMenuItems(context),
        floating: false,
        context: context
    });

    return context.menu;
}


function isIOS() {
    if (typeof navigator == "undefined") return false;
    let agent = navigator.userAgent;
    return !/Edge\/\d/.test(agent) && /AppleWebKit/.test(agent) && /Mobile\/\w+/.test(agent);
}

// :: (Object) → Plugin
// A plugin that will place a menu bar above the editor. Note that
// this involves wrapping the editor in an additional `<div>`.
//
//   options::-
//   Supports the following options:
//
//     content:: [[MenuElement]]
//     Provides the content of the menu, as a nested array to be
//     passed to `renderGrouped`.
//
//     floating:: ?bool
//     Determines whether the menu floats, i.e. whether it sticks to
//     the top of the viewport when the editor is partially scrolled
//     out of view.
export function menuBar(options) {
    return new Plugin({
        view(editorView) {
            options.context.menu = new MenuBarView(editorView, options);
            options.context.event.trigger('afterMenuBarInit', options.context.menu);
            return options.context.menu;
        }
    })
}

function translate(view, text) {
    return view._props.translate ? view._props.translate(text) : text;
}

let lastFocusedElement = null;

class MenuBarView {
    constructor(editorView, options) {
        this.editorView = editorView;
        this.options = options;
        this.context = this.options.context;
        this.focusIconIndex = 0;
        const $editor = this.context.editor.$;
        this.wrapper = crelt("div", {class: prefix + "-wrapper"});

        this.menu = this.wrapper.appendChild(crelt("div", {
            class: prefix,
            'aria-label': translate(editorView, 'Text Formatting'),
            'aria-controls': $editor.attr('id'),
            role: 'toolbar'
        }));

        this.menu.className = prefix;
        this.spacer = null;

        editorView.dom.parentNode.replaceChild(this.wrapper, editorView.dom);
        this.wrapper.appendChild(editorView.dom);

        this.maxHeight = 0;
        this.widthForMaxHeight = 0;
        this.floating = false;
        this.eventType = 'click';

        this.groupItem = new MenuItemGroup(this.options.content, {id: 'main-menu-group'});

        this.context.menuWrapperPlugins.forEach((plugin) => {
            wrapMenuItem(plugin, this.context, this.groupItem);
        });

        // TODO: In case of focus menu render only on first focus
        this.menu.appendChild(this.groupItem.render(this.editorView));

        this.$ = $(this.menu);

        // Focus and blur editor handler
        if ($editor.is('.focusMenu')) {
            this.$.addClass('d-none');

            $editor.off('focus', '.ProseMirror, textarea').off('blur', '.ProseMirror, textarea')
                .on('focus', '.ProseMirror, textarea', (event) => {
                    if (this.$.data('isHiding')) {
                        this.$.data('isHiding', false)
                            .addClass('d-none')
                            .stop(true, false);
                    }
                    if (this.$.hasClass('d-none')) {
                        this.$.removeClass('d-none');
                        const that = this;

                        $editor.on('keyup', (e) => {
                            const code = e.keyCode ? e.keyCode : e.which;
                            if (code === 9 && lastFocusedElement !== $(lastFocusedElement).closest(event.target).prevObject[0]) {
                                setTimeout(() => {
                                    $(that.groupItem.dom).find('.' + buildMenuClass('trigger:first')).attr('tabindex', 0).focus();
                                    $editor.off('keyup');
                                },0);
                            }
                        });
                    }
                })
                .on('blur', '.ProseMirror, textarea', (e) => {
                    const targetHasMenuBtn = e.relatedTarget ? e.relatedTarget.classList.contains('ProseMirror-menu-trigger') : false;
                    lastFocusedElement = e.target;

                    if (!$editor.is('.fullscreen') && !targetHasMenuBtn && !$(e.target).hasClass('cm-editor')) {
                        lastFocusedElement = null;
                        const that = this.$;
                        that.data('isHiding', true)
                            // Don't apply the d-none immediately to allow clicking on links which might move after hiding the toolbar
                            .hide(100, function(){that.addClass('d-none')});
                    }
                });
        }

        this.$.on('mousedown', (e) => {
            // Prevent focusout if we click outside a menu item, but still inside menu container
            e.preventDefault();
        }).on("keydown", (e) => {
            const keyCode = e.keyCode || e.which;

            switch (keyCode) {
                case 39: // ArrowRight
                    e.preventDefault();
                    this.focusNext();
                    break;
                case 37: // ArrowLeft
                    e.preventDefault();
                    this.focusPrev();
                    break;
            }
        });

        this.$.data('menuBarInstance', this);

        this.update();

        if (options.floating && !isIOS()) {
            this.updateFloat();
            this.scrollFunc = () => {
                let root = this.editorView.root;
                if (!(root.body || root).contains(this.wrapper))
                    window.removeEventListener("scroll", this.scrollFunc);
                else
                    this.updateFloat();
            };
            window.addEventListener("scroll", this.scrollFunc);
        }
    }

    update() {
        this.groupItem.update(this.editorView.state, this.context);

        if (this.floating) {
            this.updateScrollCursor();
        } else {
            if (this.menu.offsetWidth !== this.widthForMaxHeight) {
                this.widthForMaxHeight = this.menu.offsetWidth;
                this.maxHeight = 0
            }
            if (this.menu.offsetHeight > this.maxHeight) {
                this.maxHeight = this.menu.offsetHeight;
            }
        }

        this.context.event.trigger('afterMenuBarUpdate', this);
    }

    focusPrev() {
        let $prev = null;
        let $focus = null;
        let newFocusIconIndex = 0;
        let $current = this.$.find('.' + buildMenuClass('trigger:focus'));
        let $items = this.$.find('.' + buildMenuClass('trigger'));

        $items.each(function (index) {
            let $this = $(this);

            if ($this.is($current)) {
                $focus = $prev;
                newFocusIconIndex = index - 1;
            }

            $this.attr('tabindex', -1);

            if ($this.is(':visible')) {
                $prev = $this;
            }
        });

        if (!$focus) {
            $focus = $items.last();
            newFocusIconIndex = $items.length - 1;
        }

        this.focusIconIndex = newFocusIconIndex;

        $focus.attr('tabindex', 0).focus();
    }

    focusNext() {
        let $next = null;
        let newFocusIconIndex = 0;
        let focusNextItem = false;
        let $current = this.$.find('.' + buildMenuClass('trigger:focus'));

        this.$.find('.' + buildMenuClass('trigger')).each(function (index) {
            let $this = $(this);
            if (!$this.is(':visible')) {
                return;
            }

            if (focusNextItem) {
                $next = $this;
                focusNextItem = false;
                newFocusIconIndex = index;
            } else {
                $this.attr('tabindex', -1);
                focusNextItem = $this.is($current);
            }
        });

        if (!$next) {
            $next = this.$.find('.' + buildMenuClass('trigger:first'));
        }

        this.focusIconIndex = newFocusIconIndex;

        $next.attr('tabindex', 0).focus();
    }

    updateScrollCursor() {
        let selection = this.editorView.root.getSelection();
        if (!selection.focusNode) return;
        let rects = selection.getRangeAt(0).getClientRects();
        let selRect = rects[selectionIsInverted(selection) ? 0 : rects.length - 1];
        if (!selRect) return;
        let menuRect = this.menu.getBoundingClientRect();
        if (selRect.top < menuRect.bottom && selRect.bottom > menuRect.top) {
            let scrollable = findWrappingScrollable(this.wrapper);
            if (scrollable) scrollable.scrollTop -= (menuRect.bottom - selRect.top);
        }
    }

    updateFloat() {
        let parent = this.wrapper, editorRect = parent.getBoundingClientRect();
        if (this.floating) {
            if (editorRect.top >= 0 || editorRect.bottom < this.menu.offsetHeight + 10) {
                this.floating = false;
                this.menu.style.position = this.menu.style.left = this.menu.style.width = "";
                this.menu.style.display = "";
                this.spacer.parentNode.removeChild(this.spacer);
                this.spacer = null;
            } else {
                let border = (parent.offsetWidth - parent.clientWidth) / 2;
                this.menu.style.left = (editorRect.left + border) + "px";
                this.menu.style.display = (editorRect.top > window.innerHeight ? "none" : "");
            }
        } else {
            if (editorRect.top < 0 && editorRect.bottom >= this.menu.offsetHeight + 10) {
                this.floating = true;
                let menuRect = this.menu.getBoundingClientRect();
                this.menu.style.left = menuRect.left + "px";
                this.menu.style.width = menuRect.width + "px";
                this.menu.style.position = "fixed";
                this.spacer = crel("div", {class: prefix + "-spacer", style: `height: ${menuRect.height}px`});
                parent.insertBefore(this.spacer, this.menu);
            }
        }
    }

    destroy() {
        if (this.wrapper.parentNode)
            this.wrapper.parentNode.replaceChild(this.editorView.dom, this.wrapper);
    }
}

// Not precise, but close enough
function selectionIsInverted(selection) {
    if (selection.anchorNode === selection.focusNode)
        return selection.anchorOffset > selection.focusOffset;
    return selection.anchorNode.compareDocumentPosition(selection.focusNode) === Node.DOCUMENT_POSITION_FOLLOWING;
}

function findWrappingScrollable(node) {
    for (let cur = node.parentNode; cur; cur = cur.parentNode)
        if (cur.scrollHeight > cur.clientHeight) return cur;
}
