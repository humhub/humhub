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
  const _sfc_main$a = {
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
  const _hoisted_1$8 = { class: "form-check" };
  const _hoisted_2$6 = ["id", "name", "disabled", "aria-required", "aria-invalid", "aria-describedby"];
  const _hoisted_3$5 = ["for"];
  const _hoisted_4$4 = ["id"];
  const _hoisted_5$2 = ["id"];
  function _sfc_render$a(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: vue.normalizeClass(["mb-3", [`field-${_ctx.fieldId}`, { required: _ctx.required }]])
      },
      [
        vue.createElementVNode("div", _hoisted_1$8, [
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
          }, null, 10, _hoisted_2$6), [
            [vue.vModelCheckbox, $options.internalValue]
          ]),
          _ctx.label ? (vue.openBlock(), vue.createElementBlock("label", {
            key: 0,
            for: _ctx.fieldId,
            class: "form-check-label"
          }, vue.toDisplayString(_ctx.label), 9, _hoisted_3$5)) : vue.createCommentVNode("v-if", true),
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
          ], 8, _hoisted_4$4)) : vue.createCommentVNode("v-if", true),
          _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
            key: 2,
            id: _ctx.hintId,
            class: "form-text text-muted"
          }, vue.toDisplayString(_ctx.hint), 9, _hoisted_5$2)) : vue.createCommentVNode("v-if", true)
        ])
      ],
      2
      /* CLASS */
    );
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$a, [["render", _sfc_render$a]]);
  const _sfc_main$9 = {
    props: {
      toggleAriaLabel: { type: String, required: true },
      alignEnd: { type: Boolean, default: true },
      toggleClass: { type: String, default: "nav-link dropdown-toggle" },
      menuId: { type: String, default: null },
      entries: { type: Array, default: () => [] },
      context: { type: Object, default: () => ({}) }
    },
    computed: {
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
      resolveLabel(entry) {
        return typeof entry.label === "function" ? entry.label(this.context) : entry.label;
      },
      onEntryClick(entry) {
        if (typeof entry.onClick === "function") {
          entry.onClick(this.context);
        }
      }
    }
  };
  const _hoisted_1$7 = { class: "nav nav-pills preferences" };
  const _hoisted_2$5 = { class: "nav-item dropdown" };
  const _hoisted_3$4 = ["aria-label"];
  const _hoisted_4$3 = ["onClick"];
  const _hoisted_5$1 = ["onClick"];
  function _sfc_render$9(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("ul", _hoisted_1$7, [
      vue.createElementVNode("li", _hoisted_2$5, [
        vue.createElementVNode("a", {
          href: "#",
          class: vue.normalizeClass($props.toggleClass),
          "data-bs-toggle": "dropdown",
          role: "button",
          "aria-haspopup": "true",
          "aria-expanded": "false",
          "aria-label": $props.toggleAriaLabel
        }, null, 10, _hoisted_3$4),
        vue.createElementVNode(
          "ul",
          {
            class: vue.normalizeClass(["dropdown-menu", { "dropdown-menu-end": $props.alignEnd }])
          },
          [
            vue.renderSlot(_ctx.$slots, "default"),
            (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              null,
              vue.renderList($options.resolvedEntries, (entry) => {
                return vue.openBlock(), vue.createElementBlock("li", {
                  key: entry.id
                }, [
                  entry.component ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(entry.component), {
                    key: 0,
                    context: $props.context
                  }, null, 8, ["context"])) : entry.icon ? (vue.openBlock(), vue.createElementBlock("a", {
                    key: 1,
                    href: "#",
                    class: "dropdown-item d-flex align-items-center gap-2",
                    onClick: vue.withModifiers(($event) => $options.onEntryClick(entry), ["prevent"])
                  }, [
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
                  ], 8, _hoisted_4$3)) : (vue.openBlock(), vue.createElementBlock("a", {
                    key: 2,
                    href: "#",
                    class: "dropdown-item",
                    onClick: vue.withModifiers(($event) => $options.onEntryClick(entry), ["prevent"])
                  }, vue.toDisplayString($options.resolveLabel(entry)), 9, _hoisted_5$1))
                ]);
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ],
          2
          /* CLASS */
        )
      ])
    ]);
  }
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main$9, [["render", _sfc_render$9]]);
  const _sfc_main$8 = {
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
  function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
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
  const C2 = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["render", _sfc_render$8]]);
  const _sfc_main$7 = {
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
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "form",
      {
        onSubmit: _cache[0] || (_cache[0] = vue.withModifiers((...args) => $options.onSubmit && $options.onSubmit(...args), ["prevent"]))
      },
      [
        vue.renderSlot(_ctx.$slots, "default")
      ],
      32
      /* NEED_HYDRATION */
    );
  }
  const C3 = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$7]]);
  const FORM_TOKEN = "__VUEFORM__";
  const RICHTEXT_SELECTOR = '[data-ui-widget="ui.richtext.prosemirror.RichTextEditor"]';
  const RICHTEXT_COMPONENT_DATA = "humhub-ui-richtexteditor";
  const UPLOAD_SELECTOR = ".vueform-upload";
  const UPLOAD_COMPONENT_DATA = "humhub-file-upload";
  let instanceCounter = 0;
  const _sfc_main$6 = {
    props: {
      shellHtml: { type: String, required: true }
    },
    data() {
      return {
        // Module-scope counter (not Math.random()) so builds/output stay
        // deterministic; unique per mounted instance on the page.
        instanceId: "vueform-" + ++instanceCounter
      };
    },
    computed: {
      processedShell() {
        return this.shellHtml.split(FORM_TOKEN).join(this.instanceId);
      }
    },
    methods: {
      getEditorInstance() {
        const node = this.$el.querySelector(RICHTEXT_SELECTOR);
        return node ? jQuery(node).data(RICHTEXT_COMPONENT_DATA) : null;
      },
      getUploadInstance() {
        const node = this.$el.querySelector(UPLOAD_SELECTOR);
        return node ? jQuery(node).data(UPLOAD_COMPONENT_DATA) : null;
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
      /** Empties the editor and resets the upload preview/file inputs. */
      clear() {
        const editor = this.getEditorInstance();
        if (editor) {
          editor.$.trigger("clear");
        }
        const upload = this.getUploadInstance();
        if (upload) {
          upload.reset();
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
      },
      /** @returns {string[]} guids of files currently attached via the upload widget. */
      getFileGuids() {
        const upload = this.getUploadInstance();
        if (!upload) {
          return [];
        }
        const name = upload.options.uploadSubmitName;
        return jQuery(this.$el).find('input[name="' + name + '"]').map(function() {
          return this.value;
        }).get();
      }
    }
  };
  const _hoisted_1$6 = ["innerHTML"];
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", { innerHTML: $options.processedShell }, null, 8, _hoisted_1$6)), [
      [_directive_additions]
    ]);
  }
  const C4 = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  const _sfc_main$5 = {
    mixins: [fieldMixin],
    props: {
      shellHtml: { type: String, required: true }
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
      getFileGuids() {
        return this.$refs.wrapper.getFileGuids();
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
  const _hoisted_1$5 = ["id"];
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_LegacyFormWrapper = vue.resolveComponent("LegacyFormWrapper");
    return vue.openBlock(), vue.createElementBlock(
      vue.Fragment,
      null,
      [
        vue.createVNode(_component_LegacyFormWrapper, {
          ref: "wrapper",
          "shell-html": $props.shellHtml
        }, null, 8, ["shell-html"]),
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
        ], 8, _hoisted_1$5)) : vue.createCommentVNode("v-if", true)
      ],
      64
      /* STABLE_FRAGMENT */
    );
  }
  const C5 = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const OEMBED_URL_ENTITY_MAP = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" };
  const _sfc_main$4 = {
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
  const _hoisted_1$4 = { key: 0 };
  const _hoisted_2$4 = ["data-oembed", "innerHTML"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return $props.message ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$4, [
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
            }, null, 8, _hoisted_2$4);
          }),
          128
          /* KEYED_FRAGMENT */
        ))
      ])) : vue.createCommentVNode("v-if", true)
    ])), [
      [_directive_additions]
    ]) : vue.createCommentVNode("v-if", true);
  }
  const C6 = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = {
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
  const _hoisted_1$3 = ["for"];
  const _hoisted_2$3 = ["id", "name", "disabled", "aria-required", "aria-invalid", "aria-describedby"];
  const _hoisted_3$3 = {
    key: 0,
    value: ""
  };
  const _hoisted_4$2 = ["value"];
  const _hoisted_5 = ["id"];
  const _hoisted_6 = ["id"];
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
            _hoisted_3$3,
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
              }, vue.toDisplayString(option.label), 9, _hoisted_4$2);
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ], 10, _hoisted_2$3), [
          [vue.vModelSelect, $options.internalValue]
        ]),
        _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 1,
          id: _ctx.hintId,
          class: "form-text text-muted"
        }, vue.toDisplayString(_ctx.hint), 9, _hoisted_5)) : vue.createCommentVNode("v-if", true),
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
        ], 8, _hoisted_6)) : vue.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C7 = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = {
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
  const _hoisted_1$2 = ["disabled"];
  const _hoisted_2$2 = {
    key: 0,
    class: "hh-loader text-center"
  };
  const _hoisted_3$2 = {
    role: "status",
    class: "visually-hidden"
  };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("button", {
      type: "submit",
      disabled: $options.isDisabled
    }, [
      $options.showLoader ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$2, [
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
          _hoisted_3$2,
          vue.toDisplayString($options.loadingText),
          1
          /* TEXT */
        )
      ])) : vue.renderSlot(_ctx.$slots, "default", {}, void 0, void 0, 1)
    ], 8, _hoisted_1$2);
  }
  const C8 = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = {
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
  const _hoisted_1$1 = ["for"];
  const _hoisted_2$1 = ["id", "name", "type", "placeholder", "disabled", "aria-required", "aria-invalid", "aria-describedby"];
  const _hoisted_3$1 = ["id"];
  const _hoisted_4$1 = ["id"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
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
        }, vue.toDisplayString(_ctx.label), 9, _hoisted_1$1)) : vue.createCommentVNode("v-if", true),
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
        }, null, 10, _hoisted_2$1), [
          [vue.vModelDynamic, $options.internalValue]
        ]),
        _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 1,
          id: _ctx.hintId,
          class: "form-text text-muted"
        }, vue.toDisplayString(_ctx.hint), 9, _hoisted_3$1)) : vue.createCommentVNode("v-if", true),
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
        ], 8, _hoisted_4$1)) : vue.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C9 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = {
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
  const _hoisted_1 = ["for"];
  const _hoisted_2 = ["id", "name", "placeholder", "disabled", "rows", "aria-required", "aria-invalid", "aria-describedby"];
  const _hoisted_3 = ["id"];
  const _hoisted_4 = ["id"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
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
        }, vue.toDisplayString(_ctx.label), 9, _hoisted_1)) : vue.createCommentVNode("v-if", true),
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
        }, null, 10, _hoisted_2), [
          [vue.vModelText, $options.internalValue]
        ]),
        _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 1,
          id: _ctx.hintId,
          class: "form-text text-muted"
        }, vue.toDisplayString(_ctx.hint), 9, _hoisted_3)) : vue.createCommentVNode("v-if", true),
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
        ], 8, _hoisted_4)) : vue.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C10 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("CheckboxField", C0);
  vue$1.register("DropdownMenu", C1);
  vue$1.register("ExtensionSlot", C2);
  vue$1.register("HumHubForm", C3);
  vue$1.register("LegacyFormWrapper", C4);
  vue$1.register("RichTextField", C5);
  vue$1.register("RichTextOutput", C6);
  vue$1.register("SelectField", C7);
  vue$1.register("SubmitButton", C8);
  vue$1.register("TextField", C9);
  vue$1.register("TextareaField", C10);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.core.vue.js.map
