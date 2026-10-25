/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from core/vue/ via `grunt build-vue --module=core`.
 * See docs/develop/ui-js-vuejs.md
 */
(function(vue$1, vue) {
  "use strict";
  const _export_sfc = (sfc, props) => {
    const target = sfc.__vccOpts || sfc;
    for (const [key, val] of props) {
      target[key] = val;
    }
    return target;
  };
  const _sfc_main$w = {
    name: "CardSkeleton"
  };
  const _hoisted_1$u = {
    class: "card entity-card entity-card-skeleton placeholder-glow",
    "aria-hidden": "true"
  };
  function _sfc_render$w(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$u, [..._cache[0] || (_cache[0] = [
      vue.createStaticVNode('<div class="entity-card-cover entity-card-cover-plain"><span class="placeholder entity-card-skeleton-image"></span></div><div class="entity-card-header"><span class="placeholder entity-card-skeleton-title"></span><span class="placeholder entity-card-skeleton-subtitle"></span></div><div class="entity-card-body"><span class="placeholder entity-card-skeleton-line"></span><span class="placeholder entity-card-skeleton-line entity-card-skeleton-line-short"></span></div><div class="entity-card-footer"><span class="placeholder entity-card-skeleton-action"></span><span class="placeholder entity-card-skeleton-icon"></span></div>', 4)
    ])]);
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$w, [["render", _sfc_render$w]]);
  const FORM_CONTEXT_KEY = "humhubForm";
  const ID_REPLACEMENTS = [
    ["[]", ""],
    ["][", "-"],
    ["[", "-"],
    ["]", ""],
    [" ", "-"],
    [".", "-"],
    ["--", "-"]
  ];
  function toInputId(name) {
    return ID_REPLACEMENTS.reduce(
      (value, [search, replace]) => value.split(search).join(replace),
      name.toLowerCase()
    );
  }
  const fieldMixin = {
    inject: {
      humhubForm: { from: FORM_CONTEXT_KEY, default: null }
    },
    props: {
      attribute: { type: String, required: true },
      label: { type: String, default: null },
      hint: { type: String, default: null },
      placeholder: { type: String, default: null },
      // Visual marker only (a "required" wrapper class + aria-required on the
      // input) — see each field's own docblock. Validation stays server-side (Yii
      // model rules are the single source of truth); this prop never blocks
      // submission client-side.
      required: { type: Boolean, default: false },
      disabled: { type: Boolean, default: false }
    },
    computed: {
      formModelName() {
        return this.humhubForm ? this.humhubForm.modelName.value : "";
      },
      formErrors() {
        return this.humhubForm ? this.humhubForm.errors.value : {};
      },
      formBusy() {
        return this.humhubForm ? this.humhubForm.busy.value : false;
      },
      fieldName() {
        return this.formModelName ? `${this.formModelName}[${this.attribute}]` : this.attribute;
      },
      fieldId() {
        return toInputId(this.fieldName);
      },
      hintId() {
        return this.hint ? `${this.fieldId}-hint` : null;
      },
      errorId() {
        return this.hasError ? `${this.fieldId}-error` : null;
      },
      describedBy() {
        return [this.hintId, this.errorId].filter(Boolean).join(" ") || null;
      },
      errorMessages() {
        const messages = this.formErrors[this.attribute];
        return Array.isArray(messages) ? messages : [];
      },
      hasError() {
        return this.errorMessages.length > 0;
      },
      isDisabled() {
        return this.disabled || this.formBusy;
      }
    },
    methods: {
      clearOwnError() {
        if (this.hasError && this.humhubForm) {
          this.humhubForm.clearError(this.attribute);
        }
      }
    },
    mounted() {
      if (this.humhubForm) {
        this.humhubForm.registerField(this.attribute, this);
      }
    },
    beforeUnmount() {
      if (this.humhubForm) {
        this.humhubForm.unregisterField(this.attribute, this);
      }
    }
  };
  const _sfc_main$v = {
    mixins: [fieldMixin],
    props: {
      modelValue: { type: Boolean, default: false }
    },
    emits: ["update:modelValue"],
    computed: {
      internalValue: {
        get() {
          return this.modelValue;
        },
        set(value) {
          this.$emit("update:modelValue", value);
          this.clearOwnError();
        }
      }
    },
    methods: {
      focus() {
        if (this.$refs.input) {
          this.$refs.input.focus();
        }
      }
    }
  };
  const _hoisted_1$t = { class: "form-check" };
  const _hoisted_2$n = ["id", "name", "disabled", "aria-required", "aria-invalid", "aria-describedby"];
  const _hoisted_3$l = ["for"];
  const _hoisted_4$k = ["id"];
  const _hoisted_5$g = ["id"];
  function _sfc_render$v(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: vue.normalizeClass(["mb-3", [`field-${_ctx.fieldId}`, { required: _ctx.required }]])
      },
      [
        vue.createElementVNode("div", _hoisted_1$t, [
          vue.withDirectives(vue.createElementVNode("input", {
            ref: "input",
            id: _ctx.fieldId,
            name: _ctx.fieldName,
            type: "checkbox",
            class: vue.normalizeClass(["form-check-input", { "is-invalid": _ctx.hasError }]),
            value: "1",
            disabled: _ctx.isDisabled,
            "aria-required": _ctx.required ? "true" : null,
            "aria-invalid": _ctx.hasError ? "true" : null,
            "aria-describedby": _ctx.describedBy,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $options.internalValue = $event)
          }, null, 10, _hoisted_2$n), [
            [vue.vModelCheckbox, $options.internalValue]
          ]),
          _ctx.label ? (vue.openBlock(), vue.createElementBlock("label", {
            key: 0,
            for: _ctx.fieldId,
            class: "form-check-label"
          }, vue.toDisplayString(_ctx.label), 9, _hoisted_3$l)) : vue.createCommentVNode("v-if", true),
          _ctx.hasError ? (vue.openBlock(), vue.createElementBlock("div", {
            key: 1,
            id: _ctx.errorId,
            class: "invalid-feedback"
          }, [
            (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              null,
              vue.renderList(_ctx.errorMessages, (message, index) => {
                return vue.openBlock(), vue.createElementBlock(
                  "div",
                  { key: index },
                  vue.toDisplayString(message),
                  1
                  /* TEXT */
                );
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ], 8, _hoisted_4$k)) : vue.createCommentVNode("v-if", true),
          _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
            key: 2,
            id: _ctx.hintId,
            class: "form-text text-muted"
          }, vue.toDisplayString(_ctx.hint), 9, _hoisted_5$g)) : vue.createCommentVNode("v-if", true)
        ])
      ],
      2
      /* CLASS */
    );
  }
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main$v, [["render", _sfc_render$v]]);
  const _sfc_main$u = {
    props: {
      toggleAriaLabel: { type: String, required: true },
      toggleTitle: { type: String, default: null },
      alignEnd: { type: Boolean, default: true },
      toggleClass: { type: String, default: "nav-link dropdown-toggle" },
      rootClass: { type: String, default: "nav nav-pills preferences" },
      menuId: { type: String, default: null },
      entries: { type: Array, default: () => [] },
      context: { type: Object, default: () => ({}) },
      // Renders a disabled spinner item while the consumer is still resolving what belongs
      // in this menu - see the `open` event below.
      loading: { type: Boolean, default: false }
    },
    // `open` fires when the menu is actually opened (not on the closing click), so a consumer
    // can load menu content on demand instead of up front - Bootstrap's own
    // `show.bs.dropdown` is the signal, since toggling is Bootstrap-owned (see the docblock).
    emits: ["open"],
    created() {
      this.pointerPosition = null;
      this.ownDropdown = null;
      this.shown = false;
    },
    mounted() {
      this.$refs.toggle.addEventListener("show.bs.dropdown", this.onShow);
      this.$refs.toggle.addEventListener("hidden.bs.dropdown", this.onHidden);
    },
    beforeUnmount() {
      var _a;
      this.$refs.toggle.removeEventListener("show.bs.dropdown", this.onShow);
      this.$refs.toggle.removeEventListener("hidden.bs.dropdown", this.onHidden);
      this.disposeOwnDropdown();
      if (typeof bootstrap !== "undefined" && this.$refs.toggle) {
        (_a = bootstrap.Dropdown.getInstance(this.$refs.toggle)) == null ? void 0 : _a.dispose();
      }
    },
    computed: {
      loadingLabel() {
        return vue$1.i18n.t("base", "Loading...");
      },
      resolvedEntries() {
        if (!this.menuId) {
          return [];
        }
        const registry = vue$1.getMenuEntries(this.menuId);
        const registryById = new Map(registry.entries.map((entry) => [entry.id, entry]));
        const usedRegistryIds = /* @__PURE__ */ new Set();
        const merged = this.entries.map((entry) => {
          const override = registryById.get(entry.id);
          if (override) {
            usedRegistryIds.add(entry.id);
            return override;
          }
          return entry;
        });
        registry.entries.forEach((entry) => {
          if (!usedRegistryIds.has(entry.id)) {
            merged.push(entry);
          }
        });
        const removed = registry.removed;
        const context = this.context;
        const sortOrderOf = (entry) => typeof entry.sortOrder === "number" ? entry.sortOrder : 1e3;
        return merged.filter((entry) => removed.indexOf(entry.id) === -1 && (!entry.condition || entry.condition(context)) && (!entry.component || vue$1.isRegistered(entry.component))).map((entry, index) => ({ entry, index })).sort((a, b) => sortOrderOf(a.entry) - sortOrderOf(b.entry) || a.index - b.index).map((wrapped) => wrapped.entry);
      }
    },
    methods: {
      /**
       * Opens this menu — at the pointer when handed a mouse event, under the toggle
       * otherwise.
       *
       * The pointer case is what a host's `@contextmenu` handler wants: a right-click
       * anywhere on a row should raise that row's menu where the cursor is, which is what
       * the platform's legacy `$.fn.contextMenu` did for server-rendered lists (see
       * `humhub.ui.additions.js`). Hosts stay out of Bootstrap and out of positioning:
       *
       * ```html
       * <div @contextmenu.prevent="$refs.menu.open($event)">
       *     <DropdownMenu ref="menu" … />
       * </div>
       * ```
       *
       * Leave the argument off to open the menu as a click on the toggle would.
       */
      open(event = null) {
        const dropdown = this.dropdown();
        if (!dropdown) {
          this.$refs.toggle.click();
          return;
        }
        if (this.shown) {
          dropdown.hide();
        }
        this.pointerPosition = event ? { x: event.clientX, y: event.clientY } : null;
        dropdown.show();
      },
      /**
       * This menu's Bootstrap dropdown, positioned against {@link referenceRect}.
       *
       * Owned here rather than left to Bootstrap's data-api, because only an instance we
       * created can be given a `reference` — and that same instance goes on serving plain
       * clicks on the toggle, which is why the reference falls back to the toggle's own box.
       *
       * @return {?object} null when Bootstrap is not available
       */
      dropdown() {
        const Dropdown = typeof bootstrap === "undefined" ? null : bootstrap.Dropdown;
        if (!Dropdown) {
          return null;
        }
        const toggle = this.$refs.toggle;
        const current = Dropdown.getInstance(toggle);
        if (current && current === this.ownDropdown) {
          return current;
        }
        if (current) {
          current.dispose();
        }
        this.ownDropdown = new Dropdown(toggle, {
          reference: { getBoundingClientRect: this.referenceRect },
          // A context menu opens down and to the right of the cursor, whatever
          // `alignEnd` says about where the menu sits under its toggle.
          popperConfig: (_unused, defaults) => this.pointerPosition ? { ...defaults, placement: "bottom-start" } : defaults
        });
        return this.ownDropdown;
      },
      /**
       * The box Popper positions the menu against: a zero-size rect at the pointer while a
       * pointer-opened menu is up (Popper's virtual-element convention), and the toggle's
       * own box the rest of the time.
       */
      referenceRect() {
        if (!this.pointerPosition) {
          return this.$refs.toggle.getBoundingClientRect();
        }
        const { x, y } = this.pointerPosition;
        return { width: 0, height: 0, top: y, right: x, bottom: y, left: x, x, y };
      },
      disposeOwnDropdown() {
        if (this.ownDropdown) {
          this.ownDropdown.dispose();
          this.ownDropdown = null;
        }
      },
      onShow() {
        this.shown = true;
        this.$emit("open");
      },
      onHidden() {
        this.shown = false;
        this.pointerPosition = null;
      },
      resolveLabel(entry) {
        return typeof entry.label === "function" ? entry.label(this.context) : entry.label;
      },
      onEntryClick(entry, event) {
        if (typeof entry.onClick !== "function") {
          if (!entry.url) {
            event.preventDefault();
          }
          return;
        }
        event.preventDefault();
        entry.onClick(this.context);
      }
    }
  };
  const _hoisted_1$s = { class: "nav-item dropdown" };
  const _hoisted_2$m = ["aria-label", "title"];
  const _hoisted_3$k = { key: 0 };
  const _hoisted_4$j = { class: "dropdown-item disabled d-flex align-items-center gap-2" };
  const _hoisted_5$f = { role: "status" };
  const _hoisted_6$e = ["innerHTML"];
  const _hoisted_7$b = { key: 1 };
  const _hoisted_8$b = {
    key: 1,
    class: "dropdown-divider"
  };
  const _hoisted_9$8 = ["href", "onClick"];
  const _hoisted_10$7 = ["href", "onClick"];
  function _sfc_render$u(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return vue.openBlock(), vue.createElementBlock(
      "ul",
      {
        class: vue.normalizeClass($props.rootClass)
      },
      [
        vue.createElementVNode("li", _hoisted_1$s, [
          vue.createElementVNode("a", {
            ref: "toggle",
            href: "#",
            class: vue.normalizeClass($props.toggleClass),
            "data-bs-toggle": "dropdown",
            role: "button",
            "aria-haspopup": "true",
            "aria-expanded": "false",
            "aria-label": $props.toggleAriaLabel,
            title: $props.toggleTitle
          }, [
            vue.renderSlot(_ctx.$slots, "toggle")
          ], 10, _hoisted_2$m),
          vue.createElementVNode(
            "ul",
            {
              class: vue.normalizeClass(["dropdown-menu", { "dropdown-menu-end": $props.alignEnd }])
            },
            [
              vue.renderSlot(_ctx.$slots, "default"),
              $props.loading ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_3$k, [
                vue.createElementVNode("span", _hoisted_4$j, [
                  _cache[0] || (_cache[0] = vue.createElementVNode(
                    "span",
                    {
                      class: "spinner-border spinner-border-sm",
                      "aria-hidden": "true"
                    },
                    null,
                    -1
                    /* CACHED */
                  )),
                  vue.createElementVNode(
                    "span",
                    _hoisted_5$f,
                    vue.toDisplayString($options.loadingLabel),
                    1
                    /* TEXT */
                  )
                ])
              ])) : vue.createCommentVNode("v-if", true),
              (vue.openBlock(true), vue.createElementBlock(
                vue.Fragment,
                null,
                vue.renderList($options.resolvedEntries, (entry) => {
                  return vue.openBlock(), vue.createElementBlock(
                    vue.Fragment,
                    {
                      key: entry.id
                    },
                    [
                      entry.html ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("li", {
                        key: 0,
                        innerHTML: entry.html
                      }, null, 8, _hoisted_6$e)), [
                        [_directive_additions]
                      ]) : (vue.openBlock(), vue.createElementBlock("li", _hoisted_7$b, [
                        entry.component ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(entry.component), {
                          key: 0,
                          context: $props.context
                        }, null, 8, ["context"])) : entry.divider ? (vue.openBlock(), vue.createElementBlock("hr", _hoisted_8$b)) : entry.icon ? (vue.openBlock(), vue.createElementBlock("a", vue.mergeProps({
                          key: 2,
                          ref_for: true
                        }, entry.htmlOptions, {
                          href: entry.url || "#",
                          class: "dropdown-item",
                          onClick: ($event) => $options.onEntryClick(entry, $event)
                        }), [
                          vue.createElementVNode(
                            "i",
                            {
                              class: vue.normalizeClass("ti ti-" + entry.icon + " dropdown-item-icon"),
                              "aria-hidden": "true"
                            },
                            null,
                            2
                            /* CLASS */
                          ),
                          vue.createTextVNode(
                            vue.toDisplayString($options.resolveLabel(entry)),
                            1
                            /* TEXT */
                          )
                        ], 16, _hoisted_9$8)) : (vue.openBlock(), vue.createElementBlock("a", vue.mergeProps({
                          key: 3,
                          ref_for: true
                        }, entry.htmlOptions, {
                          href: entry.url || "#",
                          class: "dropdown-item",
                          onClick: ($event) => $options.onEntryClick(entry, $event)
                        }), vue.toDisplayString($options.resolveLabel(entry)), 17, _hoisted_10$7))
                      ]))
                    ],
                    64
                    /* STABLE_FRAGMENT */
                  );
                }),
                128
                /* KEYED_FRAGMENT */
              ))
            ],
            2
            /* CLASS */
          )
        ])
      ],
      2
      /* CLASS */
    );
  }
  const C5 = /* @__PURE__ */ _export_sfc(_sfc_main$u, [["render", _sfc_render$u]]);
  let uid$8 = 0;
  const _sfc_main$t = {
    name: "HeaderActions",
    components: { DropdownMenu: C5 },
    props: {
      actions: { type: Array, default: () => [] },
      menu: { type: Array, default: () => [] },
      rootClass: { type: String, required: true },
      toolVariant: { type: String, default: "secondary" }
    },
    data() {
      return {
        menuId: `header-menu-${++uid$8}`
      };
    },
    computed: {
      groups() {
        const groups = { tools: [], primary: null };
        for (const action of this.actions) {
          if (action.primary && !groups.primary) {
            groups.primary = action;
          } else {
            groups.tools.push(action);
          }
        }
        return groups;
      },
      menuEntries() {
        return this.menu.map((action) => ({
          id: action.id,
          icon: action.icon,
          label: action.label,
          url: action.url,
          htmlOptions: action.htmlOptions || {},
          onClick: action.modal ? () => vue$1.modal.load(action.url) : void 0
        }));
      },
      moreLabel() {
        return vue$1.i18n.t("base", "More actions");
      }
    },
    methods: {
      /**
       * Opens a `modal: true` action in the global modal; any other action is a plain link.
       */
      onAction(event, action) {
        if (action.modal) {
          event.preventDefault();
          vue$1.modal.load(action.url);
        }
      }
    }
  };
  const _hoisted_1$r = ["href", "aria-label", "title", "data-action-id", "onClick"];
  const _hoisted_2$l = ["href", "aria-label", "title", "data-action-id"];
  function _sfc_render$t(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DropdownMenu = vue.resolveComponent("DropdownMenu");
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: vue.normalizeClass($props.rootClass)
      },
      [
        (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          null,
          vue.renderList($options.groups.tools, (action) => {
            return vue.openBlock(), vue.createElementBlock("a", vue.mergeProps({
              key: action.id
            }, { ref_for: true }, action.htmlOptions || {}, {
              class: ["btn", `btn-${$props.toolVariant}`, "btn-icon-only"],
              href: action.url,
              "aria-label": action.label,
              title: action.label,
              "data-action-id": action.id,
              onClick: ($event) => $options.onAction($event, action)
            }), [
              vue.createElementVNode(
                "i",
                {
                  class: vue.normalizeClass(["ti", `ti-${action.icon}`]),
                  "aria-hidden": "true"
                },
                null,
                2
                /* CLASS */
              )
            ], 16, _hoisted_1$r);
          }),
          128
          /* KEYED_FRAGMENT */
        )),
        vue.renderSlot(_ctx.$slots, "default"),
        $options.groups.primary ? (vue.openBlock(), vue.createElementBlock("a", vue.mergeProps({ key: 0 }, $options.groups.primary.htmlOptions || {}, {
          class: ["btn", "btn-accent", "btn-icon-only"],
          href: $options.groups.primary.url,
          "aria-label": $options.groups.primary.label,
          title: $options.groups.primary.label,
          "data-action-id": $options.groups.primary.id,
          onClick: _cache[0] || (_cache[0] = ($event) => $options.onAction($event, $options.groups.primary))
        }), [
          vue.createElementVNode(
            "i",
            {
              class: vue.normalizeClass(["ti", `ti-${$options.groups.primary.icon}`]),
              "aria-hidden": "true"
            },
            null,
            2
            /* CLASS */
          )
        ], 16, _hoisted_2$l)) : vue.createCommentVNode("v-if", true),
        $props.menu.length ? (vue.openBlock(), vue.createBlock(_component_DropdownMenu, {
          key: 1,
          "menu-id": $data.menuId,
          entries: $options.menuEntries,
          "toggle-aria-label": $options.moreLabel,
          "toggle-title": $options.moreLabel,
          "toggle-class": "btn btn-ghost btn-icon-only",
          "root-class": `${$props.rootClass}-menu list-unstyled m-0`
        }, {
          toggle: vue.withCtx(() => [..._cache[1] || (_cache[1] = [
            vue.createElementVNode(
              "i",
              {
                class: "ti ti-dots-vertical",
                "aria-hidden": "true"
              },
              null,
              -1
              /* CACHED */
            )
          ])]),
          _: 1
          /* STABLE */
        }, 8, ["menu-id", "entries", "toggle-aria-label", "toggle-title", "root-class"])) : vue.createCommentVNode("v-if", true),
        vue.renderSlot(_ctx.$slots, "end")
      ],
      2
      /* CLASS */
    );
  }
  const HeaderActions = /* @__PURE__ */ _export_sfc(_sfc_main$t, [["render", _sfc_render$t]]);
  let uid$7 = 0;
  const _sfc_main$s = {
    name: "ContentCard",
    components: { HeaderActions },
    props: {
      title: { type: String, default: "" },
      titleTag: { type: String, default: "h2" },
      description: { type: String, default: "" },
      actions: { type: Array, default: () => [] },
      menu: { type: Array, default: () => [] }
    },
    data() {
      return {
        titleId: `card-title-${++uid$7}`
      };
    },
    computed: {
      hasHeader() {
        return !!(this.title || this.actions.length || this.menu.length || this.$slots.header || this.$slots.actions);
      }
    }
  };
  const _hoisted_1$q = {
    key: 0,
    class: "card-header"
  };
  const _hoisted_2$k = {
    key: 0,
    class: "card-heading"
  };
  const _hoisted_3$j = {
    key: 0,
    class: "card-subtitle"
  };
  const _hoisted_4$i = { class: "card-body" };
  const _hoisted_5$e = {
    key: 1,
    class: "card-footer"
  };
  function _sfc_render$s(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_HeaderActions = vue.resolveComponent("HeaderActions");
    return vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent($props.title ? "section" : "div"), {
      class: "card content-card",
      "aria-labelledby": $props.title ? $data.titleId : null
    }, {
      default: vue.withCtx(() => [
        $options.hasHeader ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$q, [
          $props.title ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$k, [
            (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent($props.titleTag), {
              id: $data.titleId,
              class: "card-title"
            }, {
              default: vue.withCtx(() => [
                vue.createTextVNode(
                  vue.toDisplayString($props.title),
                  1
                  /* TEXT */
                )
              ]),
              _: 1
              /* STABLE */
            }, 8, ["id"])),
            $props.description ? (vue.openBlock(), vue.createElementBlock(
              "p",
              _hoisted_3$j,
              vue.toDisplayString($props.description),
              1
              /* TEXT */
            )) : vue.createCommentVNode("v-if", true)
          ])) : vue.createCommentVNode("v-if", true),
          vue.renderSlot(_ctx.$slots, "header"),
          $props.actions.length || $props.menu.length || _ctx.$slots.actions ? (vue.openBlock(), vue.createBlock(_component_HeaderActions, {
            key: 1,
            actions: $props.actions,
            menu: $props.menu,
            "root-class": "card-header-actions",
            "tool-variant": "ghost"
          }, {
            default: vue.withCtx(() => [
              vue.renderSlot(_ctx.$slots, "actions")
            ]),
            _: 3
            /* FORWARDED */
          }, 8, ["actions", "menu"])) : vue.createCommentVNode("v-if", true)
        ])) : vue.createCommentVNode("v-if", true),
        vue.createElementVNode("div", _hoisted_4$i, [
          vue.renderSlot(_ctx.$slots, "default")
        ]),
        _ctx.$slots.footer ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$e, [
          vue.renderSlot(_ctx.$slots, "footer")
        ])) : vue.createCommentVNode("v-if", true)
      ]),
      _: 3
      /* FORWARDED */
    }, 8, ["aria-labelledby"]);
  }
  const C2 = /* @__PURE__ */ _export_sfc(_sfc_main$s, [["render", _sfc_render$s]]);
  const SEARCH_DEBOUNCE_MS$1 = 250;
  let uid$6 = 0;
  const toValues = (value) => {
    if (Array.isArray(value)) {
      return value.map(String).filter((entry) => entry !== "");
    }
    return value === null || value === void 0 || value === "" ? [] : [String(value)];
  };
  const normalize = (option) => ({
    value: String(option.value),
    label: String(option.label ?? option.value),
    count: option.count !== void 0 && option.count !== null ? Number(option.count) : null
  });
  const contains = (label, term) => term === "" || label.toLocaleLowerCase().includes(term.toLocaleLowerCase());
  const _sfc_main$r = {
    name: "FilterPicker",
    props: {
      modelValue: { type: [String, Array], default: "" },
      options: { type: Array, default: () => [] },
      optionsUrl: { type: String, default: null },
      multiple: { type: Boolean, default: false },
      allowCustom: { type: Boolean, default: false },
      placeholder: { type: String, default: "" },
      label: { type: String, default: "" },
      disabled: { type: Boolean, default: false },
      id: { type: String, default: null },
      reloadKey: { type: [Number, String], default: 0 }
    },
    emits: ["update:modelValue"],
    data() {
      const key = ++uid$6;
      return {
        open: false,
        activeIndex: -1,
        // The typed text, and (single mode) whether the input shows it instead of the choice.
        query: "",
        editing: false,
        // The last applied answer of `optionsUrl` and the text it answered (`null`: none yet).
        remote: [],
        remoteTerm: null,
        loading: false,
        failed: false,
        // Labels of values once seen among the options, by value.
        labels: {},
        listboxId: `filter-picker-${key}-listbox`,
        fallbackId: `filter-picker-${key}`
      };
    },
    computed: {
      inputId() {
        return this.id || this.fallbackId;
      },
      values() {
        return this.multiple ? toValues(this.modelValue) : toValues(this.modelValue).slice(0, 1);
      },
      hasSelection() {
        return this.values.length > 0;
      },
      term() {
        return this.query.trim();
      },
      staticOptions() {
        return this.options.filter((option) => String(option.value) !== "").map(normalize);
      },
      suggestions() {
        const seen = new Set(this.multiple ? this.values : []);
        const remote = this.remoteTerm === this.term ? this.remote : this.remote.filter((option) => contains(option.label, this.term));
        return [...this.staticOptions.filter((option) => contains(option.label, this.term)), ...remote].filter((option) => {
          if (seen.has(option.value)) {
            return false;
          }
          seen.add(option.value);
          return true;
        });
      },
      // The typed text as a choice of its own (`allowCustom`), unless a suggestion carries it.
      customChoice() {
        if (!this.allowCustom || this.multiple || this.term === "" || this.suggestions.some((option) => option.value === this.term)) {
          return null;
        }
        return { value: this.term, label: this.term, count: null, custom: true };
      },
      choices() {
        return this.customChoice ? [this.customChoice, ...this.suggestions] : this.suggestions;
      },
      chips() {
        return this.multiple ? this.values.map((value) => ({ value, label: this.labelOf(value) })) : [];
      },
      inputText() {
        if (this.multiple || this.editing) {
          return this.query;
        }
        return this.hasSelection ? this.labelOf(this.values[0]) : "";
      },
      placeholderText() {
        return this.placeholder || this.label;
      },
      inputPlaceholder() {
        if (this.multiple) {
          return this.hasSelection ? "" : this.placeholderText;
        }
        return this.editing && this.hasSelection ? this.labelOf(this.values[0]) : this.placeholderText;
      },
      activeDescendant() {
        return this.open && this.activeIndex >= 0 && this.activeIndex < this.choices.length ? this.optionId(this.activeIndex) : null;
      },
      accessibleName() {
        return this.label || this.placeholder || null;
      },
      feedback() {
        if (this.suggestions.length) {
          return null;
        }
        if (this.loading) {
          return this.loadingLabel;
        }
        return this.term !== "" ? vue$1.i18n.t("base", "No results found!") : vue$1.i18n.t("base", "No options");
      },
      statusText() {
        return this.feedback || "";
      },
      clearLabel() {
        return vue$1.i18n.t("base", "Clear selection");
      },
      loadingLabel() {
        return vue$1.i18n.t("base", "Loading...");
      }
    },
    watch: {
      disabled(disabled) {
        if (disabled) {
          this.close();
        }
      },
      options: {
        immediate: true,
        handler(options) {
          this.remember(options.map(normalize));
        }
      },
      choices(choices) {
        if (this.activeIndex >= choices.length) {
          this.activeIndex = choices.length - 1;
        }
      },
      reloadKey() {
        this.cache.clear();
        this.remote = [];
        this.remoteTerm = null;
        if (this.open) {
          this.load(this.term);
        }
      }
    },
    created() {
      this.cache = /* @__PURE__ */ new Map();
      this.requestSeq = 0;
      this.searchTimer = null;
    },
    beforeUnmount() {
      clearTimeout(this.searchTimer);
      this.unbindOutside();
    },
    methods: {
      optionId(index) {
        return `${this.listboxId}-${index}`;
      },
      labelOf(value) {
        return this.labels[value] ?? value;
      },
      remember(options) {
        options.forEach((option) => {
          this.labels[option.value] = option.label;
        });
      },
      isChosen(value) {
        return this.values.includes(value);
      },
      customLabel(text) {
        return vue$1.i18n.t("base", "Use “{text}”", { text });
      },
      removeLabel(label) {
        return vue$1.i18n.t("base", "Remove {label}", { label });
      },
      requestUrl(term) {
        if (term === "") {
          return this.optionsUrl;
        }
        return `${this.optionsUrl}${this.optionsUrl.includes("?") ? "&" : "?"}q=${encodeURIComponent(term)}`;
      },
      // Loads the suggestions for a text; only the answer to the latest request is applied.
      load(term) {
        if (!this.optionsUrl) {
          return;
        }
        clearTimeout(this.searchTimer);
        const seq = ++this.requestSeq;
        if (this.cache.has(term)) {
          this.remote = this.cache.get(term);
          this.remoteTerm = term;
          this.loading = false;
          this.failed = false;
          return;
        }
        this.loading = true;
        vue$1.client.get(this.requestUrl(term)).then((response) => {
          if (seq !== this.requestSeq) {
            return;
          }
          const options = (response.results || []).map((result) => normalize({ value: result.id, label: result.name, count: result.count }));
          this.cache.set(term, options);
          this.remember(options);
          this.remote = options;
          this.remoteTerm = term;
          this.failed = false;
        }).catch((response) => {
          if (seq !== this.requestSeq) {
            return;
          }
          this.failed = true;
          vue$1.log.error(response);
        }).finally(() => {
          if (seq === this.requestSeq) {
            this.loading = false;
          }
        });
      },
      // The first page is fetched once the input gets the focus, before anything is typed.
      onFocus() {
        if (this.optionsUrl && this.remoteTerm === null && !this.loading) {
          this.load("");
        }
      },
      onFieldClick() {
        var _a;
        if (this.disabled) {
          return;
        }
        (_a = this.$refs.input) == null ? void 0 : _a.focus();
        this.show(-1);
      },
      onInput(event) {
        this.query = event.target.value;
        this.editing = true;
        this.search();
        this.show(0, false);
        this.activeIndex = 0;
      },
      search() {
        if (!this.optionsUrl) {
          return;
        }
        clearTimeout(this.searchTimer);
        const term = this.term;
        if (term === "" || this.cache.has(term)) {
          this.load(term);
          return;
        }
        this.requestSeq++;
        this.loading = true;
        this.searchTimer = setTimeout(() => this.load(term), SEARCH_DEBOUNCE_MS$1);
      },
      show(activeIndex = -1, load = true) {
        if (this.disabled || this.open) {
          return;
        }
        this.open = true;
        this.activeIndex = activeIndex;
        this.bindOutside();
        if (load && this.optionsUrl && this.remoteTerm !== this.term && !this.loading) {
          this.load(this.term);
        }
      },
      close() {
        clearTimeout(this.searchTimer);
        if (this.loading && this.remoteTerm !== this.term) {
          this.requestSeq++;
          this.loading = false;
        }
        this.query = "";
        this.editing = false;
        if (!this.open) {
          return;
        }
        this.open = false;
        this.activeIndex = -1;
        this.unbindOutside();
      },
      choose(option) {
        var _a;
        this.remember([option]);
        if (this.multiple) {
          this.query = "";
          this.search();
          (_a = this.$refs.input) == null ? void 0 : _a.focus();
          if (!this.values.includes(option.value)) {
            this.$emit("update:modelValue", [...this.values, option.value]);
          }
          return;
        }
        this.close();
        if (option.value !== this.values[0]) {
          this.$emit("update:modelValue", option.value);
        }
      },
      remove(value) {
        var _a;
        (_a = this.$refs.input) == null ? void 0 : _a.focus();
        this.$emit("update:modelValue", this.values.filter((entry) => entry !== value));
      },
      clear() {
        var _a;
        this.query = "";
        this.editing = false;
        (_a = this.$refs.input) == null ? void 0 : _a.focus();
        if (this.hasSelection) {
          this.$emit("update:modelValue", this.multiple ? [] : "");
        }
      },
      moveTo(index) {
        if (index < 0 || !this.choices.length) {
          return;
        }
        this.activeIndex = Math.min(index, this.choices.length - 1);
        this.$nextTick(() => this.scrollActiveIntoView());
      },
      onKeydown(event) {
        const last = this.choices.length - 1;
        switch (event.key) {
          case "ArrowDown":
            event.preventDefault();
            if (!this.open) {
              this.show(0);
            } else {
              this.moveTo(Math.min(this.activeIndex + 1, last));
            }
            break;
          case "ArrowUp":
            event.preventDefault();
            if (!this.open) {
              this.show(last);
            } else {
              this.moveTo(Math.max(this.activeIndex - 1, 0));
            }
            break;
          case "Home":
          case "End":
            if (this.open && this.choices.length) {
              event.preventDefault();
              this.moveTo(event.key === "Home" ? 0 : last);
            }
            break;
          case "Enter":
            if (this.open || this.customChoice) {
              event.preventDefault();
              const active = this.open ? this.choices[this.activeIndex] : null;
              if (active || this.customChoice) {
                this.choose(active || this.customChoice);
              }
            }
            break;
          case "Escape":
            if (this.open || this.query !== "") {
              event.preventDefault();
              event.stopPropagation();
              if (this.open) {
                this.open = false;
                this.activeIndex = -1;
                this.unbindOutside();
              } else {
                this.close();
              }
            }
            break;
          case "Backspace":
            if (this.multiple && this.query === "" && this.hasSelection) {
              event.preventDefault();
              this.remove(this.values[this.values.length - 1]);
            }
            break;
          case "Tab":
            this.close();
            break;
        }
      },
      scrollActiveIntoView() {
        var _a;
        const node = this.activeIndex >= 0 ? (_a = this.$refs.listbox) == null ? void 0 : _a.children[this.activeIndex] : null;
        if (node && typeof node.scrollIntoView === "function") {
          node.scrollIntoView({ block: "nearest" });
        }
      },
      bindOutside() {
        if (this.outsideHandler) {
          return;
        }
        this.outsideHandler = (event) => {
          if (this.$refs.root && !this.$refs.root.contains(event.target)) {
            this.close();
          }
        };
        document.addEventListener("pointerdown", this.outsideHandler, true);
        document.addEventListener("focusin", this.outsideHandler, true);
      },
      unbindOutside() {
        if (!this.outsideHandler) {
          return;
        }
        document.removeEventListener("pointerdown", this.outsideHandler, true);
        document.removeEventListener("focusin", this.outsideHandler, true);
        this.outsideHandler = null;
      }
    }
  };
  const _hoisted_1$p = { class: "picker-chip-label" };
  const _hoisted_2$j = ["disabled", "aria-label", "title", "onClick"];
  const _hoisted_3$i = ["id", "aria-expanded", "aria-controls", "aria-activedescendant", "aria-label", "aria-busy", "placeholder", "value", "disabled"];
  const _hoisted_4$h = {
    key: 0,
    class: "spinner-border spinner-border-sm select-menu-spinner",
    "aria-hidden": "true"
  };
  const _hoisted_5$d = {
    key: 1,
    class: "ti ti-chevron-up select-menu-chevron",
    "aria-hidden": "true"
  };
  const _hoisted_6$d = ["disabled", "aria-hidden", "aria-label", "title"];
  const _hoisted_7$a = ["id", "aria-label", "aria-multiselectable", "aria-busy"];
  const _hoisted_8$a = ["id", "aria-selected", "onClick", "onMousemove"];
  const _hoisted_9$7 = { class: "picker-option-label" };
  const _hoisted_10$6 = {
    key: 0,
    class: "picker-option-count"
  };
  const _hoisted_11$4 = {
    key: 0,
    class: "dropdown-item-text select-menu-feedback",
    role: "presentation"
  };
  const _hoisted_12$4 = {
    class: "visually-hidden",
    role: "status"
  };
  function _sfc_render$r(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        ref: "root",
        class: vue.normalizeClass(["select-menu picker", { "is-open": $data.open, "has-selection": $options.hasSelection, "is-disabled": $props.disabled, "is-loading": $data.loading, "is-multiple": $props.multiple }])
      },
      [
        vue.createElementVNode("div", {
          class: "form-control picker-field",
          onClick: _cache[3] || (_cache[3] = (...args) => $options.onFieldClick && $options.onFieldClick(...args))
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($options.chips, (chip) => {
              return vue.openBlock(), vue.createElementBlock("span", {
                key: chip.value,
                class: "picker-chip"
              }, [
                vue.createElementVNode(
                  "span",
                  _hoisted_1$p,
                  vue.toDisplayString(chip.label),
                  1
                  /* TEXT */
                ),
                vue.createElementVNode("button", {
                  type: "button",
                  class: "picker-chip-remove",
                  disabled: $props.disabled,
                  "aria-label": $options.removeLabel(chip.label),
                  title: $options.removeLabel(chip.label),
                  onClick: vue.withModifiers(($event) => $options.remove(chip.value), ["stop"])
                }, [..._cache[6] || (_cache[6] = [
                  vue.createElementVNode(
                    "i",
                    {
                      class: "ti ti-x",
                      "aria-hidden": "true"
                    },
                    null,
                    -1
                    /* CACHED */
                  )
                ])], 8, _hoisted_2$j)
              ]);
            }),
            128
            /* KEYED_FRAGMENT */
          )),
          vue.createElementVNode("input", {
            id: $options.inputId,
            ref: "input",
            type: "text",
            class: "picker-input",
            role: "combobox",
            autocomplete: "off",
            "aria-autocomplete": "list",
            "aria-expanded": $data.open ? "true" : "false",
            "aria-controls": $data.listboxId,
            "aria-activedescendant": $options.activeDescendant,
            "aria-label": $options.accessibleName,
            "aria-busy": $data.loading ? "true" : null,
            placeholder: $options.inputPlaceholder,
            value: $options.inputText,
            disabled: $props.disabled,
            onInput: _cache[0] || (_cache[0] = (...args) => $options.onInput && $options.onInput(...args)),
            onKeydown: _cache[1] || (_cache[1] = (...args) => $options.onKeydown && $options.onKeydown(...args)),
            onFocus: _cache[2] || (_cache[2] = (...args) => $options.onFocus && $options.onFocus(...args))
          }, null, 40, _hoisted_3$i)
        ]),
        $data.loading ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_4$h)) : (vue.openBlock(), vue.createElementBlock("i", _hoisted_5$d)),
        vue.createElementVNode("button", {
          type: "button",
          class: "select-menu-clear",
          disabled: !$options.hasSelection || $props.disabled,
          "aria-hidden": $options.hasSelection ? null : "true",
          "aria-label": $options.clearLabel,
          title: $options.clearLabel,
          onClick: _cache[4] || (_cache[4] = vue.withModifiers((...args) => $options.clear && $options.clear(...args), ["stop"]))
        }, [..._cache[7] || (_cache[7] = [
          vue.createElementVNode(
            "i",
            {
              class: "ti ti-x",
              "aria-hidden": "true"
            },
            null,
            -1
            /* CACHED */
          )
        ])], 8, _hoisted_6$d),
        vue.createElementVNode("ul", {
          id: $data.listboxId,
          ref: "listbox",
          class: vue.normalizeClass(["dropdown-menu select-menu-drawer", { show: $data.open }]),
          "data-bs-popper": "static",
          role: "listbox",
          "aria-label": $options.accessibleName,
          "aria-multiselectable": $props.multiple ? "true" : null,
          "aria-busy": $data.loading ? "true" : null
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($options.choices, (option, index) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                id: $options.optionId(index),
                key: option.custom ? `custom:${option.value}` : option.value,
                class: vue.normalizeClass(["dropdown-item select-menu-option", { active: !option.custom && $options.isChosen(option.value), "is-focused": index === $data.activeIndex, "is-custom": option.custom }]),
                role: "option",
                "aria-selected": !option.custom && $options.isChosen(option.value) ? "true" : "false",
                onPointerdown: _cache[5] || (_cache[5] = vue.withModifiers(() => {
                }, ["prevent"])),
                onClick: ($event) => $options.choose(option),
                onMousemove: ($event) => $data.activeIndex = index
              }, [
                vue.createElementVNode(
                  "span",
                  _hoisted_9$7,
                  vue.toDisplayString(option.custom ? $options.customLabel(option.value) : option.label),
                  1
                  /* TEXT */
                ),
                option.count !== null ? (vue.openBlock(), vue.createElementBlock(
                  "span",
                  _hoisted_10$6,
                  vue.toDisplayString(option.count),
                  1
                  /* TEXT */
                )) : vue.createCommentVNode("v-if", true)
              ], 42, _hoisted_8$a);
            }),
            128
            /* KEYED_FRAGMENT */
          )),
          $options.feedback ? (vue.openBlock(), vue.createElementBlock(
            "li",
            _hoisted_11$4,
            vue.toDisplayString($options.feedback),
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true)
        ], 10, _hoisted_7$a),
        vue.createElementVNode(
          "span",
          _hoisted_12$4,
          vue.toDisplayString($data.open ? $options.statusText : ""),
          1
          /* TEXT */
        )
      ],
      2
      /* CLASS */
    );
  }
  const C10 = /* @__PURE__ */ _export_sfc(_sfc_main$r, [["render", _sfc_render$r]]);
  let uid$5 = 0;
  const _sfc_main$q = {
    name: "FilterSelect",
    props: {
      modelValue: { type: String, default: "" },
      options: { type: Array, default: () => [] },
      placeholder: { type: String, default: "" },
      label: { type: String, default: "" },
      disabled: { type: Boolean, default: false },
      loading: { type: Boolean, default: false },
      id: { type: String, default: null }
    },
    emits: ["update:modelValue"],
    data() {
      const key = ++uid$5;
      return {
        open: false,
        activeIndex: -1,
        listboxId: `filter-select-${key}-listbox`,
        fallbackId: `filter-select-${key}`
      };
    },
    computed: {
      buttonId() {
        return this.id || this.fallbackId;
      },
      choices() {
        return this.options.filter((option) => String(option.value) !== "").map((option) => ({ value: String(option.value), label: String(option.label) }));
      },
      placeholderText() {
        if (this.placeholder) {
          return this.placeholder;
        }
        const empty = this.options.find((option) => String(option.value) === "");
        if (empty) {
          return String(empty.label);
        }
        return this.label;
      },
      selected() {
        return this.choices.find((option) => option.value === this.modelValue) || null;
      },
      hasSelection() {
        return this.modelValue !== "" && this.modelValue !== null && this.modelValue !== void 0;
      },
      displayLabel() {
        if (!this.hasSelection) {
          return this.placeholderText;
        }
        return this.selected ? this.selected.label : this.modelValue;
      },
      accessibleName() {
        return this.label || this.placeholderText || null;
      },
      isDisabled() {
        return this.disabled || this.loading;
      },
      clearLabel() {
        return vue$1.i18n.t("base", "Clear selection");
      },
      loadingLabel() {
        return vue$1.i18n.t("base", "Loading...");
      },
      emptyLabel() {
        return vue$1.i18n.t("base", "No options");
      }
    },
    watch: {
      isDisabled(disabled) {
        if (disabled) {
          this.close(false);
        }
      }
    },
    beforeUnmount() {
      this.unbindOutside();
    },
    methods: {
      optionId(index) {
        return `${this.listboxId}-${index}`;
      },
      toggle() {
        if (this.open) {
          this.close(true);
        } else {
          this.show();
        }
      },
      show(activeIndex) {
        if (this.isDisabled || this.open) {
          return;
        }
        const current = this.choices.findIndex((option) => option.value === this.modelValue);
        this.activeIndex = activeIndex !== void 0 ? activeIndex : Math.max(current, 0);
        if (!this.choices.length) {
          this.activeIndex = -1;
        }
        this.open = true;
        this.bindOutside();
        this.$nextTick(() => {
          var _a;
          (_a = this.$refs.listbox) == null ? void 0 : _a.focus();
          this.scrollActiveIntoView();
        });
      },
      close(returnFocus) {
        var _a;
        if (!this.open) {
          return;
        }
        this.open = false;
        this.unbindOutside();
        if (returnFocus) {
          (_a = this.$refs.button) == null ? void 0 : _a.focus();
        }
      },
      select(option) {
        this.close(true);
        if (option.value !== this.modelValue) {
          this.$emit("update:modelValue", option.value);
        }
      },
      clear() {
        var _a;
        this.close(false);
        (_a = this.$refs.button) == null ? void 0 : _a.focus();
        if (this.hasSelection) {
          this.$emit("update:modelValue", "");
        }
      },
      onButtonKeydown(event) {
        if (["Enter", " ", "ArrowDown", "ArrowUp"].includes(event.key)) {
          event.preventDefault();
          const last = this.choices.length - 1;
          this.show(event.key === "ArrowUp" && !this.hasSelection ? last : void 0);
        }
      },
      onListKeydown(event) {
        const last = this.choices.length - 1;
        switch (event.key) {
          case "ArrowDown":
            event.preventDefault();
            this.moveTo(Math.min(this.activeIndex + 1, last));
            break;
          case "ArrowUp":
            event.preventDefault();
            this.moveTo(Math.max(this.activeIndex - 1, 0));
            break;
          case "Home":
            event.preventDefault();
            this.moveTo(0);
            break;
          case "End":
            event.preventDefault();
            this.moveTo(last);
            break;
          case "Enter":
          case " ":
            event.preventDefault();
            if (this.choices[this.activeIndex]) {
              this.select(this.choices[this.activeIndex]);
            } else {
              this.close(true);
            }
            break;
          case "Escape":
            event.preventDefault();
            event.stopPropagation();
            this.close(true);
            break;
          case "Tab":
            this.close(false);
            break;
          default:
            if (event.key.length === 1 && /\S/.test(event.key)) {
              this.typeAhead(event.key.toLowerCase());
            }
        }
      },
      moveTo(index) {
        if (index < 0) {
          return;
        }
        this.activeIndex = index;
        this.$nextTick(() => this.scrollActiveIntoView());
      },
      typeAhead(char) {
        const count = this.choices.length;
        for (let step = 1; step <= count; step++) {
          const index = (this.activeIndex + step) % count;
          if (this.choices[index].label.toLowerCase().startsWith(char)) {
            this.moveTo(index);
            return;
          }
        }
      },
      scrollActiveIntoView() {
        var _a;
        const node = this.activeIndex >= 0 ? (_a = this.$refs.listbox) == null ? void 0 : _a.children[this.activeIndex] : null;
        if (node && typeof node.scrollIntoView === "function") {
          node.scrollIntoView({ block: "nearest" });
        }
      },
      bindOutside() {
        if (this.outsideHandler) {
          return;
        }
        this.outsideHandler = (event) => {
          if (this.$refs.root && !this.$refs.root.contains(event.target)) {
            this.close(false);
          }
        };
        document.addEventListener("pointerdown", this.outsideHandler, true);
        document.addEventListener("focusin", this.outsideHandler, true);
      },
      unbindOutside() {
        if (!this.outsideHandler) {
          return;
        }
        document.removeEventListener("pointerdown", this.outsideHandler, true);
        document.removeEventListener("focusin", this.outsideHandler, true);
        this.outsideHandler = null;
      }
    }
  };
  const _hoisted_1$o = ["id", "aria-expanded", "aria-controls", "aria-label", "aria-busy", "disabled"];
  const _hoisted_2$i = { class: "select-menu-value" };
  const _hoisted_3$h = ["aria-label"];
  const _hoisted_4$g = {
    key: 1,
    class: "ti ti-chevron-up select-menu-chevron",
    "aria-hidden": "true"
  };
  const _hoisted_5$c = ["disabled", "aria-hidden", "aria-label", "title"];
  const _hoisted_6$c = ["id", "aria-label", "aria-activedescendant"];
  const _hoisted_7$9 = ["id", "aria-selected", "onClick", "onMousemove"];
  const _hoisted_8$9 = {
    key: 0,
    class: "dropdown-item-text select-menu-feedback",
    role: "presentation"
  };
  function _sfc_render$q(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        ref: "root",
        class: vue.normalizeClass(["select-menu", { "is-open": $data.open, "has-selection": $options.hasSelection, "is-disabled": $options.isDisabled, "is-loading": $props.loading }])
      },
      [
        vue.createElementVNode("button", {
          id: $options.buttonId,
          ref: "button",
          type: "button",
          class: "form-control select-menu-button",
          "aria-haspopup": "listbox",
          "aria-expanded": $data.open ? "true" : "false",
          "aria-controls": $data.listboxId,
          "aria-label": $options.accessibleName,
          "aria-busy": $props.loading ? "true" : null,
          disabled: $options.isDisabled,
          onClick: _cache[0] || (_cache[0] = (...args) => $options.toggle && $options.toggle(...args)),
          onKeydown: _cache[1] || (_cache[1] = (...args) => $options.onButtonKeydown && $options.onButtonKeydown(...args))
        }, [
          vue.createElementVNode(
            "span",
            _hoisted_2$i,
            vue.toDisplayString($options.displayLabel),
            1
            /* TEXT */
          )
        ], 40, _hoisted_1$o),
        $props.loading ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          class: "spinner-border spinner-border-sm select-menu-spinner",
          role: "status",
          "aria-label": $options.loadingLabel
        }, null, 8, _hoisted_3$h)) : (vue.openBlock(), vue.createElementBlock("i", _hoisted_4$g)),
        vue.createElementVNode("button", {
          type: "button",
          class: "select-menu-clear",
          disabled: !$options.hasSelection || $options.isDisabled,
          "aria-hidden": $options.hasSelection ? null : "true",
          "aria-label": $options.clearLabel,
          title: $options.clearLabel,
          onClick: _cache[2] || (_cache[2] = (...args) => $options.clear && $options.clear(...args))
        }, [..._cache[4] || (_cache[4] = [
          vue.createElementVNode(
            "i",
            {
              class: "ti ti-x",
              "aria-hidden": "true"
            },
            null,
            -1
            /* CACHED */
          )
        ])], 8, _hoisted_5$c),
        vue.createElementVNode("ul", {
          id: $data.listboxId,
          ref: "listbox",
          class: vue.normalizeClass(["dropdown-menu select-menu-drawer", { show: $data.open }]),
          "data-bs-popper": "static",
          role: "listbox",
          tabindex: "-1",
          "aria-label": $options.accessibleName,
          "aria-activedescendant": $data.open && $data.activeIndex >= 0 ? $options.optionId($data.activeIndex) : null,
          onKeydown: _cache[3] || (_cache[3] = (...args) => $options.onListKeydown && $options.onListKeydown(...args))
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($options.choices, (option, index) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                id: $options.optionId(index),
                key: option.value,
                class: vue.normalizeClass(["dropdown-item select-menu-option", { active: option.value === $props.modelValue, "is-focused": index === $data.activeIndex }]),
                role: "option",
                "aria-selected": option.value === $props.modelValue ? "true" : "false",
                onClick: ($event) => $options.select(option),
                onMousemove: ($event) => $data.activeIndex = index
              }, vue.toDisplayString(option.label), 43, _hoisted_7$9);
            }),
            128
            /* KEYED_FRAGMENT */
          )),
          !$options.choices.length ? (vue.openBlock(), vue.createElementBlock(
            "li",
            _hoisted_8$9,
            vue.toDisplayString($options.emptyLabel),
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true)
        ], 42, _hoisted_6$c)
      ],
      2
      /* CLASS */
    );
  }
  const C11 = /* @__PURE__ */ _export_sfc(_sfc_main$q, [["render", _sfc_render$q]]);
  const _sfc_main$p = {
    name: "FilterControl",
    components: { FilterPicker: C10, FilterSelect: C11 },
    props: {
      filter: { type: Object, required: true },
      modelValue: { type: [String, Array, Boolean, Number], default: "" },
      inputId: { type: String, required: true },
      options: { type: Array, default: () => [] },
      loading: { type: Boolean, default: false },
      component: { type: String, default: null },
      // Changed by the bar's `reloadOptions()`: a picker drops the suggestions it loaded.
      reloadKey: { type: Number, default: 0 }
    },
    emits: ["update:modelValue"],
    methods: {
      isTagActive(value) {
        if (this.filter.multiple === true) {
          return value === "" ? this.modelValue.length === 0 : this.modelValue.includes(value);
        }
        return this.modelValue === value;
      },
      toggleTag(value) {
        const current = this.modelValue;
        if (this.filter.multiple !== true) {
          this.$emit("update:modelValue", current === value ? "" : value);
          return;
        }
        if (value === "") {
          this.$emit("update:modelValue", []);
          return;
        }
        this.$emit("update:modelValue", current.includes(value) ? current.filter((v) => v !== value) : [...current, value]);
      }
    }
  };
  const _hoisted_1$n = {
    key: 0,
    class: "search-field"
  };
  const _hoisted_2$h = ["id", "value", "placeholder", "aria-label"];
  const _hoisted_3$g = ["aria-label"];
  const _hoisted_4$f = {
    key: 0,
    class: "filter-tags-label",
    "aria-hidden": "true"
  };
  const _hoisted_5$b = ["aria-pressed", "onClick"];
  const _hoisted_6$b = {
    key: 4,
    class: "filter-check form-check"
  };
  const _hoisted_7$8 = ["id", "checked"];
  const _hoisted_8$8 = ["for"];
  function _sfc_render$p(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FilterSelect = vue.resolveComponent("FilterSelect");
    const _component_FilterPicker = vue.resolveComponent("FilterPicker");
    return $props.filter.type === "text" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$n, [
      _cache[5] || (_cache[5] = vue.createElementVNode(
        "i",
        {
          class: "ti ti-search search-field-icon",
          "aria-hidden": "true"
        },
        null,
        -1
        /* CACHED */
      )),
      vue.createElementVNode("input", {
        id: $props.inputId,
        type: "text",
        class: "form-control search-field-input",
        autocomplete: "off",
        value: $props.modelValue,
        placeholder: $props.filter.placeholder || $props.filter.label || "",
        "aria-label": $props.filter.label || $props.filter.placeholder || null,
        onInput: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("update:modelValue", $event.target.value))
      }, null, 40, _hoisted_2$h)
    ])) : $props.filter.type === "select" ? (vue.openBlock(), vue.createBlock(_component_FilterSelect, {
      key: 1,
      id: $props.inputId,
      "model-value": String($props.modelValue ?? ""),
      options: $props.options,
      placeholder: $props.filter.placeholder || "",
      label: $props.filter.label || "",
      loading: $props.loading,
      "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => _ctx.$emit("update:modelValue", $event))
    }, null, 8, ["id", "model-value", "options", "placeholder", "label", "loading"])) : $props.filter.type === "picker" ? (vue.openBlock(), vue.createBlock(_component_FilterPicker, {
      key: 2,
      id: $props.inputId,
      "model-value": $props.filter.multiple === true ? Array.isArray($props.modelValue) ? $props.modelValue : [] : String($props.modelValue ?? ""),
      multiple: $props.filter.multiple === true,
      "allow-custom": $props.filter.custom === true,
      options: $props.options,
      "options-url": $props.filter.optionsUrl || null,
      placeholder: $props.filter.placeholder || "",
      label: $props.filter.label || "",
      "reload-key": $props.reloadKey,
      "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => _ctx.$emit("update:modelValue", $event))
    }, null, 8, ["id", "model-value", "multiple", "allow-custom", "options", "options-url", "placeholder", "label", "reload-key"])) : $props.filter.type === "tags" ? (vue.openBlock(), vue.createElementBlock("div", {
      key: 3,
      class: "filter-tags",
      role: "group",
      "aria-label": $props.filter.label
    }, [
      $props.filter.label ? (vue.openBlock(), vue.createElementBlock(
        "span",
        _hoisted_4$f,
        vue.toDisplayString($props.filter.label),
        1
        /* TEXT */
      )) : vue.createCommentVNode("v-if", true),
      (vue.openBlock(true), vue.createElementBlock(
        vue.Fragment,
        null,
        vue.renderList($props.options, (option) => {
          return vue.openBlock(), vue.createElementBlock("button", {
            key: option.value,
            type: "button",
            class: vue.normalizeClass(["btn btn-light btn-sm rounded-pill filter-tags-tag", { active: $options.isTagActive(option.value) }]),
            "aria-pressed": $options.isTagActive(option.value) ? "true" : "false",
            onClick: ($event) => $options.toggleTag(option.value)
          }, vue.toDisplayString(option.label), 11, _hoisted_5$b);
        }),
        128
        /* KEYED_FRAGMENT */
      ))
    ], 8, _hoisted_3$g)) : $props.filter.type === "checkbox" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$b, [
      vue.createElementVNode("input", {
        id: $props.inputId,
        class: "form-check-input",
        type: "checkbox",
        checked: $props.modelValue,
        onChange: _cache[3] || (_cache[3] = ($event) => _ctx.$emit("update:modelValue", $event.target.checked))
      }, null, 40, _hoisted_7$8),
      vue.createElementVNode("label", {
        class: "form-check-label",
        for: $props.inputId
      }, vue.toDisplayString($props.filter.label), 9, _hoisted_8$8)
    ])) : $props.component ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent($props.component), {
      key: 5,
      filter: $props.filter,
      "model-value": $props.modelValue,
      "input-id": $props.inputId,
      "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.$emit("update:modelValue", $event))
    }, null, 8, ["filter", "model-value", "input-id"])) : vue.createCommentVNode("v-if", true);
  }
  const FilterControl = /* @__PURE__ */ _export_sfc(_sfc_main$p, [["render", _sfc_render$p]]);
  const SINGLE_VALUE_TYPES = ["text", "select", "checkbox"];
  const isMultiple = (filter) => filter.multiple === true && !SINGLE_VALUE_TYPES.includes(filter.type);
  const toArray = (value) => Array.isArray(value) ? value.map(String) : String(value).split(",").filter((part) => part !== "");
  const defaultValue = (filter) => {
    if (filter.default !== void 0 && filter.default !== null) {
      if (filter.type === "checkbox") {
        return filter.default === true || filter.default === 1 || filter.default === "1";
      }
      return isMultiple(filter) ? toArray(filter.default) : String(filter.default);
    }
    if (filter.type === "checkbox") {
      return false;
    }
    return isMultiple(filter) ? [] : "";
  };
  const defaultValues = (filters) => Object.fromEntries(filters.map((filter) => [filter.key, defaultValue(filter)]));
  const parseValue = (filter, raw) => {
    if (filter.type === "checkbox") {
      return raw === "1" || raw === "true";
    }
    return isMultiple(filter) ? toArray(raw) : String(raw);
  };
  const serializeValue = (filter, value) => {
    if (filter.type === "checkbox") {
      return value ? "1" : "0";
    }
    return Array.isArray(value) ? value.join(",") : String(value ?? "");
  };
  const isDefault = (filter, value) => serializeValue(filter, value) === serializeValue(filter, defaultValue(filter));
  const sanitizeValue = (filter, value) => {
    if (!["select", "picker", "tags"].includes(filter.type) || filter.optionsUrl || !Array.isArray(filter.options)) {
      return value;
    }
    const known = new Set(filter.options.map((option) => String(option.value)));
    if (isMultiple(filter)) {
      return toArray(value ?? []).filter((entry) => known.has(entry));
    }
    const serialized = String(value ?? "");
    return serialized === "" || known.has(serialized) ? value : defaultValue(filter);
  };
  const sanitizeValues = (filters, values) => {
    const sanitized = { ...values };
    for (const filter of filters) {
      if (filter.key in sanitized) {
        sanitized[filter.key] = sanitizeValue(filter, sanitized[filter.key]);
      }
    }
    return sanitized;
  };
  const readValues = (filters, search, base = defaultValues(filters)) => {
    const params = new URLSearchParams(search);
    const values = { ...base };
    for (const filter of filters) {
      if (params.has(filter.key)) {
        const raw = isMultiple(filter) ? params.getAll(filter.key).join(",") : params.get(filter.key);
        values[filter.key] = sanitizeValue(filter, parseValue(filter, raw));
      }
    }
    return values;
  };
  const writeQuery = (filters, values, search) => {
    const params = new URLSearchParams(search);
    for (const filter of filters) {
      const value = values[filter.key];
      if (isDefault(filter, value)) {
        params.delete(filter.key);
      } else {
        params.set(filter.key, serializeValue(filter, value));
      }
    }
    const query = params.toString();
    return query === "" ? "" : `?${query}`;
  };
  const serializeFixed = (value) => {
    if (typeof value === "boolean") {
      return value ? "1" : "0";
    }
    return Array.isArray(value) ? value.join(",") : String(value ?? "");
  };
  const fixedSignature = (fixed) => JSON.stringify(Object.keys(fixed || {}).sort().map((key) => [key, serializeFixed(fixed[key])]));
  const requestParams = (filters, values, fixed = {}) => {
    const params = {};
    for (const filter of filters) {
      const serialized = serializeValue(filter, values[filter.key]);
      if (filter.type === "checkbox" || serialized !== "") {
        params[filter.key] = serialized;
      }
    }
    for (const [key, value] of Object.entries(fixed || {})) {
      params[key] = serializeFixed(value);
    }
    return params;
  };
  const TEXT_DEBOUNCE_MS = 300;
  const ANIMATION_MS = 300;
  const CORE_TYPES = ["text", "select", "picker", "tags", "checkbox"];
  let uid$4 = 0;
  const reducedMotion = () => typeof window.matchMedia === "function" && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const same = (keys, a, b) => keys.every((key) => JSON.stringify(a == null ? void 0 : a[key]) === JSON.stringify(b == null ? void 0 : b[key]));
  const keysOf = (filters, fixed) => [.../* @__PURE__ */ new Set([...filters.map((filter) => filter.key), ...Object.keys(fixed || {})])];
  const isPanel = (filter) => filter.placement === "panel";
  const keySlug = (filter) => String(filter.key).replace(/[^A-Za-z0-9_-]+/g, "-").replace(/^-+|-+$/g, "");
  const _sfc_main$o = {
    name: "FilterBar",
    components: { FilterControl },
    props: {
      filters: { type: Array, required: true },
      modelValue: { type: Object, default: () => ({}) },
      syncUrl: { type: Boolean, default: true },
      idPrefix: { type: String, default: "filter" },
      /**
       * Values set by the embedding page or component: sent, not rendered, not URL-synced.
       * @since 1.20
       */
      fixed: { type: Object, default: () => ({}) }
    },
    emits: ["update:modelValue"],
    data() {
      const own = this.filters.filter((filter) => !Object.hasOwn(this.fixed, filter.key));
      const base = sanitizeValues(own, { ...defaultValues(this.filters), ...this.modelValue });
      const initial = { ...this.syncUrl ? readValues(own, window.location.search, base) : base, ...this.fixed };
      return {
        draft: initial,
        remoteOptions: {},
        loading: {},
        // Counted up by `reloadOptions()`: the pickers drop the suggestions they loaded.
        optionsVersion: 0,
        // A panel filter set on load opens the panel.
        open: own.some((filter) => isPanel(filter) && !filter.hidden && !isDefault(filter, initial[filter.key])),
        collapsing: false,
        animating: false,
        barId: `filter-bar-${++uid$4}`
      };
    },
    computed: {
      // The filters the bar owns: every definition whose key is not fixed.
      ownFilters() {
        return this.filters.filter((filter) => !Object.hasOwn(this.fixed, filter.key));
      },
      visibleFilters() {
        return this.ownFilters.filter((filter) => !filter.hidden);
      },
      primaryFilters() {
        return this.visibleFilters.filter((filter) => !isPanel(filter));
      },
      panelFilters() {
        return this.visibleFilters.filter(isPanel);
      },
      hasPanel() {
        return this.panelFilters.length > 0;
      },
      panelId() {
        return `${this.barId}-panel`;
      },
      // The item that stays visible while the bar is collapsed: the first search field.
      keepIndex() {
        return this.primaryFilters.findIndex((filter) => filter.type === "text");
      },
      collapsible() {
        return this.hasPanel || this.primaryFilters.length > (this.keepIndex === -1 ? 0 : 1);
      },
      // The row: the primary filters and the toggle, which follows the kept search field (or
      // leads the row without one).
      rowEntries() {
        const entries = this.primaryFilters.map((filter, index) => ({ key: `filter-${filter.key}`, filter, keep: index === this.keepIndex }));
        if (this.collapsible) {
          entries.splice(Math.max(this.keepIndex, 0) + 1, 0, { key: "toggle", filter: null });
        }
        return entries;
      },
      resettable() {
        return this.visibleFilters.some((filter) => !isDefault(filter, this.draft[filter.key]));
      },
      activePanelCount() {
        return this.panelFilters.filter((filter) => !isDefault(filter, this.draft[filter.key])).length;
      },
      resetLabel() {
        return vue$1.i18n.t("base", "Clear all filters");
      },
      toggleLabel() {
        return this.open ? vue$1.i18n.t("base", "Hide filters") : vue$1.i18n.t("base", "Show filters");
      },
      panelLabel() {
        return vue$1.i18n.t("base", "Filters");
      },
      activePanelCountLabel() {
        return vue$1.i18n.t("base", "{count} active", { count: this.activePanelCount });
      }
    },
    watch: {
      draft: "onDraftChange",
      modelValue(value) {
        if (!same(keysOf(this.filters, this.fixed), { ...value, ...this.fixed }, this.applied)) {
          clearTimeout(this.debounceTimer);
          this.applied = { ...sanitizeValues(this.ownFilters, { ...defaultValues(this.filters), ...value }), ...this.fixed };
          this.draft = { ...this.applied };
          this.writeUrl();
          if (!same(keysOf(this.filters, this.fixed), { ...value, ...this.fixed }, this.applied)) {
            this.$emit("update:modelValue", { ...this.applied });
          }
        }
      },
      fixed: {
        deep: true,
        handler(value) {
          const signature = fixedSignature(value);
          if (signature === this.fixedSignature) {
            return;
          }
          const removed = Object.keys(this.appliedFixed).filter((key) => !Object.hasOwn(value, key));
          this.fixedSignature = signature;
          this.appliedFixed = { ...value };
          const draft = { ...this.draft, ...value };
          removed.forEach((key) => {
            const filter = this.filters.find((candidate) => candidate.key === key);
            if (filter) {
              draft[key] = defaultValue(filter);
            } else {
              delete draft[key];
            }
          });
          this.draft = draft;
          this.apply();
        }
      }
    },
    created() {
      this.debounceTimer = null;
      this.applyNow = false;
      this.reportedTypes = /* @__PURE__ */ new Set();
      this.fixedSignature = fixedSignature(this.fixed);
      this.appliedFixed = { ...this.fixed };
      this.applied = { ...this.draft };
      if (!same(keysOf(this.filters, this.fixed), this.applied, this.modelValue)) {
        this.$emit("update:modelValue", { ...this.applied });
      }
      this.reloadOptions();
    },
    mounted() {
      if (typeof ResizeObserver === "function") {
        this.resizeObserver = new ResizeObserver(() => this.sync());
        this.resizeObserver.observe(this.$refs.bar);
      }
    },
    beforeUnmount() {
      var _a;
      clearTimeout(this.debounceTimer);
      clearTimeout(this.settleTimer);
      clearTimeout(this.flipTimer);
      (_a = this.resizeObserver) == null ? void 0 : _a.disconnect();
    },
    methods: {
      setFilter(key, value, { add = false } = {}) {
        const filter = this.ownFilters.find((candidate) => candidate.key === key);
        if (!filter) {
          return false;
        }
        if (add && Array.isArray(this.draft[key])) {
          const current = this.draft[key];
          const added = (Array.isArray(value) ? value : [value]).map(String).filter((entry) => entry !== "" && !current.includes(entry));
          value = [...current, ...added];
        }
        this.applyNow = true;
        this.update(key, sanitizeValue(filter, value));
        return true;
      },
      reset() {
        this.applyNow = true;
        this.draft = { ...defaultValues(this.filters), ...this.fixed };
      },
      reloadOptions() {
        this.ownFilters.filter((filter) => filter.optionsUrl && filter.type !== "picker").forEach((filter) => this.loadOptions(filter));
        this.optionsVersion++;
      },
      inputId(filter) {
        return `${this.idPrefix}-${keySlug(filter)}`;
      },
      itemClass(filter) {
        return ["filter-bar-item", `filter-bar-item-${filter.type}`, `form-search-filter-${keySlug(filter)}`, { "filter-bar-item-wide": filter.wide }];
      },
      // The component registered for a filter's type, once both halves of the registration
      // are there (read from the reactive registry, so a late registration re-renders the bar).
      customType(filter) {
        if (CORE_TYPES.includes(filter.type)) {
          return null;
        }
        const name = vue$1.getFilterType(filter.type);
        return name && vue$1.isRegistered(name) ? name : null;
      },
      // `$slots` is not reactive, so this is called from the template, not a computed.
      hasControl(filter) {
        if (CORE_TYPES.includes(filter.type) || this.$slots[`filter-${filter.key}`] || this.customType(filter)) {
          return true;
        }
        if (!this.reportedTypes.has(filter.type)) {
          this.reportedTypes.add(filter.type);
          vue$1.log.debug(`FilterBar: no component is registered for the filter type "${filter.type}" (filter "${filter.key}") — not rendered`);
        }
        return false;
      },
      loadOptions(filter) {
        this.loading[filter.key] = true;
        vue$1.client.get(filter.optionsUrl).then((response) => {
          this.remoteOptions[filter.key] = (response.results || []).map((option) => ({
            value: String(option.id),
            label: option.count !== void 0 && option.count !== null ? `${option.name} (${option.count})` : option.name
          }));
        }).catch((response) => {
          vue$1.log.error(response);
        }).finally(() => {
          this.loading[filter.key] = false;
        });
      },
      isLoading(filter) {
        return this.loading[filter.key] === true;
      },
      optionsOf(filter) {
        return [...filter.options || [], ...this.remoteOptions[filter.key] || []];
      },
      update(key, value) {
        this.draft = { ...this.draft, [key]: value };
      },
      onDraftChange(values) {
        clearTimeout(this.debounceTimer);
        const changed = keysOf(this.filters, this.fixed).filter((key) => JSON.stringify(values[key]) !== JSON.stringify(this.applied[key]));
        if (!changed.length) {
          this.applyNow = false;
          return;
        }
        const changedFilters = this.ownFilters.filter((filter) => changed.includes(filter.key));
        if (changedFilters.some((filter) => !filter.hidden)) {
          const stale = this.ownFilters.filter((filter) => filter.hidden && !isDefault(filter, values[filter.key]));
          if (stale.length) {
            this.draft = { ...values, ...Object.fromEntries(stale.map((filter) => [filter.key, defaultValue(filter)])) };
            return;
          }
        }
        if (!this.applyNow && changedFilters.length === changed.length && changedFilters.every((filter) => filter.type === "text")) {
          this.debounceTimer = setTimeout(() => this.apply(), TEXT_DEBOUNCE_MS);
        } else {
          this.applyNow = false;
          this.apply();
        }
      },
      apply() {
        clearTimeout(this.debounceTimer);
        this.applied = { ...this.draft };
        this.writeUrl();
        this.$emit("update:modelValue", { ...this.applied });
      },
      writeUrl() {
        if (!this.syncUrl) {
          return;
        }
        const { pathname, search, hash } = window.location;
        const path = pathname + writeQuery(this.ownFilters, this.applied, search) + hash;
        window.history.replaceState(this.nextHistoryState(path), "", path);
      },
      // PJAX (jquery.pjax.modified.js) keeps its own state object in `history.state`
      // (`{id, url, title, container, fragment, timeout}`) and compares `state.url` against
      // the current location on `popstate` to detect a same-page navigation - replacing the
      // URL without refreshing that `url` field would leave it pointing at the filter values
      // from before this change. Any other kind of state (none, or a plain object without a
      // `url` string) is passed through unchanged - it is none of this component's business.
      nextHistoryState(path) {
        const state = window.history.state;
        if (!state || typeof state !== "object" || typeof state.url !== "string") {
          return state;
        }
        return { ...state, url: window.location.origin + path };
      },
      toggleElement() {
        const toggle = this.$refs.toggle;
        return Array.isArray(toggle) ? toggle[0] : toggle;
      },
      // CSS owns the breakpoint (a container query), so "does the bar collapse right now?" is
      // answered by whether it shows the funnel toggle, not by a width kept in step with the
      // SCSS. A bar with a panel always opens and closes (the "Filters" toggle is always shown).
      collapsesNow() {
        if (this.hasPanel) {
          return true;
        }
        const toggle = this.toggleElement();
        return Boolean(toggle) && window.getComputedStyle(toggle).display !== "none";
      },
      onToggle() {
        if (this.collapsesNow()) {
          this.setOpen(!this.open);
        }
      },
      onPanelEscape() {
        var _a;
        if (this.open) {
          this.setOpen(false);
          (_a = this.toggleElement()) == null ? void 0 : _a.focus();
        }
      },
      setOpen(open, animate = !reducedMotion()) {
        var _a;
        const bar = this.$refs.bar;
        const from = bar.getBoundingClientRect().height;
        const clear = this.$refs.clear || null;
        const flipping = animate && clear && clear.getClientRects().length > 0;
        const clearFrom = flipping ? clear.getBoundingClientRect() : null;
        const focused = document.activeElement;
        this.collapsing = false;
        this.animating = false;
        this.open = open;
        bar.classList.remove("is-collapsing", "is-animating");
        bar.classList.toggle("is-open", open);
        bar.style.maxHeight = "none";
        const to = bar.getBoundingClientRect().height;
        const clearTo = flipping ? clear.getBoundingClientRect() : null;
        if (!open && focused && bar.contains(focused) && focused.closest(".filter-bar-panel, .filter-bar-item:not([data-filter-bar-keep])")) {
          (_a = this.toggleElement()) == null ? void 0 : _a.focus();
        }
        clearTimeout(this.settleTimer);
        if (!animate) {
          bar.style.maxHeight = open ? "none" : "";
          return;
        }
        this.collapsing = !open;
        this.animating = true;
        bar.classList.toggle("is-collapsing", !open);
        bar.classList.add("is-animating");
        if (flipping) {
          this.flipClear(clear, clearFrom, clearTo, open);
        }
        bar.style.maxHeight = `${from}px`;
        void bar.offsetHeight;
        bar.style.maxHeight = `${to}px`;
        this.settleTimer = setTimeout(() => {
          bar.style.maxHeight = open ? "none" : "";
          this.collapsing = false;
          this.animating = false;
        }, ANIMATION_MS);
      },
      // FLIP the clear X between the row it has when expanded (after the last filter) and the
      // one beside the toggle when collapsed, instead of letting it jump there.
      flipClear(clear, from, to, opening) {
        const dx = from.left - to.left;
        const dy = from.top - to.top;
        if (!dx && !dy) {
          return;
        }
        clear.classList.add("is-flipping");
        clear.style.transform = opening ? `translate(${dx}px, ${dy}px)` : "";
        void clear.offsetHeight;
        clear.style.transform = opening ? "" : `translate(${-dx}px, ${-dy}px)`;
        clearTimeout(this.flipTimer);
        this.flipTimer = setTimeout(() => {
          clear.classList.remove("is-flipping");
          clear.style.transform = "";
        }, ANIMATION_MS);
      },
      // A bar that stops collapsing (grown past the container breakpoint) drops its open state
      // and any inline height, so it cannot be stranded half-open. A bar with a panel keeps
      // it: open means "the panel is shown" at every width.
      sync() {
        if (this.collapsesNow() || !this.open && !this.collapsing) {
          return;
        }
        clearTimeout(this.settleTimer);
        this.setOpen(false, false);
      }
    }
  };
  const _hoisted_1$m = { class: "filter-bar" };
  const _hoisted_2$g = ["id"];
  const _hoisted_3$f = ["data-filter-bar-keep"];
  const _hoisted_4$e = ["aria-expanded", "aria-controls", "aria-label", "title"];
  const _hoisted_5$a = { class: "filter-bar-toggle-label" };
  const _hoisted_6$a = {
    key: 0,
    class: "badge rounded-pill filter-bar-count",
    "aria-hidden": "true"
  };
  const _hoisted_7$7 = {
    key: 1,
    class: "visually-hidden"
  };
  const _hoisted_8$7 = ["aria-label", "title"];
  const _hoisted_9$6 = ["id", "aria-label"];
  function _sfc_render$o(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FilterControl = vue.resolveComponent("FilterControl");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$m, [
      vue.createElementVNode("form", {
        id: $data.barId,
        ref: "bar",
        class: vue.normalizeClass(["filter-bar-body", { "is-open": $data.open, "is-collapsing": $data.collapsing, "is-animating": $data.animating, "has-panel": $options.hasPanel }]),
        role: "search",
        onSubmit: _cache[3] || (_cache[3] = vue.withModifiers(() => {
        }, ["prevent"]))
      }, [
        (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          null,
          vue.renderList($options.rowEntries, (entry) => {
            return vue.openBlock(), vue.createElementBlock(
              vue.Fragment,
              {
                key: entry.key
              },
              [
                entry.filter && $options.hasControl(entry.filter) ? (vue.openBlock(), vue.createElementBlock("div", {
                  key: 0,
                  class: vue.normalizeClass($options.itemClass(entry.filter)),
                  "data-filter-bar-keep": entry.keep ? "" : null
                }, [
                  vue.renderSlot(_ctx.$slots, `filter-${entry.filter.key}`, {
                    filter: entry.filter,
                    value: $data.draft[entry.filter.key],
                    update: (value) => $options.update(entry.filter.key, value)
                  }, () => [
                    vue.createVNode(_component_FilterControl, {
                      filter: entry.filter,
                      "model-value": $data.draft[entry.filter.key],
                      "input-id": $options.inputId(entry.filter),
                      options: $options.optionsOf(entry.filter),
                      loading: $options.isLoading(entry.filter),
                      component: $options.customType(entry.filter),
                      "reload-key": $data.optionsVersion,
                      "onUpdate:modelValue": ($event) => $options.update(entry.filter.key, $event)
                    }, null, 8, ["filter", "model-value", "input-id", "options", "loading", "component", "reload-key", "onUpdate:modelValue"])
                  ])
                ], 10, _hoisted_3$f)) : !entry.filter ? (vue.openBlock(), vue.createElementBlock("button", {
                  key: 1,
                  ref_for: true,
                  ref: "toggle",
                  type: "button",
                  class: vue.normalizeClass(["btn btn-ghost btn-icon-only filter-bar-toggle", { "is-active": $data.open, "filter-bar-toggle-labeled": $options.hasPanel }]),
                  "data-filter-bar-toggle": "",
                  "aria-expanded": $data.open ? "true" : "false",
                  "aria-controls": $options.hasPanel ? $options.panelId : $data.barId,
                  "aria-label": $options.hasPanel ? null : $options.toggleLabel,
                  title: $options.toggleLabel,
                  onClick: _cache[0] || (_cache[0] = (...args) => $options.onToggle && $options.onToggle(...args))
                }, [
                  _cache[4] || (_cache[4] = vue.createElementVNode(
                    "i",
                    {
                      class: "ti ti-filter",
                      "aria-hidden": "true"
                    },
                    null,
                    -1
                    /* CACHED */
                  )),
                  $options.hasPanel ? (vue.openBlock(), vue.createElementBlock(
                    vue.Fragment,
                    { key: 0 },
                    [
                      vue.createElementVNode(
                        "span",
                        _hoisted_5$a,
                        vue.toDisplayString($options.panelLabel),
                        1
                        /* TEXT */
                      ),
                      $options.activePanelCount ? (vue.openBlock(), vue.createElementBlock(
                        "span",
                        _hoisted_6$a,
                        vue.toDisplayString($options.activePanelCount),
                        1
                        /* TEXT */
                      )) : vue.createCommentVNode("v-if", true),
                      $options.activePanelCount ? (vue.openBlock(), vue.createElementBlock(
                        "span",
                        _hoisted_7$7,
                        vue.toDisplayString($options.activePanelCountLabel),
                        1
                        /* TEXT */
                      )) : vue.createCommentVNode("v-if", true)
                    ],
                    64
                    /* STABLE_FRAGMENT */
                  )) : vue.createCommentVNode("v-if", true)
                ], 10, _hoisted_4$e)) : vue.createCommentVNode("v-if", true)
              ],
              64
              /* STABLE_FRAGMENT */
            );
          }),
          128
          /* KEYED_FRAGMENT */
        )),
        vue.createVNode(vue.Transition, { name: "filter-bar-clear" }, {
          default: vue.withCtx(() => [
            $options.resettable ? (vue.openBlock(), vue.createElementBlock("button", {
              key: 0,
              ref: "clear",
              type: "button",
              class: "btn btn-ghost btn-icon-only filter-bar-clear",
              "data-filter-bar-keep": "",
              "data-filter-bar-clear": "",
              "aria-label": $options.resetLabel,
              title: $options.resetLabel,
              onClick: _cache[1] || (_cache[1] = (...args) => $options.reset && $options.reset(...args))
            }, [..._cache[5] || (_cache[5] = [
              vue.createElementVNode(
                "i",
                {
                  class: "ti ti-x",
                  "aria-hidden": "true"
                },
                null,
                -1
                /* CACHED */
              )
            ])], 8, _hoisted_8$7)) : vue.createCommentVNode("v-if", true)
          ]),
          _: 1
          /* STABLE */
        }),
        $options.hasPanel ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 0,
          id: $options.panelId,
          ref: "panel",
          class: "filter-bar-panel",
          role: "group",
          "aria-label": $options.panelLabel,
          onKeydown: _cache[2] || (_cache[2] = vue.withKeys((...args) => $options.onPanelEscape && $options.onPanelEscape(...args), ["esc"]))
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($options.panelFilters, (filter) => {
              return vue.openBlock(), vue.createElementBlock(
                vue.Fragment,
                {
                  key: filter.key
                },
                [
                  $options.hasControl(filter) ? (vue.openBlock(), vue.createElementBlock(
                    "div",
                    {
                      key: 0,
                      class: vue.normalizeClass($options.itemClass(filter))
                    },
                    [
                      vue.renderSlot(_ctx.$slots, `filter-${filter.key}`, {
                        filter,
                        value: $data.draft[filter.key],
                        update: (value) => $options.update(filter.key, value)
                      }, () => [
                        vue.createVNode(_component_FilterControl, {
                          filter,
                          "model-value": $data.draft[filter.key],
                          "input-id": $options.inputId(filter),
                          options: $options.optionsOf(filter),
                          loading: $options.isLoading(filter),
                          component: $options.customType(filter),
                          "reload-key": $data.optionsVersion,
                          "onUpdate:modelValue": ($event) => $options.update(filter.key, $event)
                        }, null, 8, ["filter", "model-value", "input-id", "options", "loading", "component", "reload-key", "onUpdate:modelValue"])
                      ])
                    ],
                    2
                    /* CLASS */
                  )) : vue.createCommentVNode("v-if", true)
                ],
                64
                /* STABLE_FRAGMENT */
              );
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ], 40, _hoisted_9$6)) : vue.createCommentVNode("v-if", true)
      ], 42, _hoisted_2$g)
    ]);
  }
  const C9 = /* @__PURE__ */ _export_sfc(_sfc_main$o, [["render", _sfc_render$o]]);
  const _sfc_main$n = {
    name: "TableCell",
    props: {
      column: { type: Object, required: true },
      cell: { type: [Object, Array], required: true }
    },
    emits: ["filter"],
    computed: {
      chips() {
        return Array.isArray(this.cell) ? this.cell : [this.cell];
      }
    },
    methods: {
      filterLabel(chip) {
        return vue$1.i18n.t("base", "Filter by {value}", { value: chip.text });
      }
    }
  };
  const _hoisted_1$l = {
    key: 0,
    class: "data-table-main"
  };
  const _hoisted_2$f = ["src"];
  const _hoisted_3$e = { class: "data-table-main-text" };
  const _hoisted_4$d = ["href"];
  const _hoisted_5$9 = {
    key: 1,
    class: "data-table-main-name"
  };
  const _hoisted_6$9 = {
    key: 2,
    class: "data-table-main-subtitle"
  };
  const _hoisted_7$6 = {
    key: 1,
    class: "data-table-chips"
  };
  const _hoisted_8$6 = ["title", "aria-label", "onClick"];
  const _hoisted_9$5 = ["datetime", "title"];
  const _hoisted_10$5 = { key: 3 };
  function _sfc_render$n(_ctx, _cache, $props, $setup, $data, $options) {
    return $props.column.type === "main" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$l, [
      $props.cell.image ? (vue.openBlock(), vue.createElementBlock("img", {
        key: 0,
        src: $props.cell.image,
        class: "data-table-main-image",
        alt: "",
        "aria-hidden": "true"
      }, null, 8, _hoisted_2$f)) : vue.createCommentVNode("v-if", true),
      vue.createElementVNode("span", _hoisted_3$e, [
        $props.cell.url ? (vue.openBlock(), vue.createElementBlock("a", {
          key: 0,
          href: $props.cell.url,
          class: "data-table-main-name"
        }, vue.toDisplayString($props.cell.text), 9, _hoisted_4$d)) : (vue.openBlock(), vue.createElementBlock(
          "span",
          _hoisted_5$9,
          vue.toDisplayString($props.cell.text),
          1
          /* TEXT */
        )),
        $props.cell.subtitle ? (vue.openBlock(), vue.createElementBlock(
          "span",
          _hoisted_6$9,
          vue.toDisplayString($props.cell.subtitle),
          1
          /* TEXT */
        )) : vue.createCommentVNode("v-if", true)
      ])
    ])) : $props.column.type === "chip" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_7$6, [
      (vue.openBlock(true), vue.createElementBlock(
        vue.Fragment,
        null,
        vue.renderList($options.chips, (chip) => {
          return vue.openBlock(), vue.createElementBlock(
            vue.Fragment,
            {
              key: chip.value || chip.text
            },
            [
              $props.column.filter && chip.value !== void 0 ? (vue.openBlock(), vue.createElementBlock("button", {
                key: 0,
                type: "button",
                class: vue.normalizeClass(["badge", chip.tone ? `badge-${chip.tone}` : null]),
                title: $options.filterLabel(chip),
                "aria-label": $options.filterLabel(chip),
                onClick: vue.withModifiers(($event) => _ctx.$emit("filter", $props.column.filter, chip.value), ["stop"])
              }, vue.toDisplayString(chip.text), 11, _hoisted_8$6)) : (vue.openBlock(), vue.createElementBlock(
                "span",
                {
                  key: 1,
                  class: vue.normalizeClass(["badge", chip.tone ? `badge-${chip.tone}` : null])
                },
                vue.toDisplayString(chip.text),
                3
                /* TEXT, CLASS */
              ))
            ],
            64
            /* STABLE_FRAGMENT */
          );
        }),
        128
        /* KEYED_FRAGMENT */
      ))
    ])) : $props.column.type === "date" && $props.cell.value ? (vue.openBlock(), vue.createElementBlock("time", {
      key: 2,
      datetime: $props.cell.value,
      title: $props.cell.title
    }, vue.toDisplayString($props.cell.text), 9, _hoisted_9$5)) : (vue.openBlock(), vue.createElementBlock(
      "span",
      _hoisted_10$5,
      vue.toDisplayString($props.cell.text),
      1
      /* TEXT */
    ));
  }
  const TableCell = /* @__PURE__ */ _export_sfc(_sfc_main$n, [["render", _sfc_render$n]]);
  let uidSeq = 0;
  const _sfc_main$m = {
    name: "UiModal",
    props: {
      show: { type: Boolean, default: false },
      title: { type: String, default: null },
      size: {
        type: String,
        default: "normal",
        validator: (value) => ["small", "normal", "large"].includes(value)
      },
      dialogClass: { type: [String, Array, Object], default: null },
      backdropClose: { type: Boolean, default: true },
      keyboard: { type: Boolean, default: true }
    },
    emits: ["update:show", "opened", "closed"],
    data() {
      return {
        visible: false,
        titleId: `ui-modal-title-${++uidSeq}`,
        previouslyFocused: null,
        // Set by `onBackdropMousedown` on every mousedown targeting the `.modal` root,
        // cleared on any mousedown that doesn't - see the "Backdrop" docblock section
        // above and Bootstrap's own `_addEventListeners()` for the mechanism this mirrors.
        mousedownOnBackdrop: false
      };
    },
    computed: {
      sizeClass() {
        return {
          "modal-sm": this.size === "small",
          "modal-lg": this.size === "large"
        };
      }
    },
    watch: {
      show(isOpen) {
        if (isOpen) {
          this.handleOpen();
        } else {
          this.handleClose();
        }
      }
    },
    mounted() {
      if (this.show) {
        this.handleOpen();
      }
    },
    beforeUnmount() {
      document.removeEventListener("keydown", this.onKeydown);
      if (this.show) {
        document.body.classList.remove("modal-open");
        if (this.previouslyFocused && typeof this.previouslyFocused.focus === "function") {
          this.previouslyFocused.focus();
        }
        this.previouslyFocused = null;
      }
    },
    methods: {
      handleOpen() {
        this.previouslyFocused = document.activeElement;
        document.body.classList.add("modal-open");
        document.addEventListener("keydown", this.onKeydown);
        this.$nextTick(() => {
          this.visible = true;
          this.$nextTick(() => {
            if (this.$refs.dialog) {
              this.$refs.dialog.focus();
            }
            this.$emit("opened");
          });
        });
      },
      handleClose() {
        this.visible = false;
        document.body.classList.remove("modal-open");
        document.removeEventListener("keydown", this.onKeydown);
        if (this.previouslyFocused && typeof this.previouslyFocused.focus === "function") {
          this.previouslyFocused.focus();
        }
        this.previouslyFocused = null;
        this.$emit("closed");
      },
      onKeydown(event) {
        if (event.key === "Escape" && this.keyboard) {
          this.requestClose();
        }
      },
      onBackdropMousedown(event) {
        this.mousedownOnBackdrop = event.target === event.currentTarget;
      },
      onBackdropClick() {
        const mousedownWasOnBackdrop = this.mousedownOnBackdrop;
        this.mousedownOnBackdrop = false;
        if (this.backdropClose && mousedownWasOnBackdrop) {
          this.requestClose();
        }
      },
      requestClose() {
        this.$emit("update:show", false);
      }
    }
  };
  const _hoisted_1$k = ["aria-labelledby"];
  const _hoisted_2$e = { class: "modal-content" };
  const _hoisted_3$d = { class: "modal-header" };
  const _hoisted_4$c = ["id"];
  const _hoisted_5$8 = { class: "modal-body" };
  const _hoisted_6$8 = {
    key: 0,
    class: "modal-footer"
  };
  function _sfc_render$m(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createBlock(vue.Teleport, { to: "body" }, [
      $props.show ? (vue.openBlock(), vue.createElementBlock("div", {
        key: 0,
        ref: "dialog",
        class: vue.normalizeClass(["modal fade", { show: $data.visible }]),
        style: { "display": "block" },
        tabindex: "-1",
        role: "dialog",
        "aria-modal": "true",
        "aria-labelledby": $data.titleId,
        onMousedown: _cache[1] || (_cache[1] = (...args) => $options.onBackdropMousedown && $options.onBackdropMousedown(...args)),
        onClick: _cache[2] || (_cache[2] = vue.withModifiers((...args) => $options.onBackdropClick && $options.onBackdropClick(...args), ["self"]))
      }, [
        vue.createElementVNode(
          "div",
          {
            class: vue.normalizeClass(["modal-dialog", [$options.sizeClass, $props.dialogClass]])
          },
          [
            vue.createElementVNode("div", _hoisted_2$e, [
              vue.createElementVNode("div", _hoisted_3$d, [
                vue.renderSlot(_ctx.$slots, "header", { titleId: $data.titleId }, () => [
                  vue.createElementVNode("h5", {
                    class: "modal-title",
                    id: $data.titleId
                  }, vue.toDisplayString($props.title), 9, _hoisted_4$c),
                  vue.createElementVNode("button", {
                    type: "button",
                    class: "btn-close",
                    "aria-label": "Close",
                    onClick: _cache[0] || (_cache[0] = (...args) => $options.requestClose && $options.requestClose(...args))
                  })
                ])
              ]),
              vue.createElementVNode("div", _hoisted_5$8, [
                vue.renderSlot(_ctx.$slots, "default")
              ]),
              _ctx.$slots.footer ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$8, [
                vue.renderSlot(_ctx.$slots, "footer")
              ])) : vue.createCommentVNode("v-if", true)
            ])
          ],
          2
          /* CLASS */
        )
      ], 42, _hoisted_1$k)) : vue.createCommentVNode("v-if", true),
      $props.show ? (vue.openBlock(), vue.createElementBlock(
        "div",
        {
          key: 1,
          class: vue.normalizeClass(["modal-backdrop fade", { show: $data.visible }])
        },
        null,
        2
        /* CLASS */
      )) : vue.createCommentVNode("v-if", true)
    ]);
  }
  const C27 = /* @__PURE__ */ _export_sfc(_sfc_main$m, [["render", _sfc_render$m]]);
  const COLUMN_WIDTHS = { main: 240, chip: 160, date: 120, number: 90, text: 150 };
  const SIDE_COLUMN_WIDTH = 48;
  const FOLD_HYSTERESIS = 32;
  const PAGE_SIZES = [25, 50, 100];
  let uid$3 = 0;
  const _sfc_main$l = {
    name: "DataTable",
    components: { DropdownMenu: C5, FilterBar: C9, HeaderActions, TableCell, UiModal: C27 },
    props: {
      tableId: { type: String, required: true },
      label: { type: String, required: true },
      title: { type: String, default: "" },
      url: { type: String, required: true },
      columns: { type: Array, required: true },
      actions: { type: Array, default: () => [] },
      filters: { type: Array, default: () => [] },
      headerActions: { type: Array, default: () => [] },
      headerMenu: { type: Array, default: () => [] },
      labels: { type: Object, default: () => ({}) },
      syncUrl: { type: Boolean, default: true },
      skeletonCount: { type: Number, default: 5 },
      /**
       * The keys of the optional columns the user chose (`null`: the columns' defaults), the
       * endpoint saving a new choice (`PUT`, `{columns}`) and the most optional columns shown
       * at once (0: no limit).
       */
      columnChoice: { type: Array, default: null },
      columnsUrl: { type: String, default: "" },
      maxColumns: { type: Number, default: 0 }
    },
    data() {
      return {
        values: defaultValues(this.filters),
        sortKey: "",
        sortDescending: false,
        page: 1,
        pageSize: this.storedPageSize(),
        rows: [],
        total: 0,
        pages: 0,
        loading: true,
        error: null,
        expanded: {},
        busy: {},
        foldedKeys: [],
        pageSizes: PAGE_SIZES,
        chosenKeys: this.initialChoice(),
        chooserOpen: false,
        draftKeys: [],
        // The selected rows by id (their rows, for the actions they offer), or every row of
        // the list's filters (`allSelected`).
        selected: {},
        allSelected: false,
        lastSelectedIndex: null
      };
    },
    computed: {
      titleId() {
        return `${this.tableId}-title`;
      },
      texts() {
        return {
          actions: vue$1.i18n.t("base", "Actions"),
          details: vue$1.i18n.t("base", "Details"),
          retry: vue$1.i18n.t("base", "Retry"),
          empty: vue$1.i18n.t("base", "No results found!"),
          clearFilters: vue$1.i18n.t("base", "Clear filters"),
          rowsPerPage: vue$1.i18n.t("base", "Rows per page"),
          pages: vue$1.i18n.t("base", "Pages"),
          previous: vue$1.i18n.t("base", "Previous page"),
          next: vue$1.i18n.t("base", "Next page"),
          columns: vue$1.i18n.t("base", "Columns"),
          hiddenAtWidth: vue$1.i18n.t("base", "Hidden at this width"),
          reset: vue$1.i18n.t("base", "Reset"),
          cancel: vue$1.i18n.t("base", "Cancel"),
          save: vue$1.i18n.t("base", "Save"),
          selectPage: vue$1.i18n.t("base", "Select all on this page"),
          clearSelection: vue$1.i18n.t("base", "Clear selection")
        };
      },
      bulkActions() {
        return this.actions.filter((action) => action.bulkUrl);
      },
      hasBulk() {
        return this.bulkActions.length > 0;
      },
      selectedCount() {
        return this.allSelected ? this.total : Object.keys(this.selected).length;
      },
      selectionLabel() {
        return vue$1.i18n.t("base", "{count} selected", { count: this.selectedCount });
      },
      // `all`, `some` or `none` of the rows on this page selected (the header checkbox).
      pageSelected() {
        const count = this.rows.filter((row) => this.isSelected(row)).length;
        if (count === 0) {
          return "none";
        }
        return count === this.rows.length ? "all" : "some";
      },
      // The selection menu: each bulk action with the selected rows it applies to, then
      // "Select all" (across pages) and "Clear selection".
      bulkEntries() {
        const entries = [];
        this.bulkActions.forEach((action, index) => {
          const count = this.allSelected ? null : Object.values(this.selected).filter((row) => (row.actions || []).some((rowAction) => rowAction.id === action.id)).length;
          if (count === 0) {
            return;
          }
          entries.push({
            id: `bulk-${action.id}`,
            label: count === null ? action.label : `${action.label} (${count})`,
            sortOrder: index * 10,
            htmlOptions: action.destructive ? { class: "dropdown-item-danger" } : {},
            onClick: () => this.runBulk(action, count)
          });
        });
        entries.push({ id: "bulk-divider", divider: true, sortOrder: 900 });
        if (!this.allSelected && this.total > this.selectedCount) {
          entries.push({
            id: "bulk-select-all",
            label: vue$1.i18n.t("base", "Select all {count}", { count: this.total }),
            sortOrder: 910,
            onClick: () => {
              this.allSelected = true;
            }
          });
        }
        entries.push({ id: "bulk-clear", label: this.texts.clearSelection, sortOrder: 920, onClick: () => this.clearSelection() });
        return entries;
      },
      optionalColumns() {
        return this.columns.filter((column) => column.type !== "main");
      },
      defaultKeys() {
        return this.optionalColumns.filter((column) => column.visible !== false).map((column) => column.key);
      },
      hasChooser() {
        return this.optionalColumns.length > 0;
      },
      // The main column and the chosen optional ones, in the columns' order.
      activeColumns() {
        return this.columns.filter((column) => column.type === "main" || this.chosenKeys.includes(column.key));
      },
      atLimit() {
        return this.maxColumns > 0 && this.draftKeys.length >= this.maxColumns;
      },
      draftDiffersFromDefault() {
        return [...this.draftKeys].sort().join() !== [...this.defaultKeys].sort().join();
      },
      limitText() {
        return vue$1.i18n.t("base", "Choose up to {count} columns.", { count: this.maxColumns });
      },
      actionsById() {
        return Object.fromEntries(this.actions.map((action) => [action.id, action]));
      },
      shownColumns() {
        return this.activeColumns.filter((column) => !this.foldedKeys.includes(column.key));
      },
      foldedColumns() {
        return this.activeColumns.filter((column) => this.foldedKeys.includes(column.key));
      },
      hasDetails() {
        return this.foldedKeys.length > 0;
      },
      colspan() {
        return this.shownColumns.length + (this.hasBulk ? 1 : 0) + (this.hasDetails ? 1 : 0) + (this.actions.length ? 1 : 0);
      },
      filtered() {
        return this.filters.some((filter) => !isDefault(filter, this.values[filter.key]));
      },
      sortParam() {
        if (!this.sortKey) {
          return null;
        }
        return this.sortDescending ? `-${this.sortKey}` : this.sortKey;
      },
      countText() {
        if (this.pages <= 1) {
          const template = this.total === 1 ? this.labels.one : this.labels.other;
          return this.format(template || "{count}", { count: this.total });
        }
        const from = (this.page - 1) * this.pageSize + 1;
        const to = Math.min(this.total, this.page * this.pageSize);
        return this.format(this.labels.range || "{from}–{to} / {total}", { from, to, total: this.total });
      },
      // At most five slots: 1 2 3 4 5, 1 2 3 … 12, 1 … 6 … 12, 1 … 10 11 12 (null = gap).
      pagerSlots() {
        const { page, pages } = this;
        if (pages <= 5) {
          return Array.from({ length: pages }, (_, index) => index + 1);
        }
        if (page <= 3) {
          return [1, 2, 3, null, pages];
        }
        if (page >= pages - 2) {
          return [1, null, pages - 2, pages - 1, pages];
        }
        return [1, null, page, null, pages];
      }
    },
    created() {
      this.requestSeq = 0;
      this.started = false;
      this.uid = ++uid$3;
    },
    mounted() {
      this.started = true;
      this.fetch();
      if (typeof ResizeObserver !== "undefined" && this.$refs.wrap) {
        this.resizeObserver = new ResizeObserver(() => this.fold());
        this.resizeObserver.observe(this.$refs.wrap);
      }
    },
    beforeUnmount() {
      var _a;
      this.requestSeq++;
      (_a = this.resizeObserver) == null ? void 0 : _a.disconnect();
    },
    methods: {
      reload() {
        return this.fetch();
      },
      retry() {
        return this.fetch();
      },
      setFilter(key, value) {
        return this.$refs.filterBar ? this.$refs.filterBar.setFilter(key, value) : false;
      },
      clearFilters() {
        var _a;
        (_a = this.$refs.filterBar) == null ? void 0 : _a.reset();
      },
      onFilterChange(values) {
        this.values = values;
        if (this.started) {
          this.page = 1;
          this.clearSelection();
          this.fetch();
        }
      },
      toggleSort(column) {
        if (this.sortKey !== column.sort) {
          this.sortKey = column.sort;
          this.sortDescending = false;
        } else if (!this.sortDescending) {
          this.sortDescending = true;
        } else {
          this.sortKey = "";
          this.sortDescending = false;
        }
        this.page = 1;
        this.clearSelection();
        this.fetch();
      },
      ariaSort(column) {
        if (!column.sort) {
          return null;
        }
        if (this.sortKey !== column.sort) {
          return "none";
        }
        return this.sortDescending ? "descending" : "ascending";
      },
      sortIcon(column) {
        if (this.sortKey !== column.sort) {
          return "ti-arrows-sort";
        }
        return this.sortDescending ? "ti-arrow-down" : "ti-arrow-up";
      },
      // The label names what a click does next.
      sortLabel(column) {
        if (this.sortKey === column.sort && !this.sortDescending) {
          return vue$1.i18n.t("base", "{column}, sort descending", { column: column.label });
        }
        if (this.sortKey === column.sort) {
          return vue$1.i18n.t("base", "{column}, default order", { column: column.label });
        }
        return vue$1.i18n.t("base", "{column}, sort ascending", { column: column.label });
      },
      goTo(page) {
        if (page < 1 || page > this.pages || page === this.page) {
          return;
        }
        this.page = page;
        this.fetch().then(() => {
          var _a, _b;
          return (_b = (_a = this.$refs.root) == null ? void 0 : _a.scrollIntoView) == null ? void 0 : _b.call(_a, { block: "start", behavior: "smooth" });
        });
      },
      setPageSize(size) {
        this.pageSize = PAGE_SIZES.includes(size) ? size : PAGE_SIZES[0];
        try {
          window.localStorage.setItem(this.storageKey(), String(this.pageSize));
        } catch (e) {
        }
        this.page = 1;
        this.fetch();
      },
      storageKey() {
        return `humhub.dataTable.${this.tableId}.pageSize`;
      },
      storedPageSize() {
        try {
          const stored = Number(window.localStorage.getItem(this.storageKey()));
          return PAGE_SIZES.includes(stored) ? stored : PAGE_SIZES[0];
        } catch (e) {
          return PAGE_SIZES[0];
        }
      },
      pageLabel(page) {
        return vue$1.i18n.t("base", "Page {page}", { page });
      },
      rowName(row) {
        const main = this.columns.find((column) => column.type === "main");
        return main && row.cells[main.key] && row.cells[main.key].text || String(row.id);
      },
      // A label naming the row's record: "Details for Emma Williams".
      rowLabel(message, row) {
        return vue$1.i18n.t("base", message, { name: this.rowName(row) });
      },
      actionLabel(action, row) {
        return `${action.label}: ${this.rowName(row)}`;
      },
      format(template, params) {
        return String(template).replace(/\{(\w+)\}/g, (match, key) => params[key] !== void 0 ? params[key] : match);
      },
      fetch() {
        const seq = ++this.requestSeq;
        this.loading = true;
        this.error = null;
        const params = {
          ...requestParams(this.filters, this.values),
          page: String(this.page),
          pageSize: String(this.pageSize)
        };
        if (this.sortParam) {
          params.sort = this.sortParam;
        }
        const url = this.url + (this.url.includes("?") ? "&" : "?") + new URLSearchParams(params).toString();
        return vue$1.client.get(url).then((response) => {
          if (seq !== this.requestSeq) {
            return;
          }
          this.rows = response.results || [];
          this.total = response.total || 0;
          this.pages = response.pages || 0;
          this.page = response.page || this.page;
          this.expanded = {};
          this.loading = false;
          this.$nextTick(() => this.fold());
        }).catch((response) => {
          if (seq !== this.requestSeq) {
            return;
          }
          this.loading = false;
          this.error = response && typeof response.message === "string" && response.message !== "" ? response.message : vue$1.i18n.t("base", "The list could not be loaded.");
          vue$1.log.error(response);
        });
      },
      // Folds the columns that no longer fit, the highest priority number first.
      fold() {
        const wrap = this.$refs.wrap;
        if (!wrap || !wrap.clientWidth) {
          return;
        }
        const foldable = this.activeColumns.filter((column) => column.type !== "main").sort((a, b) => (b.priority || 1) - (a.priority || 1));
        const width = (columns) => columns.reduce(
          (sum, column) => sum + (COLUMN_WIDTHS[column.type] || 150),
          SIDE_COLUMN_WIDTH * ((this.actions.length ? 1 : 0) + (columns.length < this.activeColumns.length ? 1 : 0))
        );
        const folded = [];
        let shown = [...this.activeColumns];
        for (const column of foldable) {
          if (width(shown) <= wrap.clientWidth) {
            break;
          }
          folded.push(column.key);
          shown = shown.filter((candidate) => candidate !== column);
        }
        const unfolding = this.foldedKeys.filter((key) => !folded.includes(key));
        if (unfolding.length && width(shown) > wrap.clientWidth - FOLD_HYSTERESIS) {
          return;
        }
        if (folded.join() !== this.foldedKeys.join()) {
          this.foldedKeys = folded;
        }
      },
      initialChoice() {
        const keys = this.columns.filter((column) => column.type !== "main").map((column) => column.key);
        if (Array.isArray(this.columnChoice)) {
          return this.columnChoice.filter((key) => keys.includes(key));
        }
        return this.columns.filter((column) => column.type !== "main" && column.visible !== false).map((column) => column.key);
      },
      openChooser() {
        this.draftKeys = [...this.chosenKeys];
        this.chooserOpen = true;
      },
      toggleDraft(key, on) {
        const keys = this.draftKeys.filter((candidate) => candidate !== key);
        this.draftKeys = on ? [...keys, key] : keys;
      },
      resetDraft() {
        this.draftKeys = [...this.defaultKeys];
      },
      // Applies the choice at once and saves it for the user (back to the defaults: `null`).
      saveColumns() {
        const keys = this.optionalColumns.map((column) => column.key).filter((key) => this.draftKeys.includes(key));
        this.chosenKeys = keys;
        this.chooserOpen = false;
        this.$nextTick(() => this.fold());
        if (!this.columnsUrl) {
          return Promise.resolve();
        }
        const columns = this.draftDiffersFromDefault ? keys : null;
        return vue$1.client.put(this.columnsUrl, { data: JSON.stringify({ columns }), contentType: "application/json" }).catch((response) => {
          vue$1.status("error", response && response.message || vue$1.i18n.t("base", "An unexpected error occurred."));
          vue$1.log.error(response);
        });
      },
      isSelected(row) {
        return this.allSelected || this.selected[row.id] !== void 0;
      },
      // A row's checkbox; with Shift every row between the last one clicked and this one.
      onSelect(row, index, event) {
        const on = event.target.checked;
        const rows = event.shiftKey && this.lastSelectedIndex !== null ? this.rows.slice(Math.min(this.lastSelectedIndex, index), Math.max(this.lastSelectedIndex, index) + 1) : [row];
        this.setSelected(rows, on);
        this.lastSelectedIndex = index;
      },
      selectPage(on) {
        this.setSelected(this.rows, on);
      },
      setSelected(rows, on) {
        const selected = this.allSelected ? Object.fromEntries(this.rows.map((row) => [row.id, row])) : { ...this.selected };
        this.allSelected = false;
        rows.forEach((row) => {
          if (on) {
            selected[row.id] = row;
          } else {
            delete selected[row.id];
          }
        });
        this.selected = selected;
      },
      clearSelection() {
        this.selected = {};
        this.allSelected = false;
        this.lastSelectedIndex = null;
      },
      // Applies a bulk action to the selection (its ids, or `all` with the list's filters),
      // then reloads the page.
      runBulk(action, count) {
        this.closeMenus();
        const body = count === null ? vue$1.i18n.t("base", 'Apply "{action}" to all {count} entries?', { action: action.label, count: this.total }) : vue$1.i18n.t("base", 'Apply "{action}" to {count} selected entries?', { action: action.label, count });
        return vue$1.modal.confirm({ body, confirmText: action.label }).then((ok) => {
          if (!ok) {
            return null;
          }
          let url = action.bulkUrl;
          let data;
          if (this.allSelected) {
            const params = new URLSearchParams(requestParams(this.filters, this.values)).toString();
            url += params ? (url.includes("?") ? "&" : "?") + params : "";
            data = { all: 1 };
          } else {
            data = { ids: Object.values(this.selected).filter((row) => (row.actions || []).some((rowAction) => rowAction.id === action.id)).map((row) => row.id) };
          }
          return vue$1.client.post(url, { data }).then((response) => {
            if (response && response.message) {
              vue$1.status("success", response.message);
            }
            this.clearSelection();
            return this.fetch();
          }).catch((response) => {
            vue$1.status("error", response && response.message || vue$1.i18n.t("base", "An unexpected error occurred."));
            vue$1.log.error(response);
          });
        });
      },
      isExpanded(row) {
        return !!this.expanded[row.id];
      },
      toggleDetails(row) {
        this.expanded = { ...this.expanded, [row.id]: !this.expanded[row.id] };
      },
      detailsId(row) {
        return `${this.tableId}-details-${this.uid}-${row.id}`;
      },
      // The record the row links to: its main cell's URL.
      rowUrl(row) {
        const main = this.columns.find((column) => column.type === "main");
        return main && row.cells[main.key] && row.cells[main.key].url || null;
      },
      // A click on the row opens its record (with Ctrl / Cmd in a new tab); a row without one
      // toggles its details while columns are folded — but never a click on a link or a
      // button inside it.
      onRowClick(row, event) {
        if (event.target.closest("a, button, input, select, label")) {
          return;
        }
        const url = this.rowUrl(row);
        if (url) {
          this.open(url, event.ctrlKey || event.metaKey);
          return;
        }
        if (this.hasDetails) {
          this.toggleDetails(row);
        }
      },
      open(url, newTab = false) {
        if (newTab) {
          window.open(url, "_blank", "noopener");
        } else {
          window.location.assign(url);
        }
      },
      rowActions(row) {
        return (row.actions || []).map((rowAction) => this.actionsById[rowAction.id] ? { ...this.actionsById[rowAction.id], url: rowAction.url } : null).filter(Boolean);
      },
      menuEntries(row) {
        const entries = [];
        const actions = this.rowActions(row);
        actions.forEach((action, index) => {
          if (action.destructive && index > 0 && !actions[index - 1].destructive) {
            entries.push({ id: `divider-${action.id}`, divider: true, sortOrder: index * 10 - 5 });
          }
          const entry = {
            id: action.id,
            label: action.label,
            url: action.url,
            sortOrder: index * 10,
            htmlOptions: action.destructive ? { class: "dropdown-item-danger" } : {}
          };
          if (action.kind !== "link" || action.confirm) {
            entry.onClick = () => this.runAction(row, action);
          }
          entries.push(entry);
        });
        return entries;
      },
      runAction(row, action, event) {
        if (action.kind === "link" && !action.confirm) {
          return Promise.resolve();
        }
        event == null ? void 0 : event.preventDefault();
        this.closeMenus();
        const confirmed = action.confirm ? vue$1.modal.confirm({ body: action.confirm, confirmText: action.label }) : Promise.resolve(true);
        return confirmed.then((ok) => {
          if (!ok) {
            return null;
          }
          switch (action.kind) {
            case "modal":
              return vue$1.modal.load(action.url);
            case "post":
              return this.submitPost(action.url);
            case "request":
              return this.request(row, action);
            default:
              window.location.assign(action.url);
              return null;
          }
        });
      },
      // The ⋮ menu an action was chosen from stays open on its own (its entry handles the click).
      closeMenus() {
        var _a, _b;
        const Dropdown = typeof bootstrap === "undefined" ? null : bootstrap.Dropdown;
        (_b = (_a = this.$el).querySelectorAll) == null ? void 0 : _b.call(_a, '[data-bs-toggle="dropdown"][aria-expanded="true"]').forEach((toggle) => {
          var _a2;
          (_a2 = Dropdown == null ? void 0 : Dropdown.getInstance(toggle)) == null ? void 0 : _a2.hide();
        });
      },
      submitPost(url) {
        var _a, _b;
        const form = document.createElement("form");
        form.method = "post";
        form.action = url;
        const param = (_a = document.querySelector('meta[name="csrf-param"]')) == null ? void 0 : _a.getAttribute("content");
        const token = (_b = document.querySelector('meta[name="csrf-token"]')) == null ? void 0 : _b.getAttribute("content");
        if (param && token) {
          const input = document.createElement("input");
          input.type = "hidden";
          input.name = param;
          input.value = token;
          form.appendChild(input);
        }
        document.body.appendChild(form);
        form.submit();
      },
      request(row, action) {
        this.busy = { ...this.busy, [row.id]: true };
        return vue$1.client.post(action.url).then((response) => {
          if (response && response.removed) {
            this.rows = this.rows.filter((candidate) => candidate.id !== row.id);
            this.total = Math.max(0, this.total - 1);
          } else if (response && response.row) {
            const index = this.rows.findIndex((candidate) => candidate.id === row.id);
            if (index !== -1) {
              this.rows.splice(index, 1, response.row);
            }
          }
          if (response && response.message) {
            vue$1.status("success", response.message);
          }
        }).catch((response) => {
          vue$1.status("error", response && response.message || vue$1.i18n.t("base", "An unexpected error occurred."));
          vue$1.log.error(response);
        }).finally(() => {
          const busy = { ...this.busy };
          delete busy[row.id];
          this.busy = busy;
        });
      }
    }
  };
  const _hoisted_1$j = ["id", "aria-labelledby"];
  const _hoisted_2$d = {
    key: 0,
    class: "card-header"
  };
  const _hoisted_3$c = ["id"];
  const _hoisted_4$b = ["aria-label", "title"];
  const _hoisted_5$7 = {
    class: "visually-hidden",
    role: "status"
  };
  const _hoisted_6$7 = { class: "card-body" };
  const _hoisted_7$5 = {
    ref: "wrap",
    class: "data-table-wrap"
  };
  const _hoisted_8$5 = ["aria-label", "aria-busy"];
  const _hoisted_9$4 = {
    key: 0,
    class: "data-table-col-select"
  };
  const _hoisted_10$4 = ["checked", ".indeterminate", "disabled", "aria-label"];
  const _hoisted_11$3 = ["aria-sort"];
  const _hoisted_12$3 = ["aria-label", "onClick"];
  const _hoisted_13$1 = {
    key: 1,
    class: "data-table-col-details"
  };
  const _hoisted_14 = { class: "visually-hidden" };
  const _hoisted_15 = {
    key: 2,
    class: "data-table-col-actions"
  };
  const _hoisted_16 = { class: "visually-hidden" };
  const _hoisted_17 = { key: 0 };
  const _hoisted_18 = {
    key: 0,
    class: "data-table-col-select"
  };
  const _hoisted_19 = {
    key: 0,
    class: "data-table-main"
  };
  const _hoisted_20 = {
    key: 1,
    class: "placeholder col-6"
  };
  const _hoisted_21 = {
    key: 1,
    class: "data-table-col-details"
  };
  const _hoisted_22 = {
    key: 2,
    class: "data-table-col-actions"
  };
  const _hoisted_23 = { key: 1 };
  const _hoisted_24 = { class: "data-table-message" };
  const _hoisted_25 = ["colspan"];
  const _hoisted_26 = { role: "alert" };
  const _hoisted_27 = { role: "status" };
  const _hoisted_28 = { key: 2 };
  const _hoisted_29 = ["data-id", "onClick"];
  const _hoisted_30 = {
    key: 0,
    class: "data-table-col-select"
  };
  const _hoisted_31 = ["checked", "aria-label", "onClick"];
  const _hoisted_32 = {
    key: 1,
    class: "data-table-col-details"
  };
  const _hoisted_33 = ["aria-expanded", "aria-controls", "aria-label", "onClick"];
  const _hoisted_34 = ["href", "aria-label", "title", "onClick"];
  const _hoisted_35 = ["id"];
  const _hoisted_36 = ["colspan"];
  const _hoisted_37 = {
    key: 1,
    class: "alert alert-danger data-table-error",
    role: "alert"
  };
  const _hoisted_38 = {
    key: 1,
    class: "card-footer data-table-footer"
  };
  const _hoisted_39 = {
    class: "data-table-count",
    role: "status"
  };
  const _hoisted_40 = { class: "data-table-page-size" };
  const _hoisted_41 = ["value"];
  const _hoisted_42 = ["value"];
  const _hoisted_43 = ["aria-label"];
  const _hoisted_44 = { class: "pagination pagination-sm" };
  const _hoisted_45 = ["aria-label", "disabled"];
  const _hoisted_46 = {
    key: 0,
    class: "page-link",
    "aria-hidden": "true"
  };
  const _hoisted_47 = ["aria-current", "aria-label", "onClick"];
  const _hoisted_48 = ["aria-label", "disabled"];
  const _hoisted_49 = {
    key: 0,
    class: "form-text data-table-columns-limit"
  };
  const _hoisted_50 = ["id", "checked", "disabled", "onChange"];
  const _hoisted_51 = ["for"];
  const _hoisted_52 = {
    key: 0,
    class: "form-text data-table-columns-folded"
  };
  function _sfc_render$l(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DropdownMenu = vue.resolveComponent("DropdownMenu");
    const _component_HeaderActions = vue.resolveComponent("HeaderActions");
    const _component_FilterBar = vue.resolveComponent("FilterBar");
    const _component_TableCell = vue.resolveComponent("TableCell");
    const _component_UiModal = vue.resolveComponent("UiModal");
    return vue.openBlock(), vue.createElementBlock("section", {
      id: $props.tableId,
      ref: "root",
      class: "card content-card data-table",
      "aria-labelledby": $props.title ? $options.titleId : null
    }, [
      $props.title || $props.headerActions.length || $props.headerMenu.length || $options.hasChooser || $options.hasBulk ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$d, [
        $props.title ? (vue.openBlock(), vue.createElementBlock("h2", {
          key: 0,
          id: $options.titleId,
          class: "card-title"
        }, vue.toDisplayString($props.title), 9, _hoisted_3$c)) : vue.createCommentVNode("v-if", true),
        vue.createVNode(_component_HeaderActions, {
          actions: $props.headerActions,
          menu: $options.selectedCount ? [] : $props.headerMenu,
          "root-class": "card-header-actions",
          "tool-variant": "ghost"
        }, {
          end: vue.withCtx(() => [
            vue.createCommentVNode(" While rows are selected, the ⋮ is the selection menu with the count "),
            $options.selectedCount ? (vue.openBlock(), vue.createBlock(_component_DropdownMenu, {
              key: 0,
              "menu-id": `${$props.tableId}.bulk`,
              entries: $options.bulkEntries,
              "toggle-aria-label": $options.selectionLabel,
              "toggle-title": $options.selectionLabel,
              "toggle-class": "btn btn-secondary data-table-bulk-toggle",
              "root-class": "data-table-bulk list-unstyled m-0"
            }, {
              toggle: vue.withCtx(() => [
                vue.createTextVNode(
                  vue.toDisplayString($options.selectedCount),
                  1
                  /* TEXT */
                ),
                _cache[14] || (_cache[14] = vue.createElementVNode(
                  "i",
                  {
                    class: "ti ti-dots-vertical",
                    "aria-hidden": "true"
                  },
                  null,
                  -1
                  /* CACHED */
                ))
              ]),
              _: 1
              /* STABLE */
            }, 8, ["menu-id", "entries", "toggle-aria-label", "toggle-title"])) : vue.createCommentVNode("v-if", true)
          ]),
          default: vue.withCtx(() => [
            $options.hasChooser ? (vue.openBlock(), vue.createElementBlock("button", {
              key: 0,
              type: "button",
              class: "btn btn-ghost btn-icon-only data-table-columns-toggle",
              "aria-label": $options.texts.columns,
              title: $options.texts.columns,
              onClick: _cache[0] || (_cache[0] = (...args) => $options.openChooser && $options.openChooser(...args))
            }, [..._cache[13] || (_cache[13] = [
              vue.createElementVNode(
                "i",
                {
                  class: "ti ti-layout-columns",
                  "aria-hidden": "true"
                },
                null,
                -1
                /* CACHED */
              )
            ])], 8, _hoisted_4$b)) : vue.createCommentVNode("v-if", true)
          ]),
          _: 1
          /* STABLE */
        }, 8, ["actions", "menu"]),
        vue.createElementVNode(
          "span",
          _hoisted_5$7,
          vue.toDisplayString($options.selectedCount ? $options.selectionLabel : ""),
          1
          /* TEXT */
        )
      ])) : vue.createCommentVNode("v-if", true),
      vue.createElementVNode("div", _hoisted_6$7, [
        $props.filters.length ? (vue.openBlock(), vue.createBlock(_component_FilterBar, {
          key: 0,
          ref: "filterBar",
          "model-value": $data.values,
          filters: $props.filters,
          "sync-url": $props.syncUrl,
          "id-prefix": `${$props.tableId}-filter`,
          "onUpdate:modelValue": $options.onFilterChange
        }, null, 8, ["model-value", "filters", "sync-url", "id-prefix", "onUpdate:modelValue"])) : vue.createCommentVNode("v-if", true),
        vue.createElementVNode(
          "div",
          _hoisted_7$5,
          [
            vue.createElementVNode("table", {
              class: vue.normalizeClass(["table table-hover", { "is-loading": $data.loading && $data.rows.length > 0 }]),
              "aria-label": $props.label,
              "aria-busy": $data.loading ? "true" : "false"
            }, [
              vue.createElementVNode("thead", null, [
                vue.createElementVNode("tr", null, [
                  $options.hasBulk ? (vue.openBlock(), vue.createElementBlock("th", _hoisted_9$4, [
                    vue.createElementVNode("input", {
                      class: "form-check-input",
                      type: "checkbox",
                      checked: $options.pageSelected === "all",
                      ".indeterminate": $options.pageSelected === "some",
                      disabled: !$data.rows.length,
                      "aria-label": $options.texts.selectPage,
                      onChange: _cache[1] || (_cache[1] = ($event) => $options.selectPage($event.target.checked))
                    }, null, 40, _hoisted_10$4)
                  ])) : vue.createCommentVNode("v-if", true),
                  (vue.openBlock(true), vue.createElementBlock(
                    vue.Fragment,
                    null,
                    vue.renderList($options.shownColumns, (column) => {
                      return vue.openBlock(), vue.createElementBlock("th", {
                        key: column.key,
                        scope: "col",
                        class: vue.normalizeClass(`data-table-col-${column.type}`),
                        "aria-sort": $options.ariaSort(column)
                      }, [
                        column.sort ? (vue.openBlock(), vue.createElementBlock("button", {
                          key: 0,
                          type: "button",
                          class: vue.normalizeClass(["data-table-sort", { active: $data.sortKey === column.sort }]),
                          "aria-label": $options.sortLabel(column),
                          onClick: ($event) => $options.toggleSort(column)
                        }, [
                          vue.createTextVNode(
                            vue.toDisplayString(column.label),
                            1
                            /* TEXT */
                          ),
                          vue.createElementVNode(
                            "i",
                            {
                              class: vue.normalizeClass(["ti", $options.sortIcon(column)]),
                              "aria-hidden": "true"
                            },
                            null,
                            2
                            /* CLASS */
                          )
                        ], 10, _hoisted_12$3)) : (vue.openBlock(), vue.createElementBlock(
                          vue.Fragment,
                          { key: 1 },
                          [
                            vue.createTextVNode(
                              vue.toDisplayString(column.label),
                              1
                              /* TEXT */
                            )
                          ],
                          64
                          /* STABLE_FRAGMENT */
                        ))
                      ], 10, _hoisted_11$3);
                    }),
                    128
                    /* KEYED_FRAGMENT */
                  )),
                  $options.hasDetails ? (vue.openBlock(), vue.createElementBlock("th", _hoisted_13$1, [
                    vue.createElementVNode(
                      "span",
                      _hoisted_14,
                      vue.toDisplayString($options.texts.details),
                      1
                      /* TEXT */
                    )
                  ])) : vue.createCommentVNode("v-if", true),
                  $props.actions.length ? (vue.openBlock(), vue.createElementBlock("th", _hoisted_15, [
                    vue.createElementVNode(
                      "span",
                      _hoisted_16,
                      vue.toDisplayString($options.texts.actions),
                      1
                      /* TEXT */
                    )
                  ])) : vue.createCommentVNode("v-if", true)
                ])
              ]),
              $data.loading && !$data.rows.length && !$data.error ? (vue.openBlock(), vue.createElementBlock("tbody", _hoisted_17, [
                (vue.openBlock(true), vue.createElementBlock(
                  vue.Fragment,
                  null,
                  vue.renderList($props.skeletonCount, (index) => {
                    return vue.openBlock(), vue.createElementBlock("tr", {
                      key: index,
                      class: "data-table-skeleton placeholder-glow",
                      "aria-hidden": "true"
                    }, [
                      $options.hasBulk ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_18)) : vue.createCommentVNode("v-if", true),
                      (vue.openBlock(true), vue.createElementBlock(
                        vue.Fragment,
                        null,
                        vue.renderList($options.shownColumns, (column) => {
                          return vue.openBlock(), vue.createElementBlock(
                            "td",
                            {
                              key: column.key,
                              class: vue.normalizeClass(`data-table-col-${column.type}`)
                            },
                            [
                              column.type === "main" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_19, [..._cache[15] || (_cache[15] = [
                                vue.createElementVNode(
                                  "span",
                                  { class: "placeholder data-table-main-image" },
                                  null,
                                  -1
                                  /* CACHED */
                                ),
                                vue.createElementVNode(
                                  "span",
                                  { class: "data-table-main-text" },
                                  [
                                    vue.createElementVNode("span", { class: "placeholder col-8" }),
                                    vue.createElementVNode("span", { class: "placeholder placeholder-sm col-6" })
                                  ],
                                  -1
                                  /* CACHED */
                                )
                              ])])) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_20))
                            ],
                            2
                            /* CLASS */
                          );
                        }),
                        128
                        /* KEYED_FRAGMENT */
                      )),
                      $options.hasDetails ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_21)) : vue.createCommentVNode("v-if", true),
                      $props.actions.length ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_22)) : vue.createCommentVNode("v-if", true)
                    ]);
                  }),
                  128
                  /* KEYED_FRAGMENT */
                ))
              ])) : !$data.rows.length ? (vue.openBlock(), vue.createElementBlock("tbody", _hoisted_23, [
                vue.createElementVNode("tr", _hoisted_24, [
                  vue.createElementVNode("td", { colspan: $options.colspan }, [
                    $data.error ? (vue.openBlock(), vue.createElementBlock(
                      vue.Fragment,
                      { key: 0 },
                      [
                        vue.createElementVNode(
                          "span",
                          _hoisted_26,
                          vue.toDisplayString($data.error),
                          1
                          /* TEXT */
                        ),
                        vue.createElementVNode(
                          "button",
                          {
                            type: "button",
                            class: "btn btn-link btn-sm",
                            onClick: _cache[2] || (_cache[2] = (...args) => $options.retry && $options.retry(...args))
                          },
                          vue.toDisplayString($options.texts.retry),
                          1
                          /* TEXT */
                        )
                      ],
                      64
                      /* STABLE_FRAGMENT */
                    )) : (vue.openBlock(), vue.createElementBlock(
                      vue.Fragment,
                      { key: 1 },
                      [
                        vue.createElementVNode(
                          "span",
                          _hoisted_27,
                          vue.toDisplayString($options.texts.empty),
                          1
                          /* TEXT */
                        ),
                        $options.filtered ? (vue.openBlock(), vue.createElementBlock(
                          "button",
                          {
                            key: 0,
                            type: "button",
                            class: "btn btn-link btn-sm",
                            onClick: _cache[3] || (_cache[3] = (...args) => $options.clearFilters && $options.clearFilters(...args))
                          },
                          vue.toDisplayString($options.texts.clearFilters),
                          1
                          /* TEXT */
                        )) : vue.createCommentVNode("v-if", true)
                      ],
                      64
                      /* STABLE_FRAGMENT */
                    ))
                  ], 8, _hoisted_25)
                ])
              ])) : (vue.openBlock(), vue.createElementBlock("tbody", _hoisted_28, [
                (vue.openBlock(true), vue.createElementBlock(
                  vue.Fragment,
                  null,
                  vue.renderList($data.rows, (row, index) => {
                    return vue.openBlock(), vue.createElementBlock(
                      vue.Fragment,
                      {
                        key: row.id
                      },
                      [
                        vue.createElementVNode("tr", {
                          class: vue.normalizeClass({ "is-expanded": $options.isExpanded(row), "is-busy": $data.busy[row.id], "table-active": $options.isSelected(row), "data-table-row-link": !!$options.rowUrl(row), "data-table-row-toggle": !$options.rowUrl(row) && $options.hasDetails }),
                          "data-id": row.id,
                          onClick: ($event) => $options.onRowClick(row, $event)
                        }, [
                          $options.hasBulk ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_30, [
                            vue.createElementVNode("input", {
                              class: "form-check-input",
                              type: "checkbox",
                              checked: $options.isSelected(row),
                              "aria-label": $options.rowLabel("Select {name}", row),
                              onClick: ($event) => $options.onSelect(row, index, $event)
                            }, null, 8, _hoisted_31)
                          ])) : vue.createCommentVNode("v-if", true),
                          (vue.openBlock(true), vue.createElementBlock(
                            vue.Fragment,
                            null,
                            vue.renderList($options.shownColumns, (column) => {
                              return vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(column.type === "main" ? "th" : "td"), {
                                key: column.key,
                                scope: column.type === "main" ? "row" : null,
                                class: vue.normalizeClass(`data-table-col-${column.type}`)
                              }, {
                                default: vue.withCtx(() => [
                                  row.cells[column.key] !== void 0 ? (vue.openBlock(), vue.createBlock(_component_TableCell, {
                                    key: 0,
                                    column,
                                    cell: row.cells[column.key],
                                    onFilter: $options.setFilter
                                  }, null, 8, ["column", "cell", "onFilter"])) : vue.createCommentVNode("v-if", true)
                                ]),
                                _: 2
                                /* DYNAMIC */
                              }, 1032, ["scope", "class"]);
                            }),
                            128
                            /* KEYED_FRAGMENT */
                          )),
                          $options.hasDetails ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_32, [
                            vue.createElementVNode("button", {
                              type: "button",
                              class: "btn btn-ghost btn-icon-only btn-sm data-table-details-toggle",
                              "aria-expanded": $options.isExpanded(row) ? "true" : "false",
                              "aria-controls": $options.detailsId(row),
                              "aria-label": $options.rowLabel("Details for {name}", row),
                              onClick: vue.withModifiers(($event) => $options.toggleDetails(row), ["stop"])
                            }, [
                              vue.createElementVNode(
                                "i",
                                {
                                  class: vue.normalizeClass(["ti", $options.isExpanded(row) ? "ti-chevron-up" : "ti-chevron-down"]),
                                  "aria-hidden": "true"
                                },
                                null,
                                2
                                /* CLASS */
                              )
                            ], 8, _hoisted_33)
                          ])) : vue.createCommentVNode("v-if", true),
                          $props.actions.length ? (vue.openBlock(), vue.createElementBlock("td", {
                            key: 2,
                            class: "data-table-col-actions",
                            onClick: _cache[4] || (_cache[4] = vue.withModifiers(() => {
                            }, ["stop"]))
                          }, [
                            $options.rowActions(row).length === 1 ? (vue.openBlock(), vue.createElementBlock("a", {
                              key: 0,
                              href: $options.rowActions(row)[0].url,
                              class: vue.normalizeClass(["btn btn-sm", [$options.rowActions(row)[0].destructive ? "btn-ghost-danger" : "btn-ghost", { "btn-icon-only": $options.rowActions(row)[0].icon }]]),
                              "aria-label": $options.actionLabel($options.rowActions(row)[0], row),
                              title: $options.rowActions(row)[0].label,
                              onClick: ($event) => $options.runAction(row, $options.rowActions(row)[0], $event)
                            }, [
                              $options.rowActions(row)[0].icon ? (vue.openBlock(), vue.createElementBlock(
                                "i",
                                {
                                  key: 0,
                                  class: vue.normalizeClass(["ti", `ti-${$options.rowActions(row)[0].icon}`]),
                                  "aria-hidden": "true"
                                },
                                null,
                                2
                                /* CLASS */
                              )) : (vue.openBlock(), vue.createElementBlock(
                                vue.Fragment,
                                { key: 1 },
                                [
                                  vue.createTextVNode(
                                    vue.toDisplayString($options.rowActions(row)[0].label),
                                    1
                                    /* TEXT */
                                  )
                                ],
                                64
                                /* STABLE_FRAGMENT */
                              ))
                            ], 10, _hoisted_34)) : $options.rowActions(row).length > 1 ? (vue.openBlock(), vue.createBlock(_component_DropdownMenu, {
                              key: 1,
                              "menu-id": `${$props.tableId}.row`,
                              entries: $options.menuEntries(row),
                              context: { row },
                              "toggle-aria-label": $options.rowLabel("More actions for {name}", row),
                              "toggle-title": $options.texts.actions,
                              "toggle-class": "btn btn-ghost btn-icon-only btn-sm",
                              "root-class": "data-table-actions-menu list-unstyled m-0"
                            }, {
                              toggle: vue.withCtx(() => [..._cache[16] || (_cache[16] = [
                                vue.createElementVNode(
                                  "i",
                                  {
                                    class: "ti ti-dots-vertical",
                                    "aria-hidden": "true"
                                  },
                                  null,
                                  -1
                                  /* CACHED */
                                )
                              ])]),
                              _: 1
                              /* STABLE */
                            }, 8, ["menu-id", "entries", "context", "toggle-aria-label", "toggle-title"])) : vue.createCommentVNode("v-if", true)
                          ])) : vue.createCommentVNode("v-if", true)
                        ], 10, _hoisted_29),
                        $options.hasDetails && $options.isExpanded(row) ? (vue.openBlock(), vue.createElementBlock("tr", {
                          key: 0,
                          id: $options.detailsId(row),
                          class: "data-table-row-details"
                        }, [
                          vue.createElementVNode("td", { colspan: $options.colspan }, [
                            vue.createElementVNode("dl", null, [
                              (vue.openBlock(true), vue.createElementBlock(
                                vue.Fragment,
                                null,
                                vue.renderList($options.foldedColumns, (column) => {
                                  return vue.openBlock(), vue.createElementBlock(
                                    vue.Fragment,
                                    {
                                      key: column.key
                                    },
                                    [
                                      row.cells[column.key] !== void 0 ? (vue.openBlock(), vue.createElementBlock(
                                        vue.Fragment,
                                        { key: 0 },
                                        [
                                          vue.createElementVNode(
                                            "dt",
                                            null,
                                            vue.toDisplayString(column.label),
                                            1
                                            /* TEXT */
                                          ),
                                          vue.createElementVNode("dd", null, [
                                            vue.createVNode(_component_TableCell, {
                                              column,
                                              cell: row.cells[column.key],
                                              onFilter: $options.setFilter
                                            }, null, 8, ["column", "cell", "onFilter"])
                                          ])
                                        ],
                                        64
                                        /* STABLE_FRAGMENT */
                                      )) : vue.createCommentVNode("v-if", true)
                                    ],
                                    64
                                    /* STABLE_FRAGMENT */
                                  );
                                }),
                                128
                                /* KEYED_FRAGMENT */
                              ))
                            ])
                          ], 8, _hoisted_36)
                        ], 8, _hoisted_35)) : vue.createCommentVNode("v-if", true)
                      ],
                      64
                      /* STABLE_FRAGMENT */
                    );
                  }),
                  128
                  /* KEYED_FRAGMENT */
                ))
              ]))
            ], 10, _hoisted_8$5)
          ],
          512
          /* NEED_PATCH */
        ),
        $data.error && $data.rows.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_37, [
          vue.createTextVNode(
            vue.toDisplayString($data.error) + " ",
            1
            /* TEXT */
          ),
          vue.createElementVNode(
            "button",
            {
              type: "button",
              class: "btn btn-link btn-sm",
              onClick: _cache[5] || (_cache[5] = (...args) => $options.retry && $options.retry(...args))
            },
            vue.toDisplayString($options.texts.retry),
            1
            /* TEXT */
          )
        ])) : vue.createCommentVNode("v-if", true)
      ]),
      $data.total > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_38, [
        vue.createElementVNode(
          "span",
          _hoisted_39,
          vue.toDisplayString($options.countText),
          1
          /* TEXT */
        ),
        vue.createElementVNode("label", _hoisted_40, [
          vue.createElementVNode(
            "span",
            null,
            vue.toDisplayString($options.texts.rowsPerPage),
            1
            /* TEXT */
          ),
          vue.createElementVNode("select", {
            class: "form-select form-select-sm",
            value: $data.pageSize,
            onChange: _cache[6] || (_cache[6] = ($event) => $options.setPageSize(Number($event.target.value)))
          }, [
            (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              null,
              vue.renderList($data.pageSizes, (size) => {
                return vue.openBlock(), vue.createElementBlock("option", {
                  key: size,
                  value: size
                }, vue.toDisplayString(size), 9, _hoisted_42);
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ], 40, _hoisted_41)
        ]),
        $data.pages > 1 ? (vue.openBlock(), vue.createElementBlock("nav", {
          key: 0,
          class: "data-table-pager",
          "aria-label": $options.texts.pages
        }, [
          vue.createElementVNode("ul", _hoisted_44, [
            vue.createElementVNode(
              "li",
              {
                class: vue.normalizeClass(["page-item", { disabled: $data.page <= 1 }])
              },
              [
                vue.createElementVNode("button", {
                  type: "button",
                  class: "page-link",
                  "aria-label": $options.texts.previous,
                  disabled: $data.page <= 1,
                  onClick: _cache[7] || (_cache[7] = ($event) => $options.goTo($data.page - 1))
                }, [..._cache[17] || (_cache[17] = [
                  vue.createElementVNode(
                    "i",
                    {
                      class: "ti ti-chevron-left",
                      "aria-hidden": "true"
                    },
                    null,
                    -1
                    /* CACHED */
                  )
                ])], 8, _hoisted_45)
              ],
              2
              /* CLASS */
            ),
            (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              null,
              vue.renderList($options.pagerSlots, (slot, index) => {
                return vue.openBlock(), vue.createElementBlock(
                  "li",
                  {
                    key: index,
                    class: vue.normalizeClass(["page-item", { active: slot === $data.page, disabled: slot === null }])
                  },
                  [
                    slot === null ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_46, "…")) : (vue.openBlock(), vue.createElementBlock("button", {
                      key: 1,
                      type: "button",
                      class: "page-link",
                      "aria-current": slot === $data.page ? "page" : null,
                      "aria-label": $options.pageLabel(slot),
                      onClick: ($event) => $options.goTo(slot)
                    }, vue.toDisplayString(slot), 9, _hoisted_47))
                  ],
                  2
                  /* CLASS */
                );
              }),
              128
              /* KEYED_FRAGMENT */
            )),
            vue.createElementVNode(
              "li",
              {
                class: vue.normalizeClass(["page-item", { disabled: $data.page >= $data.pages }])
              },
              [
                vue.createElementVNode("button", {
                  type: "button",
                  class: "page-link",
                  "aria-label": $options.texts.next,
                  disabled: $data.page >= $data.pages,
                  onClick: _cache[8] || (_cache[8] = ($event) => $options.goTo($data.page + 1))
                }, [..._cache[18] || (_cache[18] = [
                  vue.createElementVNode(
                    "i",
                    {
                      class: "ti ti-chevron-right",
                      "aria-hidden": "true"
                    },
                    null,
                    -1
                    /* CACHED */
                  )
                ])], 8, _hoisted_48)
              ],
              2
              /* CLASS */
            )
          ])
        ], 8, _hoisted_43)) : vue.createCommentVNode("v-if", true)
      ])) : vue.createCommentVNode("v-if", true),
      $options.hasChooser ? (vue.openBlock(), vue.createBlock(_component_UiModal, {
        key: 2,
        show: $data.chooserOpen,
        title: $options.texts.columns,
        "dialog-class": "data-table-columns",
        "onUpdate:show": _cache[12] || (_cache[12] = ($event) => $data.chooserOpen = $event)
      }, {
        footer: vue.withCtx(() => [
          $options.draftDiffersFromDefault ? (vue.openBlock(), vue.createElementBlock(
            "button",
            {
              key: 0,
              type: "button",
              class: "btn btn-link me-auto data-table-columns-reset",
              onClick: _cache[9] || (_cache[9] = (...args) => $options.resetDraft && $options.resetDraft(...args))
            },
            vue.toDisplayString($options.texts.reset),
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true),
          vue.createElementVNode(
            "button",
            {
              type: "button",
              class: "btn btn-light",
              onClick: _cache[10] || (_cache[10] = ($event) => $data.chooserOpen = false)
            },
            vue.toDisplayString($options.texts.cancel),
            1
            /* TEXT */
          ),
          vue.createElementVNode(
            "button",
            {
              type: "button",
              class: "btn btn-primary data-table-columns-save",
              onClick: _cache[11] || (_cache[11] = (...args) => $options.saveColumns && $options.saveColumns(...args))
            },
            vue.toDisplayString($options.texts.save),
            1
            /* TEXT */
          )
        ]),
        default: vue.withCtx(() => [
          $props.maxColumns ? (vue.openBlock(), vue.createElementBlock(
            "p",
            _hoisted_49,
            vue.toDisplayString($options.limitText),
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true),
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($options.optionalColumns, (column) => {
              return vue.openBlock(), vue.createElementBlock("div", {
                key: column.key,
                class: "form-check form-switch data-table-columns-option"
              }, [
                vue.createElementVNode("input", {
                  id: `${$props.tableId}-column-${column.key}`,
                  class: "form-check-input",
                  type: "checkbox",
                  role: "switch",
                  checked: $data.draftKeys.includes(column.key),
                  disabled: !$data.draftKeys.includes(column.key) && $options.atLimit,
                  onChange: ($event) => $options.toggleDraft(column.key, $event.target.checked)
                }, null, 40, _hoisted_50),
                vue.createElementVNode("label", {
                  class: "form-check-label",
                  for: `${$props.tableId}-column-${column.key}`
                }, vue.toDisplayString(column.label), 9, _hoisted_51),
                $data.draftKeys.includes(column.key) && $data.foldedKeys.includes(column.key) ? (vue.openBlock(), vue.createElementBlock(
                  "span",
                  _hoisted_52,
                  vue.toDisplayString($options.texts.hiddenAtWidth),
                  1
                  /* TEXT */
                )) : vue.createCommentVNode("v-if", true)
              ]);
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ]),
        _: 1
        /* STABLE */
      }, 8, ["show", "title"])) : vue.createCommentVNode("v-if", true)
    ], 8, _hoisted_1$j);
  }
  const C3 = /* @__PURE__ */ _export_sfc(_sfc_main$l, [["render", _sfc_render$l]]);
  const carriesFiles = (event) => {
    var _a;
    return Array.prototype.includes.call(((_a = event.dataTransfer) == null ? void 0 : _a.types) || [], "Files");
  };
  const _sfc_main$k = {
    name: "DropZone",
    props: {
      accept: { type: Boolean, default: true },
      label: { type: String, default: null },
      refusedLabel: { type: String, default: null }
    },
    emits: ["drop"],
    data() {
      return { depth: 0 };
    },
    computed: {
      active() {
        return this.depth > 0;
      },
      text() {
        return this.accept ? this.label || vue$1.i18n.t("base", "Drop files here to upload them") : this.refusedLabel || vue$1.i18n.t("base", "Files cannot be uploaded here");
      }
    },
    methods: {
      onEnter(event) {
        if (carriesFiles(event)) {
          this.depth++;
        }
      },
      onLeave(event) {
        if (!carriesFiles(event) || this.depth === 0) {
          return;
        }
        const to = event.relatedTarget;
        this.depth = to instanceof Node && !this.$el.contains(to) ? 0 : this.depth - 1;
      },
      onOver(event) {
        if (!carriesFiles(event)) {
          return;
        }
        event.preventDefault();
        if (event.dataTransfer) {
          event.dataTransfer.dropEffect = this.accept ? "copy" : "none";
        }
      },
      reset() {
        this.depth = 0;
      },
      onDrop(event) {
        if (!carriesFiles(event)) {
          return;
        }
        event.preventDefault();
        if (this.accept && event.dataTransfer.files.length) {
          this.$emit("drop", event.dataTransfer.files, event);
        }
      }
    }
  };
  const _hoisted_1$i = { class: "drop-zone-label" };
  function _sfc_render$k(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: "drop-zone",
        onDragenterCapture: _cache[0] || (_cache[0] = (...args) => $options.onEnter && $options.onEnter(...args)),
        onDragleaveCapture: _cache[1] || (_cache[1] = (...args) => $options.onLeave && $options.onLeave(...args)),
        onDragover: _cache[2] || (_cache[2] = (...args) => $options.onOver && $options.onOver(...args)),
        onDropCapture: _cache[3] || (_cache[3] = (...args) => $options.reset && $options.reset(...args)),
        onDrop: _cache[4] || (_cache[4] = (...args) => $options.onDrop && $options.onDrop(...args))
      },
      [
        vue.renderSlot(_ctx.$slots, "default"),
        vue.createVNode(vue.Transition, { name: "drop-zone-fade" }, {
          default: vue.withCtx(() => [
            $options.active ? (vue.openBlock(), vue.createElementBlock(
              "div",
              {
                key: 0,
                class: vue.normalizeClass(["drop-zone-overlay", { "is-refused": !$props.accept }]),
                "aria-hidden": "true"
              },
              [
                vue.createElementVNode("span", _hoisted_1$i, [
                  vue.createElementVNode(
                    "i",
                    {
                      class: vue.normalizeClass(["ti", $props.accept ? "ti-upload" : "ti-ban"])
                    },
                    null,
                    2
                    /* CLASS */
                  ),
                  vue.createTextVNode(
                    vue.toDisplayString($options.text),
                    1
                    /* TEXT */
                  )
                ])
              ],
              2
              /* CLASS */
            )) : vue.createCommentVNode("v-if", true)
          ]),
          _: 1
          /* STABLE */
        })
      ],
      32
      /* NEED_HYDRATION */
    );
  }
  const C4 = /* @__PURE__ */ _export_sfc(_sfc_main$k, [["render", _sfc_render$k]]);
  const _sfc_main$j = {
    name: "EntityGrid",
    components: { CardSkeleton: C0 },
    props: {
      items: { type: Array, required: true },
      itemKey: { type: String, default: "id" },
      loading: { type: Boolean, default: false },
      // The running request appends a further page (no dimming, see "States").
      loadingMore: { type: Boolean, default: false },
      error: { type: String, default: null },
      hasMore: { type: Boolean, default: false },
      skeletonCount: { type: Number, default: 12 },
      cardClass: { type: String, default: null },
      pageStarts: { type: Array, default: () => [0] }
    },
    emits: ["load-more", "retry"],
    computed: {
      emptyTitle() {
        return vue$1.i18n.t("base", "No results found!");
      },
      emptyHint() {
        return vue$1.i18n.t("base", "Try other keywords or remove filters.");
      },
      retryLabel() {
        return vue$1.i18n.t("base", "Try again");
      },
      moreLabel() {
        return vue$1.i18n.t("base", "Show more");
      },
      loadingLabel() {
        return vue$1.i18n.t("base", "Loading...");
      }
    },
    watch: {
      loading(isLoading, wasLoading) {
        if (wasLoading && !isLoading) {
          this.rearm();
        }
      }
    },
    mounted() {
      this.observe();
    },
    updated() {
      this.observe();
    },
    beforeUnmount() {
      if (this.observer) {
        this.observer.disconnect();
      }
    },
    methods: {
      staggerIndex(index) {
        let start = 0;
        for (const offset of this.pageStarts) {
          if (offset <= index && offset > start) {
            start = offset;
          }
        }
        return index - start;
      },
      observe() {
        if (typeof IntersectionObserver === "undefined") {
          return;
        }
        const sentinel = this.$refs.sentinel || null;
        if (sentinel === this.observed) {
          return;
        }
        if (this.observer) {
          this.observer.disconnect();
        }
        this.observed = sentinel;
        if (!sentinel) {
          return;
        }
        this.observer = this.observer || new IntersectionObserver((entries) => {
          if (entries.some((entry) => entry.isIntersecting) && this.hasMore && !this.loading && !this.error) {
            this.$emit("load-more");
          }
        }, { rootMargin: "400px" });
        this.observer.observe(sentinel);
      },
      rearm() {
        const sentinel = this.$refs.sentinel || null;
        if (this.observer && sentinel) {
          this.observer.unobserve(sentinel);
          this.observer.observe(sentinel);
        }
      }
    }
  };
  const _hoisted_1$h = ["aria-busy"];
  const _hoisted_2$c = ["data-id"];
  const _hoisted_3$b = {
    key: 1,
    class: "entity-grid-message entity-grid-message-error"
  };
  const _hoisted_4$a = {
    role: "alert",
    class: "entity-grid-message-text"
  };
  const _hoisted_5$6 = {
    key: 2,
    class: "entity-grid-message entity-grid-message-empty"
  };
  const _hoisted_6$6 = {
    role: "status",
    class: "entity-grid-message-text"
  };
  const _hoisted_7$4 = {
    key: 0,
    class: "entity-grid-error cards-error"
  };
  const _hoisted_8$4 = {
    role: "alert",
    class: "entity-grid-message-text"
  };
  const _hoisted_9$3 = {
    key: 1,
    ref: "sentinel",
    class: "entity-grid-more cards-more"
  };
  const _hoisted_10$3 = ["aria-label"];
  function _sfc_render$j(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_CardSkeleton = vue.resolveComponent("CardSkeleton");
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: vue.normalizeClass(["entity-grid", { "is-loading": $props.loading && !$props.loadingMore && $props.items.length > 0 }])
      },
      [
        vue.createElementVNode("div", {
          class: "entity-grid-cells",
          "aria-busy": $props.loading ? "true" : "false"
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($props.items, (item, index) => {
              return vue.openBlock(), vue.createElementBlock("div", {
                key: item[$props.itemKey],
                class: vue.normalizeClass(["entity-grid-cell", $props.cardClass]),
                style: vue.normalizeStyle({ "--card-stagger-index": $options.staggerIndex(index) }),
                "data-id": item[$props.itemKey]
              }, [
                vue.renderSlot(_ctx.$slots, "card", {
                  item,
                  index: $options.staggerIndex(index)
                })
              ], 14, _hoisted_2$c);
            }),
            128
            /* KEYED_FRAGMENT */
          )),
          !$props.items.length ? (vue.openBlock(), vue.createElementBlock(
            vue.Fragment,
            { key: 0 },
            [
              $props.loading ? (vue.openBlock(true), vue.createElementBlock(
                vue.Fragment,
                { key: 0 },
                vue.renderList($props.skeletonCount, (n) => {
                  return vue.openBlock(), vue.createElementBlock(
                    "div",
                    {
                      key: `skeleton-${n}`,
                      class: vue.normalizeClass(["entity-grid-cell entity-grid-cell-skeleton", $props.cardClass]),
                      style: vue.normalizeStyle({ "--card-stagger-index": n - 1 })
                    },
                    [
                      vue.renderSlot(_ctx.$slots, "skeleton", {
                        index: n - 1
                      }, () => [
                        vue.createVNode(_component_CardSkeleton)
                      ])
                    ],
                    6
                    /* CLASS, STYLE */
                  );
                }),
                128
                /* KEYED_FRAGMENT */
              )) : $props.error ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$b, [
                vue.createElementVNode(
                  "p",
                  _hoisted_4$a,
                  vue.toDisplayString($props.error),
                  1
                  /* TEXT */
                ),
                vue.createElementVNode(
                  "button",
                  {
                    type: "button",
                    class: "btn btn-light btn-sm",
                    onClick: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("retry"))
                  },
                  vue.toDisplayString($options.retryLabel),
                  1
                  /* TEXT */
                )
              ])) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$6, [
                vue.createElementVNode("p", _hoisted_6$6, [
                  vue.renderSlot(_ctx.$slots, "empty", {}, () => [
                    vue.createElementVNode(
                      "strong",
                      null,
                      vue.toDisplayString($options.emptyTitle),
                      1
                      /* TEXT */
                    ),
                    _cache[3] || (_cache[3] = vue.createElementVNode(
                      "br",
                      null,
                      null,
                      -1
                      /* CACHED */
                    )),
                    vue.createTextVNode(
                      vue.toDisplayString($options.emptyHint),
                      1
                      /* TEXT */
                    )
                  ])
                ])
              ]))
            ],
            64
            /* STABLE_FRAGMENT */
          )) : vue.createCommentVNode("v-if", true)
        ], 8, _hoisted_1$h),
        $props.error && $props.items.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_7$4, [
          vue.createElementVNode(
            "p",
            _hoisted_8$4,
            vue.toDisplayString($props.error),
            1
            /* TEXT */
          ),
          vue.createElementVNode(
            "button",
            {
              type: "button",
              class: "btn btn-light btn-sm",
              onClick: _cache[1] || (_cache[1] = ($event) => _ctx.$emit("retry"))
            },
            vue.toDisplayString($options.retryLabel),
            1
            /* TEXT */
          )
        ])) : $props.hasMore ? (vue.openBlock(), vue.createElementBlock(
          "div",
          _hoisted_9$3,
          [
            $props.loading ? (vue.openBlock(), vue.createElementBlock("span", {
              key: 0,
              class: "spinner-border spinner-border-sm",
              role: "status",
              "aria-label": $options.loadingLabel
            }, null, 8, _hoisted_10$3)) : (vue.openBlock(), vue.createElementBlock(
              "button",
              {
                key: 1,
                type: "button",
                class: "btn btn-light btn-sm",
                onClick: _cache[2] || (_cache[2] = ($event) => _ctx.$emit("load-more"))
              },
              vue.toDisplayString($options.moreLabel),
              1
              /* TEXT */
            ))
          ],
          512
          /* NEED_PATCH */
        )) : vue.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C7 = /* @__PURE__ */ _export_sfc(_sfc_main$j, [["render", _sfc_render$j]]);
  let uid$2 = 0;
  const _sfc_main$i = {
    name: "PageHeader",
    components: { HeaderActions },
    props: {
      title: { type: String, default: "" },
      titleTag: { type: String, default: "h1" },
      actions: { type: Array, default: () => [] },
      menu: { type: Array, default: () => [] },
      backUrl: { type: String, default: "" }
    },
    data() {
      return {
        titleId: `page-header-title-${++uid$2}`
      };
    },
    computed: {
      backLabel() {
        return vue$1.i18n.t("base", "Back");
      }
    }
  };
  const _hoisted_1$g = ["aria-labelledby"];
  const _hoisted_2$b = {
    key: 0,
    class: "page-header-bar"
  };
  const _hoisted_3$a = ["href", "aria-label", "title"];
  const _hoisted_4$9 = {
    key: 1,
    class: "page-header-body"
  };
  function _sfc_render$i(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_HeaderActions = vue.resolveComponent("HeaderActions");
    return vue.openBlock(), vue.createElementBlock("section", {
      class: "page-header",
      "aria-labelledby": $props.title ? $data.titleId : null
    }, [
      $props.title || $props.actions.length || $props.menu.length || _ctx.$slots.actions ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$b, [
        $props.backUrl ? (vue.openBlock(), vue.createElementBlock("a", {
          key: 0,
          href: $props.backUrl,
          class: "page-header-back btn btn-ghost btn-icon-only",
          "aria-label": $options.backLabel,
          title: $options.backLabel
        }, [..._cache[0] || (_cache[0] = [
          vue.createElementVNode(
            "i",
            {
              class: "ti ti-arrow-left",
              "aria-hidden": "true"
            },
            null,
            -1
            /* CACHED */
          )
        ])], 8, _hoisted_3$a)) : vue.createCommentVNode("v-if", true),
        $props.title ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent($props.titleTag), {
          key: 1,
          id: $data.titleId,
          class: "page-header-title"
        }, {
          default: vue.withCtx(() => [
            vue.createTextVNode(
              vue.toDisplayString($props.title),
              1
              /* TEXT */
            )
          ]),
          _: 1
          /* STABLE */
        }, 8, ["id"])) : vue.createCommentVNode("v-if", true),
        $props.actions.length || $props.menu.length || _ctx.$slots.actions ? (vue.openBlock(), vue.createBlock(_component_HeaderActions, {
          key: 2,
          actions: $props.actions,
          menu: $props.menu,
          "root-class": "page-header-actions"
        }, {
          default: vue.withCtx(() => [
            vue.renderSlot(_ctx.$slots, "actions")
          ]),
          _: 3
          /* FORWARDED */
        }, 8, ["actions", "menu"])) : vue.createCommentVNode("v-if", true)
      ])) : vue.createCommentVNode("v-if", true),
      _ctx.$slots.default ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$9, [
        vue.renderSlot(_ctx.$slots, "default")
      ])) : vue.createCommentVNode("v-if", true)
    ], 8, _hoisted_1$g);
  }
  const C14 = /* @__PURE__ */ _export_sfc(_sfc_main$i, [["render", _sfc_render$i]]);
  const _sfc_main$h = {
    name: "EntityBrowser",
    components: { EntityGrid: C7, FilterBar: C9, PageHeader: C14 },
    props: {
      url: { type: String, required: true },
      title: { type: String, default: "" },
      titleTag: { type: String, default: "h1" },
      filters: { type: Array, default: () => [] },
      pageSize: { type: Number, default: 24 },
      skeletonCount: { type: Number, default: 12 },
      cardClass: { type: String, default: void 0 },
      itemKey: { type: String, default: "id" },
      metaKeys: { type: Array, default: () => [] },
      syncUrl: { type: Boolean, default: true },
      idPrefix: { type: String, default: "filter" },
      /**
       * The page header's actions as data, handed to `PageHeader`.
       * @since 1.20
       */
      actions: { type: Array, default: () => [] },
      /**
       * `(ids) => Promise<{ [id]: state }>`, called once per loaded page (see "Item states").
       * @since 1.20
       */
      itemStates: { type: Function, default: null },
      /**
       * Values sent with every request, not rendered, not URL-synced (see `FilterBar`).
       * @since 1.20
       */
      fixed: { type: Object, default: () => ({}) }
    },
    emits: ["loaded"],
    data() {
      return {
        // The applied filter values - the FilterBar's `v-model`, which it sets (from the page
        // URL) while it is created, before this component's first fetch in `mounted()`.
        values: defaultValues(this.filters),
        items: [],
        page: 0,
        pages: 0,
        total: 0,
        meta: {},
        // The first page is requested in `mounted()`; loading from the first render on shows the
        // skeletons at once.
        loading: true,
        // The running request is a further page, appended to the shown cards.
        loadingMore: false,
        error: null,
        failedPage: null,
        pageStarts: [0],
        // The item states by item key (see "Item states"), reset with every page 1.
        states: {}
      };
    },
    computed: {
      hasMore() {
        return this.page < this.pages;
      }
    },
    watch: {
      fixed: {
        deep: true,
        handler(value) {
          const signature = fixedSignature(value);
          if (signature === this.fixedSignature) {
            return;
          }
          this.fixedSignature = signature;
          if (this.started && !this.$refs.filterBar) {
            this.fetch(1);
          }
        }
      }
    },
    created() {
      this.requestSeq = 0;
      this.listSeq = 0;
      this.started = false;
      this.fixedSignature = fixedSignature(this.fixed);
    },
    mounted() {
      this.started = true;
      this.fetch(1);
    },
    beforeUnmount() {
      this.requestSeq++;
      this.listSeq++;
    },
    methods: {
      reload() {
        return this.fetch(1);
      },
      loadMore() {
        if (!this.hasMore || this.loading) {
          return Promise.resolve();
        }
        return this.fetch(this.page + 1);
      },
      retry() {
        return this.fetch(this.failedPage || 1);
      },
      replaceItem(id, item) {
        const index = this.items.findIndex((candidate) => candidate[this.itemKey] === id);
        if (index !== -1) {
          this.items.splice(index, 1, item);
        }
      },
      /**
       * Merges `patch` into the state of the item keyed `id` (shallow).
       * @since 1.20
       */
      replaceState(id, patch) {
        this.states[id] = { ...this.states[id] || {}, ...patch };
      },
      setFilter(key, value, options = {}) {
        return this.$refs.filterBar ? this.$refs.filterBar.setFilter(key, value, options) : false;
      },
      reloadFilterOptions() {
        var _a;
        (_a = this.$refs.filterBar) == null ? void 0 : _a.reloadOptions();
      },
      // `$slots` is a plain object the render function replaces on every render, not a
      // reactive one a computed can track — called from the template instead, so it is
      // re-evaluated with every render instead of caching a value that never invalidates.
      filterSlotNames() {
        return Object.keys(this.$slots).filter((name) => name.startsWith("filter-"));
      },
      onFilterChange(values) {
        this.values = values;
        if (this.started) {
          this.fetch(1);
        }
      },
      fetch(page) {
        const seq = ++this.requestSeq;
        this.loading = true;
        this.loadingMore = page > 1;
        this.error = null;
        const query = new URLSearchParams({
          ...requestParams(this.filters, this.values, this.fixed),
          page: String(page),
          pageSize: String(this.pageSize)
        }).toString();
        const url = this.url + (this.url.includes("?") ? "&" : "?") + query;
        return vue$1.client.get(url).then((response) => {
          if (seq !== this.requestSeq) {
            return;
          }
          const results = response.results || [];
          if (page === 1) {
            this.listSeq++;
            this.states = {};
          }
          this.pageStarts = page === 1 ? [0] : [...this.pageStarts, this.items.length];
          this.items = page === 1 ? results : [...this.items, ...results];
          this.page = response.page || page;
          this.pages = response.pages || 0;
          this.total = response.total || 0;
          this.meta = Object.fromEntries(this.metaKeys.map((key) => [key, response[key]]));
          this.loading = false;
          this.failedPage = null;
          this.$emit("loaded", { total: this.total, meta: this.meta, values: { ...this.values } });
          this.fetchStates(results.map((item) => item[this.itemKey]));
        }).catch((response) => {
          if (seq !== this.requestSeq) {
            return;
          }
          this.loading = false;
          this.failedPage = page;
          if (page === 1) {
            this.listSeq++;
            this.states = {};
            this.items = [];
            this.pageStarts = [0];
            this.page = 0;
            this.pages = 0;
            this.total = 0;
            this.meta = {};
          }
          this.error = response && typeof response.message === "string" && response.message !== "" ? response.message : vue$1.i18n.t("base", "The list could not be loaded.");
          vue$1.log.error(response);
        });
      },
      fetchStates(ids) {
        if (!this.itemStates || !ids.length) {
          return;
        }
        const seq = this.listSeq;
        const apply = (answer) => {
          if (seq !== this.listSeq) {
            return;
          }
          const states = { ...this.states };
          ids.forEach((id) => {
            const answered = answer && answer[id] !== void 0 ? answer[id] : null;
            states[id] = states[id] !== void 0 ? { ...answered || {}, ...states[id] } : answered;
          });
          this.states = states;
        };
        Promise.resolve().then(() => this.itemStates(ids)).then(apply, (error) => {
          if (seq === this.listSeq) {
            vue$1.log.error(error);
          }
          apply(null);
        });
      }
    }
  };
  const _hoisted_1$f = { class: "entity-browser" };
  const _hoisted_2$a = {
    key: 0,
    class: "entity-browser-notice"
  };
  function _sfc_render$h(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FilterBar = vue.resolveComponent("FilterBar");
    const _component_PageHeader = vue.resolveComponent("PageHeader");
    const _component_EntityGrid = vue.resolveComponent("EntityGrid");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$f, [
      vue.createVNode(_component_PageHeader, {
        title: $props.title,
        "title-tag": $props.titleTag,
        actions: $props.actions
      }, vue.createSlots({
        default: vue.withCtx(() => [
          $props.filters.length ? (vue.openBlock(), vue.createBlock(_component_FilterBar, {
            key: 0,
            ref: "filterBar",
            "model-value": $data.values,
            filters: $props.filters,
            "sync-url": $props.syncUrl,
            "id-prefix": $props.idPrefix,
            fixed: $props.fixed,
            "onUpdate:modelValue": $options.onFilterChange
          }, vue.createSlots({
            _: 2
            /* DYNAMIC */
          }, [
            vue.renderList($options.filterSlotNames(), (name) => {
              return {
                name,
                fn: vue.withCtx((scope) => [
                  vue.renderSlot(_ctx.$slots, name, vue.normalizeProps(vue.guardReactiveProps(scope)))
                ])
              };
            })
          ]), 1032, ["model-value", "filters", "sync-url", "id-prefix", "fixed", "onUpdate:modelValue"])) : vue.createCommentVNode("v-if", true)
        ]),
        _: 2
        /* DYNAMIC */
      }, [
        _ctx.$slots.actions ? {
          name: "actions",
          fn: vue.withCtx(() => [
            vue.renderSlot(_ctx.$slots, "actions", {
              meta: $data.meta,
              total: $data.total
            })
          ]),
          key: "0"
        } : void 0
      ]), 1032, ["title", "title-tag", "actions"]),
      _ctx.$slots.notice ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$a, [
        vue.renderSlot(_ctx.$slots, "notice", {
          meta: $data.meta,
          total: $data.total
        })
      ])) : vue.createCommentVNode("v-if", true),
      vue.createVNode(_component_EntityGrid, {
        items: $data.items,
        "item-key": $props.itemKey,
        loading: $data.loading,
        "loading-more": $data.loadingMore,
        error: $data.error,
        "has-more": $options.hasMore,
        "skeleton-count": $props.skeletonCount,
        "card-class": $props.cardClass,
        "page-starts": $data.pageStarts,
        onLoadMore: $options.loadMore,
        onRetry: $options.retry
      }, vue.createSlots({
        card: vue.withCtx(({ item, index }) => [
          vue.renderSlot(_ctx.$slots, "card", {
            item,
            index,
            state: $data.states[item[$props.itemKey]]
          })
        ]),
        _: 2
        /* DYNAMIC */
      }, [
        _ctx.$slots.skeleton ? {
          name: "skeleton",
          fn: vue.withCtx(({ index }) => [
            vue.renderSlot(_ctx.$slots, "skeleton", { index })
          ]),
          key: "0"
        } : void 0,
        _ctx.$slots.empty ? {
          name: "empty",
          fn: vue.withCtx(() => [
            vue.renderSlot(_ctx.$slots, "empty")
          ]),
          key: "1"
        } : void 0
      ]), 1032, ["items", "item-key", "loading", "loading-more", "error", "has-more", "skeleton-count", "card-class", "page-starts", "onLoadMore", "onRetry"])
    ]);
  }
  const C6 = /* @__PURE__ */ _export_sfc(_sfc_main$h, [["render", _sfc_render$h]]);
  const _sfc_main$g = {
    name: "ExtensionSlot",
    props: {
      name: { type: String, required: true },
      context: { type: Object, default: () => ({}) }
    },
    computed: {
      visibleEntries() {
        return vue$1.getSlotComponents(this.name).filter((entry) => vue$1.isRegistered(entry.component));
      }
    }
  };
  function _sfc_render$g(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(true), vue.createElementBlock(
      vue.Fragment,
      null,
      vue.renderList($options.visibleEntries, (entry) => {
        return vue.openBlock(), vue.createBlock(
          vue.resolveDynamicComponent(entry.component),
          vue.mergeProps({
            key: entry.id
          }, { ref_for: true }, $props.context),
          null,
          16
          /* FULL_PROPS */
        );
      }),
      128
      /* KEYED_FRAGMENT */
    );
  }
  const C8 = /* @__PURE__ */ _export_sfc(_sfc_main$g, [["render", _sfc_render$g]]);
  const _sfc_main$f = {
    props: {
      modelName: { type: String, default: "" },
      busy: { type: Boolean, default: false }
    },
    emits: ["submit"],
    data() {
      return {
        // Mutated in place, never reassigned — see the class docblock's
        // "Reactivity note" section.
        errors: {}
      };
    },
    provide() {
      return {
        [FORM_CONTEXT_KEY]: {
          modelName: vue.computed(() => this.modelName),
          busy: vue.computed(() => this.busy),
          errors: vue.computed(() => this.errors),
          clearError: this.clearError,
          registerField: this.registerField,
          unregisterField: this.unregisterField
        }
      };
    },
    created() {
      this._fields = [];
    },
    computed: {
      // Messages for attributes that currently have an error but no registered field to
      // show it on — see the class docblock's "Form-level fallback for unowned errors"
      // section. Excludes every attribute `this._fields` (registerField()/unregisterField(),
      // see `form/fieldMixin.js`) knows about, so a field's own inline `invalid-feedback`
      // never gets a duplicate here.
      unownedErrorMessages() {
        const ownedAttributes = new Set(this._fields.map((field) => field.attribute));
        const messages = [];
        Object.keys(this.errors).forEach((attribute) => {
          if (ownedAttributes.has(attribute)) {
            return;
          }
          const attributeMessages = this.errors[attribute];
          if (Array.isArray(attributeMessages)) {
            messages.push(...attributeMessages);
          }
        });
        return messages;
      }
    },
    methods: {
      onSubmit() {
        this.$emit("submit");
      },
      setErrors(payload) {
        const source = payload || {};
        let unwrapped = source;
        if (source.errors && typeof source.errors === "object") {
          unwrapped = source.errors;
        } else if (source.error && source.error.errors && typeof source.error.errors === "object") {
          unwrapped = source.error.errors;
        }
        this.clearErrors();
        if (!unwrapped || typeof unwrapped !== "object" || Array.isArray(unwrapped)) {
          return;
        }
        Object.assign(this.errors, unwrapped);
      },
      clearErrors() {
        Object.keys(this.errors).forEach((attribute) => {
          delete this.errors[attribute];
        });
      },
      clearError(attribute) {
        if (Object.prototype.hasOwnProperty.call(this.errors, attribute)) {
          delete this.errors[attribute];
        }
      },
      registerField(attribute, instance) {
        this._fields.push({ attribute, instance });
      },
      unregisterField(attribute, instance) {
        const index = this._fields.findIndex((entry) => entry.instance === instance);
        if (index !== -1) {
          this._fields.splice(index, 1);
        }
      },
      /**
       * Focuses this form's first focusable field — what a form opened in a dialog wants on
       * the modal's `opened`, so the user can start typing without reaching for the mouse.
       *
       * Fields register on mount, so "first" is the first one in the template, which is the
       * first one the user sees. A field without a `focus()` method is skipped rather than
       * ending the search.
       */
      focusFirstField() {
        const entry = this._fields.find((field) => typeof field.instance.focus === "function");
        if (entry) {
          entry.instance.focus();
        }
      },
      focusFirstError() {
        const entry = this._fields.find((field) => {
          const messages = this.errors[field.attribute];
          return Array.isArray(messages) && messages.length > 0;
        });
        if (entry && typeof entry.instance.focus === "function") {
          entry.instance.focus();
        }
      }
    }
  };
  const _hoisted_1$e = {
    key: 0,
    class: "alert alert-danger error-summary"
  };
  function _sfc_render$f(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "form",
      {
        onSubmit: _cache[0] || (_cache[0] = vue.withModifiers((...args) => $options.onSubmit && $options.onSubmit(...args), ["prevent"]))
      },
      [
        $options.unownedErrorMessages.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$e, [
          vue.createElementVNode("ul", null, [
            (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              null,
              vue.renderList($options.unownedErrorMessages, (message, index) => {
                return vue.openBlock(), vue.createElementBlock(
                  "li",
                  { key: index },
                  vue.toDisplayString(message),
                  1
                  /* TEXT */
                );
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ])
        ])) : vue.createCommentVNode("v-if", true),
        vue.renderSlot(_ctx.$slots, "default")
      ],
      32
      /* NEED_HYDRATION */
    );
  }
  const C12 = /* @__PURE__ */ _export_sfc(_sfc_main$f, [["render", _sfc_render$f]]);
  const FORM_TOKEN = "__VUEFORM__";
  const RICHTEXT_SELECTOR = '[data-ui-widget="ui.richtext.prosemirror.RichTextEditor"]';
  const RICHTEXT_COMPONENT_DATA = "humhub-ui-richtexteditor";
  let instanceCounter = 0;
  const _sfc_main$e = {
    props: {
      shellHtml: { type: String, required: true },
      // Deterministic identity for this instance's DOM ids — see the class
      // docblock's "Unique-id contract" section for the uniqueness/stability
      // contract a caller-supplied key must satisfy (and why callers whose
      // shell hosts a backup-enabled richtext editor must pass one).
      instanceKey: { type: String, default: null }
    },
    data() {
      return {
        // From instanceKey when given (stable across page loads); from the
        // module-scope counter otherwise (unique per page load only — and a
        // counter, not Math.random(), so builds/output stay deterministic).
        instanceId: this.instanceKey ? "vueform-" + this.instanceKey.replace(/[^A-Za-z0-9_-]/g, "-") : "vueform-" + ++instanceCounter
      };
    },
    computed: {
      processedShell() {
        return this.shellHtml.split(FORM_TOKEN).join(this.instanceId);
      },
      // Cheap proxy for "the parsed shell is supposed to contain a <form>" — see the
      // class docblock's "Nested <form> via v-html" section. Tested against the RAW
      // prop rather than `processedShell` since the token substitution never touches
      // the tag itself.
      expectsForm() {
        return /<form[\s>]/i.test(this.shellHtml);
      }
    },
    mounted() {
      this.checkFormPresence();
    },
    updated() {
      this.checkFormPresence();
    },
    methods: {
      /**
       * See the class docblock's "Nested <form> via v-html" section — logs a clear,
       * loud error instead of letting a dropped inner `<form>` fail silently the next
       * time `resetAcknowledge()`/`getFileGuids()` (or `onSubmit`'s own native
       * `'submit'` listener in `CommentForm.vue`) quietly finds nothing to act on.
       */
      checkFormPresence() {
        if (this.expectsForm && !this.$el.querySelector("form")) {
          vue$1.log.error(
            `LegacyFormWrapper: the rendered shell was expected to contain a <form> (the shellHtml prop has one) but none was found in the DOM — the browser's HTML fragment parser may have silently dropped it because this component's root was already attached to the document when its markup was (re-)parsed; see this component's own docblock, "Nested <form> via v-html".`
          );
        }
      },
      getEditorInstance() {
        const node = this.$el.querySelector(RICHTEXT_SELECTOR);
        return node ? jQuery(node).data(RICHTEXT_COMPONENT_DATA) : null;
      },
      /** @returns {string} the current markdown value of the richtext editor. */
      getValue() {
        const editor = this.getEditorInstance();
        return editor ? editor.editor.serialize() : "";
      },
      /** Prefills the editor with markdown (e.g. for edit mode). */
      setValue(markdown) {
        const editor = this.getEditorInstance();
        if (editor) {
          editor.editor.init(markdown || "");
        }
      },
      /** Empties the editor. */
      clear() {
        const editor = this.getEditorInstance();
        if (editor) {
          editor.$.trigger("clear");
        }
        this.resetAcknowledge();
      },
      /**
       * Neutralizes humhub.client.js's acknowledgeForm unsaved-changes baseline for this
       * instance's `<form>` - see the class docblock's "Unsaved-changes guard" section.
       * `.data('state')` is the exact (and only) thing `resetChanges()` itself touches;
       * writing `null` through the same public jQuery `.data()` store makes
       * `formStateChanged()` short-circuit to "unchanged" on its very next check,
       * regardless of what the form's serialized content actually looks like.
       */
      resetAcknowledge() {
        const form = this.$el.querySelector("form");
        if (form) {
          jQuery(form).data("state", null);
        }
      },
      /** Focuses the richtext editor (e.g. on reply). */
      focus() {
        const editor = this.getEditorInstance();
        if (editor) {
          editor.focus();
        }
      }
    }
  };
  const _hoisted_1$d = ["innerHTML"];
  function _sfc_render$e(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", { innerHTML: $options.processedShell }, null, 8, _hoisted_1$d)), [
      [_directive_additions]
    ]);
  }
  const C13 = /* @__PURE__ */ _export_sfc(_sfc_main$e, [["render", _sfc_render$e]]);
  const plainClick = (event) => !(event.button > 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey);
  const _sfc_main$d = {
    name: "PathBar",
    props: {
      path: { type: Array, default: () => [] },
      rootLabel: { type: String, required: true },
      rootUrl: { type: String, required: true },
      canDrop: { type: Function, default: () => false },
      // No `type`: `dropTargetId` must keep `null` (the root) distinct from `undefined` (no
      // target, the default) - `type: [Number, String, null]` trips Vue's prop validator (it
      // treats `null` as "no type restriction" only when it is the *sole* type, not inside an
      // array) and can warn or coerce `null` back to the default.
      dropTargetId: { default: void 0 }
    },
    emits: ["navigate", "drag-over", "drag-leave", "drop-on"],
    computed: {
      parentId() {
        return this.path.length > 1 ? this.path[this.path.length - 2].id : null;
      },
      backLabel() {
        return vue$1.i18n.t("base", "Back");
      },
      navLabel() {
        return vue$1.i18n.t("base", "Breadcrumb");
      }
    },
    methods: {
      focusCurrent() {
        var _a;
        const target = this.path.length ? this.$refs.current : this.$refs.root;
        (_a = Array.isArray(target) ? target[0] : target) == null ? void 0 : _a.focus();
      },
      follow(id, event) {
        if (!plainClick(event)) {
          return;
        }
        event.preventDefault();
        this.$emit("navigate", id);
      },
      targetClass(id) {
        return { "is-drop-target": this.dropTargetId !== void 0 && this.dropTargetId === id };
      },
      dropHandlers(id) {
        return {
          dragover: (event) => {
            if (this.canDrop(id, event)) {
              event.preventDefault();
              this.$emit("drag-over", id, event);
            }
          },
          dragleave: (event) => {
            if (!event.currentTarget.contains(event.relatedTarget)) {
              this.$emit("drag-leave", id, event);
            }
          },
          drop: (event) => {
            if (!this.canDrop(id, event)) {
              return;
            }
            event.preventDefault();
            event.stopPropagation();
            this.$emit("drop-on", id, event);
          }
        };
      }
    }
  };
  const _hoisted_1$c = { class: "path-bar" };
  const _hoisted_2$9 = ["aria-label", "title"];
  const _hoisted_3$9 = ["aria-label"];
  const _hoisted_4$8 = { class: "breadcrumb" };
  const _hoisted_5$5 = ["href", "aria-label", "title", "aria-current"];
  const _hoisted_6$5 = ["aria-current"];
  const _hoisted_7$3 = ["href", "title", "onClick"];
  const _hoisted_8$3 = ["title"];
  function _sfc_render$d(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$c, [
      $props.path.length ? (vue.openBlock(), vue.createElementBlock("button", {
        key: 0,
        type: "button",
        class: "btn btn-ghost btn-icon-only path-bar-back",
        "aria-label": $options.backLabel,
        title: $options.backLabel,
        onClick: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("navigate", $options.parentId))
      }, [..._cache[2] || (_cache[2] = [
        vue.createElementVNode(
          "i",
          {
            class: "ti ti-arrow-left",
            "aria-hidden": "true"
          },
          null,
          -1
          /* CACHED */
        )
      ])], 8, _hoisted_2$9)) : vue.createCommentVNode("v-if", true),
      vue.createElementVNode("nav", {
        class: "path-bar-nav",
        "aria-label": $options.navLabel
      }, [
        vue.createElementVNode("ol", _hoisted_4$8, [
          vue.createElementVNode(
            "li",
            vue.mergeProps({
              class: ["breadcrumb-item", [$options.targetClass(null), { active: !$props.path.length }]]
            }, vue.toHandlers($props.path.length ? $options.dropHandlers(null) : {}, true)),
            [
              vue.createElementVNode("a", {
                ref: "root",
                href: $props.rootUrl,
                class: "path-bar-root",
                "aria-label": $props.rootLabel,
                title: $props.rootLabel,
                "aria-current": $props.path.length ? null : "page",
                onClick: _cache[1] || (_cache[1] = ($event) => $options.follow(null, $event))
              }, [..._cache[3] || (_cache[3] = [
                vue.createElementVNode(
                  "i",
                  {
                    class: "ti ti-folders",
                    "aria-hidden": "true"
                  },
                  null,
                  -1
                  /* CACHED */
                )
              ])], 8, _hoisted_5$5)
            ],
            16
            /* FULL_PROPS */
          ),
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($props.path, (crumb, index) => {
              return vue.openBlock(), vue.createElementBlock("li", vue.mergeProps({
                key: crumb.id,
                class: ["breadcrumb-item", [$options.targetClass(crumb.id), { active: index === $props.path.length - 1 }]],
                "aria-current": index === $props.path.length - 1 ? "page" : null
              }, vue.toHandlers(index < $props.path.length - 1 ? $options.dropHandlers(crumb.id) : {}, true)), [
                index < $props.path.length - 1 ? (vue.openBlock(), vue.createElementBlock("a", {
                  key: 0,
                  href: crumb.url,
                  title: crumb.title,
                  onClick: ($event) => $options.follow(crumb.id, $event)
                }, vue.toDisplayString(crumb.title), 9, _hoisted_7$3)) : (vue.openBlock(), vue.createElementBlock("span", {
                  key: 1,
                  ref_for: true,
                  ref: "current",
                  tabindex: "-1",
                  title: crumb.title
                }, vue.toDisplayString(crumb.title), 9, _hoisted_8$3))
              ], 16, _hoisted_6$5);
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ])
      ], 8, _hoisted_3$9),
      vue.renderSlot(_ctx.$slots, "end")
    ]);
  }
  const C15 = /* @__PURE__ */ _export_sfc(_sfc_main$d, [["render", _sfc_render$d]]);
  const SEARCH_DEBOUNCE_MS = TEXT_DEBOUNCE_MS;
  const MAX_SUGGESTIONS = 8;
  let uid$1 = 0;
  const idOf = (value) => value === null || value === void 0 ? "" : String(value);
  const ID_PATTERN = /^[1-9]\d*$/;
  const _sfc_main$c = {
    name: "PickerFilterControl",
    props: {
      filter: { type: Object, required: true },
      modelValue: { type: [String, Number, Array], default: "" },
      inputId: { type: String, default: null },
      multiple: { type: Boolean, default: false },
      search: { type: Function, required: true },
      resolve: { type: Function, required: true },
      itemLabel: { type: Function, required: true },
      itemTitle: { type: Function, default: null },
      icon: { type: String, default: null },
      block: { type: String, required: true },
      keepPlaceholder: { type: Boolean, default: false }
    },
    emits: ["update:modelValue"],
    data() {
      return {
        query: "",
        open: false,
        activeIndex: -1,
        results: [],
        searching: false,
        resolving: false,
        // Items seen among the suggestions or resolved, by id: a value of them needs no request.
        known: {},
        listboxId: `picker-filter-${++uid$1}-listbox`
      };
    },
    computed: {
      ids() {
        const value = this.modelValue;
        if (Array.isArray(value)) {
          return value.map(idOf).filter((id2) => id2 !== "");
        }
        const id = idOf(value);
        if (id === "") {
          return [];
        }
        return this.multiple ? id.split(",").filter((part) => part !== "") : [id];
      },
      // The comparable form of the value, watched instead of the (new with every change) array.
      valueKey() {
        return this.ids.join(",");
      },
      hasValue() {
        return this.ids.length > 0;
      },
      chips() {
        return this.ids.map((id) => ({ id, item: this.known[id] || null })).filter((chip) => chip.item || !this.resolving).map((chip) => ({ ...chip, label: chip.item ? this.itemLabel(chip.item) : chip.id }));
      },
      term() {
        return this.query.trim();
      },
      placeholder() {
        return this.filter.placeholder || this.filter.label || "";
      },
      // With chips the placeholder stays: the field keeps room for it (`.keeps-placeholder`).
      keepsPlaceholder() {
        return this.keepPlaceholder && this.multiple && this.hasValue && this.placeholder !== "";
      },
      accessibleName() {
        return this.filter.label || this.filter.placeholder || null;
      },
      activeDescendant() {
        return this.open && this.activeIndex >= 0 && this.activeIndex < this.results.length ? this.optionId(this.activeIndex) : null;
      },
      feedback() {
        if (this.results.length) {
          return null;
        }
        return this.searching ? vue$1.i18n.t("base", "Loading...") : vue$1.i18n.t("base", "No results found!");
      }
    },
    watch: {
      valueKey() {
        this.show();
        if (this.pendingFocus) {
          this.pendingFocus = false;
          this.$nextTick(() => {
            var _a;
            return (_a = this.$refs.input || this.$refs.remove) == null ? void 0 : _a.focus();
          });
        }
      }
    },
    created() {
      this.searchSeq = 0;
      this.resolveSeq = 0;
      this.searchTimer = null;
      this.pendingFocus = false;
      this.show();
    },
    beforeUnmount() {
      clearTimeout(this.searchTimer);
      this.searchSeq++;
      this.resolveSeq++;
      this.unbindOutside();
    },
    methods: {
      idOf,
      optionId(index) {
        return `${this.listboxId}-${index}`;
      },
      chipTitle(chip) {
        return chip.item && this.itemTitle && this.itemTitle(chip.item) || null;
      },
      removeLabel(label) {
        return vue$1.i18n.t("base", "Remove {label}", { label });
      },
      emitIds(ids) {
        this.$emit("update:modelValue", this.multiple ? ids : ids[0] ?? "");
      },
      // The items a (new) value names: known, resolved, or dropped.
      show() {
        this.resolveSeq++;
        this.resolving = false;
        const ids = this.ids;
        const valid = ids.filter((id) => ID_PATTERN.test(id));
        if (valid.length !== ids.length) {
          this.emitIds(valid);
          return;
        }
        const unknown = ids.filter((id) => !this.known[id]);
        if (unknown.length) {
          this.resolveIds(ids, unknown);
        }
      },
      resolveIds(ids, unknown) {
        const seq = this.resolveSeq;
        this.resolving = true;
        this.resolve(unknown).then((items) => {
          if (seq !== this.resolveSeq) {
            return;
          }
          (items || []).forEach((item) => this.known[idOf(item.id)] = item);
          const missing = unknown.filter((id) => !this.known[id]);
          if (missing.length) {
            this.emitIds(ids.filter((id) => !missing.includes(id)));
          }
        }).catch((response) => {
          if (seq === this.resolveSeq) {
            vue$1.log.error(response);
          }
        }).finally(() => {
          if (seq === this.resolveSeq) {
            this.resolving = false;
          }
        });
      },
      onFieldClick() {
        var _a;
        if (!this.resolving) {
          (_a = this.$refs.input) == null ? void 0 : _a.focus();
        }
      },
      onInput(event) {
        this.query = event.target.value;
        this.runSearch();
      },
      runSearch() {
        clearTimeout(this.searchTimer);
        const seq = ++this.searchSeq;
        const term = this.term;
        if (term === "") {
          this.searching = false;
          this.results = [];
          this.close();
          return;
        }
        this.searching = true;
        this.searchTimer = setTimeout(() => this.load(term, seq), SEARCH_DEBOUNCE_MS);
      },
      load(term, seq) {
        this.search(term, MAX_SUGGESTIONS).then((items) => {
          if (seq !== this.searchSeq) {
            return;
          }
          const chosen = this.multiple ? this.ids : [];
          this.results = (items || []).filter((item) => !chosen.includes(idOf(item.id)));
          this.results.forEach((item) => this.known[idOf(item.id)] = item);
          this.activeIndex = this.results.length ? 0 : -1;
        }).catch((response) => {
          if (seq !== this.searchSeq) {
            return;
          }
          vue$1.log.error(response);
          this.results = [];
          this.activeIndex = -1;
        }).finally(() => {
          if (seq === this.searchSeq) {
            this.searching = false;
            this.openList();
          }
        });
      },
      openList() {
        if (this.open || this.term === "") {
          return;
        }
        this.open = true;
        this.activeIndex = this.results.length ? 0 : -1;
        this.bindOutside();
      },
      close() {
        if (!this.open) {
          return;
        }
        this.open = false;
        this.activeIndex = -1;
        this.unbindOutside();
      },
      choose(item) {
        clearTimeout(this.searchTimer);
        this.searchSeq++;
        this.searching = false;
        const id = idOf(item.id);
        this.known[id] = item;
        this.query = "";
        this.results = [];
        this.close();
        const ids = this.multiple ? this.ids.includes(id) ? this.ids : [...this.ids, id] : [id];
        this.pendingFocus = ids.join(",") !== this.valueKey;
        this.emitIds(ids);
      },
      remove(id) {
        this.pendingFocus = true;
        this.emitIds(this.ids.filter((entry) => entry !== id));
      },
      moveTo(index) {
        if (!this.results.length) {
          return;
        }
        this.activeIndex = Math.max(0, Math.min(index, this.results.length - 1));
        this.$nextTick(() => this.scrollActiveIntoView());
      },
      onKeydown(event) {
        const last = this.results.length - 1;
        switch (event.key) {
          case "ArrowDown":
          case "ArrowUp":
            event.preventDefault();
            if (!this.open) {
              this.openList();
              if (event.key === "ArrowUp") {
                this.moveTo(last);
              }
            } else {
              this.moveTo(this.activeIndex + (event.key === "ArrowDown" ? 1 : -1));
            }
            break;
          case "Home":
          case "End":
            if (this.open && this.results.length) {
              event.preventDefault();
              this.moveTo(event.key === "Home" ? 0 : last);
            }
            break;
          case "Enter":
            if (this.open) {
              event.preventDefault();
              const active = this.results[this.activeIndex];
              if (active) {
                this.choose(active);
              }
            }
            break;
          case "Escape":
            if (this.open || this.query !== "") {
              event.preventDefault();
              event.stopPropagation();
              if (this.open) {
                this.close();
              } else {
                this.query = "";
                this.runSearch();
              }
            }
            break;
          case "Tab":
            this.close();
            break;
        }
      },
      scrollActiveIntoView() {
        var _a;
        const node = this.activeIndex >= 0 ? (_a = this.$refs.listbox) == null ? void 0 : _a.children[this.activeIndex] : null;
        if (node && typeof node.scrollIntoView === "function") {
          node.scrollIntoView({ block: "nearest" });
        }
      },
      bindOutside() {
        if (this.outsideHandler) {
          return;
        }
        this.outsideHandler = (event) => {
          if (this.$refs.root && !this.$refs.root.contains(event.target)) {
            this.close();
          }
        };
        document.addEventListener("pointerdown", this.outsideHandler, true);
        document.addEventListener("focusin", this.outsideHandler, true);
      },
      unbindOutside() {
        if (!this.outsideHandler) {
          return;
        }
        document.removeEventListener("pointerdown", this.outsideHandler, true);
        document.removeEventListener("focusin", this.outsideHandler, true);
        this.outsideHandler = null;
      }
    }
  };
  const _hoisted_1$b = ["title"];
  const _hoisted_2$8 = { class: "picker-chip-label" };
  const _hoisted_3$8 = ["aria-label", "title", "onClick"];
  const _hoisted_4$7 = ["title", "aria-label"];
  const _hoisted_5$4 = { class: "picker-chip-label" };
  const _hoisted_6$4 = ["id", "aria-label", "title"];
  const _hoisted_7$2 = ["id", "aria-expanded", "aria-controls", "aria-activedescendant", "aria-label", "aria-busy", "placeholder", "value", "disabled"];
  const _hoisted_8$2 = {
    key: 0,
    class: "spinner-border spinner-border-sm select-menu-spinner",
    "aria-hidden": "true"
  };
  const _hoisted_9$2 = ["id", "aria-label", "aria-busy"];
  const _hoisted_10$2 = ["id", "aria-selected", "onClick", "onMousemove"];
  const _hoisted_11$2 = {
    key: 0,
    class: "dropdown-item-text select-menu-feedback",
    role: "presentation"
  };
  const _hoisted_12$2 = {
    class: "visually-hidden",
    role: "status"
  };
  function _sfc_render$c(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        ref: "root",
        class: vue.normalizeClass(["select-menu picker picker-filter", [$props.block, { "is-open": $data.open, "has-selection": $options.hasValue, "is-disabled": $data.resolving, "is-loading": $data.resolving, "is-multiple": $props.multiple, "keeps-placeholder": $options.keepsPlaceholder }]]),
        style: vue.normalizeStyle($options.keepsPlaceholder ? { "--hh-picker-placeholder-chars": $options.placeholder.length } : null)
      },
      [
        vue.createElementVNode("div", {
          class: "form-control picker-field",
          onClick: _cache[3] || (_cache[3] = (...args) => $options.onFieldClick && $options.onFieldClick(...args))
        }, [
          $props.multiple ? (vue.openBlock(), vue.createElementBlock(
            vue.Fragment,
            { key: 0 },
            [
              $props.icon ? (vue.openBlock(), vue.createElementBlock(
                "i",
                {
                  key: 0,
                  class: vue.normalizeClass(["ti picker-filter-icon", [$props.icon, `${$props.block}-icon`]]),
                  "aria-hidden": "true"
                },
                null,
                2
                /* CLASS */
              )) : vue.createCommentVNode("v-if", true),
              (vue.openBlock(true), vue.createElementBlock(
                vue.Fragment,
                null,
                vue.renderList($options.chips, (chip) => {
                  return vue.openBlock(), vue.createElementBlock("span", {
                    key: chip.id,
                    class: vue.normalizeClass(["picker-chip picker-filter-chip", `${$props.block}-chip`]),
                    title: $options.chipTitle(chip)
                  }, [
                    vue.createElementVNode(
                      "span",
                      {
                        class: vue.normalizeClass(["picker-filter-avatar", `${$props.block}-avatar`]),
                        "aria-hidden": "true"
                      },
                      [
                        chip.item ? vue.renderSlot(_ctx.$slots, "chip", {
                          item: chip.item
                        }, void 0, void 0, 0) : vue.createCommentVNode("v-if", true)
                      ],
                      2
                      /* CLASS */
                    ),
                    vue.createElementVNode(
                      "span",
                      _hoisted_2$8,
                      vue.toDisplayString(chip.label),
                      1
                      /* TEXT */
                    ),
                    vue.createElementVNode("button", {
                      type: "button",
                      class: "picker-chip-remove",
                      "aria-label": $options.removeLabel(chip.label),
                      title: $options.removeLabel(chip.label),
                      onClick: vue.withModifiers(($event) => $options.remove(chip.id), ["stop"])
                    }, [..._cache[5] || (_cache[5] = [
                      vue.createElementVNode(
                        "i",
                        {
                          class: "ti ti-x",
                          "aria-hidden": "true"
                        },
                        null,
                        -1
                        /* CACHED */
                      )
                    ])], 8, _hoisted_3$8)
                  ], 10, _hoisted_1$b);
                }),
                128
                /* KEYED_FRAGMENT */
              ))
            ],
            64
            /* STABLE_FRAGMENT */
          )) : $options.hasValue && !$data.resolving ? (vue.openBlock(), vue.createElementBlock("span", {
            key: 1,
            class: vue.normalizeClass(["picker-chip picker-filter-chip", `${$props.block}-chip`]),
            title: $options.chipTitle($options.chips[0]),
            role: "group",
            "aria-label": $options.accessibleName
          }, [
            vue.createElementVNode(
              "span",
              {
                class: vue.normalizeClass(["picker-filter-avatar", `${$props.block}-avatar`]),
                "aria-hidden": "true"
              },
              [
                $options.chips[0].item ? vue.renderSlot(_ctx.$slots, "chip", {
                  item: $options.chips[0].item
                }, void 0, void 0, 0) : vue.createCommentVNode("v-if", true)
              ],
              2
              /* CLASS */
            ),
            vue.createElementVNode(
              "span",
              _hoisted_5$4,
              vue.toDisplayString($options.chips[0].label),
              1
              /* TEXT */
            ),
            vue.createElementVNode("button", {
              id: $props.inputId,
              ref: "remove",
              type: "button",
              class: "picker-chip-remove",
              "aria-label": $options.removeLabel($options.chips[0].label),
              title: $options.removeLabel($options.chips[0].label),
              onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => $options.remove($options.chips[0].id), ["stop"]))
            }, [..._cache[6] || (_cache[6] = [
              vue.createElementVNode(
                "i",
                {
                  class: "ti ti-x",
                  "aria-hidden": "true"
                },
                null,
                -1
                /* CACHED */
              )
            ])], 8, _hoisted_6$4)
          ], 10, _hoisted_4$7)) : vue.createCommentVNode("v-if", true),
          !$props.multiple && !($options.hasValue && !$data.resolving) && $props.icon ? (vue.openBlock(), vue.createElementBlock(
            "i",
            {
              key: 2,
              class: vue.normalizeClass(["ti picker-filter-icon", [$props.icon, `${$props.block}-icon`]]),
              "aria-hidden": "true"
            },
            null,
            2
            /* CLASS */
          )) : vue.createCommentVNode("v-if", true),
          $props.multiple || !($options.hasValue && !$data.resolving) ? (vue.openBlock(), vue.createElementBlock("input", {
            key: 3,
            id: $props.inputId,
            ref: "input",
            type: "text",
            class: "picker-input",
            role: "combobox",
            autocomplete: "off",
            "aria-autocomplete": "list",
            "aria-expanded": $data.open ? "true" : "false",
            "aria-controls": $data.listboxId,
            "aria-activedescendant": $options.activeDescendant,
            "aria-label": $options.accessibleName,
            "aria-busy": $data.resolving || $data.searching ? "true" : null,
            placeholder: $props.multiple && $options.hasValue && !$props.keepPlaceholder ? "" : $options.placeholder,
            value: $data.query,
            disabled: $data.resolving,
            onInput: _cache[1] || (_cache[1] = (...args) => $options.onInput && $options.onInput(...args)),
            onKeydown: _cache[2] || (_cache[2] = (...args) => $options.onKeydown && $options.onKeydown(...args))
          }, null, 40, _hoisted_7$2)) : vue.createCommentVNode("v-if", true)
        ]),
        $data.resolving ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_8$2)) : vue.createCommentVNode("v-if", true),
        vue.createElementVNode("ul", {
          id: $data.listboxId,
          ref: "listbox",
          class: vue.normalizeClass(["dropdown-menu select-menu-drawer", { show: $data.open }]),
          "data-bs-popper": "static",
          role: "listbox",
          "aria-label": $options.accessibleName,
          "aria-busy": $data.searching ? "true" : null
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($data.results, (result, index) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                id: $options.optionId(index),
                key: $options.idOf(result.id),
                class: vue.normalizeClass(["dropdown-item select-menu-option picker-filter-option", [`${$props.block}-option`, { "is-focused": index === $data.activeIndex }]]),
                role: "option",
                "aria-selected": index === $data.activeIndex ? "true" : "false",
                onPointerdown: _cache[4] || (_cache[4] = vue.withModifiers(() => {
                }, ["prevent"])),
                onClick: ($event) => $options.choose(result),
                onMousemove: ($event) => $data.activeIndex = index
              }, [
                vue.createElementVNode(
                  "span",
                  {
                    class: vue.normalizeClass(["picker-filter-avatar", `${$props.block}-avatar`]),
                    "aria-hidden": "true"
                  },
                  [
                    vue.renderSlot(_ctx.$slots, "option", { item: result })
                  ],
                  2
                  /* CLASS */
                ),
                vue.createElementVNode(
                  "span",
                  {
                    class: vue.normalizeClass(["picker-filter-name", `${$props.block}-name`])
                  },
                  vue.toDisplayString($props.itemLabel(result)),
                  3
                  /* TEXT, CLASS */
                ),
                _ctx.$slots["option-detail"] ? (vue.openBlock(), vue.createElementBlock(
                  "span",
                  {
                    key: 0,
                    class: vue.normalizeClass(["picker-filter-detail", `${$props.block}-detail`])
                  },
                  [
                    _cache[7] || (_cache[7] = vue.createElementVNode(
                      "span",
                      { class: "visually-hidden" },
                      ", ",
                      -1
                      /* CACHED */
                    )),
                    vue.renderSlot(_ctx.$slots, "option-detail", { item: result })
                  ],
                  2
                  /* CLASS */
                )) : vue.createCommentVNode("v-if", true)
              ], 42, _hoisted_10$2);
            }),
            128
            /* KEYED_FRAGMENT */
          )),
          $options.feedback ? (vue.openBlock(), vue.createElementBlock(
            "li",
            _hoisted_11$2,
            vue.toDisplayString($options.feedback),
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true)
        ], 10, _hoisted_9$2),
        vue.createElementVNode(
          "span",
          _hoisted_12$2,
          vue.toDisplayString($data.open ? $options.feedback || "" : ""),
          1
          /* TEXT */
        )
      ],
      6
      /* CLASS, STYLE */
    );
  }
  const C16 = /* @__PURE__ */ _export_sfc(_sfc_main$c, [["render", _sfc_render$c]]);
  const _sfc_main$b = {
    name: "ProgressFrame",
    props: {
      value: { type: Number, default: 0 },
      label: { type: String, default: null }
    },
    computed: {
      clamped() {
        return Math.min(100, Math.max(0, Math.round(this.value)));
      }
    }
  };
  const _hoisted_1$a = ["aria-valuenow", "aria-label"];
  function _sfc_render$b(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("svg", {
      class: "progress-frame",
      role: "progressbar",
      "aria-valuemin": "0",
      "aria-valuemax": "100",
      "aria-valuenow": $options.clamped,
      "aria-label": $props.label
    }, [
      _cache[0] || (_cache[0] = vue.createElementVNode(
        "rect",
        {
          class: "progress-frame-track",
          width: "100%",
          height: "100%",
          rx: "6",
          pathLength: "100"
        },
        null,
        -1
        /* CACHED */
      )),
      vue.createElementVNode(
        "rect",
        {
          class: "progress-frame-bar",
          width: "100%",
          height: "100%",
          rx: "6",
          pathLength: "100",
          style: vue.normalizeStyle({ strokeDashoffset: 100 - $options.clamped })
        },
        null,
        4
        /* STYLE */
      )
    ], 8, _hoisted_1$a);
  }
  const C17 = /* @__PURE__ */ _export_sfc(_sfc_main$b, [["render", _sfc_render$b]]);
  const _sfc_main$a = {
    mixins: [fieldMixin],
    props: {
      shellHtml: { type: String, required: true },
      // Passed through to LegacyFormWrapper — see ITS "Unique-id contract"
      // docblock section for the uniqueness/stability contract (and why a
      // caller whose shell hosts the backup-enabled richtext editor — i.e.
      // every caller of THIS field — should pass one).
      instanceKey: { type: String, default: null }
    },
    mounted() {
      this.$refs.wrapper.$el.addEventListener("input", this.clearOwnError);
    },
    beforeUnmount() {
      this.$refs.wrapper.$el.removeEventListener("input", this.clearOwnError);
    },
    methods: {
      getValue() {
        return this.$refs.wrapper.getValue();
      },
      setValue(markdown) {
        this.$refs.wrapper.setValue(markdown);
      },
      clear() {
        this.$refs.wrapper.clear();
      },
      resetAcknowledge() {
        this.$refs.wrapper.resetAcknowledge();
      },
      focus() {
        this.$refs.wrapper.focus();
      },
      /** @returns {Element} the shell's own root DOM node (see the class docblock's "API" section). */
      getShellElement() {
        return this.$refs.wrapper.$el;
      }
    }
  };
  const _hoisted_1$9 = ["id"];
  function _sfc_render$a(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_LegacyFormWrapper = vue.resolveComponent("LegacyFormWrapper");
    return vue.openBlock(), vue.createElementBlock(
      vue.Fragment,
      null,
      [
        vue.createVNode(_component_LegacyFormWrapper, {
          ref: "wrapper",
          "shell-html": $props.shellHtml,
          "instance-key": $props.instanceKey
        }, null, 8, ["shell-html", "instance-key"]),
        _ctx.hasError ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 0,
          id: _ctx.errorId,
          class: "invalid-feedback d-block"
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList(_ctx.errorMessages, (message, index) => {
              return vue.openBlock(), vue.createElementBlock(
                "div",
                { key: index },
                vue.toDisplayString(message),
                1
                /* TEXT */
              );
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ], 8, _hoisted_1$9)) : vue.createCommentVNode("v-if", true)
      ],
      64
      /* STABLE_FRAGMENT */
    );
  }
  const C18 = /* @__PURE__ */ _export_sfc(_sfc_main$a, [["render", _sfc_render$a]]);
  const ENVELOPE_ATTRS = {
    "data-ui-richtext": "",
    "data-ui-widget": "ui.richtext.prosemirror.RichText",
    "data-ui-init": ""
  };
  const OEMBED_LINK = /\]\(oembed:([^)\s]+)/g;
  const MAX_OEMBEDS = 10;
  const oembedUrls = (message) => {
    const urls = [];
    for (const match of String(message || "").matchAll(OEMBED_LINK)) {
      if (!urls.includes(match[1])) {
        urls.push(match[1]);
      }
      if (urls.length === MAX_OEMBEDS) {
        break;
      }
    }
    return urls;
  };
  const _sfc_main$9 = {
    props: {
      message: { type: String, default: null }
    },
    data() {
      return {
        ENVELOPE_ATTRS,
        // False while the previews of the current message are being loaded.
        ready: false
      };
    },
    watch: {
      message: { immediate: true, handler: "prepare" }
    },
    methods: {
      prepare(message) {
        const urls = oembedUrls(message);
        if (!urls.length) {
          this.ready = true;
          return;
        }
        this.ready = false;
        const done = () => {
          if (this.message === message) {
            this.ready = true;
          }
        };
        vue$1.oembed.load(urls, { consent: true, silent: true }).then(done, (error) => {
          vue$1.log.warn("RichTextOutput: could not load oembed previews, rendering plain links", error);
          done();
        });
      }
    }
  };
  const _hoisted_1$8 = { key: 0 };
  function _sfc_render$9(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return $props.message && $data.ready ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$8, [
      (vue.openBlock(), vue.createElementBlock(
        "div",
        vue.mergeProps({ key: $props.message }, $data.ENVELOPE_ATTRS),
        vue.toDisplayString($props.message),
        17
        /* TEXT, FULL_PROPS */
      ))
    ])), [
      [_directive_additions]
    ]) : vue.createCommentVNode("v-if", true);
  }
  const C19 = /* @__PURE__ */ _export_sfc(_sfc_main$9, [["render", _sfc_render$9]]);
  const _sfc_main$8 = {
    mixins: [fieldMixin],
    props: {
      modelValue: { type: [String, Number], default: "" },
      options: { type: Array, default: () => [] },
      prompt: { type: String, default: null }
    },
    emits: ["update:modelValue"],
    computed: {
      internalValue: {
        get() {
          return this.modelValue;
        },
        set(value) {
          this.$emit("update:modelValue", value);
          this.clearOwnError();
        }
      }
    },
    methods: {
      focus() {
        if (this.$refs.input) {
          this.$refs.input.focus();
        }
      }
    }
  };
  const _hoisted_1$7 = ["for"];
  const _hoisted_2$7 = ["id", "name", "disabled", "aria-required", "aria-invalid", "aria-describedby"];
  const _hoisted_3$7 = {
    key: 0,
    value: ""
  };
  const _hoisted_4$6 = ["value"];
  const _hoisted_5$3 = ["id"];
  const _hoisted_6$3 = ["id"];
  function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: vue.normalizeClass(["mb-3", [`field-${_ctx.fieldId}`, { required: _ctx.required }]])
      },
      [
        _ctx.label ? (vue.openBlock(), vue.createElementBlock("label", {
          key: 0,
          for: _ctx.fieldId,
          class: "form-label"
        }, vue.toDisplayString(_ctx.label), 9, _hoisted_1$7)) : vue.createCommentVNode("v-if", true),
        vue.withDirectives(vue.createElementVNode("select", {
          ref: "input",
          id: _ctx.fieldId,
          name: _ctx.fieldName,
          class: vue.normalizeClass(["form-select", { "is-invalid": _ctx.hasError }]),
          disabled: _ctx.isDisabled,
          "aria-required": _ctx.required ? "true" : null,
          "aria-invalid": _ctx.hasError ? "true" : null,
          "aria-describedby": _ctx.describedBy,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $options.internalValue = $event)
        }, [
          $props.prompt !== null ? (vue.openBlock(), vue.createElementBlock(
            "option",
            _hoisted_3$7,
            vue.toDisplayString($props.prompt),
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true),
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($props.options, (option) => {
              return vue.openBlock(), vue.createElementBlock("option", {
                key: option.value,
                value: option.value
              }, vue.toDisplayString(option.label), 9, _hoisted_4$6);
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ], 10, _hoisted_2$7), [
          [vue.vModelSelect, $options.internalValue]
        ]),
        _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 1,
          id: _ctx.hintId,
          class: "form-text text-muted"
        }, vue.toDisplayString(_ctx.hint), 9, _hoisted_5$3)) : vue.createCommentVNode("v-if", true),
        _ctx.hasError ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 2,
          id: _ctx.errorId,
          class: "invalid-feedback"
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList(_ctx.errorMessages, (message, index) => {
              return vue.openBlock(), vue.createElementBlock(
                "div",
                { key: index },
                vue.toDisplayString(message),
                1
                /* TEXT */
              );
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ], 8, _hoisted_6$3)) : vue.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C20 = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["render", _sfc_render$8]]);
  const _sfc_main$7 = {
    name: "SelectionMenu",
    components: { DropdownMenu: C5 },
    props: {
      count: { type: Number, required: true },
      menuId: { type: String, required: true },
      entries: { type: Array, default: () => [] },
      context: { type: Object, default: () => ({}) }
    },
    computed: {
      toggleLabel() {
        return vue$1.i18n.t("base", "Actions for {count} selected", { count: this.count });
      }
    }
  };
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DropdownMenu = vue.resolveComponent("DropdownMenu");
    return vue.openBlock(), vue.createBlock(vue.Transition, { name: "selection-menu" }, {
      default: vue.withCtx(() => [
        $props.count > 0 ? (vue.openBlock(), vue.createBlock(_component_DropdownMenu, {
          key: 0,
          "root-class": "nav selection-menu",
          "toggle-class": "btn btn-ghost btn-icon-only selection-menu-toggle",
          "toggle-aria-label": $options.toggleLabel,
          "toggle-title": $options.toggleLabel,
          "menu-id": $props.menuId,
          entries: $props.entries,
          context: $props.context
        }, {
          toggle: vue.withCtx(() => [..._cache[0] || (_cache[0] = [
            vue.createElementVNode(
              "i",
              {
                class: "ti ti-dots-vertical",
                "aria-hidden": "true"
              },
              null,
              -1
              /* CACHED */
            )
          ])]),
          _: 1
          /* STABLE */
        }, 8, ["toggle-aria-label", "toggle-title", "menu-id", "entries", "context"])) : vue.createCommentVNode("v-if", true)
      ]),
      _: 1
      /* STABLE */
    });
  }
  const C21 = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$7]]);
  const TRANSITION_MS = 150;
  const AUTOCLOSE = {
    info: 6e3,
    success: 2e3,
    warn: 1e4,
    error: 0
  };
  const ICONS = {
    info: "ti ti-info-circle",
    success: "ti ti-circle-check",
    warn: "ti ti-alert-triangle",
    error: "ti ti-alert-circle"
  };
  const TONES = {
    info: "toast-info",
    success: "toast-success",
    warn: "toast-warning",
    error: "toast-danger"
  };
  const normalizeDetails = (details) => {
    if (details === void 0 || details === null || details === "") {
      return null;
    }
    if (typeof details === "string") {
      return details;
    }
    if (details instanceof Error) {
      const text = details.toString();
      if (!details.stack) {
        return text;
      }
      return details.stack.indexOf(text) === 0 ? details.stack : text + "\n" + details.stack;
    }
    try {
      return JSON.stringify(details, null, 4);
    } catch (e) {
      return String(details);
    }
  };
  const _sfc_main$6 = {
    props: {
      closeLabel: { type: String, default: "Close" },
      detailsLabel: { type: String, default: "Show more" }
    },
    data() {
      return {
        entry: null,
        visible: false,
        detailsOpen: false,
        hovered: false,
        focused: false
      };
    },
    computed: {
      iconClass() {
        return ICONS[this.entry.level] || ICONS.info;
      },
      toneClass() {
        return TONES[this.entry.level] || TONES.info;
      },
      isError() {
        return this.entry.level === "error";
      },
      detailsText() {
        return this.entry ? this.entry.details : null;
      },
      hasDetails() {
        return !!this.detailsText;
      }
    },
    mounted() {
      vue$1.setStatusHandler(this.handle);
    },
    unmounted() {
      vue$1.setStatusHandler(null);
      this.clearTimers();
    },
    methods: {
      /** Bridge handler - see humhub.vue.js `status()`. */
      handle(message) {
        const entry = {
          level: AUTOCLOSE[message.level] !== void 0 ? message.level : "info",
          message: message.message,
          details: normalizeDetails(message.details),
          closeAfter: message.closeAfter
        };
        if (this.entry) {
          this.startHide(() => this.present(entry));
        } else {
          this.present(entry);
        }
      },
      present(entry) {
        this.clearTimers();
        this.entry = entry;
        this.detailsOpen = false;
        this.visible = false;
        this.hovered = false;
        this.focused = false;
        this.$nextTick(() => {
          if (this.$el && typeof this.$el.getBoundingClientRect === "function") {
            void this.$el.getBoundingClientRect().height;
          }
          this.visible = true;
        });
        this.scheduleClose(TRANSITION_MS);
      },
      scheduleClose(offset = 0) {
        this.clearCloseTimer();
        const closeAfter = this.autoCloseDelay(this.entry);
        if (closeAfter > 0) {
          this.closeTimer = setTimeout(() => this.startHide(), offset + closeAfter);
        }
      },
      /** Pointer or focus on the toast pauses the auto-close; it starts over once both left. */
      interact(kind, on) {
        this[kind] = on;
        if (!this.entry || !this.visible) {
          return;
        }
        if (on) {
          this.clearCloseTimer();
        } else if (!this.hovered && !this.focused) {
          this.scheduleClose();
        }
      },
      onFocusOut(event) {
        if (this.$refs.toast && this.$refs.toast.contains(event.relatedTarget)) {
          return;
        }
        this.interact("focused", false);
      },
      /**
       * `closeAfter || default` - the legacy expression, quirk included: for
       * info/success/warn a 0 or undefined value means "use the default", and
       * only `error` (whose default is 0) stays until dismissed.
       */
      autoCloseDelay(entry) {
        return entry.closeAfter || AUTOCLOSE[entry.level] || 0;
      },
      startHide(after) {
        this.clearTimers();
        this.visible = false;
        this.hideTimer = setTimeout(() => {
          this.entry = null;
          this.detailsOpen = false;
          if (after) {
            after();
          }
        }, TRANSITION_MS);
      },
      close() {
        this.startHide();
      },
      toggleDetails() {
        if (this.hasDetails) {
          this.detailsOpen = !this.detailsOpen;
        }
      },
      clearCloseTimer() {
        if (this.closeTimer) {
          clearTimeout(this.closeTimer);
          this.closeTimer = null;
        }
      },
      clearTimers() {
        this.clearCloseTimer();
        if (this.hideTimer) {
          clearTimeout(this.hideTimer);
          this.hideTimer = null;
        }
      }
    }
  };
  const _hoisted_1$6 = {
    key: 0,
    class: "toast-container position-fixed bottom-0 start-50 translate-middle-x p-3"
  };
  const _hoisted_2$6 = ["role", "aria-live"];
  const _hoisted_3$6 = { class: "toast-body" };
  const _hoisted_4$5 = ["aria-expanded", "aria-label", "title"];
  const _hoisted_5$2 = ["aria-label"];
  const _hoisted_6$2 = {
    key: 0,
    class: "toast-details"
  };
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
    return $data.entry ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$6, [
      vue.createElementVNode("div", {
        ref: "toast",
        class: vue.normalizeClass(["toast fade show", [$options.toneClass, { showing: !$data.visible }]]),
        role: $options.isError ? "alert" : "status",
        "aria-live": $options.isError ? "assertive" : "polite",
        "aria-atomic": "true",
        onMouseenter: _cache[3] || (_cache[3] = ($event) => $options.interact("hovered", true)),
        onMouseleave: _cache[4] || (_cache[4] = ($event) => $options.interact("hovered", false)),
        onFocusin: _cache[5] || (_cache[5] = ($event) => $options.interact("focused", true)),
        onFocusout: _cache[6] || (_cache[6] = (...args) => $options.onFocusOut && $options.onFocusOut(...args))
      }, [
        vue.createElementVNode("div", _hoisted_3$6, [
          vue.createElementVNode(
            "i",
            {
              class: vue.normalizeClass(["toast-icon", $options.iconClass]),
              "aria-hidden": "true"
            },
            null,
            2
            /* CLASS */
          ),
          vue.createElementVNode(
            "span",
            {
              class: vue.normalizeClass(["toast-message", { "is-toggle": $options.hasDetails }]),
              onClick: _cache[0] || (_cache[0] = (...args) => $options.toggleDetails && $options.toggleDetails(...args))
            },
            vue.toDisplayString($data.entry.message),
            3
            /* TEXT, CLASS */
          ),
          $options.hasDetails ? (vue.openBlock(), vue.createElementBlock("button", {
            key: 0,
            type: "button",
            class: "btn btn-ghost btn-icon-only btn-sm toast-details-toggle",
            "aria-expanded": $data.detailsOpen ? "true" : "false",
            "aria-label": $props.detailsLabel,
            title: $props.detailsLabel,
            onClick: _cache[1] || (_cache[1] = (...args) => $options.toggleDetails && $options.toggleDetails(...args))
          }, [
            vue.createElementVNode(
              "i",
              {
                class: vue.normalizeClass(["ti", $data.detailsOpen ? "ti-chevron-down" : "ti-chevron-up"]),
                "aria-hidden": "true"
              },
              null,
              2
              /* CLASS */
            )
          ], 8, _hoisted_4$5)) : vue.createCommentVNode("v-if", true),
          vue.createElementVNode("button", {
            type: "button",
            class: "btn-close toast-close",
            "aria-label": $props.closeLabel,
            onClick: _cache[2] || (_cache[2] = (...args) => $options.close && $options.close(...args))
          }, null, 8, _hoisted_5$2)
        ]),
        $data.detailsOpen ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$2, [
          vue.createElementVNode(
            "pre",
            null,
            vue.toDisplayString($options.detailsText),
            1
            /* TEXT */
          )
        ])) : vue.createCommentVNode("v-if", true)
      ], 42, _hoisted_2$6)
    ])) : vue.createCommentVNode("v-if", true);
  }
  const C22 = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  const _sfc_main$5 = {
    inject: {
      humhubForm: { from: FORM_CONTEXT_KEY, default: null }
    },
    props: {
      disabled: { type: Boolean, default: false },
      loader: { type: Boolean, default: true }
    },
    computed: {
      formBusy() {
        return this.humhubForm ? this.humhubForm.busy.value : false;
      },
      isDisabled() {
        return this.disabled || this.formBusy;
      },
      showLoader() {
        return this.loader && this.formBusy;
      },
      loadingText() {
        return vue$1.i18n.t("base", "Loading...");
      }
    }
  };
  const _hoisted_1$5 = ["disabled"];
  const _hoisted_2$5 = {
    key: 0,
    class: "hh-loader text-center"
  };
  const _hoisted_3$5 = {
    role: "status",
    class: "visually-hidden"
  };
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("button", {
      type: "submit",
      disabled: $options.isDisabled
    }, [
      $options.showLoader ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$5, [
        _cache[0] || (_cache[0] = vue.createElementVNode(
          "span",
          {
            class: "spinner-border spinner-border-sm",
            "aria-hidden": "true"
          },
          null,
          -1
          /* CACHED */
        )),
        vue.createElementVNode(
          "span",
          _hoisted_3$5,
          vue.toDisplayString($options.loadingText),
          1
          /* TEXT */
        )
      ])) : vue.renderSlot(_ctx.$slots, "default", {}, void 0, void 0, 1)
    ], 8, _hoisted_1$5);
  }
  const C23 = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const _sfc_main$4 = {
    mixins: [fieldMixin],
    props: {
      modelValue: { type: String, default: "" },
      type: { type: String, default: "text" }
    },
    emits: ["update:modelValue"],
    computed: {
      internalValue: {
        get() {
          return this.modelValue;
        },
        set(value) {
          this.$emit("update:modelValue", value);
          this.clearOwnError();
        }
      }
    },
    methods: {
      focus() {
        if (this.$refs.input) {
          this.$refs.input.focus();
        }
      }
    }
  };
  const _hoisted_1$4 = ["for"];
  const _hoisted_2$4 = ["id", "name", "type", "placeholder", "disabled", "aria-required", "aria-invalid", "aria-describedby"];
  const _hoisted_3$4 = ["id"];
  const _hoisted_4$4 = ["id"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: vue.normalizeClass(["mb-3", [`field-${_ctx.fieldId}`, { required: _ctx.required }]])
      },
      [
        _ctx.label ? (vue.openBlock(), vue.createElementBlock("label", {
          key: 0,
          for: _ctx.fieldId,
          class: "form-label"
        }, vue.toDisplayString(_ctx.label), 9, _hoisted_1$4)) : vue.createCommentVNode("v-if", true),
        vue.withDirectives(vue.createElementVNode("input", {
          ref: "input",
          id: _ctx.fieldId,
          name: _ctx.fieldName,
          type: $props.type,
          class: vue.normalizeClass(["form-control", { "is-invalid": _ctx.hasError }]),
          placeholder: _ctx.placeholder,
          disabled: _ctx.isDisabled,
          "aria-required": _ctx.required ? "true" : null,
          "aria-invalid": _ctx.hasError ? "true" : null,
          "aria-describedby": _ctx.describedBy,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $options.internalValue = $event)
        }, null, 10, _hoisted_2$4), [
          [vue.vModelDynamic, $options.internalValue]
        ]),
        _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 1,
          id: _ctx.hintId,
          class: "form-text text-muted"
        }, vue.toDisplayString(_ctx.hint), 9, _hoisted_3$4)) : vue.createCommentVNode("v-if", true),
        _ctx.hasError ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 2,
          id: _ctx.errorId,
          class: "invalid-feedback"
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList(_ctx.errorMessages, (message, index) => {
              return vue.openBlock(), vue.createElementBlock(
                "div",
                { key: index },
                vue.toDisplayString(message),
                1
                /* TEXT */
              );
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ], 8, _hoisted_4$4)) : vue.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C24 = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = {
    mixins: [fieldMixin],
    props: {
      modelValue: { type: String, default: "" },
      rows: { type: Number, default: 4 }
    },
    emits: ["update:modelValue"],
    computed: {
      internalValue: {
        get() {
          return this.modelValue;
        },
        set(value) {
          this.$emit("update:modelValue", value);
          this.clearOwnError();
        }
      }
    },
    methods: {
      focus() {
        if (this.$refs.input) {
          this.$refs.input.focus();
        }
      }
    }
  };
  const _hoisted_1$3 = ["for"];
  const _hoisted_2$3 = ["id", "name", "placeholder", "disabled", "rows", "aria-required", "aria-invalid", "aria-describedby"];
  const _hoisted_3$3 = ["id"];
  const _hoisted_4$3 = ["id"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: vue.normalizeClass(["mb-3", [`field-${_ctx.fieldId}`, { required: _ctx.required }]])
      },
      [
        _ctx.label ? (vue.openBlock(), vue.createElementBlock("label", {
          key: 0,
          for: _ctx.fieldId,
          class: "form-label"
        }, vue.toDisplayString(_ctx.label), 9, _hoisted_1$3)) : vue.createCommentVNode("v-if", true),
        vue.withDirectives(vue.createElementVNode("textarea", {
          ref: "input",
          id: _ctx.fieldId,
          name: _ctx.fieldName,
          class: vue.normalizeClass(["form-control", { "is-invalid": _ctx.hasError }]),
          placeholder: _ctx.placeholder,
          disabled: _ctx.isDisabled,
          rows: $props.rows,
          "aria-required": _ctx.required ? "true" : null,
          "aria-invalid": _ctx.hasError ? "true" : null,
          "aria-describedby": _ctx.describedBy,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $options.internalValue = $event)
        }, null, 10, _hoisted_2$3), [
          [vue.vModelText, $options.internalValue]
        ]),
        _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 1,
          id: _ctx.hintId,
          class: "form-text text-muted"
        }, vue.toDisplayString(_ctx.hint), 9, _hoisted_3$3)) : vue.createCommentVNode("v-if", true),
        _ctx.hasError ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 2,
          id: _ctx.errorId,
          class: "invalid-feedback"
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList(_ctx.errorMessages, (message, index) => {
              return vue.openBlock(), vue.createElementBlock(
                "div",
                { key: index },
                vue.toDisplayString(message),
                1
                /* TEXT */
              );
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ], 8, _hoisted_4$3)) : vue.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C25 = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = {
    name: "TileGrid",
    components: { ProgressFrame: C17 },
    props: {
      items: { type: Array, required: true },
      itemKey: { type: String, default: "id" },
      selectable: { type: Boolean, default: false },
      selection: { type: Array, default: () => [] },
      draggable: { type: Boolean, default: false },
      canDrop: { type: Function, default: () => false },
      dropTargetKey: { type: [String, Number], default: null },
      loading: { type: Boolean, default: false },
      skeletonCount: { type: Number, default: 12 },
      hasMore: { type: Boolean, default: false },
      loadingMore: { type: Boolean, default: false },
      level: { type: [String, Number], default: 0 },
      direction: { type: String, default: "forward" },
      labelFor: { type: Function, default: (item) => item.title ?? "" }
    },
    emits: ["toggle-select", "context-menu", "drag-start", "drag-end", "drag-over", "drag-leave", "drop-on", "load-more"],
    computed: {
      showSkeleton() {
        return this.loading && !this.items.length;
      },
      swapName() {
        return this.direction === "back" ? "tile-grid-swap-back" : "tile-grid-swap-forward";
      },
      emptyLabel() {
        return vue$1.i18n.t("base", "No results found!");
      },
      moreLabel() {
        return vue$1.i18n.t("base", "Show more");
      },
      loadingLabel() {
        return vue$1.i18n.t("base", "Loading...");
      },
      uploadingLabel() {
        return vue$1.i18n.t("base", "Uploading...");
      }
    },
    watch: {
      loadingMore(isLoading, wasLoading) {
        if (wasLoading && !isLoading) {
          this.rearm();
        }
      }
    },
    created() {
      this.dragKey = void 0;
    },
    mounted() {
      this.observe();
    },
    updated() {
      this.observe();
    },
    beforeUnmount() {
      if (this.observer) {
        this.observer.disconnect();
      }
    },
    methods: {
      isSelected(item) {
        return this.selection.includes(item[this.itemKey]);
      },
      isDraggable(item) {
        return this.draggable && !item.uploading;
      },
      selectLabel(item) {
        return vue$1.i18n.t("base", "Select {name}", { name: this.labelFor(item) });
      },
      tileClass(item) {
        return {
          "is-selected": this.isSelected(item),
          "is-drop-target": this.dropTargetKey !== null && this.dropTargetKey === item[this.itemKey],
          "is-uploading": !!item.uploading
        };
      },
      onContextMenu(item, event) {
        if (event.ctrlKey) {
          return;
        }
        event.preventDefault();
        this.$emit("context-menu", item, event);
      },
      onCheck(item, event) {
        this.$emit("toggle-select", item, { range: event.shiftKey });
        this.$nextTick(() => {
          event.target.checked = this.isSelected(item);
        });
      },
      onDragStart(item, event) {
        var _a;
        if (!this.isDraggable(item)) {
          return;
        }
        this.dragKey = item[this.itemKey];
        if (event.target !== event.currentTarget && ((_a = event.dataTransfer) == null ? void 0 : _a.setDragImage)) {
          const rect = event.currentTarget.getBoundingClientRect();
          event.dataTransfer.setDragImage(event.currentTarget, event.clientX - rect.left, event.clientY - rect.top);
        }
        this.$emit("drag-start", item, event);
      },
      onDragEnd(item, event) {
        if (this.dragKey === void 0 || this.dragKey !== item[this.itemKey]) {
          return;
        }
        this.dragKey = void 0;
        this.$emit("drag-end", item, event);
      },
      onDragOver(item, event) {
        if (this.canDrop(item, event)) {
          event.preventDefault();
          this.$emit("drag-over", item, event);
        }
      },
      onDragLeave(item, event) {
        if (!event.currentTarget.contains(event.relatedTarget)) {
          this.$emit("drag-leave", item, event);
        }
      },
      onDrop(item, event) {
        if (!this.canDrop(item, event)) {
          return;
        }
        event.preventDefault();
        event.stopPropagation();
        this.$emit("drop-on", item, event);
      },
      // Same auto-paging as EntityGrid: observe the "Show more" button, re-arm after a page.
      observe() {
        if (typeof IntersectionObserver === "undefined") {
          return;
        }
        const sentinel = this.$refs.sentinel || null;
        if (sentinel === this.observed) {
          return;
        }
        if (this.observer) {
          this.observer.disconnect();
        }
        this.observed = sentinel;
        if (!sentinel) {
          return;
        }
        this.observer = this.observer || new IntersectionObserver((entries) => {
          if (entries.some((entry) => entry.isIntersecting) && this.hasMore && !this.loadingMore) {
            this.$emit("load-more");
          }
        }, { rootMargin: "400px" });
        this.observer.observe(sentinel);
      },
      rearm() {
        const sentinel = this.$refs.sentinel || null;
        if (this.observer && sentinel) {
          this.observer.unobserve(sentinel);
          this.observer.observe(sentinel);
        }
      }
    }
  };
  const _hoisted_1$2 = ["aria-busy"];
  const _hoisted_2$2 = { class: "tile-grid-stage" };
  const _hoisted_3$2 = {
    key: 0,
    class: "tile-grid-tiles",
    "aria-hidden": "true"
  };
  const _hoisted_4$2 = ["data-key", "draggable", "onContextmenu", "onDragstart", "onDragend", "onDragover", "onDragleave", "onDrop"];
  const _hoisted_5$1 = {
    class: "tile-thumb",
    "aria-hidden": "true"
  };
  const _hoisted_6$1 = { class: "tile-name" };
  const _hoisted_7$1 = { class: "tile-meta" };
  const _hoisted_8$1 = ["checked", "aria-label", "onClick"];
  const _hoisted_9$1 = {
    key: 2,
    class: "tile-actions"
  };
  const _hoisted_10$1 = {
    key: 2,
    class: "tile-grid-empty"
  };
  const _hoisted_11$1 = {
    key: 0,
    class: "tile-grid-more"
  };
  const _hoisted_12$1 = ["disabled"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ProgressFrame = vue.resolveComponent("ProgressFrame");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass(["tile-grid", { "is-selecting": $props.selection.length > 0 }]),
      "aria-busy": $props.loading ? "true" : null
    }, [
      vue.createElementVNode("div", _hoisted_2$2, [
        vue.createVNode(vue.Transition, { name: $options.swapName }, {
          default: vue.withCtx(() => [
            (vue.openBlock(), vue.createElementBlock("div", {
              key: $props.level,
              class: "tile-grid-pane"
            }, [
              $options.showSkeleton ? (vue.openBlock(), vue.createElementBlock("ul", _hoisted_3$2, [
                (vue.openBlock(true), vue.createElementBlock(
                  vue.Fragment,
                  null,
                  vue.renderList($props.skeletonCount, (n) => {
                    return vue.openBlock(), vue.createElementBlock("li", {
                      key: "skeleton-" + n,
                      class: "tile tile-skeleton placeholder-glow"
                    }, [..._cache[1] || (_cache[1] = [
                      vue.createElementVNode(
                        "span",
                        { class: "placeholder tile-skeleton-thumb" },
                        null,
                        -1
                        /* CACHED */
                      ),
                      vue.createElementVNode(
                        "span",
                        { class: "placeholder tile-skeleton-name" },
                        null,
                        -1
                        /* CACHED */
                      )
                    ])]);
                  }),
                  128
                  /* KEYED_FRAGMENT */
                ))
              ])) : $props.items.length ? (vue.openBlock(), vue.createBlock(vue.TransitionGroup, {
                key: 1,
                tag: "ul",
                name: "tile-grid-fade",
                class: "tile-grid-tiles"
              }, {
                default: vue.withCtx(() => [
                  (vue.openBlock(true), vue.createElementBlock(
                    vue.Fragment,
                    null,
                    vue.renderList($props.items, (item) => {
                      return vue.openBlock(), vue.createElementBlock("li", {
                        key: item[$props.itemKey],
                        class: vue.normalizeClass(["tile", $options.tileClass(item)]),
                        "data-key": item[$props.itemKey],
                        draggable: $options.isDraggable(item) ? "true" : null,
                        onContextmenu: ($event) => $options.onContextMenu(item, $event),
                        onDragstart: ($event) => $options.onDragStart(item, $event),
                        onDragend: ($event) => $options.onDragEnd(item, $event),
                        onDragover: ($event) => $options.onDragOver(item, $event),
                        onDragleave: ($event) => $options.onDragLeave(item, $event),
                        onDrop: ($event) => $options.onDrop(item, $event)
                      }, [
                        vue.createElementVNode("div", _hoisted_5$1, [
                          vue.renderSlot(_ctx.$slots, "thumb", { item })
                        ]),
                        vue.createElementVNode("div", _hoisted_6$1, [
                          vue.renderSlot(_ctx.$slots, "name", { item })
                        ]),
                        vue.createElementVNode("span", _hoisted_7$1, [
                          vue.renderSlot(_ctx.$slots, "meta", { item })
                        ]),
                        item.uploading ? (vue.openBlock(), vue.createBlock(_component_ProgressFrame, {
                          key: 0,
                          class: "tile-progress",
                          value: item.progress || 0,
                          label: $options.uploadingLabel
                        }, null, 8, ["value", "label"])) : vue.createCommentVNode("v-if", true),
                        $props.selectable && !item.uploading ? (vue.openBlock(), vue.createElementBlock("input", {
                          key: 1,
                          type: "checkbox",
                          class: "form-check-input tile-check",
                          checked: $options.isSelected(item),
                          "aria-label": $options.selectLabel(item),
                          onClick: ($event) => $options.onCheck(item, $event)
                        }, null, 8, _hoisted_8$1)) : vue.createCommentVNode("v-if", true),
                        _ctx.$slots.actions && !item.uploading ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_9$1, [
                          vue.renderSlot(_ctx.$slots, "actions", { item })
                        ])) : vue.createCommentVNode("v-if", true)
                      ], 42, _hoisted_4$2);
                    }),
                    128
                    /* KEYED_FRAGMENT */
                  ))
                ]),
                _: 3
                /* FORWARDED */
              })) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_10$1, [
                vue.renderSlot(_ctx.$slots, "empty", {}, () => [
                  vue.createTextVNode(
                    vue.toDisplayString($options.emptyLabel),
                    1
                    /* TEXT */
                  )
                ])
              ]))
            ]))
          ]),
          _: 3
          /* FORWARDED */
        }, 8, ["name"])
      ]),
      $props.hasMore ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_11$1, [
        vue.createElementVNode("button", {
          ref: "sentinel",
          type: "button",
          class: "btn btn-light",
          disabled: $props.loadingMore,
          onClick: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("load-more"))
        }, vue.toDisplayString($props.loadingMore ? $options.loadingLabel : $options.moreLabel), 9, _hoisted_12$1)
      ])) : vue.createCommentVNode("v-if", true)
    ], 10, _hoisted_1$2);
  }
  const C26 = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  function uploadFiles(files, onProgress) {
    const formData = new FormData();
    files.forEach((file) => formData.append("files[]", file));
    return vue$1.client.post(vue$1.apiUrl("file"), {
      data: formData,
      // Hand the FormData to the browser untouched: jQuery must neither serialize it nor
      // set a Content-Type, or the multipart boundary is lost.
      processData: false,
      contentType: false,
      dataType: "json",
      // The only reason this goes through a custom xhr factory: upload progress is an
      // XHR-level event jQuery does not surface. Everything else (CSRF header prefilter,
      // Response wrapping, error handling) stays with the platform client.
      xhr: () => {
        const xhr = jQuery.ajaxSettings.xhr();
        if (onProgress && xhr.upload) {
          xhr.upload.addEventListener("progress", (event) => {
            if (event.lengthComputable && event.total > 0) {
              onProgress(Math.round(event.loaded / event.total * 100));
            }
          });
        }
        return xhr;
      }
    });
  }
  const UPLOAD_BY_TYPE_ACTION = "file.uploadByType";
  const _sfc_main$1 = {
    mixins: [fieldMixin],
    props: {
      modelValue: { type: Array, default: () => [] },
      max: { type: Number, default: 0 },
      accept: { type: String, default: null },
      multiple: { type: Boolean, default: true },
      title: { type: String, default: null },
      handlersHtml: { type: String, default: "" },
      triggerTarget: { type: [Object, String], default: null }
    },
    emits: ["update:modelValue", "busy"],
    data() {
      return {
        // Progress of the request in flight, `null` while none is.
        progress: null,
        // Per-file outcomes of the last request: [{fileName, messages}].
        fileErrors: [],
        // Messages about the request as a whole (a 422, or a client-side refusal).
        requestMessages: [],
        // `accept` of the file input for ONE picker opening (an upload-by-type handler),
        // reset as soon as the picker was opened.
        pickerAccept: null
      };
    },
    computed: {
      files() {
        return this.modelValue || [];
      },
      triggerTitle() {
        return this.title || vue$1.i18n.t("FileModule.base", "Upload files");
      },
      toggleLabel() {
        return vue$1.i18n.t("base", "Toggle Dropdown");
      },
      removeLabel() {
        return vue$1.i18n.t("base", "Delete");
      },
      allMessages() {
        return [
          ...this.requestMessages,
          ...this.fileErrors.flatMap((error) => (error.messages || []).map(
            (message) => `${error.fileName}: ${message}`
          )),
          // Errors the surrounding form assigned to this attribute (a 422 of the form's
          // own request, e.g. a guid the server rejected).
          ...this.errorMessages
        ];
      }
    },
    mounted() {
      this.$el.addEventListener("humhub:file:attach", this.onAttachEvent);
    },
    beforeUnmount() {
      this.$el.removeEventListener("humhub:file:attach", this.onAttachEvent);
    },
    methods: {
      openPicker(accept = null) {
        if (this.isDisabled) {
          return;
        }
        this.pickerAccept = accept;
        this.$nextTick(() => {
          if (this.$refs.input) {
            this.$refs.input.click();
          }
        });
      },
      onInputChange(event) {
        const files = Array.from(event.target.files || []);
        event.target.value = "";
        this.pickerAccept = null;
        this.addFiles(files);
      },
      /**
       * Handles a click inside the handler dropdown. Only core's own "upload with this
       * accept type" entries are taken over (see the class docblock); everything else falls
       * through to `humhub.action.js`'s document-level `data-action-click` delegate.
       */
      onHandlerClick(event) {
        const entry = event.target.closest("[data-action-click]");
        if (!entry || entry.getAttribute("data-action-click") !== UPLOAD_BY_TYPE_ACTION) {
          return;
        }
        event.preventDefault();
        event.stopPropagation();
        let accept = null;
        try {
          accept = JSON.parse(entry.getAttribute("data-action-params") || "{}").type || null;
        } catch (error) {
          vue$1.log.warn("UploadField: could not read data-action-params of a file handler entry", error);
        }
        this.openPicker(accept);
      },
      onDrop(event) {
        this.addFiles(Array.from(event.dataTransfer && event.dataTransfer.files || []));
      },
      onPaste(event) {
        const files = Array.from(event.clipboardData && event.clipboardData.files || []);
        if (files.length) {
          this.addFiles(files);
        }
      },
      onAttachEvent(event) {
        const files = event.detail && event.detail.files || [];
        if (files.length) {
          this.emitFiles([...this.files, ...files]);
        }
      },
      /**
       * Uploads browser `File` objects and appends what the server stored.
       *
       * @param {File[]|FileList} files
       * @returns {Promise} resolves once the request finished (rejections are handled here)
       */
      addFiles(files) {
        const list = Array.from(files || []);
        this.clearMessages();
        if (!list.length || this.isDisabled) {
          return Promise.resolve();
        }
        if (!this.acceptsCount(list.length)) {
          return Promise.resolve();
        }
        this.progress = 0;
        this.$emit("busy", true);
        return uploadFiles(list, (percent) => {
          this.progress = percent;
        }).then((response) => {
          const results = response && response.results || [];
          this.fileErrors = response && response.errors || [];
          if (results.length) {
            this.emitFiles([...this.files, ...results]);
          }
        }).catch((response) => {
          if (response && response.status === 422 && response.errors) {
            this.requestMessages = Object.values(response.errors).flat();
          } else {
            vue$1.log.error(response, true);
          }
        }).finally(() => {
          this.progress = null;
          this.$emit("busy", false);
        });
      },
      removeFile(file) {
        if (this.isDisabled) {
          return;
        }
        this.clearMessages();
        this.emitFiles(this.files.filter((candidate) => candidate.guid !== file.guid));
      },
      /** Drops every attached file (e.g. after the surrounding form was submitted). */
      clear() {
        this.clearMessages();
        this.emitFiles([]);
      },
      emitFiles(files) {
        this.clearOwnError();
        this.$emit("update:modelValue", files);
      },
      /**
       * Client-side guard against a selection the server would reject wholesale: this
       * field's own maximum, and PHP's `max_file_uploads` per request. Both messages reuse
       * the keys the legacy upload widget passes to the browser, so translations exist.
       */
      acceptsCount(count) {
        if (this.max > 0 && this.files.length + count > this.max) {
          this.requestMessages = [vue$1.i18n.t(
            "FileModule.base",
            "This upload field only allows a maximum of {n,plural,=1{# file} other{# files}}.",
            { n: this.max }
          )];
          return false;
        }
        return true;
      },
      clearMessages() {
        this.fileErrors = [];
        this.requestMessages = [];
      },
      /** `HumHubForm.focusFirstError()` entry point. */
      focus() {
        const trigger = this.$el.querySelector(".fileinput-button");
        if (trigger) {
          trigger.focus();
        }
      }
    }
  };
  const _hoisted_1$1 = { class: "btn-group btn-group-sm" };
  const _hoisted_2$1 = ["aria-disabled", "title", "data-bs-title"];
  const _hoisted_3$1 = ["multiple", "accept", "disabled"];
  const _hoisted_4$1 = {
    type: "button",
    class: "btn btn-light btn-icon-only dropdown-toggle",
    "data-bs-toggle": "dropdown",
    "aria-haspopup": "true",
    "aria-expanded": "false"
  };
  const _hoisted_5 = { class: "visually-hidden" };
  const _hoisted_6 = ["innerHTML"];
  const _hoisted_7 = {
    key: 0,
    class: "progress mt-2",
    style: { "height": "6px" }
  };
  const _hoisted_8 = ["aria-valuenow"];
  const _hoisted_9 = {
    key: 1,
    class: "files"
  };
  const _hoisted_10 = ["data-preview-guid"];
  const _hoisted_11 = { class: "file-preview-content" };
  const _hoisted_12 = ["aria-label", "onClick", "onKeydown"];
  const _hoisted_13 = ["id"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: "vue-upload-field",
        onDrop: _cache[6] || (_cache[6] = vue.withModifiers((...args) => $options.onDrop && $options.onDrop(...args), ["prevent"])),
        onDragover: _cache[7] || (_cache[7] = vue.withModifiers(() => {
        }, ["prevent"])),
        onPaste: _cache[8] || (_cache[8] = (...args) => $options.onPaste && $options.onPaste(...args))
      },
      [
        (vue.openBlock(), vue.createBlock(vue.Teleport, {
          to: $props.triggerTarget,
          disabled: !$props.triggerTarget
        }, [
          vue.createElementVNode("div", _hoisted_1$1, [
            vue.createElementVNode("span", {
              class: "btn btn-light fileinput-button tt",
              role: "button",
              tabindex: "0",
              "aria-disabled": _ctx.isDisabled ? "true" : "false",
              "data-bs-toggle": "tooltip",
              "data-placement": "bottom",
              title: $options.triggerTitle,
              "data-bs-title": $options.triggerTitle,
              onClick: _cache[2] || (_cache[2] = ($event) => $options.openPicker()),
              onKeydown: [
                _cache[3] || (_cache[3] = vue.withKeys(vue.withModifiers(($event) => $options.openPicker(), ["prevent"]), ["enter"])),
                _cache[4] || (_cache[4] = vue.withKeys(vue.withModifiers(($event) => $options.openPicker(), ["prevent"]), ["space"]))
              ]
            }, [
              _cache[9] || (_cache[9] = vue.createElementVNode(
                "i",
                {
                  class: "ti ti-cloud-upload",
                  "aria-hidden": "true"
                },
                null,
                -1
                /* CACHED */
              )),
              vue.createElementVNode("input", {
                ref: "input",
                type: "file",
                class: "d-none",
                multiple: $props.multiple,
                accept: $data.pickerAccept || $props.accept || null,
                disabled: _ctx.isDisabled,
                onChange: _cache[0] || (_cache[0] = (...args) => $options.onInputChange && $options.onInputChange(...args)),
                onClick: _cache[1] || (_cache[1] = vue.withModifiers(() => {
                }, ["stop"]))
              }, null, 40, _hoisted_3$1)
            ], 40, _hoisted_2$1),
            $props.handlersHtml ? (vue.openBlock(), vue.createElementBlock(
              vue.Fragment,
              { key: 0 },
              [
                vue.createElementVNode("button", _hoisted_4$1, [
                  vue.createElementVNode(
                    "span",
                    _hoisted_5,
                    vue.toDisplayString($options.toggleLabel),
                    1
                    /* TEXT */
                  )
                ]),
                vue.createElementVNode("ul", {
                  class: "dropdown-menu dropdown-menu-end",
                  innerHTML: $props.handlersHtml,
                  onClick: _cache[5] || (_cache[5] = (...args) => $options.onHandlerClick && $options.onHandlerClick(...args))
                }, null, 8, _hoisted_6)
              ],
              64
              /* STABLE_FRAGMENT */
            )) : vue.createCommentVNode("v-if", true)
          ])
        ], 8, ["to", "disabled"])),
        $data.progress !== null ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_7, [
          vue.createElementVNode("div", {
            class: "progress-bar progress-bar-info",
            role: "progressbar",
            "aria-valuenow": $data.progress,
            "aria-valuemin": "0",
            "aria-valuemax": "100",
            style: vue.normalizeStyle({ width: $data.progress + "%" })
          }, null, 12, _hoisted_8)
        ])) : vue.createCommentVNode("v-if", true),
        $options.files.length ? (vue.openBlock(), vue.createElementBlock("ul", _hoisted_9, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($options.files, (file) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                key: file.guid,
                class: vue.normalizeClass(["file-preview-item mime", file.mimeIcon]),
                "data-preview-guid": file.guid
              }, [
                vue.createElementVNode("span", _hoisted_11, [
                  vue.createTextVNode(
                    vue.toDisplayString(file.fileName) + "  ",
                    1
                    /* TEXT */
                  ),
                  vue.createElementVNode("span", {
                    class: "file_upload_remove_link",
                    role: "button",
                    tabindex: "0",
                    "aria-label": $options.removeLabel,
                    onClick: ($event) => $options.removeFile(file),
                    onKeydown: vue.withKeys(vue.withModifiers(($event) => $options.removeFile(file), ["prevent"]), ["enter"])
                  }, [..._cache[10] || (_cache[10] = [
                    vue.createElementVNode(
                      "i",
                      {
                        class: "ti ti-trash",
                        "aria-hidden": "true"
                      },
                      null,
                      -1
                      /* CACHED */
                    ),
                    vue.createTextVNode(
                      " ",
                      -1
                      /* CACHED */
                    )
                  ])], 40, _hoisted_12)
                ])
              ], 10, _hoisted_10);
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ])) : vue.createCommentVNode("v-if", true),
        $options.allMessages.length ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 2,
          id: _ctx.errorId,
          class: "invalid-feedback d-block"
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($options.allMessages, (message, index) => {
              return vue.openBlock(), vue.createElementBlock(
                "div",
                { key: index },
                vue.toDisplayString(message),
                1
                /* TEXT */
              );
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ], 8, _hoisted_13)) : vue.createCommentVNode("v-if", true)
      ],
      32
      /* NEED_HYDRATION */
    )), [
      [_directive_additions]
    ]);
  }
  const C28 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  let uid = 0;
  const _sfc_main = {
    name: "ViewSwitch",
    props: {
      modelValue: { type: String, required: true },
      options: { type: Array, required: true },
      label: { type: String, default: null }
    },
    emits: ["update:modelValue"],
    data() {
      return {
        uid: `view-switch-${++uid}`
      };
    },
    methods: {
      choose(value) {
        if (value !== this.modelValue) {
          this.$emit("update:modelValue", value);
        }
      }
    }
  };
  const _hoisted_1 = ["aria-label"];
  const _hoisted_2 = ["id", "name", "value", "checked", "onChange"];
  const _hoisted_3 = ["for", "title"];
  const _hoisted_4 = { class: "visually-hidden" };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", {
      class: "btn-group view-switch",
      role: "group",
      "aria-label": $props.label
    }, [
      (vue.openBlock(true), vue.createElementBlock(
        vue.Fragment,
        null,
        vue.renderList($props.options, (option) => {
          return vue.openBlock(), vue.createElementBlock(
            vue.Fragment,
            {
              key: option.value
            },
            [
              vue.createElementVNode("input", {
                id: `${$data.uid}-${option.value}`,
                type: "radio",
                class: "btn-check",
                name: $data.uid,
                value: option.value,
                checked: option.value === $props.modelValue,
                autocomplete: "off",
                onChange: ($event) => $options.choose(option.value)
              }, null, 40, _hoisted_2),
              vue.createElementVNode("label", {
                class: "btn btn-ghost btn-icon-only",
                for: `${$data.uid}-${option.value}`,
                title: option.label
              }, [
                vue.createElementVNode(
                  "i",
                  {
                    class: vue.normalizeClass("ti ti-" + option.icon),
                    "aria-hidden": "true"
                  },
                  null,
                  2
                  /* CLASS */
                ),
                vue.createElementVNode(
                  "span",
                  _hoisted_4,
                  vue.toDisplayString(option.label),
                  1
                  /* TEXT */
                )
              ], 8, _hoisted_3)
            ],
            64
            /* STABLE_FRAGMENT */
          );
        }),
        128
        /* KEYED_FRAGMENT */
      ))
    ], 8, _hoisted_1);
  }
  const C29 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("CardSkeleton", C0);
  vue$1.register("CheckboxField", C1);
  vue$1.register("ContentCard", C2);
  vue$1.register("DataTable", C3);
  vue$1.register("DropZone", C4);
  vue$1.register("DropdownMenu", C5);
  vue$1.register("EntityBrowser", C6);
  vue$1.register("EntityGrid", C7);
  vue$1.register("ExtensionSlot", C8);
  vue$1.register("FilterBar", C9);
  vue$1.register("FilterPicker", C10);
  vue$1.register("FilterSelect", C11);
  vue$1.register("HumHubForm", C12);
  vue$1.register("LegacyFormWrapper", C13);
  vue$1.register("PageHeader", C14);
  vue$1.register("PathBar", C15);
  vue$1.register("PickerFilterControl", C16);
  vue$1.register("ProgressFrame", C17);
  vue$1.register("RichTextField", C18);
  vue$1.register("RichTextOutput", C19);
  vue$1.register("SelectField", C20);
  vue$1.register("SelectionMenu", C21);
  vue$1.register("StatusBar", C22);
  vue$1.register("SubmitButton", C23);
  vue$1.register("TextField", C24);
  vue$1.register("TextareaField", C25);
  vue$1.register("TileGrid", C26);
  vue$1.register("UiModal", C27);
  vue$1.register("UploadField", C28);
  vue$1.register("ViewSwitch", C29);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.core.vue.js.map
