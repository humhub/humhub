/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from core/vue/ via `grunt build-vue --module=core`.
 * See docs/develop/ui-js-vuejs.md
 */
(function(vue$1, vue) {
  "use strict";
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
  const _export_sfc = (sfc, props) => {
    const target = sfc.__vccOpts || sfc;
    for (const [key, val] of props) {
      target[key] = val;
    }
    return target;
  };
  const _sfc_main$d = {
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
  const _hoisted_1$c = { class: "form-check" };
  const _hoisted_2$9 = ["id", "name", "disabled", "aria-required", "aria-invalid", "aria-describedby"];
  const _hoisted_3$7 = ["for"];
  const _hoisted_4$6 = ["id"];
  const _hoisted_5$4 = ["id"];
  function _sfc_render$d(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: vue.normalizeClass(["mb-3", [`field-${_ctx.fieldId}`, { required: _ctx.required }]])
      },
      [
        vue.createElementVNode("div", _hoisted_1$c, [
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
          }, null, 10, _hoisted_2$9), [
            [vue.vModelCheckbox, $options.internalValue]
          ]),
          _ctx.label ? (vue.openBlock(), vue.createElementBlock("label", {
            key: 0,
            for: _ctx.fieldId,
            class: "form-check-label"
          }, vue.toDisplayString(_ctx.label), 9, _hoisted_3$7)) : vue.createCommentVNode("v-if", true),
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
          ], 8, _hoisted_4$6)) : vue.createCommentVNode("v-if", true),
          _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
            key: 2,
            id: _ctx.hintId,
            class: "form-text text-muted"
          }, vue.toDisplayString(_ctx.hint), 9, _hoisted_5$4)) : vue.createCommentVNode("v-if", true)
        ])
      ],
      2
      /* CLASS */
    );
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$d, [["render", _sfc_render$d]]);
  const _sfc_main$c = {
    props: {
      toggleAriaLabel: { type: String, required: true },
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
    mounted() {
      this.$refs.toggle.addEventListener("show.bs.dropdown", this.onShow);
    },
    beforeUnmount() {
      this.$refs.toggle.removeEventListener("show.bs.dropdown", this.onShow);
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
      onShow() {
        this.$emit("open");
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
  const _hoisted_1$b = { class: "nav-item dropdown" };
  const _hoisted_2$8 = ["aria-label"];
  const _hoisted_3$6 = { key: 0 };
  const _hoisted_4$5 = { class: "dropdown-item disabled d-flex align-items-center gap-2" };
  const _hoisted_5$3 = { role: "status" };
  const _hoisted_6$3 = ["innerHTML"];
  const _hoisted_7$1 = { key: 1 };
  const _hoisted_8$1 = {
    key: 1,
    class: "dropdown-divider"
  };
  const _hoisted_9$1 = ["href", "onClick"];
  const _hoisted_10$1 = ["href", "onClick"];
  function _sfc_render$c(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return vue.openBlock(), vue.createElementBlock(
      "ul",
      {
        class: vue.normalizeClass($props.rootClass)
      },
      [
        vue.createElementVNode("li", _hoisted_1$b, [
          vue.createElementVNode("a", {
            ref: "toggle",
            href: "#",
            class: vue.normalizeClass($props.toggleClass),
            "data-bs-toggle": "dropdown",
            role: "button",
            "aria-haspopup": "true",
            "aria-expanded": "false",
            "aria-label": $props.toggleAriaLabel
          }, [
            vue.renderSlot(_ctx.$slots, "toggle")
          ], 10, _hoisted_2$8),
          vue.createElementVNode(
            "ul",
            {
              class: vue.normalizeClass(["dropdown-menu", { "dropdown-menu-end": $props.alignEnd }])
            },
            [
              vue.renderSlot(_ctx.$slots, "default"),
              $props.loading ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_3$6, [
                vue.createElementVNode("span", _hoisted_4$5, [
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
                    _hoisted_5$3,
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
                      }, null, 8, _hoisted_6$3)), [
                        [_directive_additions]
                      ]) : (vue.openBlock(), vue.createElementBlock("li", _hoisted_7$1, [
                        entry.component ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(entry.component), {
                          key: 0,
                          context: $props.context
                        }, null, 8, ["context"])) : entry.divider ? (vue.openBlock(), vue.createElementBlock("hr", _hoisted_8$1)) : entry.icon ? (vue.openBlock(), vue.createElementBlock("a", vue.mergeProps({
                          key: 2,
                          ref_for: true
                        }, entry.htmlOptions, {
                          href: entry.url || "#",
                          class: "dropdown-item d-flex align-items-center gap-2",
                          onClick: ($event) => $options.onEntryClick(entry, $event)
                        }), [
                          vue.createElementVNode(
                            "i",
                            {
                              class: vue.normalizeClass("fa fa-" + entry.icon),
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
                        ], 16, _hoisted_9$1)) : (vue.openBlock(), vue.createElementBlock("a", vue.mergeProps({
                          key: 3,
                          ref_for: true
                        }, entry.htmlOptions, {
                          href: entry.url || "#",
                          class: "dropdown-item",
                          onClick: ($event) => $options.onEntryClick(entry, $event)
                        }), vue.toDisplayString($options.resolveLabel(entry)), 17, _hoisted_10$1))
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
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main$c, [["render", _sfc_render$c]]);
  const _sfc_main$b = {
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
  function _sfc_render$b(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(true), vue.createElementBlock(
      vue.Fragment,
      null,
      vue.renderList($options.visibleEntries, (entry) => {
        return vue.openBlock(), vue.createBlock(
          vue.resolveDynamicComponent(entry.component),
          vue.mergeProps({
            key: entry.component
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
  const C2 = /* @__PURE__ */ _export_sfc(_sfc_main$b, [["render", _sfc_render$b]]);
  const _sfc_main$a = {
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
  const _hoisted_1$a = {
    key: 0,
    class: "alert alert-danger error-summary"
  };
  function _sfc_render$a(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "form",
      {
        onSubmit: _cache[0] || (_cache[0] = vue.withModifiers((...args) => $options.onSubmit && $options.onSubmit(...args), ["prevent"]))
      },
      [
        $options.unownedErrorMessages.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$a, [
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
  const C3 = /* @__PURE__ */ _export_sfc(_sfc_main$a, [["render", _sfc_render$a]]);
  const FORM_TOKEN = "__VUEFORM__";
  const RICHTEXT_SELECTOR = '[data-ui-widget="ui.richtext.prosemirror.RichTextEditor"]';
  const RICHTEXT_COMPONENT_DATA = "humhub-ui-richtexteditor";
  let instanceCounter = 0;
  const _sfc_main$9 = {
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
  const _hoisted_1$9 = ["innerHTML"];
  function _sfc_render$9(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", { innerHTML: $options.processedShell }, null, 8, _hoisted_1$9)), [
      [_directive_additions]
    ]);
  }
  const C4 = /* @__PURE__ */ _export_sfc(_sfc_main$9, [["render", _sfc_render$9]]);
  const _sfc_main$8 = {
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
  const _hoisted_1$8 = ["id"];
  function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
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
        ], 8, _hoisted_1$8)) : vue.createCommentVNode("v-if", true)
      ],
      64
      /* STABLE_FRAGMENT */
    );
  }
  const C5 = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["render", _sfc_render$8]]);
  const OEMBED_URL_ENTITY_MAP = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" };
  const _sfc_main$7 = {
    props: {
      message: { type: String, default: null },
      renderOptions: { type: Object, default: () => ({}) }
    },
    computed: {
      envelopeAttrs() {
        const attrs = {};
        Object.entries(this.renderOptions || {}).forEach(([key, value]) => {
          if (key === "oembeds" || value === false || value === null || value === void 0) {
            return;
          }
          if (value === true) {
            attrs["data-" + key] = "";
            return;
          }
          attrs["data-" + key] = typeof value === "object" ? JSON.stringify(value) : value;
        });
        return attrs;
      },
      oembeds() {
        return this.renderOptions && this.renderOptions.oembeds || {};
      },
      hasOembeds() {
        return Object.keys(this.oembeds).length > 0;
      },
      /**
       * Serialized `renderOptions`, reused as (part of) the `:key`s described in the class
       * docblock's "`:key`-forced remount on content change" section above.
       */
      renderOptionsKey() {
        return JSON.stringify(this.renderOptions || {});
      },
      /**
       * @see the class docblock's "`:key`-forced remount on content change" section above.
       * NUL-separated rather than plain concatenation: `message` is free-form user text, and
       * a plain join could otherwise collide across the message/renderOptions boundary (two
       * different (message, renderOptions) pairs producing the same joined string). A NUL
       * byte cannot occur in `message` (always a JSON string round-tripped from the server).
       */
      envelopeKey() {
        return this.message + "\0" + this.renderOptionsKey;
      }
    },
    methods: {
      /**
       * Mirrors `util.string.escapeHtml(value, true)` in
       * `protected/humhub/resources/js/humhub/humhub.util.js` byte-for-byte (its "simple"
       * variant - second arg `true` - which escapes only `& < > " '`, leaving backtick/`=`/`/`
       * alone). `humhub.oembed.js`'s `findSnippetByUrl()` locates this fragment by querying
       * `[data-oembed="' + $.escapeSelector(util.string.escapeHtml(url, true)) + '"]` - so the
       * `data-oembed` attribute rendered here MUST equal that exact escaped string, not the
       * raw url, or the lookup silently fails for any url containing one of those five
       * characters (a `&` in a query string being the common case) and the embed degrades to
       * a plain link with no live preview/lazy-load behavior. Kept as a tiny local function -
       * rather than reaching into `@humhub/vue`/`humhub.modules.util` - because it is a pure,
       * dependency-free string transform and no sibling island component reaches into legacy
       * modules directly either.
       */
      escapeOembedUrl(url) {
        return String(url).replace(/[&<>"']/g, (char) => OEMBED_URL_ENTITY_MAP[char]);
      }
    }
  };
  const _hoisted_1$7 = { key: 0 };
  const _hoisted_2$7 = ["data-oembed", "innerHTML"];
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return $props.message ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$7, [
      (vue.openBlock(), vue.createElementBlock(
        "div",
        vue.mergeProps({ key: $options.envelopeKey }, $options.envelopeAttrs),
        vue.toDisplayString($props.message),
        17
        /* TEXT, FULL_PROPS */
      )),
      $options.hasOembeds ? (vue.openBlock(), vue.createElementBlock("div", {
        key: $options.renderOptionsKey,
        class: "richtext-oembed-container",
        style: { "display": "none" }
      }, [
        (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          null,
          vue.renderList($options.oembeds, (html, url) => {
            return vue.openBlock(), vue.createElementBlock("div", {
              key: url,
              "data-oembed": $options.escapeOembedUrl(url),
              innerHTML: html
            }, null, 8, _hoisted_2$7);
          }),
          128
          /* KEYED_FRAGMENT */
        ))
      ])) : vue.createCommentVNode("v-if", true)
    ])), [
      [_directive_additions]
    ]) : vue.createCommentVNode("v-if", true);
  }
  const C6 = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$7]]);
  const _sfc_main$6 = {
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
  const _hoisted_1$6 = ["for"];
  const _hoisted_2$6 = ["id", "name", "disabled", "aria-required", "aria-invalid", "aria-describedby"];
  const _hoisted_3$5 = {
    key: 0,
    value: ""
  };
  const _hoisted_4$4 = ["value"];
  const _hoisted_5$2 = ["id"];
  const _hoisted_6$2 = ["id"];
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
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
        }, vue.toDisplayString(_ctx.label), 9, _hoisted_1$6)) : vue.createCommentVNode("v-if", true),
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
            _hoisted_3$5,
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
              }, vue.toDisplayString(option.label), 9, _hoisted_4$4);
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ], 10, _hoisted_2$6), [
          [vue.vModelSelect, $options.internalValue]
        ]),
        _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 1,
          id: _ctx.hintId,
          class: "form-text text-muted"
        }, vue.toDisplayString(_ctx.hint), 9, _hoisted_5$2)) : vue.createCommentVNode("v-if", true),
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
        ], 8, _hoisted_6$2)) : vue.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C7 = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  const TRANSITION_MS = 500;
  const AUTOCLOSE = {
    info: 6e3,
    success: 2e3,
    warn: 1e4,
    error: 0
  };
  const ICONS = {
    info: "fa fa-info-circle info",
    success: "fa fa-check-circle success",
    warn: "fa fa-exclamation-triangle warning",
    error: "fa fa-exclamation-circle error"
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
  const _sfc_main$5 = {
    data() {
      return {
        entry: null,
        visible: false,
        detailsOpen: false
      };
    },
    computed: {
      iconClass() {
        return ICONS[this.entry.level] || ICONS.info;
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
        this.$nextTick(() => {
          if (this.$el && typeof this.$el.getBoundingClientRect === "function") {
            void this.$el.getBoundingClientRect().height;
          }
          this.visible = true;
        });
        const closeAfter = this.autoCloseDelay(entry);
        if (closeAfter > 0) {
          this.closeTimer = setTimeout(() => this.startHide(), TRANSITION_MS + closeAfter);
        }
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
      clearTimers() {
        if (this.closeTimer) {
          clearTimeout(this.closeTimer);
          this.closeTimer = null;
        }
        if (this.hideTimer) {
          clearTimeout(this.hideTimer);
          this.hideTimer = null;
        }
      }
    }
  };
  const _hoisted_1$5 = { class: "status-bar-content" };
  const _hoisted_2$5 = {
    key: 1,
    class: "status-bar-details"
  };
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    return $data.entry ? (vue.openBlock(), vue.createElementBlock(
      "div",
      {
        key: 0,
        class: vue.normalizeClass(["status-bar-body", { "status-bar-visible": $data.visible }])
      },
      [
        vue.createElementVNode("div", _hoisted_1$5, [
          vue.createElementVNode("a", {
            class: "status-bar-close float-end",
            onClick: _cache[0] || (_cache[0] = (...args) => $options.close && $options.close(...args))
          }, "×"),
          vue.createElementVNode(
            "i",
            {
              class: vue.normalizeClass($options.iconClass)
            },
            null,
            2
            /* CLASS */
          ),
          vue.createElementVNode(
            "span",
            {
              class: vue.normalizeClass({ "status-bar-toggle": $options.hasDetails }),
              onClick: _cache[1] || (_cache[1] = (...args) => $options.toggleDetails && $options.toggleDetails(...args))
            },
            vue.toDisplayString($data.entry.message),
            3
            /* TEXT, CLASS */
          ),
          $options.hasDetails ? (vue.openBlock(), vue.createElementBlock("a", {
            key: 0,
            class: "showMore",
            onClick: _cache[2] || (_cache[2] = (...args) => $options.toggleDetails && $options.toggleDetails(...args))
          }, [
            vue.createElementVNode(
              "i",
              {
                class: vue.normalizeClass($data.detailsOpen ? "fa fa-angle-down" : "fa fa-angle-up")
              },
              null,
              2
              /* CLASS */
            )
          ])) : vue.createCommentVNode("v-if", true),
          $data.detailsOpen ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$5, [
            vue.createElementVNode(
              "pre",
              null,
              vue.toDisplayString($options.detailsText),
              1
              /* TEXT */
            )
          ])) : vue.createCommentVNode("v-if", true)
        ])
      ],
      2
      /* CLASS */
    )) : vue.createCommentVNode("v-if", true);
  }
  const C8 = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const _sfc_main$4 = {
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
  const _hoisted_1$4 = ["disabled"];
  const _hoisted_2$4 = {
    key: 0,
    class: "hh-loader text-center"
  };
  const _hoisted_3$4 = {
    role: "status",
    class: "visually-hidden"
  };
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("button", {
      type: "submit",
      disabled: $options.isDisabled
    }, [
      $options.showLoader ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$4, [
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
          _hoisted_3$4,
          vue.toDisplayString($options.loadingText),
          1
          /* TEXT */
        )
      ])) : vue.renderSlot(_ctx.$slots, "default", {}, void 0, void 0, 1)
    ], 8, _hoisted_1$4);
  }
  const C9 = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = {
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
  const _hoisted_1$3 = ["for"];
  const _hoisted_2$3 = ["id", "name", "type", "placeholder", "disabled", "aria-required", "aria-invalid", "aria-describedby"];
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
        }, null, 10, _hoisted_2$3), [
          [vue.vModelDynamic, $options.internalValue]
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
  const C10 = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = {
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
  const _hoisted_1$2 = ["for"];
  const _hoisted_2$2 = ["id", "name", "placeholder", "disabled", "rows", "aria-required", "aria-invalid", "aria-describedby"];
  const _hoisted_3$2 = ["id"];
  const _hoisted_4$2 = ["id"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
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
        }, vue.toDisplayString(_ctx.label), 9, _hoisted_1$2)) : vue.createCommentVNode("v-if", true),
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
        }, null, 10, _hoisted_2$2), [
          [vue.vModelText, $options.internalValue]
        ]),
        _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 1,
          id: _ctx.hintId,
          class: "form-text text-muted"
        }, vue.toDisplayString(_ctx.hint), 9, _hoisted_3$2)) : vue.createCommentVNode("v-if", true),
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
        ], 8, _hoisted_4$2)) : vue.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C11 = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  let uidSeq = 0;
  const _sfc_main$1 = {
    name: "UiModal",
    props: {
      show: { type: Boolean, default: false },
      title: { type: String, default: null },
      size: {
        type: String,
        default: "normal",
        validator: (value) => ["small", "normal", "large"].includes(value)
      },
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
  const _hoisted_1$1 = ["aria-labelledby"];
  const _hoisted_2$1 = { class: "modal-content" };
  const _hoisted_3$1 = { class: "modal-header" };
  const _hoisted_4$1 = ["id"];
  const _hoisted_5$1 = { class: "modal-body" };
  const _hoisted_6$1 = {
    key: 0,
    class: "modal-footer"
  };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
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
            class: vue.normalizeClass(["modal-dialog", $options.sizeClass])
          },
          [
            vue.createElementVNode("div", _hoisted_2$1, [
              vue.createElementVNode("div", _hoisted_3$1, [
                vue.renderSlot(_ctx.$slots, "header", { titleId: $data.titleId }, () => [
                  vue.createElementVNode("h5", {
                    class: "modal-title",
                    id: $data.titleId
                  }, vue.toDisplayString($props.title), 9, _hoisted_4$1),
                  vue.createElementVNode("button", {
                    type: "button",
                    class: "btn-close",
                    "aria-label": "Close",
                    onClick: _cache[0] || (_cache[0] = (...args) => $options.requestClose && $options.requestClose(...args))
                  })
                ])
              ]),
              vue.createElementVNode("div", _hoisted_5$1, [
                vue.renderSlot(_ctx.$slots, "default")
              ]),
              _ctx.$slots.footer ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$1, [
                vue.renderSlot(_ctx.$slots, "footer")
              ])) : vue.createCommentVNode("v-if", true)
            ])
          ],
          2
          /* CLASS */
        )
      ], 42, _hoisted_1$1)) : vue.createCommentVNode("v-if", true),
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
  const C12 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
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
  const _sfc_main = {
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
  const _hoisted_1 = { class: "btn-group btn-group-sm" };
  const _hoisted_2 = ["aria-disabled", "title", "data-bs-title"];
  const _hoisted_3 = ["multiple", "accept", "disabled"];
  const _hoisted_4 = {
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
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
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
          vue.createElementVNode("div", _hoisted_1, [
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
                  class: "fa fa-cloud-upload",
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
              }, null, 40, _hoisted_3)
            ], 40, _hoisted_2),
            $props.handlersHtml ? (vue.openBlock(), vue.createElementBlock(
              vue.Fragment,
              { key: 0 },
              [
                vue.createElementVNode("button", _hoisted_4, [
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
                        class: "fa fa-trash-o",
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
  const C13 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("CheckboxField", C0);
  vue$1.register("DropdownMenu", C1);
  vue$1.register("ExtensionSlot", C2);
  vue$1.register("HumHubForm", C3);
  vue$1.register("LegacyFormWrapper", C4);
  vue$1.register("RichTextField", C5);
  vue$1.register("RichTextOutput", C6);
  vue$1.register("SelectField", C7);
  vue$1.register("StatusBar", C8);
  vue$1.register("SubmitButton", C9);
  vue$1.register("TextField", C10);
  vue$1.register("TextareaField", C11);
  vue$1.register("UiModal", C12);
  vue$1.register("UploadField", C13);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.core.vue.js.map
