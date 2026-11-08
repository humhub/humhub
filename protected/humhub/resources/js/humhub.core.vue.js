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
  const _sfc_main$C = {
    name: "CardSkeleton"
  };
  const _hoisted_1$z = {
    class: "card entity-card entity-card-skeleton placeholder-glow",
    "aria-hidden": "true"
  };
  function _sfc_render$C(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$z, [..._cache[0] || (_cache[0] = [
      vue.createStaticVNode('<div class="entity-card-cover entity-card-cover-plain"><span class="placeholder entity-card-skeleton-image"></span></div><div class="entity-card-header"><span class="placeholder entity-card-skeleton-title"></span><span class="placeholder entity-card-skeleton-subtitle"></span></div><div class="entity-card-body"><span class="placeholder entity-card-skeleton-line"></span><span class="placeholder entity-card-skeleton-line entity-card-skeleton-line-short"></span></div><div class="entity-card-footer"><span class="placeholder entity-card-skeleton-action"></span><span class="placeholder entity-card-skeleton-icon"></span></div>', 4)
    ])]);
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$C, [["render", _sfc_render$C]]);
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
  const _sfc_main$B = {
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
  const _hoisted_1$y = { class: "form-check" };
  const _hoisted_2$t = ["id", "name", "disabled", "aria-required", "aria-invalid", "aria-describedby"];
  const _hoisted_3$q = ["for"];
  const _hoisted_4$m = ["id"];
  const _hoisted_5$i = ["id"];
  function _sfc_render$B(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: vue.normalizeClass(["mb-3", [`field-${_ctx.fieldId}`, { required: _ctx.required }]])
      },
      [
        vue.createElementVNode("div", _hoisted_1$y, [
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
          }, null, 10, _hoisted_2$t), [
            [vue.vModelCheckbox, $options.internalValue]
          ]),
          _ctx.label ? (vue.openBlock(), vue.createElementBlock("label", {
            key: 0,
            for: _ctx.fieldId,
            class: "form-check-label"
          }, vue.toDisplayString(_ctx.label), 9, _hoisted_3$q)) : vue.createCommentVNode("v-if", true),
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
          ], 8, _hoisted_4$m)) : vue.createCommentVNode("v-if", true),
          _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
            key: 2,
            id: _ctx.hintId,
            class: "form-text text-muted"
          }, vue.toDisplayString(_ctx.hint), 9, _hoisted_5$i)) : vue.createCommentVNode("v-if", true)
        ])
      ],
      2
      /* CLASS */
    );
  }
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main$B, [["render", _sfc_render$B]]);
  const HEX = /^#([0-9a-f]{3}|[0-9a-f]{6})$/i;
  const normalizeHex = (value) => {
    const text = String(value ?? "").trim();
    if (!HEX.test(text)) {
      return null;
    }
    const hex = text.slice(1).toLowerCase();
    return `#${hex.length === 3 ? hex.split("").map((char) => char + char).join("") : hex}`;
  };
  const _sfc_main$A = {
    name: "ColorInput",
    props: {
      // Native mode: the id of the text input to mount over
      inputId: { type: String, default: null },
      modelValue: { type: String, default: "" },
      name: { type: String, default: null },
      id: { type: String, default: null },
      label: { type: String, default: null },
      placeholder: { type: String, default: "#rrggbb" },
      disabled: { type: Boolean, default: false },
      invalid: { type: Boolean, default: false },
      describedBy: { type: String, default: null }
    },
    emits: ["update:modelValue", "change"],
    data() {
      return {
        text: "",
        swatch: "#000000",
        nativeDisabled: false,
        nativeInvalid: false,
        nativeDescribedBy: null,
        nativeLabel: null,
        writing: false
      };
    },
    computed: {
      isNative() {
        return !!this.inputId;
      },
      isDisabled() {
        return this.disabled || this.nativeDisabled;
      },
      isInvalid() {
        return this.invalid || this.nativeInvalid;
      },
      isDescribedBy() {
        return this.describedBy || this.nativeDescribedBy || null;
      },
      textId() {
        return this.isNative ? `${this.inputId}-hex` : this.id;
      },
      textName() {
        return this.isNative ? null : this.name;
      },
      textLabel() {
        return this.isNative ? this.nativeLabel : this.label;
      },
      swatchLabel() {
        return this.textLabel;
      }
    },
    watch: {
      modelValue(value) {
        if (!this.isNative) {
          this.setText(value);
        }
      }
    },
    created() {
      if (this.isNative) {
        this.initNative();
      } else {
        this.setText(normalizeHex(this.modelValue) ?? this.modelValue);
      }
    },
    mounted() {
      this.mountNative();
    },
    beforeUnmount() {
      this.unmountNative();
    },
    methods: {
      setText(value) {
        this.text = String(value ?? "");
        const hex = normalizeHex(this.text);
        if (hex) {
          this.swatch = hex;
        }
      },
      // A click into the text field opens the picker too, a click within the focused field only moves the caret;
      // focusing it with the keyboard opens nothing. `showPicker()` needs the click's activation
      onTextMousedown(event) {
        this.textWasFocused = document.activeElement === event.target;
      },
      onTextClick() {
        if (!this.textWasFocused) {
          this.openPicker();
        }
      },
      openPicker() {
        const swatch = this.$refs.swatch;
        if (!swatch || this.isDisabled || typeof swatch.showPicker !== "function") {
          return;
        }
        try {
          swatch.showPicker();
        } catch (e) {
        }
      },
      onType(event) {
        this.setText(event.target.value);
        this.write(false);
      },
      onPick(event) {
        this.setText(event.target.value);
        this.write(false);
      },
      onCommit() {
        const hex = normalizeHex(this.text);
        if (hex) {
          this.text = hex;
        }
        this.write(true);
      },
      // The value goes to the native input, or to the model
      write(committed) {
        if (this.isNative) {
          if (!this.native) {
            return;
          }
          this.writing = true;
          this.native.value = this.text;
          this.native.dispatchEvent(new Event("input", { bubbles: true }));
          if (committed) {
            this.native.dispatchEvent(new Event("change", { bubbles: true }));
          }
          this.writing = false;
          return;
        }
        this.$emit("update:modelValue", this.text);
        if (committed) {
          this.$emit("change", this.text);
        }
      },
      initNative() {
        const native = document.getElementById(this.inputId);
        if (!native) {
          vue$1.log.error(`ColorInput: no <input id="${this.inputId}">`);
          return;
        }
        this.native = native;
        this.setText(normalizeHex(native.value) ?? native.value);
        this.readNative();
        const label = native.id ? document.querySelector(`label[for="${native.id}"]`) : null;
        if (label) {
          this.labelEl = label;
          this.nativeLabel = label.textContent.trim();
        }
      },
      mountNative() {
        var _a;
        const native = this.native;
        if (!native) {
          return;
        }
        this.nativeAttributes = Object.fromEntries(["tabindex", "aria-hidden"].map((name) => [name, native.getAttribute(name)]));
        native.classList.add("visually-hidden");
        native.setAttribute("tabindex", "-1");
        native.setAttribute("aria-hidden", "true");
        this.onNativeInput = () => {
          if (!this.writing) {
            this.setText(native.value);
          }
        };
        if (window.jQuery) {
          window.jQuery(native).on("input.colorInput change.colorInput", this.onNativeInput);
        } else {
          native.addEventListener("input", this.onNativeInput);
          native.addEventListener("change", this.onNativeInput);
        }
        this.onNativeReset = () => {
          this.resetTimer = setTimeout(() => this.setText(native.value), 0);
        };
        this.nativeForm = native.form;
        (_a = this.nativeForm) == null ? void 0 : _a.addEventListener("reset", this.onNativeReset);
        this.observer = new MutationObserver(() => this.readNative());
        this.observer.observe(native, { attributes: true, attributeFilter: ["disabled", "class", "aria-describedby"] });
        if (this.labelEl) {
          this.onLabelClick = (event) => {
            event.preventDefault();
            this.focus();
          };
          this.labelEl.addEventListener("click", this.onLabelClick);
        }
      },
      readNative() {
        const native = this.native;
        this.nativeDisabled = native.disabled;
        this.nativeInvalid = native.classList.contains("is-invalid");
        this.nativeDescribedBy = native.getAttribute("aria-describedby");
      },
      unmountNative() {
        var _a, _b, _c;
        const native = this.native;
        if (!native) {
          return;
        }
        clearTimeout(this.resetTimer);
        (_a = this.observer) == null ? void 0 : _a.disconnect();
        if (window.jQuery) {
          window.jQuery(native).off(".colorInput");
        } else {
          native.removeEventListener("input", this.onNativeInput);
          native.removeEventListener("change", this.onNativeInput);
        }
        (_b = this.nativeForm) == null ? void 0 : _b.removeEventListener("reset", this.onNativeReset);
        (_c = this.labelEl) == null ? void 0 : _c.removeEventListener("click", this.onLabelClick);
        native.classList.remove("visually-hidden");
        Object.entries(this.nativeAttributes).forEach(([name, value]) => {
          if (value === null) {
            native.removeAttribute(name);
          } else {
            native.setAttribute(name, value);
          }
        });
        this.native = null;
      },
      focus() {
        var _a;
        (_a = this.$refs.input) == null ? void 0 : _a.focus();
      }
    }
  };
  const _hoisted_1$x = {
    ref: "root",
    class: "input-group color-input"
  };
  const _hoisted_2$s = ["value", "disabled", "aria-label", "title"];
  const _hoisted_3$p = ["id", "name", "value", "placeholder", "disabled", "aria-label", "aria-invalid", "aria-describedby"];
  function _sfc_render$A(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      _hoisted_1$x,
      [
        vue.createElementVNode("input", {
          ref: "swatch",
          type: "color",
          class: "form-control form-control-color",
          value: $data.swatch,
          disabled: $options.isDisabled,
          "aria-label": $options.swatchLabel,
          title: $options.swatchLabel,
          onInput: _cache[0] || (_cache[0] = (...args) => $options.onPick && $options.onPick(...args)),
          onChange: _cache[1] || (_cache[1] = (...args) => $options.onCommit && $options.onCommit(...args))
        }, null, 40, _hoisted_2$s),
        vue.createElementVNode("input", {
          ref: "input",
          id: $options.textId,
          name: $options.textName,
          type: "text",
          class: vue.normalizeClass(["form-control color-input-hex", { "is-invalid": $options.isInvalid }]),
          value: $data.text,
          placeholder: $props.placeholder,
          disabled: $options.isDisabled,
          "aria-label": $options.textLabel,
          "aria-invalid": $options.isInvalid ? "true" : null,
          "aria-describedby": $options.isDescribedBy,
          maxlength: "7",
          spellcheck: "false",
          autocomplete: "off",
          onMousedown: _cache[2] || (_cache[2] = (...args) => $options.onTextMousedown && $options.onTextMousedown(...args)),
          onClick: _cache[3] || (_cache[3] = (...args) => $options.onTextClick && $options.onTextClick(...args)),
          onInput: _cache[4] || (_cache[4] = (...args) => $options.onType && $options.onType(...args)),
          onChange: _cache[5] || (_cache[5] = (...args) => $options.onCommit && $options.onCommit(...args))
        }, null, 42, _hoisted_3$p)
      ],
      512
      /* NEED_PATCH */
    );
  }
  const C3 = /* @__PURE__ */ _export_sfc(_sfc_main$A, [["render", _sfc_render$A]]);
  const _sfc_main$z = {
    name: "ColorField",
    components: { ColorInput: C3 },
    mixins: [fieldMixin],
    props: {
      modelValue: { type: String, default: "" }
    },
    emits: ["update:modelValue", "change"],
    methods: {
      onUpdate(value) {
        this.clearOwnError();
        this.$emit("update:modelValue", value);
      },
      focus() {
        var _a;
        (_a = this.$refs.color) == null ? void 0 : _a.focus();
      }
    }
  };
  const _hoisted_1$w = ["for"];
  const _hoisted_2$r = ["id"];
  const _hoisted_3$o = ["id"];
  function _sfc_render$z(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ColorInput = vue.resolveComponent("ColorInput");
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
        }, vue.toDisplayString(_ctx.label), 9, _hoisted_1$w)) : vue.createCommentVNode("v-if", true),
        vue.createVNode(_component_ColorInput, {
          ref: "color",
          id: _ctx.fieldId,
          name: _ctx.fieldName,
          label: _ctx.label,
          placeholder: _ctx.placeholder || "#rrggbb",
          disabled: _ctx.isDisabled,
          invalid: _ctx.hasError,
          "described-by": _ctx.describedBy,
          "model-value": $props.modelValue,
          "onUpdate:modelValue": $options.onUpdate,
          onChange: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("change", $event))
        }, null, 8, ["id", "name", "label", "placeholder", "disabled", "invalid", "described-by", "model-value", "onUpdate:modelValue"]),
        _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 1,
          id: _ctx.hintId,
          class: "form-text text-muted"
        }, vue.toDisplayString(_ctx.hint), 9, _hoisted_2$r)) : vue.createCommentVNode("v-if", true),
        _ctx.hasError ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 2,
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
        ], 8, _hoisted_3$o)) : vue.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C2 = /* @__PURE__ */ _export_sfc(_sfc_main$z, [["render", _sfc_render$z]]);
  function resolveMenuEntries(menuId, entries = [], context = {}) {
    if (!menuId) {
      return [];
    }
    const registry = vue$1.getMenuEntries(menuId);
    const registryById = new Map(registry.entries.map((entry) => [entry.id, entry]));
    const usedRegistryIds = /* @__PURE__ */ new Set();
    const merged = entries.map((entry) => {
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
    const sortOrderOf = (entry) => typeof entry.sortOrder === "number" ? entry.sortOrder : 1e3;
    return merged.filter((entry) => removed.indexOf(entry.id) === -1 && (!entry.condition || entry.condition(context)) && (!entry.component || vue$1.isRegistered(entry.component))).map((entry, index) => ({ entry, index })).sort((a, b) => sortOrderOf(a.entry) - sortOrderOf(b.entry) || a.index - b.index).map((wrapped) => wrapped.entry);
  }
  const _sfc_main$y = {
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
        return resolveMenuEntries(this.menuId, this.entries, this.context);
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
  const _hoisted_1$v = { class: "nav-item dropdown" };
  const _hoisted_2$q = ["aria-label", "title"];
  const _hoisted_3$n = { key: 0 };
  const _hoisted_4$l = { class: "dropdown-item disabled d-flex align-items-center gap-2" };
  const _hoisted_5$h = { role: "status" };
  const _hoisted_6$f = ["innerHTML"];
  const _hoisted_7$c = { key: 1 };
  const _hoisted_8$c = {
    key: 1,
    class: "dropdown-divider"
  };
  const _hoisted_9$9 = ["href", "onClick"];
  const _hoisted_10$8 = ["href", "onClick"];
  function _sfc_render$y(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return vue.openBlock(), vue.createElementBlock(
      "ul",
      {
        class: vue.normalizeClass($props.rootClass)
      },
      [
        vue.createElementVNode("li", _hoisted_1$v, [
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
          ], 10, _hoisted_2$q),
          vue.createElementVNode(
            "ul",
            {
              class: vue.normalizeClass(["dropdown-menu", { "dropdown-menu-end": $props.alignEnd }])
            },
            [
              vue.renderSlot(_ctx.$slots, "default"),
              $props.loading ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_3$n, [
                vue.createElementVNode("span", _hoisted_4$l, [
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
                    _hoisted_5$h,
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
                      }, null, 8, _hoisted_6$f)), [
                        [_directive_additions]
                      ]) : (vue.openBlock(), vue.createElementBlock("li", _hoisted_7$c, [
                        entry.component ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(entry.component), {
                          key: 0,
                          context: $props.context
                        }, null, 8, ["context"])) : entry.divider ? (vue.openBlock(), vue.createElementBlock("hr", _hoisted_8$c)) : entry.icon ? (vue.openBlock(), vue.createElementBlock("a", vue.mergeProps({
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
                        ], 16, _hoisted_9$9)) : (vue.openBlock(), vue.createElementBlock("a", vue.mergeProps({
                          key: 3,
                          ref_for: true
                        }, entry.htmlOptions, {
                          href: entry.url || "#",
                          class: "dropdown-item",
                          onClick: ($event) => $options.onEntryClick(entry, $event)
                        }), vue.toDisplayString($options.resolveLabel(entry)), 17, _hoisted_10$8))
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
  const C7 = /* @__PURE__ */ _export_sfc(_sfc_main$y, [["render", _sfc_render$y]]);
  let uid$9 = 0;
  const _sfc_main$x = {
    name: "HeaderActions",
    components: { DropdownMenu: C7 },
    props: {
      actions: { type: Array, default: () => [] },
      menu: { type: Array, default: () => [] },
      rootClass: { type: String, required: true },
      toolVariant: { type: String, default: "secondary" }
    },
    data() {
      return {
        menuId: `header-menu-${++uid$9}`
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
  const _hoisted_1$u = ["href", "aria-label", "title", "data-action-id", "onClick"];
  const _hoisted_2$p = ["href", "aria-label", "title", "data-action-id"];
  function _sfc_render$x(_ctx, _cache, $props, $setup, $data, $options) {
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
            ], 16, _hoisted_1$u);
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
        ], 16, _hoisted_2$p)) : vue.createCommentVNode("v-if", true),
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
  const HeaderActions = /* @__PURE__ */ _export_sfc(_sfc_main$x, [["render", _sfc_render$x]]);
  let uid$8 = 0;
  const _sfc_main$w = {
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
        titleId: `card-title-${++uid$8}`
      };
    },
    methods: {
      // A method, not a computed: `$slots` is not reactive, so a computed would keep the answer
      // of the first render and miss a header slot the parent passes later.
      hasHeader() {
        return !!(this.title || this.actions.length || this.menu.length || this.$slots.header || this.$slots.actions);
      }
    }
  };
  const _hoisted_1$t = {
    key: 0,
    class: "card-header"
  };
  const _hoisted_2$o = {
    key: 0,
    class: "card-heading"
  };
  const _hoisted_3$m = {
    key: 0,
    class: "card-subtitle"
  };
  const _hoisted_4$k = { class: "card-body" };
  const _hoisted_5$g = {
    key: 1,
    class: "card-footer"
  };
  function _sfc_render$w(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_HeaderActions = vue.resolveComponent("HeaderActions");
    return vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent($props.title ? "section" : "div"), {
      class: "card content-card",
      "aria-labelledby": $props.title ? $data.titleId : null
    }, {
      default: vue.withCtx(() => [
        $options.hasHeader() ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$t, [
          $props.title ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$o, [
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
              _hoisted_3$m,
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
        vue.createElementVNode("div", _hoisted_4$k, [
          vue.renderSlot(_ctx.$slots, "default")
        ]),
        _ctx.$slots.footer ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$g, [
          vue.renderSlot(_ctx.$slots, "footer")
        ])) : vue.createCommentVNode("v-if", true)
      ]),
      _: 3
      /* FORWARDED */
    }, 8, ["aria-labelledby"]);
  }
  const C4 = /* @__PURE__ */ _export_sfc(_sfc_main$w, [["render", _sfc_render$w]]);
  const DRAWER_GAP = 2;
  const positionDrawer = (field, drawer, maxHeight) => {
    drawer.style.position = "fixed";
    const box = field.getBoundingClientRect();
    drawer.style.width = `${box.width}px`;
    const viewport = window.visualViewport;
    const viewTop = viewport ? viewport.offsetTop : 0;
    const viewBottom = viewTop + (viewport ? viewport.height : window.innerHeight);
    const below = Math.max(0, viewBottom - box.bottom - DRAWER_GAP);
    const above = Math.max(0, box.top - viewTop - DRAWER_GAP);
    const border = Math.max(0, (drawer.offsetHeight || 0) - (drawer.clientHeight || 0));
    const natural = Math.min(drawer.scrollHeight ? drawer.scrollHeight + border : maxHeight, maxHeight);
    const dropUp = below < natural && above > below;
    const height = Math.min(natural, dropUp ? above : below);
    Object.assign(drawer.style, {
      position: "fixed",
      left: `${box.left}px`,
      width: `${box.width}px`,
      top: `${dropUp ? box.top - DRAWER_GAP - height : box.bottom + DRAWER_GAP}px`,
      bottom: "auto",
      maxHeight: `${height}px`,
      margin: "0"
    });
    return { dropUp };
  };
  const resetDrawer = (drawer) => {
    ["position", "left", "width", "top", "bottom", "maxHeight", "margin"].forEach((property) => {
      drawer.style[property] = "";
    });
  };
  const DRAWER_MAX_HEIGHT = 384;
  const pageStep = (listbox) => {
    const option = listbox == null ? void 0 : listbox.querySelector('[role="option"]');
    const height = option ? option.offsetHeight : 0;
    return height > 0 ? Math.max(1, Math.floor(listbox.clientHeight / height) - 1) : 10;
  };
  const comboboxMixin = {
    data() {
      return {
        open: false,
        activeIndex: -1,
        dropUp: false
      };
    },
    beforeUnmount() {
      this.unbindOutside();
    },
    methods: {
      optionId(index) {
        return `${this.listboxId}-${index}`;
      },
      // PageUp (-1) / PageDown (1): a page of options, stopping at the first and the last.
      movePage(direction) {
        const last = this.comboboxOptions.length - 1;
        const from = Math.max(this.activeIndex, 0);
        this.moveTo(Math.min(Math.max(from + direction * pageStep(this.$refs.listbox), 0), last));
      },
      moveTo(index) {
        if (index < 0 || !this.comboboxOptions.length) {
          return;
        }
        this.activeIndex = Math.min(index, this.comboboxOptions.length - 1);
        this.$nextTick(() => this.scrollActiveIntoView());
      },
      scrollActiveIntoView() {
        var _a;
        const node = this.activeIndex >= 0 ? (_a = this.$refs.listbox) == null ? void 0 : _a.querySelectorAll('[role="option"]')[this.activeIndex] : null;
        if (node && typeof node.scrollIntoView === "function") {
          node.scrollIntoView({ block: "nearest" });
        }
      },
      placeDrawer() {
        const anchor = this.$refs.field || this.$refs.root;
        if (!this.open || !anchor || !this.$refs.listbox) {
          return;
        }
        this.dropUp = positionDrawer(anchor, this.$refs.listbox, DRAWER_MAX_HEIGHT).dropUp;
      },
      bindOutside() {
        var _a;
        if (this.outsideHandler) {
          return;
        }
        this.outsideHandler = (event) => {
          if (this.$refs.root && !this.$refs.root.contains(event.target)) {
            this.close();
          }
        };
        this.placeHandler = (event) => {
          var _a2;
          if (!((event == null ? void 0 : event.target) instanceof Node) || !((_a2 = this.$refs.listbox) == null ? void 0 : _a2.contains(event.target))) {
            this.placeDrawer();
          }
        };
        document.addEventListener("pointerdown", this.outsideHandler, true);
        document.addEventListener("focusin", this.outsideHandler, true);
        window.addEventListener("scroll", this.placeHandler, true);
        window.addEventListener("resize", this.placeHandler);
        (_a = window.visualViewport) == null ? void 0 : _a.addEventListener("resize", this.placeHandler);
        this.$nextTick(() => this.placeDrawer());
      },
      unbindOutside() {
        var _a;
        if (!this.outsideHandler) {
          return;
        }
        document.removeEventListener("pointerdown", this.outsideHandler, true);
        document.removeEventListener("focusin", this.outsideHandler, true);
        window.removeEventListener("scroll", this.placeHandler, true);
        window.removeEventListener("resize", this.placeHandler);
        (_a = window.visualViewport) == null ? void 0 : _a.removeEventListener("resize", this.placeHandler);
        this.outsideHandler = null;
        this.placeHandler = null;
        this.dropUp = false;
        if (this.$refs.listbox) {
          resetDrawer(this.$refs.listbox);
        }
      }
    }
  };
  const SEARCH_DEBOUNCE_MS$2 = 250;
  let uid$7 = 0;
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
  const _sfc_main$v = {
    name: "FilterPicker",
    mixins: [comboboxMixin],
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
      const key = ++uid$7;
      return {
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
      comboboxOptions() {
        return this.choices;
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
      comboboxOptions() {
        this.$nextTick(() => this.placeDrawer());
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
    },
    methods: {
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
        this.searchTimer = setTimeout(() => this.load(term), SEARCH_DEBOUNCE_MS$2);
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
          case "PageDown":
          case "PageUp":
            if (this.open && this.choices.length) {
              event.preventDefault();
              this.movePage(event.key === "PageDown" ? 1 : -1);
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
      }
    }
  };
  const _hoisted_1$s = { class: "picker-chip-label" };
  const _hoisted_2$n = ["disabled", "aria-label", "title", "onClick"];
  const _hoisted_3$l = ["id", "aria-expanded", "aria-controls", "aria-activedescendant", "aria-label", "aria-busy", "placeholder", "value", "disabled"];
  const _hoisted_4$j = {
    key: 0,
    class: "spinner-border spinner-border-sm select-menu-spinner",
    "aria-hidden": "true"
  };
  const _hoisted_5$f = {
    key: 1,
    class: "ti ti-chevron-up select-menu-chevron",
    "aria-hidden": "true"
  };
  const _hoisted_6$e = ["disabled", "aria-hidden", "aria-label", "title"];
  const _hoisted_7$b = ["id", "aria-label", "aria-multiselectable", "aria-busy"];
  const _hoisted_8$b = ["id", "aria-selected", "onClick", "onMousemove"];
  const _hoisted_9$8 = { class: "picker-option-label" };
  const _hoisted_10$7 = {
    key: 0,
    class: "picker-option-count"
  };
  const _hoisted_11$5 = {
    key: 0,
    class: "dropdown-item-text select-menu-feedback",
    role: "presentation"
  };
  const _hoisted_12$5 = {
    class: "visually-hidden",
    role: "status"
  };
  function _sfc_render$v(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        ref: "root",
        class: vue.normalizeClass(["select-menu picker", { "is-open": _ctx.open, "has-selection": $options.hasSelection, "is-disabled": $props.disabled, "is-loading": $data.loading, "is-multiple": $props.multiple, "is-drop-up": _ctx.dropUp }])
      },
      [
        vue.createElementVNode(
          "div",
          {
            ref: "field",
            class: "form-control picker-field",
            onClick: _cache[3] || (_cache[3] = (...args) => $options.onFieldClick && $options.onFieldClick(...args))
          },
          [
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
                    _hoisted_1$s,
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
                        class: "ti ti-circle-x",
                        "aria-hidden": "true"
                      },
                      null,
                      -1
                      /* CACHED */
                    )
                  ])], 8, _hoisted_2$n)
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
              spellcheck: "false",
              autocapitalize: "off",
              "aria-autocomplete": "list",
              "aria-expanded": _ctx.open ? "true" : "false",
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
            }, null, 40, _hoisted_3$l)
          ],
          512
          /* NEED_PATCH */
        ),
        $data.loading ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_4$j)) : (vue.openBlock(), vue.createElementBlock("i", _hoisted_5$f)),
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
        ])], 8, _hoisted_6$e),
        vue.createElementVNode("ul", {
          id: $data.listboxId,
          ref: "listbox",
          class: vue.normalizeClass(["dropdown-menu select-menu-drawer", { show: _ctx.open }]),
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
                id: _ctx.optionId(index),
                key: option.custom ? `custom:${option.value}` : option.value,
                class: vue.normalizeClass(["dropdown-item select-menu-option", { active: !option.custom && $options.isChosen(option.value), "is-focused": index === _ctx.activeIndex, "is-custom": option.custom }]),
                role: "option",
                "aria-selected": !option.custom && $options.isChosen(option.value) ? "true" : "false",
                onPointerdown: _cache[5] || (_cache[5] = vue.withModifiers(() => {
                }, ["prevent"])),
                onClick: ($event) => $options.choose(option),
                onMousemove: ($event) => _ctx.activeIndex = index
              }, [
                vue.createElementVNode(
                  "span",
                  _hoisted_9$8,
                  vue.toDisplayString(option.custom ? $options.customLabel(option.value) : option.label),
                  1
                  /* TEXT */
                ),
                option.count !== null ? (vue.openBlock(), vue.createElementBlock(
                  "span",
                  _hoisted_10$7,
                  vue.toDisplayString(option.count),
                  1
                  /* TEXT */
                )) : vue.createCommentVNode("v-if", true)
              ], 42, _hoisted_8$b);
            }),
            128
            /* KEYED_FRAGMENT */
          )),
          $options.feedback ? (vue.openBlock(), vue.createElementBlock(
            "li",
            _hoisted_11$5,
            vue.toDisplayString($options.feedback),
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true)
        ], 10, _hoisted_7$b),
        vue.createElementVNode(
          "span",
          _hoisted_12$5,
          vue.toDisplayString(_ctx.open ? $options.statusText : ""),
          1
          /* TEXT */
        )
      ],
      2
      /* CLASS */
    );
  }
  const C12 = /* @__PURE__ */ _export_sfc(_sfc_main$v, [["render", _sfc_render$v]]);
  let uid$6 = 0;
  const _sfc_main$u = {
    name: "FilterSelect",
    props: {
      modelValue: { type: String, default: "" },
      options: { type: Array, default: () => [] },
      placeholder: { type: String, default: "" },
      label: { type: String, default: "" },
      disabled: { type: Boolean, default: false },
      loading: { type: Boolean, default: false },
      id: { type: String, default: null },
      clearable: { type: Boolean, default: true },
      dropUp: { type: Boolean, default: false }
    },
    emits: ["update:modelValue"],
    data() {
      const key = ++uid$6;
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
          case "PageDown":
          case "PageUp":
            event.preventDefault();
            this.moveTo(Math.min(Math.max(Math.max(this.activeIndex, 0) + (event.key === "PageDown" ? 1 : -1) * pageStep(this.$refs.listbox), 0), last));
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
  const _hoisted_1$r = ["id", "aria-expanded", "aria-controls", "aria-label", "aria-busy", "disabled"];
  const _hoisted_2$m = { class: "select-menu-value" };
  const _hoisted_3$k = ["aria-label"];
  const _hoisted_4$i = {
    key: 1,
    class: "ti ti-chevron-up select-menu-chevron",
    "aria-hidden": "true"
  };
  const _hoisted_5$e = ["disabled", "aria-hidden", "aria-label", "title"];
  const _hoisted_6$d = ["id", "aria-label", "aria-activedescendant"];
  const _hoisted_7$a = ["id", "aria-selected", "onClick", "onMousemove"];
  const _hoisted_8$a = {
    key: 0,
    class: "dropdown-item-text select-menu-feedback",
    role: "presentation"
  };
  function _sfc_render$u(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        ref: "root",
        class: vue.normalizeClass(["select-menu", { "is-open": $data.open, "has-selection": $options.hasSelection, "is-disabled": $options.isDisabled, "is-loading": $props.loading, "is-required": !$props.clearable, "is-drop-up": $props.dropUp }])
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
            _hoisted_2$m,
            vue.toDisplayString($options.displayLabel),
            1
            /* TEXT */
          )
        ], 40, _hoisted_1$r),
        $props.loading ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          class: "spinner-border spinner-border-sm select-menu-spinner",
          role: "status",
          "aria-label": $options.loadingLabel
        }, null, 8, _hoisted_3$k)) : (vue.openBlock(), vue.createElementBlock("i", _hoisted_4$i)),
        $props.clearable ? (vue.openBlock(), vue.createElementBlock("button", {
          key: 2,
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
        ])], 8, _hoisted_5$e)) : vue.createCommentVNode("v-if", true),
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
              }, vue.toDisplayString(option.label), 43, _hoisted_7$a);
            }),
            128
            /* KEYED_FRAGMENT */
          )),
          !$options.choices.length ? (vue.openBlock(), vue.createElementBlock(
            "li",
            _hoisted_8$a,
            vue.toDisplayString($options.emptyLabel),
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true)
        ], 42, _hoisted_6$d)
      ],
      2
      /* CLASS */
    );
  }
  const C13 = /* @__PURE__ */ _export_sfc(_sfc_main$u, [["render", _sfc_render$u]]);
  const _sfc_main$t = {
    name: "FilterControl",
    components: { FilterPicker: C12, FilterSelect: C13 },
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
  const _hoisted_1$q = {
    key: 0,
    class: "search-field"
  };
  const _hoisted_2$l = ["id", "value", "placeholder", "aria-label"];
  const _hoisted_3$j = ["aria-label"];
  const _hoisted_4$h = {
    key: 0,
    class: "filter-tags-label",
    "aria-hidden": "true"
  };
  const _hoisted_5$d = ["aria-pressed", "onClick"];
  const _hoisted_6$c = {
    key: 4,
    class: "filter-check form-check"
  };
  const _hoisted_7$9 = ["id", "checked"];
  const _hoisted_8$9 = ["for"];
  function _sfc_render$t(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FilterSelect = vue.resolveComponent("FilterSelect");
    const _component_FilterPicker = vue.resolveComponent("FilterPicker");
    return $props.filter.type === "text" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$q, [
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
      }, null, 40, _hoisted_2$l)
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
        _hoisted_4$h,
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
          }, vue.toDisplayString(option.label), 11, _hoisted_5$d);
        }),
        128
        /* KEYED_FRAGMENT */
      ))
    ], 8, _hoisted_3$j)) : $props.filter.type === "checkbox" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$c, [
      vue.createElementVNode("input", {
        id: $props.inputId,
        class: "form-check-input",
        type: "checkbox",
        checked: $props.modelValue,
        onChange: _cache[3] || (_cache[3] = ($event) => _ctx.$emit("update:modelValue", $event.target.checked))
      }, null, 40, _hoisted_7$9),
      vue.createElementVNode("label", {
        class: "form-check-label",
        for: $props.inputId
      }, vue.toDisplayString($props.filter.label), 9, _hoisted_8$9)
    ])) : $props.component ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent($props.component), {
      key: 5,
      filter: $props.filter,
      "model-value": $props.modelValue,
      "input-id": $props.inputId,
      "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => _ctx.$emit("update:modelValue", $event))
    }, null, 8, ["filter", "model-value", "input-id"])) : vue.createCommentVNode("v-if", true);
  }
  const FilterControl = /* @__PURE__ */ _export_sfc(_sfc_main$t, [["render", _sfc_render$t]]);
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
  let uid$5 = 0;
  const reducedMotion = () => {
    const motion = document.documentElement.getAttribute("data-hh-motion");
    return motion ? motion === "reduce" : typeof window.matchMedia === "function" && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  };
  const same = (keys, a, b) => keys.every((key) => JSON.stringify(a == null ? void 0 : a[key]) === JSON.stringify(b == null ? void 0 : b[key]));
  const keysOf = (filters, fixed) => [.../* @__PURE__ */ new Set([...filters.map((filter) => filter.key), ...Object.keys(fixed || {})])];
  const isPanel = (filter) => filter.placement === "panel";
  const keySlug = (filter) => String(filter.key).replace(/[^A-Za-z0-9_-]+/g, "-").replace(/^-+|-+$/g, "");
  const _sfc_main$s = {
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
        barId: `filter-bar-${++uid$5}`
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
  const _hoisted_1$p = { class: "filter-bar" };
  const _hoisted_2$k = ["id"];
  const _hoisted_3$i = ["data-filter-bar-keep"];
  const _hoisted_4$g = ["aria-expanded", "aria-controls", "aria-label", "title"];
  const _hoisted_5$c = { class: "filter-bar-toggle-label" };
  const _hoisted_6$b = {
    key: 0,
    class: "badge rounded-pill filter-bar-count",
    "aria-hidden": "true"
  };
  const _hoisted_7$8 = {
    key: 1,
    class: "visually-hidden"
  };
  const _hoisted_8$8 = ["aria-label", "title"];
  const _hoisted_9$7 = ["id", "aria-label"];
  function _sfc_render$s(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FilterControl = vue.resolveComponent("FilterControl");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$p, [
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
                ], 10, _hoisted_3$i)) : !entry.filter ? (vue.openBlock(), vue.createElementBlock("button", {
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
                        _hoisted_5$c,
                        vue.toDisplayString($options.panelLabel),
                        1
                        /* TEXT */
                      ),
                      $options.activePanelCount ? (vue.openBlock(), vue.createElementBlock(
                        "span",
                        _hoisted_6$b,
                        vue.toDisplayString($options.activePanelCount),
                        1
                        /* TEXT */
                      )) : vue.createCommentVNode("v-if", true),
                      $options.activePanelCount ? (vue.openBlock(), vue.createElementBlock(
                        "span",
                        _hoisted_7$8,
                        vue.toDisplayString($options.activePanelCountLabel),
                        1
                        /* TEXT */
                      )) : vue.createCommentVNode("v-if", true)
                    ],
                    64
                    /* STABLE_FRAGMENT */
                  )) : vue.createCommentVNode("v-if", true)
                ], 10, _hoisted_4$g)) : vue.createCommentVNode("v-if", true)
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
            ])], 8, _hoisted_8$8)) : vue.createCommentVNode("v-if", true)
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
        ], 40, _hoisted_9$7)) : vue.createCommentVNode("v-if", true)
      ], 42, _hoisted_2$k)
    ]);
  }
  const C11 = /* @__PURE__ */ _export_sfc(_sfc_main$s, [["render", _sfc_render$s]]);
  const _sfc_main$r = {
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
  const _hoisted_1$o = {
    key: 0,
    class: "data-table-main"
  };
  const _hoisted_2$j = ["src"];
  const _hoisted_3$h = { class: "data-table-main-text" };
  const _hoisted_4$f = ["href"];
  const _hoisted_5$b = {
    key: 1,
    class: "data-table-main-name"
  };
  const _hoisted_6$a = {
    key: 2,
    class: "data-table-main-subtitle"
  };
  const _hoisted_7$7 = {
    key: 1,
    class: "data-table-chips"
  };
  const _hoisted_8$7 = ["title", "aria-label", "onClick"];
  const _hoisted_9$6 = ["datetime", "title"];
  const _hoisted_10$6 = ["title"];
  function _sfc_render$r(_ctx, _cache, $props, $setup, $data, $options) {
    return $props.column.type === "main" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$o, [
      $props.cell.image ? (vue.openBlock(), vue.createElementBlock("img", {
        key: 0,
        src: $props.cell.image,
        class: "data-table-main-image",
        alt: "",
        "aria-hidden": "true"
      }, null, 8, _hoisted_2$j)) : $props.cell.icon ? (vue.openBlock(), vue.createElementBlock(
        "span",
        {
          key: 1,
          class: vue.normalizeClass(["data-table-main-image data-table-main-icon", { "has-color": !!$props.cell.color }]),
          style: vue.normalizeStyle($props.cell.color ? { backgroundColor: $props.cell.color } : null),
          "aria-hidden": "true"
        },
        [
          vue.createElementVNode(
            "i",
            {
              class: vue.normalizeClass("ti ti-" + $props.cell.icon)
            },
            null,
            2
            /* CLASS */
          )
        ],
        6
        /* CLASS, STYLE */
      )) : $props.cell.initials ? (vue.openBlock(), vue.createElementBlock(
        "span",
        {
          key: 2,
          class: "data-table-main-image data-table-main-initials",
          style: vue.normalizeStyle($props.cell.color ? { backgroundColor: $props.cell.color } : null),
          "aria-hidden": "true"
        },
        vue.toDisplayString($props.cell.initials),
        5
        /* TEXT, STYLE */
      )) : vue.createCommentVNode("v-if", true),
      vue.createElementVNode("span", _hoisted_3$h, [
        $props.cell.url ? (vue.openBlock(), vue.createElementBlock("a", {
          key: 0,
          href: $props.cell.url,
          class: "data-table-main-name"
        }, vue.toDisplayString($props.cell.text), 9, _hoisted_4$f)) : (vue.openBlock(), vue.createElementBlock(
          "span",
          _hoisted_5$b,
          vue.toDisplayString($props.cell.text),
          1
          /* TEXT */
        )),
        $props.cell.subtitle ? (vue.openBlock(), vue.createElementBlock(
          "span",
          _hoisted_6$a,
          vue.toDisplayString($props.cell.subtitle),
          1
          /* TEXT */
        )) : vue.createCommentVNode("v-if", true)
      ])
    ])) : $props.column.type === "chip" ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_7$7, [
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
              }, vue.toDisplayString(chip.text), 11, _hoisted_8$7)) : (vue.openBlock(), vue.createElementBlock(
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
    }, vue.toDisplayString($props.cell.text), 9, _hoisted_9$6)) : (vue.openBlock(), vue.createElementBlock("span", {
      key: 3,
      title: $props.cell.title || null
    }, vue.toDisplayString($props.cell.text), 9, _hoisted_10$6));
  }
  const TableCell = /* @__PURE__ */ _export_sfc(_sfc_main$r, [["render", _sfc_render$r]]);
  let uidSeq = 0;
  const _sfc_main$q = {
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
  const _hoisted_1$n = ["aria-labelledby"];
  const _hoisted_2$i = { class: "modal-content" };
  const _hoisted_3$g = { class: "modal-header" };
  const _hoisted_4$e = ["id"];
  const _hoisted_5$a = { class: "modal-body" };
  const _hoisted_6$9 = {
    key: 0,
    class: "modal-footer"
  };
  function _sfc_render$q(_ctx, _cache, $props, $setup, $data, $options) {
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
            vue.createElementVNode("div", _hoisted_2$i, [
              vue.createElementVNode("div", _hoisted_3$g, [
                vue.renderSlot(_ctx.$slots, "header", { titleId: $data.titleId }, () => [
                  vue.createElementVNode("h5", {
                    class: "modal-title",
                    id: $data.titleId
                  }, vue.toDisplayString($props.title), 9, _hoisted_4$e),
                  vue.createElementVNode("button", {
                    type: "button",
                    class: "btn-close",
                    "aria-label": "Close",
                    onClick: _cache[0] || (_cache[0] = (...args) => $options.requestClose && $options.requestClose(...args))
                  })
                ])
              ]),
              vue.createElementVNode("div", _hoisted_5$a, [
                vue.renderSlot(_ctx.$slots, "default")
              ]),
              _ctx.$slots.footer ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6$9, [
                vue.renderSlot(_ctx.$slots, "footer")
              ])) : vue.createCommentVNode("v-if", true)
            ])
          ],
          2
          /* CLASS */
        )
      ], 42, _hoisted_1$n)) : vue.createCommentVNode("v-if", true),
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
  const C31 = /* @__PURE__ */ _export_sfc(_sfc_main$q, [["render", _sfc_render$q]]);
  const COLUMN_WIDTHS = { main: 240, chip: 160, date: 120, number: 90, text: 150 };
  const SIDE_COLUMN_WIDTH = 48;
  const REFRESH_EVENT = "humhub:data-table:refresh";
  const FOLD_HYSTERESIS = 32;
  const PAGE_SIZES = [25, 50, 100];
  const SELECT_KEY = "_select";
  let uid$4 = 0;
  const _sfc_main$p = {
    name: "DataTable",
    components: { DropdownMenu: C7, FilterBar: C11, FilterSelect: C13, HeaderActions, TableCell, UiModal: C31 },
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
      maxColumns: { type: Number, default: 0 },
      /**
       * Inside another card (e.g. below its tabs): no card of its own, an `h3` title.
       */
      embedded: { type: Boolean, default: false },
      /**
       * The checkboxes of the bulk actions shown until the user chooses otherwise (e.g. lists
       * mostly worked through in bulk, like pending invitations).
       */
      selectByDefault: { type: Boolean, default: false },
      /**
       * The back arrow before the title of a sub-page, and its label ("Back to overview").
       */
      backUrl: { type: String, default: "" },
      backLabel: { type: String, default: "" }
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
        // The table's width, measured with the folding, for the main column's width
        tableWidth: 0,
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
          back: vue$1.i18n.t("base", "Back"),
          pages: vue$1.i18n.t("base", "Pages"),
          previous: vue$1.i18n.t("base", "Previous page"),
          next: vue$1.i18n.t("base", "Next page"),
          columns: vue$1.i18n.t("base", "Columns"),
          hiddenAtWidth: vue$1.i18n.t("base", "Hidden at this width"),
          reset: vue$1.i18n.t("base", "Reset"),
          save: vue$1.i18n.t("base", "Save"),
          bulkSelect: vue$1.i18n.t("base", "Bulk select"),
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
      // The checkbox column: for tables with a bulk action, while chosen in the columns chooser.
      showSelect() {
        return this.hasBulk && this.chosenKeys.includes(SELECT_KEY);
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
        const keys = this.optionalColumns.filter((column) => column.visible !== false).map((column) => column.key);
        return this.selectByDefault && this.hasBulk ? [SELECT_KEY, ...keys] : keys;
      },
      // What the columns chooser offers: "Bulk select" (the checkbox column), then the optional
      // columns.
      choosableColumns() {
        return [
          ...this.hasBulk ? [{ key: SELECT_KEY, label: this.texts.bulkSelect }] : [],
          ...this.optionalColumns
        ];
      },
      // A chooser only with something to choose: "Bulk select", a column hidden by default, or
      // more columns than may be shown at once.
      hasChooser() {
        return this.hasBulk || this.optionalColumns.some((column) => column.visible === false) || this.maxColumns > 0 && this.optionalColumns.length > this.maxColumns;
      },
      // The main column and the chosen optional ones (without a chooser: the defaults), in the
      // columns' order.
      activeColumns() {
        const keys = this.hasChooser ? this.chosenKeys : this.defaultKeys;
        return this.columns.filter((column) => column.type === "main" || keys.includes(column.key));
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
      // The main column twice as wide as each of the others (`table-layout: fixed`); alone, it
      // takes the table's width.
      // Narrow columns (`narrow`) have a fixed width; with only those, the main column takes the
      // rest.
      mainColumnStyle() {
        const others = this.shownColumns.filter((column) => column.type !== "main" && !column.narrow).length;
        if (others === 0) {
          return null;
        }
        const fixed = this.shownColumns.filter((column) => column.narrow).length * this.cssWidth("--hh-data-table-narrow-column-width") + ((this.showSelect ? 1 : 0) + (this.hasDetails ? 1 : 0)) * this.cssWidth("--hh-data-table-side-column-width") + (this.hasActionsColumn ? this.cssWidth("--hh-data-table-actions-column-width") : 0);
        if (!this.tableWidth || this.tableWidth <= fixed) {
          return { width: `${(200 / (others + 2)).toFixed(4)}%` };
        }
        return { width: `${Math.floor((this.tableWidth - fixed) * 2 / (others + 2))}px` };
      },
      // The row menu's id: modules add entries to it client-side (`registerMenuEntry()`)
      rowMenuId() {
        return `${this.tableId}.row`;
      },
      // A column for the row menus: the table has row actions, or a module registered entries
      hasActionsColumn() {
        return this.actions.length > 0 || vue$1.getMenuEntries(this.rowMenuId).entries.length > 0;
      },
      pageSizeOptions() {
        return this.pageSizes.map((size) => ({ value: String(size), label: String(size) }));
      },
      // The filters of the chosen columns (a column's `filter`), and those of no column (e.g. the
      // search): a hidden column's filter goes with it.
      shownFilters() {
        const columnFilters = this.columns.map((column) => column.filter).filter(Boolean);
        const chosenFilters = this.activeColumns.map((column) => column.filter).filter(Boolean);
        return this.filters.filter((filter) => !columnFilters.includes(filter.key) || chosenFilters.includes(filter.key));
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
        return this.shownColumns.length + (this.showSelect ? 1 : 0) + (this.hasDetails ? 1 : 0) + (this.hasActionsColumn ? 1 : 0);
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
      this.uid = ++uid$4;
    },
    mounted() {
      this.started = true;
      this.fetch();
      if (typeof ResizeObserver !== "undefined" && this.$refs.wrap) {
        this.resizeObserver = new ResizeObserver(() => this.fold());
        this.resizeObserver.observe(this.$refs.wrap);
      }
      this.onRefresh = (evt, data) => {
        if (!data || !data.tableId || data.tableId === this.tableId) {
          this.clearSelection();
          this.fetch();
        }
      };
      vue$1.events.on(REFRESH_EVENT, this.onRefresh);
    },
    beforeUnmount() {
      var _a;
      this.requestSeq++;
      (_a = this.resizeObserver) == null ? void 0 : _a.disconnect();
      vue$1.events.off(REFRESH_EVENT, this.onRefresh);
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
      format(template, params) {
        return String(template).replace(/\{(\w+)\}/g, (match, key) => params[key] !== void 0 ? params[key] : match);
      },
      fetch() {
        const seq = ++this.requestSeq;
        this.loading = true;
        this.error = null;
        const params = {
          ...requestParams(this.shownFilters, this.values),
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
        this.tableWidth = wrap.clientWidth;
        const foldable = this.activeColumns.filter((column) => column.type !== "main").sort((a, b) => (b.priority || 1) - (a.priority || 1));
        const width = (columns) => columns.reduce(
          (sum, column) => sum + (COLUMN_WIDTHS[column.type] || 150),
          SIDE_COLUMN_WIDTH * ((this.hasActionsColumn ? 1 : 0) + (this.showSelect ? 1 : 0) + (columns.length < this.activeColumns.length ? 1 : 0))
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
        if (this.actions.some((action) => action.bulkUrl)) {
          keys.push(SELECT_KEY);
        }
        if (Array.isArray(this.columnChoice)) {
          return this.columnChoice.filter((key) => keys.includes(key));
        }
        const defaults = this.columns.filter((column) => column.type !== "main" && column.visible !== false).map((column) => column.key);
        return this.selectByDefault && keys.includes(SELECT_KEY) ? [SELECT_KEY, ...defaults] : defaults;
      },
      openChooser() {
        this.draftKeys = [...this.chosenKeys];
        this.chooserOpen = true;
      },
      // A switch that can't be turned on: the most columns are chosen.
      isLocked(key) {
        return !this.draftKeys.includes(key) && this.atLimit;
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
        const keys = this.choosableColumns.map((column) => column.key).filter((key) => this.draftKeys.includes(key));
        const narrowed = this.columns.filter((column) => column.filter && this.chosenKeys.includes(column.key) && !keys.includes(column.key)).map((column) => this.filters.find((filter) => filter.key === column.filter)).some((filter) => filter && !isDefault(filter, this.values[filter.key]));
        this.chosenKeys = keys;
        if (narrowed) {
          this.page = 1;
          this.fetch();
        }
        this.chooserOpen = false;
        if (!this.showSelect) {
          this.clearSelection();
        }
        this.$nextTick(() => this.fold());
        if (!this.columnsUrl) {
          return Promise.resolve();
        }
        const columns = this.draftDiffersFromDefault ? keys : null;
        return vue$1.client.put(this.columnsUrl, { data: JSON.stringify({ columns }), contentType: "application/json" }).catch((response) => {
          vue$1.status("error", this.errorText(response));
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
        if (action.bulkModal) {
          const params = this.allSelected ? new URLSearchParams({ ...requestParams(this.shownFilters, this.values), all: 1 }) : new URLSearchParams(this.bulkIds(action).map((id) => ["ids[]", id]));
          return vue$1.modal.load(action.bulkUrl + (action.bulkUrl.includes("?") ? "&" : "?") + params.toString());
        }
        const body = count === null ? vue$1.i18n.t("base", 'Apply "{action}" to all {count} entries?', { action: action.label, count: this.total }) : vue$1.i18n.t("base", 'Apply "{action}" to {count} selected entries?', { action: action.label, count });
        return vue$1.modal.confirm({ body, confirmText: action.label }).then((ok) => {
          if (!ok) {
            return null;
          }
          let url = action.bulkUrl;
          let data;
          if (this.allSelected) {
            const params = new URLSearchParams(requestParams(this.shownFilters, this.values)).toString();
            url += params ? (url.includes("?") ? "&" : "?") + params : "";
            data = { all: 1 };
          } else {
            data = { ids: this.bulkIds(action) };
          }
          return vue$1.client.post(url, { data }).then((response) => {
            if (response && response.message) {
              vue$1.status("success", response.message);
            }
            this.clearSelection();
            return this.fetch();
          }).catch((response) => {
            vue$1.status("error", this.errorText(response));
            vue$1.log.error(response);
          });
        });
      },
      // A width of `_data-table.scss` (a CSS variable in px or rem) in pixels
      cssWidth(name) {
        if (!this.$el || typeof getComputedStyle === "undefined") {
          return 0;
        }
        const value = getComputedStyle(this.$el).getPropertyValue(name).trim();
        const number = parseFloat(value) || 0;
        return value.endsWith("rem") ? number * (parseFloat(getComputedStyle(document.documentElement).fontSize) || 16) : number;
      },
      // The selected rows offering the bulk action
      bulkIds(action) {
        return Object.values(this.selected).filter((row) => (row.actions || []).some((rowAction) => rowAction.id === action.id)).map((row) => row.id);
      },
      // The first message of a failed answer (`422 {errors: {field: [messages]}}`, also inside
      // the client's error envelope), its `message`, or a general one.
      errorText(response) {
        var _a, _b;
        const errors = (response == null ? void 0 : response.errors) || ((_a = response == null ? void 0 : response.error) == null ? void 0 : _a.errors) || ((_b = response == null ? void 0 : response.response) == null ? void 0 : _b.errors);
        const first = errors && typeof errors === "object" ? Object.values(errors).flat()[0] : null;
        return first || (response == null ? void 0 : response.message) || vue$1.i18n.t("base", "An unexpected error occurred.");
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
      // button inside it. A click beside the checkbox, in its cell, selects the row instead.
      onRowClick(row, event) {
        var _a;
        if (event.target.closest("a, button, input, select, label")) {
          return;
        }
        const selectCell = event.target.closest(".data-table-col-select");
        if (selectCell) {
          (_a = selectCell.querySelector("input")) == null ? void 0 : _a.dispatchEvent(new MouseEvent("click", { bubbles: true, shiftKey: event.shiftKey }));
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
      // Whether the row's menu has an entry: its actions or a module's entries for it
      hasRowMenu(row) {
        return resolveMenuEntries(this.rowMenuId, this.menuEntries(row), { row }).length > 0;
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
            icon: action.icon,
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
          } else {
            this.fetch();
          }
          if (response && response.message) {
            vue$1.status("success", response.message);
          }
        }).catch((response) => {
          vue$1.status("error", this.errorText(response));
          vue$1.log.error(response);
        }).finally(() => {
          const busy = { ...this.busy };
          delete busy[row.id];
          this.busy = busy;
        });
      }
    }
  };
  const _hoisted_1$m = ["id", "aria-labelledby"];
  const _hoisted_2$h = {
    key: 0,
    class: "card-header"
  };
  const _hoisted_3$f = ["href", "aria-label", "title"];
  const _hoisted_4$d = ["aria-label", "title"];
  const _hoisted_5$9 = {
    class: "visually-hidden",
    role: "status"
  };
  const _hoisted_6$8 = { class: "card-body" };
  const _hoisted_7$6 = {
    ref: "wrap",
    class: "data-table-wrap"
  };
  const _hoisted_8$6 = ["aria-label", "aria-busy"];
  const _hoisted_9$5 = {
    key: 0,
    class: "data-table-col-select"
  };
  const _hoisted_10$5 = {
    key: 1,
    class: "data-table-col-details"
  };
  const _hoisted_11$4 = {
    key: 2,
    class: "data-table-col-actions"
  };
  const _hoisted_12$4 = {
    key: 0,
    class: "data-table-col-select"
  };
  const _hoisted_13$1 = ["checked", ".indeterminate", "disabled", "aria-label"];
  const _hoisted_14$1 = ["aria-sort"];
  const _hoisted_15$1 = ["aria-label", "onClick"];
  const _hoisted_16$1 = {
    key: 1,
    class: "data-table-col-details"
  };
  const _hoisted_17$1 = { class: "visually-hidden" };
  const _hoisted_18$1 = {
    key: 2,
    class: "data-table-col-actions"
  };
  const _hoisted_19$1 = { class: "visually-hidden" };
  const _hoisted_20$1 = { key: 0 };
  const _hoisted_21$1 = {
    key: 0,
    class: "data-table-col-select"
  };
  const _hoisted_22$1 = {
    key: 0,
    class: "data-table-main"
  };
  const _hoisted_23$1 = {
    key: 1,
    class: "placeholder col-6"
  };
  const _hoisted_24 = {
    key: 1,
    class: "data-table-col-details"
  };
  const _hoisted_25 = {
    key: 2,
    class: "data-table-col-actions"
  };
  const _hoisted_26 = { key: 1 };
  const _hoisted_27 = { class: "data-table-message" };
  const _hoisted_28 = ["colspan"];
  const _hoisted_29 = { role: "alert" };
  const _hoisted_30 = { role: "status" };
  const _hoisted_31 = { key: 2 };
  const _hoisted_32 = ["data-id", "onClick"];
  const _hoisted_33 = {
    key: 0,
    class: "data-table-col-select"
  };
  const _hoisted_34 = ["checked", "aria-label", "onClick"];
  const _hoisted_35 = {
    key: 1,
    class: "data-table-col-details"
  };
  const _hoisted_36 = ["aria-expanded", "aria-controls", "aria-label", "onClick"];
  const _hoisted_37 = ["id"];
  const _hoisted_38 = ["colspan"];
  const _hoisted_39 = {
    key: 1,
    class: "alert alert-danger data-table-error",
    role: "alert"
  };
  const _hoisted_40 = {
    key: 1,
    class: "card-footer data-table-footer"
  };
  const _hoisted_41 = {
    class: "data-table-count",
    role: "status"
  };
  const _hoisted_42 = ["aria-label"];
  const _hoisted_43 = { class: "pagination" };
  const _hoisted_44 = ["aria-label", "disabled"];
  const _hoisted_45 = {
    key: 0,
    class: "page-link",
    "aria-hidden": "true"
  };
  const _hoisted_46 = ["aria-current", "aria-label", "onClick"];
  const _hoisted_47 = ["aria-label", "disabled"];
  const _hoisted_48 = {
    key: 0,
    class: "data-table-columns-limit"
  };
  const _hoisted_49 = { class: "list-group data-table-columns-list" };
  const _hoisted_50 = { class: "data-table-columns-label" };
  const _hoisted_51 = {
    key: 0,
    class: "form-text data-table-columns-folded"
  };
  const _hoisted_52 = { class: "form-check form-switch" };
  const _hoisted_53 = ["id", "checked", "disabled", "onChange"];
  const _hoisted_54 = ["disabled"];
  function _sfc_render$p(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DropdownMenu = vue.resolveComponent("DropdownMenu");
    const _component_HeaderActions = vue.resolveComponent("HeaderActions");
    const _component_FilterBar = vue.resolveComponent("FilterBar");
    const _component_TableCell = vue.resolveComponent("TableCell");
    const _component_FilterSelect = vue.resolveComponent("FilterSelect");
    const _component_UiModal = vue.resolveComponent("UiModal");
    return vue.openBlock(), vue.createElementBlock("section", {
      id: $props.tableId,
      ref: "root",
      class: vue.normalizeClass(["content-card data-table", $props.embedded ? "data-table-embedded" : "card"]),
      "aria-labelledby": $props.title ? $options.titleId : null
    }, [
      $props.title || $props.backUrl || $props.headerActions.length || $props.headerMenu.length || $options.hasChooser || $options.hasBulk ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$h, [
        $props.backUrl ? (vue.openBlock(), vue.createElementBlock("a", {
          key: 0,
          href: $props.backUrl,
          class: "card-back btn btn-ghost btn-icon-only",
          "aria-label": $props.backLabel || $options.texts.back,
          title: $props.backLabel || $options.texts.back
        }, [..._cache[12] || (_cache[12] = [
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
        ])], 8, _hoisted_3$f)) : vue.createCommentVNode("v-if", true),
        $props.title ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent($props.embedded ? "h3" : "h2"), {
          key: 1,
          id: $options.titleId,
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
        }, 8, ["id"])) : vue.createCommentVNode("v-if", true),
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
            ])], 8, _hoisted_4$d)) : vue.createCommentVNode("v-if", true)
          ]),
          _: 1
          /* STABLE */
        }, 8, ["actions", "menu"]),
        vue.createElementVNode(
          "span",
          _hoisted_5$9,
          vue.toDisplayString($options.selectedCount ? $options.selectionLabel : ""),
          1
          /* TEXT */
        )
      ])) : vue.createCommentVNode("v-if", true),
      vue.createElementVNode("div", _hoisted_6$8, [
        $options.shownFilters.length ? (vue.openBlock(), vue.createBlock(_component_FilterBar, {
          key: 0,
          ref: "filterBar",
          "model-value": $data.values,
          filters: $options.shownFilters,
          "sync-url": $props.syncUrl,
          "id-prefix": `${$props.tableId}-filter`,
          "onUpdate:modelValue": $options.onFilterChange
        }, null, 8, ["model-value", "filters", "sync-url", "id-prefix", "onUpdate:modelValue"])) : vue.createCommentVNode("v-if", true),
        vue.createElementVNode(
          "div",
          _hoisted_7$6,
          [
            vue.createElementVNode("table", {
              class: vue.normalizeClass(["table table-hover", { "is-loading": $data.loading && $data.rows.length > 0 }]),
              "aria-label": $props.label,
              "aria-busy": $data.loading ? "true" : "false"
            }, [
              vue.createElementVNode("colgroup", null, [
                $options.showSelect ? (vue.openBlock(), vue.createElementBlock("col", _hoisted_9$5)) : vue.createCommentVNode("v-if", true),
                (vue.openBlock(true), vue.createElementBlock(
                  vue.Fragment,
                  null,
                  vue.renderList($options.shownColumns, (column) => {
                    return vue.openBlock(), vue.createElementBlock(
                      "col",
                      {
                        key: column.key,
                        class: vue.normalizeClass([`data-table-col-${column.type}`, { "data-table-col-narrow": column.narrow }]),
                        style: vue.normalizeStyle(column.type === "main" ? $options.mainColumnStyle : null)
                      },
                      null,
                      6
                      /* CLASS, STYLE */
                    );
                  }),
                  128
                  /* KEYED_FRAGMENT */
                )),
                $options.hasDetails ? (vue.openBlock(), vue.createElementBlock("col", _hoisted_10$5)) : vue.createCommentVNode("v-if", true),
                $options.hasActionsColumn ? (vue.openBlock(), vue.createElementBlock("col", _hoisted_11$4)) : vue.createCommentVNode("v-if", true)
              ]),
              vue.createElementVNode("thead", null, [
                vue.createElementVNode("tr", null, [
                  $options.showSelect ? (vue.openBlock(), vue.createElementBlock("th", _hoisted_12$4, [
                    vue.createElementVNode("input", {
                      class: "form-check-input",
                      type: "checkbox",
                      checked: $options.pageSelected === "all",
                      ".indeterminate": $options.pageSelected === "some",
                      disabled: !$data.rows.length,
                      "aria-label": $options.texts.selectPage,
                      onChange: _cache[1] || (_cache[1] = ($event) => $options.selectPage($event.target.checked))
                    }, null, 40, _hoisted_13$1)
                  ])) : vue.createCommentVNode("v-if", true),
                  (vue.openBlock(true), vue.createElementBlock(
                    vue.Fragment,
                    null,
                    vue.renderList($options.shownColumns, (column) => {
                      return vue.openBlock(), vue.createElementBlock("th", {
                        key: column.key,
                        scope: "col",
                        class: vue.normalizeClass([`data-table-col-${column.type}`, { "data-table-col-narrow": column.narrow }]),
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
                        ], 10, _hoisted_15$1)) : (vue.openBlock(), vue.createElementBlock(
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
                      ], 10, _hoisted_14$1);
                    }),
                    128
                    /* KEYED_FRAGMENT */
                  )),
                  $options.hasDetails ? (vue.openBlock(), vue.createElementBlock("th", _hoisted_16$1, [
                    vue.createElementVNode(
                      "span",
                      _hoisted_17$1,
                      vue.toDisplayString($options.texts.details),
                      1
                      /* TEXT */
                    )
                  ])) : vue.createCommentVNode("v-if", true),
                  $options.hasActionsColumn ? (vue.openBlock(), vue.createElementBlock("th", _hoisted_18$1, [
                    vue.createElementVNode(
                      "span",
                      _hoisted_19$1,
                      vue.toDisplayString($options.texts.actions),
                      1
                      /* TEXT */
                    )
                  ])) : vue.createCommentVNode("v-if", true)
                ])
              ]),
              $data.loading && !$data.rows.length && !$data.error ? (vue.openBlock(), vue.createElementBlock("tbody", _hoisted_20$1, [
                (vue.openBlock(true), vue.createElementBlock(
                  vue.Fragment,
                  null,
                  vue.renderList($props.skeletonCount, (index) => {
                    return vue.openBlock(), vue.createElementBlock("tr", {
                      key: index,
                      class: "data-table-skeleton placeholder-glow",
                      "aria-hidden": "true"
                    }, [
                      $options.showSelect ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_21$1)) : vue.createCommentVNode("v-if", true),
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
                              column.type === "main" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_22$1, [..._cache[15] || (_cache[15] = [
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
                              ])])) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_23$1))
                            ],
                            2
                            /* CLASS */
                          );
                        }),
                        128
                        /* KEYED_FRAGMENT */
                      )),
                      $options.hasDetails ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_24)) : vue.createCommentVNode("v-if", true),
                      $options.hasActionsColumn ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_25)) : vue.createCommentVNode("v-if", true)
                    ]);
                  }),
                  128
                  /* KEYED_FRAGMENT */
                ))
              ])) : !$data.rows.length ? (vue.openBlock(), vue.createElementBlock("tbody", _hoisted_26, [
                vue.createElementVNode("tr", _hoisted_27, [
                  vue.createElementVNode("td", { colspan: $options.colspan }, [
                    $data.error ? (vue.openBlock(), vue.createElementBlock(
                      vue.Fragment,
                      { key: 0 },
                      [
                        vue.createElementVNode(
                          "span",
                          _hoisted_29,
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
                          _hoisted_30,
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
                  ], 8, _hoisted_28)
                ])
              ])) : (vue.openBlock(), vue.createElementBlock("tbody", _hoisted_31, [
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
                          $options.showSelect ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_33, [
                            vue.createElementVNode("input", {
                              class: "form-check-input",
                              type: "checkbox",
                              checked: $options.isSelected(row),
                              "aria-label": $options.rowLabel("Select {name}", row),
                              onClick: ($event) => $options.onSelect(row, index, $event)
                            }, null, 8, _hoisted_34)
                          ])) : vue.createCommentVNode("v-if", true),
                          (vue.openBlock(true), vue.createElementBlock(
                            vue.Fragment,
                            null,
                            vue.renderList($options.shownColumns, (column) => {
                              return vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(column.type === "main" ? "th" : "td"), {
                                key: column.key,
                                scope: column.type === "main" ? "row" : null,
                                class: vue.normalizeClass([`data-table-col-${column.type}`, { "data-table-col-narrow": column.narrow }])
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
                          $options.hasDetails ? (vue.openBlock(), vue.createElementBlock("td", _hoisted_35, [
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
                            ], 8, _hoisted_36)
                          ])) : vue.createCommentVNode("v-if", true),
                          $options.hasActionsColumn ? (vue.openBlock(), vue.createElementBlock("td", {
                            key: 2,
                            class: "data-table-col-actions",
                            onClick: _cache[4] || (_cache[4] = vue.withModifiers(() => {
                            }, ["stop"]))
                          }, [
                            $options.hasRowMenu(row) ? (vue.openBlock(), vue.createBlock(_component_DropdownMenu, {
                              key: 0,
                              "menu-id": $options.rowMenuId,
                              entries: $options.menuEntries(row),
                              context: { row },
                              "toggle-aria-label": $options.rowLabel("More actions for {name}", row),
                              "toggle-title": $options.texts.actions,
                              "toggle-class": "btn btn-ghost btn-icon-only data-table-actions-toggle",
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
                        ], 10, _hoisted_32),
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
                          ], 8, _hoisted_38)
                        ], 8, _hoisted_37)) : vue.createCommentVNode("v-if", true)
                      ],
                      64
                      /* STABLE_FRAGMENT */
                    );
                  }),
                  128
                  /* KEYED_FRAGMENT */
                ))
              ]))
            ], 10, _hoisted_8$6)
          ],
          512
          /* NEED_PATCH */
        ),
        $data.error && $data.rows.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_39, [
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
      $data.total > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_40, [
        vue.createElementVNode(
          "span",
          _hoisted_41,
          vue.toDisplayString($options.countText),
          1
          /* TEXT */
        ),
        $data.pages > 1 ? (vue.openBlock(), vue.createElementBlock("nav", {
          key: 0,
          class: "data-table-pager",
          "aria-label": $options.texts.pages
        }, [
          vue.createElementVNode("ul", _hoisted_43, [
            vue.createElementVNode(
              "li",
              {
                class: vue.normalizeClass(["page-item", { invisible: $data.page <= 1 }])
              },
              [
                vue.createElementVNode("button", {
                  type: "button",
                  class: "page-link",
                  "aria-label": $options.texts.previous,
                  disabled: $data.page <= 1,
                  onClick: _cache[6] || (_cache[6] = ($event) => $options.goTo($data.page - 1))
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
                ])], 8, _hoisted_44)
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
                    slot === null ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_45, "…")) : (vue.openBlock(), vue.createElementBlock("button", {
                      key: 1,
                      type: "button",
                      class: "page-link",
                      "aria-current": slot === $data.page ? "page" : null,
                      "aria-label": $options.pageLabel(slot),
                      onClick: ($event) => $options.goTo(slot)
                    }, vue.toDisplayString(slot), 9, _hoisted_46))
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
                class: vue.normalizeClass(["page-item", { invisible: $data.page >= $data.pages }])
              },
              [
                vue.createElementVNode("button", {
                  type: "button",
                  class: "page-link",
                  "aria-label": $options.texts.next,
                  disabled: $data.page >= $data.pages,
                  onClick: _cache[7] || (_cache[7] = ($event) => $options.goTo($data.page + 1))
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
                ])], 8, _hoisted_47)
              ],
              2
              /* CLASS */
            )
          ])
        ], 8, _hoisted_42)) : vue.createCommentVNode("v-if", true),
        $data.total > $data.pageSizes[0] ? (vue.openBlock(), vue.createBlock(_component_FilterSelect, {
          key: 1,
          class: "data-table-page-size",
          "model-value": String($data.pageSize),
          options: $options.pageSizeOptions,
          label: $options.texts.rowsPerPage,
          title: $options.texts.rowsPerPage,
          clearable: false,
          "drop-up": "",
          "onUpdate:modelValue": _cache[8] || (_cache[8] = ($event) => $options.setPageSize(Number($event)))
        }, null, 8, ["model-value", "options", "label", "title"])) : vue.createCommentVNode("v-if", true)
      ])) : vue.createCommentVNode("v-if", true),
      $options.hasChooser ? (vue.openBlock(), vue.createBlock(_component_UiModal, {
        key: 2,
        show: $data.chooserOpen,
        title: $options.texts.columns,
        "dialog-class": "data-table-columns",
        "onUpdate:show": _cache[11] || (_cache[11] = ($event) => $data.chooserOpen = $event)
      }, {
        footer: vue.withCtx(() => [
          vue.createElementVNode("button", {
            type: "button",
            class: "btn btn-light data-table-columns-reset",
            disabled: !$options.draftDiffersFromDefault,
            onClick: _cache[9] || (_cache[9] = (...args) => $options.resetDraft && $options.resetDraft(...args))
          }, vue.toDisplayString($options.texts.reset), 9, _hoisted_54),
          vue.createElementVNode(
            "button",
            {
              type: "button",
              class: "btn btn-primary data-table-columns-save",
              onClick: _cache[10] || (_cache[10] = (...args) => $options.saveColumns && $options.saveColumns(...args))
            },
            vue.toDisplayString($options.texts.save),
            1
            /* TEXT */
          )
        ]),
        default: vue.withCtx(() => [
          $props.maxColumns ? (vue.openBlock(), vue.createElementBlock(
            "p",
            _hoisted_48,
            vue.toDisplayString($options.limitText),
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true),
          vue.createElementVNode("div", _hoisted_49, [
            (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              null,
              vue.renderList($options.choosableColumns, (column) => {
                return vue.openBlock(), vue.createElementBlock(
                  "label",
                  {
                    key: column.key,
                    class: vue.normalizeClass(["list-group-item data-table-columns-option", { disabled: $options.isLocked(column.key) }])
                  },
                  [
                    vue.createElementVNode("span", _hoisted_50, [
                      vue.createTextVNode(
                        vue.toDisplayString(column.label) + " ",
                        1
                        /* TEXT */
                      ),
                      $data.draftKeys.includes(column.key) && $data.foldedKeys.includes(column.key) ? (vue.openBlock(), vue.createElementBlock(
                        "span",
                        _hoisted_51,
                        vue.toDisplayString($options.texts.hiddenAtWidth),
                        1
                        /* TEXT */
                      )) : vue.createCommentVNode("v-if", true)
                    ]),
                    vue.createElementVNode("span", _hoisted_52, [
                      vue.createElementVNode("input", {
                        id: `${$props.tableId}-column-${column.key}`,
                        class: "form-check-input",
                        type: "checkbox",
                        role: "switch",
                        checked: $data.draftKeys.includes(column.key),
                        disabled: $options.isLocked(column.key),
                        onChange: ($event) => $options.toggleDraft(column.key, $event.target.checked)
                      }, null, 40, _hoisted_53)
                    ])
                  ],
                  2
                  /* CLASS */
                );
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ])
        ]),
        _: 1
        /* STABLE */
      }, 8, ["show", "title"])) : vue.createCommentVNode("v-if", true)
    ], 10, _hoisted_1$m);
  }
  const C5 = /* @__PURE__ */ _export_sfc(_sfc_main$p, [["render", _sfc_render$p]]);
  const carriesFiles = (event) => {
    var _a;
    return Array.prototype.includes.call(((_a = event.dataTransfer) == null ? void 0 : _a.types) || [], "Files");
  };
  const _sfc_main$o = {
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
  const _hoisted_1$l = { class: "drop-zone-label" };
  function _sfc_render$o(_ctx, _cache, $props, $setup, $data, $options) {
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
                vue.createElementVNode("span", _hoisted_1$l, [
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
  const C6 = /* @__PURE__ */ _export_sfc(_sfc_main$o, [["render", _sfc_render$o]]);
  const _sfc_main$n = {
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
  const _hoisted_1$k = ["aria-busy"];
  const _hoisted_2$g = ["data-id"];
  const _hoisted_3$e = {
    key: 1,
    class: "entity-grid-message entity-grid-message-error"
  };
  const _hoisted_4$c = {
    role: "alert",
    class: "entity-grid-message-text"
  };
  const _hoisted_5$8 = {
    key: 2,
    class: "entity-grid-message entity-grid-message-empty"
  };
  const _hoisted_6$7 = {
    role: "status",
    class: "entity-grid-message-text"
  };
  const _hoisted_7$5 = {
    key: 0,
    class: "entity-grid-error cards-error"
  };
  const _hoisted_8$5 = {
    role: "alert",
    class: "entity-grid-message-text"
  };
  const _hoisted_9$4 = {
    key: 1,
    ref: "sentinel",
    class: "entity-grid-more cards-more"
  };
  const _hoisted_10$4 = ["aria-label"];
  function _sfc_render$n(_ctx, _cache, $props, $setup, $data, $options) {
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
              ], 14, _hoisted_2$g);
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
              )) : $props.error ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$e, [
                vue.createElementVNode(
                  "p",
                  _hoisted_4$c,
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
              ])) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$8, [
                vue.createElementVNode("p", _hoisted_6$7, [
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
        ], 8, _hoisted_1$k),
        $props.error && $props.items.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_7$5, [
          vue.createElementVNode(
            "p",
            _hoisted_8$5,
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
          _hoisted_9$4,
          [
            $props.loading ? (vue.openBlock(), vue.createElementBlock("span", {
              key: 0,
              class: "spinner-border spinner-border-sm",
              role: "status",
              "aria-label": $options.loadingLabel
            }, null, 8, _hoisted_10$4)) : (vue.openBlock(), vue.createElementBlock(
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
  const C9 = /* @__PURE__ */ _export_sfc(_sfc_main$n, [["render", _sfc_render$n]]);
  let uid$3 = 0;
  const _sfc_main$m = {
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
        titleId: `page-header-title-${++uid$3}`
      };
    },
    computed: {
      backLabel() {
        return vue$1.i18n.t("base", "Back");
      }
    }
  };
  const _hoisted_1$j = ["aria-labelledby"];
  const _hoisted_2$f = {
    key: 0,
    class: "page-header-bar"
  };
  const _hoisted_3$d = ["href", "aria-label", "title"];
  const _hoisted_4$b = {
    key: 1,
    class: "page-header-body"
  };
  function _sfc_render$m(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_HeaderActions = vue.resolveComponent("HeaderActions");
    return vue.openBlock(), vue.createElementBlock("section", {
      class: "page-header",
      "aria-labelledby": $props.title ? $data.titleId : null
    }, [
      $props.title || $props.actions.length || $props.menu.length || _ctx.$slots.actions ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$f, [
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
        ])], 8, _hoisted_3$d)) : vue.createCommentVNode("v-if", true),
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
      _ctx.$slots.default ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$b, [
        vue.renderSlot(_ctx.$slots, "default")
      ])) : vue.createCommentVNode("v-if", true)
    ], 8, _hoisted_1$j);
  }
  const C16 = /* @__PURE__ */ _export_sfc(_sfc_main$m, [["render", _sfc_render$m]]);
  const _sfc_main$l = {
    name: "EntityBrowser",
    components: { EntityGrid: C9, FilterBar: C11, PageHeader: C16 },
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
  const _hoisted_1$i = { class: "entity-browser" };
  const _hoisted_2$e = {
    key: 0,
    class: "entity-browser-notice"
  };
  function _sfc_render$l(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FilterBar = vue.resolveComponent("FilterBar");
    const _component_PageHeader = vue.resolveComponent("PageHeader");
    const _component_EntityGrid = vue.resolveComponent("EntityGrid");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$i, [
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
      _ctx.$slots.notice ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$e, [
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
  const C8 = /* @__PURE__ */ _export_sfc(_sfc_main$l, [["render", _sfc_render$l]]);
  const _sfc_main$k = {
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
  function _sfc_render$k(_ctx, _cache, $props, $setup, $data, $options) {
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
  const C10 = /* @__PURE__ */ _export_sfc(_sfc_main$k, [["render", _sfc_render$k]]);
  const _sfc_main$j = {
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
  const _hoisted_1$h = {
    key: 0,
    class: "alert alert-danger error-summary"
  };
  function _sfc_render$j(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "form",
      {
        onSubmit: _cache[0] || (_cache[0] = vue.withModifiers((...args) => $options.onSubmit && $options.onSubmit(...args), ["prevent"]))
      },
      [
        $options.unownedErrorMessages.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$h, [
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
  const C14 = /* @__PURE__ */ _export_sfc(_sfc_main$j, [["render", _sfc_render$j]]);
  const _sfc_main$i = {
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
  const _hoisted_1$g = ["for"];
  const _hoisted_2$d = ["id", "name", "type", "placeholder", "disabled", "aria-required", "aria-invalid", "aria-describedby"];
  const _hoisted_3$c = ["id"];
  const _hoisted_4$a = ["id"];
  function _sfc_render$i(_ctx, _cache, $props, $setup, $data, $options) {
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
        }, vue.toDisplayString(_ctx.label), 9, _hoisted_1$g)) : vue.createCommentVNode("v-if", true),
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
        }, null, 10, _hoisted_2$d), [
          [vue.vModelDynamic, $options.internalValue]
        ]),
        _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 1,
          id: _ctx.hintId,
          class: "form-text text-muted"
        }, vue.toDisplayString(_ctx.hint), 9, _hoisted_3$c)) : vue.createCommentVNode("v-if", true),
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
        ], 8, _hoisted_4$a)) : vue.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C28 = /* @__PURE__ */ _export_sfc(_sfc_main$i, [["render", _sfc_render$i]]);
  const _sfc_main$h = {
    name: "ImageAltTextModal",
    components: { UiModal: C31, HumHubForm: C14, TextField: C28 },
    i18nCategories: ["ContentModule.richtexteditor", "base"],
    props: {
      src: { type: String, default: null },
      required: { type: Boolean, default: false }
    },
    data() {
      return {
        show: false,
        alt: "",
        saved: false
      };
    },
    computed: {
      titleLabel() {
        return vue$1.i18n.t("ContentModule.richtexteditor", "Describe the image");
      },
      altLabel() {
        return vue$1.i18n.t("ContentModule.richtexteditor", "Alternative text");
      },
      hintLabel() {
        return this.required ? vue$1.i18n.t("ContentModule.richtexteditor", "Describe the image for people who cannot see it.") : vue$1.i18n.t("ContentModule.richtexteditor", "Describe the image for people who cannot see it. Leave it empty if the image is purely decorative.");
      },
      requiredMessage() {
        return vue$1.i18n.t("ContentModule.richtexteditor", "Please describe the image.");
      },
      skipLabel() {
        return vue$1.i18n.t("ContentModule.richtexteditor", "Skip");
      },
      removeLabel() {
        return vue$1.i18n.t("ContentModule.richtexteditor", "Remove image");
      },
      saveLabel() {
        return vue$1.i18n.t("base", "Save");
      }
    },
    mounted() {
      this.show = true;
    },
    methods: {
      focusInput() {
        var _a, _b;
        (_b = (_a = this.$refs.field) == null ? void 0 : _a.$refs.input) == null ? void 0 : _b.focus();
      },
      save() {
        if (this.required && this.alt.trim() === "") {
          this.$refs.form.setErrors({ alt: [this.requiredMessage] });
          this.focusInput();
          return;
        }
        this.saved = true;
        this.show = false;
      },
      onClosed() {
        const event = this.saved ? new CustomEvent("altText:save", { bubbles: true, detail: { alt: this.alt.trim() } }) : new CustomEvent("altText:cancel", { bubbles: true });
        this.$el.dispatchEvent(event);
      }
    }
  };
  const _hoisted_1$f = ["id"];
  const _hoisted_2$c = ["src"];
  function _sfc_render$h(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_TextField = vue.resolveComponent("TextField");
    const _component_HumHubForm = vue.resolveComponent("HumHubForm");
    const _component_UiModal = vue.resolveComponent("UiModal");
    return vue.openBlock(), vue.createBlock(_component_UiModal, {
      show: $data.show,
      "onUpdate:show": _cache[3] || (_cache[3] = ($event) => $data.show = $event),
      title: $options.titleLabel,
      "backdrop-close": !$props.required,
      keyboard: !$props.required,
      onOpened: $options.focusInput,
      onClosed: $options.onClosed
    }, vue.createSlots({
      footer: vue.withCtx(() => [
        vue.createElementVNode(
          "button",
          {
            type: "button",
            class: "btn btn-light",
            onClick: _cache[1] || (_cache[1] = ($event) => $data.show = false)
          },
          vue.toDisplayString($props.required ? $options.removeLabel : $options.skipLabel),
          1
          /* TEXT */
        ),
        vue.createElementVNode(
          "button",
          {
            type: "button",
            class: "btn btn-primary",
            onClick: _cache[2] || (_cache[2] = (...args) => $options.save && $options.save(...args))
          },
          vue.toDisplayString($options.saveLabel),
          1
          /* TEXT */
        )
      ]),
      default: vue.withCtx(() => [
        $props.src ? (vue.openBlock(), vue.createElementBlock("img", {
          key: 0,
          src: $props.src,
          alt: "",
          class: "image-alt-text-preview"
        }, null, 8, _hoisted_2$c)) : vue.createCommentVNode("v-if", true),
        vue.createVNode(_component_HumHubForm, {
          ref: "form",
          "model-name": "ImageAltText",
          onSubmit: $options.save
        }, {
          default: vue.withCtx(() => [
            vue.createVNode(_component_TextField, {
              ref: "field",
              attribute: "alt",
              label: $options.altLabel,
              hint: $options.hintLabel,
              required: $props.required,
              modelValue: $data.alt,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.alt = $event)
            }, null, 8, ["label", "hint", "required", "modelValue"])
          ]),
          _: 1
          /* STABLE */
        }, 8, ["onSubmit"])
      ]),
      _: 2
      /* DYNAMIC */
    }, [
      $props.required ? {
        name: "header",
        fn: vue.withCtx(({ titleId }) => [
          vue.createElementVNode("h5", {
            class: "modal-title",
            id: titleId
          }, vue.toDisplayString($options.titleLabel), 9, _hoisted_1$f)
        ]),
        key: "0"
      } : void 0
    ]), 1032, ["show", "title", "backdrop-close", "keyboard", "onOpened", "onClosed"]);
  }
  const C15 = /* @__PURE__ */ _export_sfc(_sfc_main$h, [["render", _sfc_render$h]]);
  const plainClick = (event) => !(event.button > 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey);
  const _sfc_main$g = {
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
  const _hoisted_1$e = { class: "path-bar" };
  const _hoisted_2$b = ["aria-label", "title"];
  const _hoisted_3$b = ["aria-label"];
  const _hoisted_4$9 = { class: "breadcrumb" };
  const _hoisted_5$7 = ["href", "aria-label", "title", "aria-current"];
  const _hoisted_6$6 = ["aria-current"];
  const _hoisted_7$4 = ["href", "title", "onClick"];
  const _hoisted_8$4 = ["title"];
  function _sfc_render$g(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$e, [
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
      ])], 8, _hoisted_2$b)) : vue.createCommentVNode("v-if", true),
      vue.createElementVNode("nav", {
        class: "path-bar-nav",
        "aria-label": $options.navLabel
      }, [
        vue.createElementVNode("ol", _hoisted_4$9, [
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
              ])], 8, _hoisted_5$7)
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
                }, vue.toDisplayString(crumb.title), 9, _hoisted_7$4)) : (vue.openBlock(), vue.createElementBlock("span", {
                  key: 1,
                  ref_for: true,
                  ref: "current",
                  tabindex: "-1",
                  title: crumb.title
                }, vue.toDisplayString(crumb.title), 9, _hoisted_8$4))
              ], 16, _hoisted_6$6);
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ])
      ], 8, _hoisted_3$b),
      vue.renderSlot(_ctx.$slots, "end")
    ]);
  }
  const C17 = /* @__PURE__ */ _export_sfc(_sfc_main$g, [["render", _sfc_render$g]]);
  const toImage = (value) => {
    if (value === null || value === void 0 || value === "") {
      return null;
    }
    if (typeof value === "object") {
      return typeof value.type === "string" && value.type !== "" ? value : null;
    }
    const text = String(value).trim();
    if (text === "") {
      return null;
    }
    if (text.startsWith("{")) {
      try {
        return toImage(JSON.parse(text));
      } catch (error) {
        return null;
      }
    }
    if (text.includes("<")) {
      vue$1.log.warn("Picker item image given as HTML is not rendered — give an image description instead", text);
      return null;
    }
    if (text.startsWith("/") || text.startsWith("http")) {
      return { type: "url", url: text };
    }
    if (text.startsWith("#")) {
      return { type: "color", color: text };
    }
    if (text.startsWith("ti-")) {
      return { type: "icon", name: text.slice(3) };
    }
    return { type: "icon", name: text };
  };
  const NUMBERS = ["maxSelection", "suggested", "minInput", "maxInput", "maxResults", "search"];
  const BOOLEANS = ["allowNew", "allowClear", "listed", "required"];
  const STRINGS = ["source", "url", "itemKey", "placeholder", "placeholderMore", "label", "newPrefix"];
  const camel = (name) => name.replace(/-([a-z])/g, (all, letter) => letter.toUpperCase());
  const readBoolean = (value) => value === "" || value === "true" || value === "1";
  const readNumber = (value) => {
    if (value === null || value === void 0 || String(value).trim() === "") {
      return void 0;
    }
    const number = Number(value);
    return Number.isNaN(number) ? void 0 : number;
  };
  const labelOf = (select) => {
    const labels = [...select.labels || []];
    return labels.find((label) => !label.contains(select)) || labels[0] || select.closest("label");
  };
  const labelText = (label, select) => {
    if (!label.contains(select)) {
      return label.textContent.trim();
    }
    const copy = label.cloneNode(true);
    copy.querySelectorAll("select, .picker-host").forEach((node) => node.remove());
    return copy.textContent.replace(/\s+/g, " ").trim();
  };
  const readOption = (option) => {
    const image = option.dataset.image ?? (option.dataset.color ? { type: "color", color: option.dataset.color } : null);
    return {
      id: option.value,
      text: option.dataset.text ?? option.textContent.trim(),
      subText: option.dataset.subText ?? null,
      image: typeof image === "string" && image.trim().startsWith("{") ? toImage(image) : image,
      disabled: option.disabled,
      locked: option.hasAttribute("data-locked"),
      group: option.parentElement && option.parentElement.tagName === "OPTGROUP" ? option.parentElement.label : null
    };
  };
  const readSelection = (select) => [...select.options].filter((option) => option.selected && option.value !== "").map(readOption);
  const readConfig = (select) => {
    var _a;
    const config = {};
    let json = {};
    try {
      json = select.dataset.pickerConfig ? JSON.parse(select.dataset.pickerConfig) : {};
    } catch (error) {
      json = {};
    }
    Object.assign(config, json);
    const minimumResultsForSearch = readNumber(select.getAttribute("data-minimum-results-for-search"));
    if (minimumResultsForSearch !== void 0) {
      config.search = minimumResultsForSearch;
    }
    if (select.hasAttribute("data-ui-select2-allow-new")) {
      config.allowNew = true;
    }
    Object.keys(select.dataset).forEach((key) => {
      if (!key.startsWith("picker") || key === "pickerConfig" || key === "pickerBound") {
        return;
      }
      const name = camel(key.slice("picker".length).replace(/^./, (letter) => letter.toLowerCase()));
      const value = select.dataset[key];
      if (NUMBERS.includes(name)) {
        const number = readNumber(value);
        if (number !== void 0) {
          config[name] = number;
        }
      } else if (BOOLEANS.includes(name)) {
        config[name] = readBoolean(value);
      } else if (STRINGS.includes(name)) {
        config[name] = value;
      }
    });
    const hasSource = !!(config.source || config.url);
    const selection = readSelection(select);
    const label = labelOf(select);
    config.multiple = select.multiple;
    config.disabled = select.disabled;
    config.invalid = select.classList.contains("is-invalid");
    config.required = config.required || select.required || select.getAttribute("aria-required") === "true";
    config.autofocus = select.autofocus;
    config.label = config.label || (label ? labelText(label, select) : "");
    config.options = hasSource ? [] : [...select.options].map(readOption);
    config.initialItems = selection;
    config.modelValue = select.multiple ? selection.map((item) => item.id) : ((_a = selection[0]) == null ? void 0 : _a.id) ?? "";
    config.allowClear = config.allowClear ?? [...select.options].some((option) => option.value === "");
    config.name = null;
    return config;
  };
  const pruneAfterReset = (select, initialValues) => {
    [...select.options].filter((option) => !option.selected && !option.defaultSelected).forEach((option) => option.remove());
    const position = (option) => {
      const index = initialValues.indexOf(option.value);
      return index < 0 ? initialValues.length : index;
    };
    [...select.options].sort((a, b) => position(a) - position(b)).forEach((option) => select.appendChild(option));
  };
  const describeImage = (image) => image && typeof image === "object" ? JSON.stringify(image) : null;
  const writeSelection = (select, items, { prune }) => {
    const ids = items.map((item) => item.id);
    const before = [...select.selectedOptions].map((option) => option.value).join("\n");
    const byValue = new Map([...select.options].map((option) => [option.value, option]));
    items.forEach((item) => {
      let option = byValue.get(item.id);
      if (!option) {
        option = select.ownerDocument.createElement("option");
        option.value = item.id;
        option.textContent = item.text;
        option.dataset.text = item.text;
        if (item.subText) {
          option.dataset.subText = item.subText;
        }
        const image = describeImage(item.image);
        if (image) {
          option.dataset.image = image;
        }
      }
      if (prune || !option.parentNode) {
        select.appendChild(option);
      }
    });
    [...select.options].forEach((option) => {
      const chosen = ids.includes(option.value);
      if (!chosen && prune && !option.defaultSelected && option.value !== "") {
        option.remove();
        return;
      }
      option.selected = chosen;
    });
    if (!select.multiple && ids.length === 0) {
      let empty = [...select.options].find((option) => option.value === "");
      if (!empty) {
        empty = select.ownerDocument.createElement("option");
        empty.value = "";
        select.prepend(empty);
      }
      empty.selected = true;
    }
    if ([...select.selectedOptions].map((option) => option.value).join("\n") !== before) {
      select.dispatchEvent(new Event("change", { bubbles: true }));
    }
  };
  const _sfc_main$f = {
    name: "PickerImage",
    props: {
      image: { type: Object, default: null },
      size: { type: Number, default: 24 }
    },
    computed: {
      registered() {
        return this.image ? vue$1.getPickerImage(this.image.type) : null;
      },
      kind() {
        if (!this.image) {
          return null;
        }
        if (["icon", "color", "url"].includes(this.image.type)) {
          return this.image.type;
        }
        return this.registered ? "registered" : null;
      },
      iconClass() {
        const name = String(this.image.name || "");
        return name.startsWith("fa-") ? ["fa", name] : ["ti", `ti-${name}`];
      }
    }
  };
  const _hoisted_1$d = ["src", "width", "height"];
  function _sfc_render$f(_ctx, _cache, $props, $setup, $data, $options) {
    return $options.kind === "icon" ? (vue.openBlock(), vue.createElementBlock(
      "i",
      {
        key: 0,
        class: vue.normalizeClass($options.iconClass),
        "aria-hidden": "true"
      },
      null,
      2
      /* CLASS */
    )) : $options.kind === "color" ? (vue.openBlock(), vue.createElementBlock(
      "span",
      {
        key: 1,
        class: "picker-color",
        style: vue.normalizeStyle({ backgroundColor: $props.image.color }),
        "aria-hidden": "true"
      },
      null,
      4
      /* STYLE */
    )) : $options.kind === "url" ? (vue.openBlock(), vue.createElementBlock("img", {
      key: 2,
      src: $props.image.url,
      width: $props.size,
      height: $props.size,
      class: "rounded",
      alt: ""
    }, null, 8, _hoisted_1$d)) : $options.kind === "registered" ? (vue.openBlock(), vue.createBlock(
      vue.resolveDynamicComponent($options.registered.component),
      vue.normalizeProps(vue.mergeProps({ key: 3 }, $options.registered.toProps($props.image, $props.size))),
      null,
      16
      /* FULL_PROPS */
    )) : vue.createCommentVNode("v-if", true);
  }
  const PickerImage = /* @__PURE__ */ _export_sfc(_sfc_main$f, [["render", _sfc_render$f]]);
  const fold = (text) => String(text).normalize("NFD").replace(new RegExp("\\p{M}", "gu"), "").toLocaleLowerCase();
  const matches = (text, term) => term === "" || fold(text).includes(fold(term));
  const normalizeItem = (raw, itemKey = "id") => ({
    id: String(raw[itemKey] ?? raw.id),
    text: String(raw.text ?? raw.title ?? raw.displayName ?? raw.name ?? raw.id),
    subText: raw.subText ?? raw.displayNameSub ?? null,
    image: toImage(raw.image),
    disabled: raw.disabled === true,
    locked: raw.locked === true
  });
  const hasKey = (raw, itemKey) => !!raw && (raw[itemKey] ?? raw.id) !== void 0 && (raw[itemKey] ?? raw.id) !== null;
  const staticSource = (items) => {
    const all = items.map((item) => normalizeItem(item));
    return {
      remote: false,
      newImage: null,
      search: (term) => Promise.resolve(all.filter((item) => matches(item.text, term)))
    };
  };
  const answerList = (response) => {
    if (Array.isArray(response)) {
      return response;
    }
    if (response && Array.isArray(response.data)) {
      return response.data;
    }
    if (response && Array.isArray(response.results)) {
      return response.results;
    }
    return [];
  };
  const legacySource = (url, itemKey = "id") => ({
    remote: true,
    newImage: null,
    search: (term) => vue$1.client.get(`${url}${url.includes("?") ? "&" : "?"}keyword=${encodeURIComponent(term)}`).then((response) => answerList(response).filter((raw) => hasKey(raw, itemKey)).map((raw) => normalizeItem(raw, itemKey)))
  });
  const createSource = ({ source = null, url = null, options = [], itemKey = "id", params = {} } = {}) => {
    if (source) {
      const factory = vue$1.getPickerSource(source);
      if (factory) {
        const named = factory({ itemKey, params });
        return {
          remote: true,
          newImage: named.newImage ?? null,
          search: (term) => Promise.resolve(named.search(term)).then((items) => (Array.isArray(items) ? items : []).filter((item) => hasKey(item, itemKey)).map((item) => normalizeItem(item, itemKey)))
        };
      }
      vue$1.log.warn(`Picker source "${source}" is not registered — the picker offers its static options only`);
      return staticSource(options);
    }
    return url ? legacySource(url, itemKey) : staticSource(options);
  };
  const SEARCH_DEBOUNCE_MS$1 = 250;
  const DEFAULT_REMOTE_RESULTS = 8;
  const RENDER_STEP = 200;
  let uid$2 = 0;
  const toIds = (value) => (Array.isArray(value) ? value : [value]).filter((id) => id !== null && id !== void 0).map(String).filter((id) => id !== "");
  const sameIds = (a, b) => a.length === b.length && a.every((id, index) => id === b[index]);
  const format = (text, params) => String(text).replace(/\{(\w+)\}/g, (all, name) => params[name] ?? all);
  const DEFAULT_MESSAGES = {
    noResults: () => vue$1.i18n.t("base", "No results found!"),
    inputTooShort: ({ n }) => vue$1.i18n.t("base", "Please enter at least {n} characters.", { n }),
    maximumSelected: ({ n }) => vue$1.i18n.t("base", "You cannot choose more than {n}.", { n }),
    loading: () => vue$1.i18n.t("base", "Loading..."),
    error: () => vue$1.i18n.t("base", "The results could not be loaded."),
    locked: () => vue$1.i18n.t("base", "You are not allowed to remove this item."),
    add: ({ text }) => vue$1.i18n.t("base", "Add: “{text}”", { text }),
    remove: ({ label }) => vue$1.i18n.t("base", "Remove {label}", { label }),
    clear: () => vue$1.i18n.t("base", "Clear selection")
  };
  const _sfc_main$e = {
    name: "PickerInput",
    components: { PickerImage },
    mixins: [comboboxMixin],
    props: {
      modelValue: { type: [String, Number, Array], default: "" },
      multiple: { type: Boolean, default: false },
      maxSelection: { type: Number, default: 0 },
      source: { type: String, default: null },
      url: { type: String, default: null },
      sourceParams: { type: Object, default: () => ({}) },
      itemKey: { type: String, default: "id" },
      options: { type: Array, default: () => [] },
      initialItems: { type: Array, default: () => [] },
      defaultResults: { type: Array, default: () => [] },
      suggested: { type: Number, default: 0 },
      minInput: { type: Number, default: 0 },
      maxInput: { type: Number, default: 0 },
      maxResults: { type: Number, default: null },
      search: { type: Number, default: 5 },
      allowNew: { type: Boolean, default: false },
      newPrefix: { type: String, default: "" },
      allowClear: { type: Boolean, default: true },
      listed: { type: Boolean, default: false },
      lockedIds: { type: Array, default: () => [] },
      disabledIds: { type: Array, default: () => [] },
      placeholder: { type: String, default: "" },
      placeholderMore: { type: String, default: "" },
      label: { type: String, default: "" },
      describedBy: { type: String, default: null },
      disabled: { type: Boolean, default: false },
      required: { type: Boolean, default: false },
      invalid: { type: Boolean, default: false },
      autofocus: { type: Boolean, default: false },
      messages: { type: Object, default: () => ({}) },
      id: { type: String, default: null },
      name: { type: String, default: null },
      selectId: { type: String, default: null }
    },
    emits: ["update:modelValue"],
    data() {
      const key = ++uid$2;
      return {
        chosen: [],
        renderLimit: RENDER_STEP,
        query: "",
        editing: false,
        results: [],
        // The term the current `results` answer (`null`: none).
        resultsTerm: null,
        loading: false,
        failed: false,
        remote: false,
        // Native mode: the config read from the `<select>`, and its live state.
        nativeConfig: null,
        nativeDisabled: false,
        nativeInvalid: false,
        nativeDescribedBy: null,
        select: null,
        listboxId: `picker-input-${key}-listbox`,
        fallbackId: `picker-input-${key}`
      };
    },
    computed: {
      // The effective settings: the props, overridden by what a native `<select>` says.
      cfg() {
        return this.nativeConfig ? { ...this.$props, ...this.nativeConfig } : this.$props;
      },
      inputId() {
        return this.cfg.id || this.fallbackId;
      },
      hasSource() {
        return !!(this.cfg.source || this.cfg.url);
      },
      mode() {
        if (this.cfg.multiple && this.cfg.maxSelection !== 1) {
          return "multiple";
        }
        return this.hasSource || this.cfg.multiple ? "single" : "select";
      },
      isDisabled() {
        return this.cfg.disabled || this.nativeDisabled;
      },
      isInvalid() {
        return this.cfg.invalid || this.nativeInvalid;
      },
      staticItems() {
        return this.cfg.options.filter((option) => String(option.id) !== "").map((option) => this.withFlags({ ...normalizeItem(option), group: option.group ?? null }));
      },
      // The text of an empty option (`prompt`) of an enhanced select, its placeholder.
      emptyText() {
        const empty = this.cfg.options.find((option) => String(option.id) === "");
        return empty ? String(empty.text ?? "") : "";
      },
      searchable() {
        if (this.mode !== "select") {
          return true;
        }
        if (this.cfg.search < 0) {
          return false;
        }
        return this.staticItems.length >= this.cfg.search;
      },
      term() {
        return this.query.trim();
      },
      chosenIds() {
        return this.chosen.map((item) => item.id);
      },
      limitReached() {
        return this.mode === "multiple" && this.cfg.maxSelection > 0 && this.chosen.length >= this.cfg.maxSelection;
      },
      staticLimit() {
        return this.cfg.maxResults ?? 0;
      },
      offered() {
        if (this.limitReached) {
          return [];
        }
        let list = this.remote ? this.results : this.staticItems.filter((item) => matches(item.text, this.editing ? this.term : ""));
        if (this.mode === "multiple") {
          list = list.filter((item) => !this.chosenIds.includes(item.id));
        }
        const limit = this.remote ? this.remoteLimit(this.resultsTerm) : this.staticLimit;
        return limit > 0 ? list.slice(0, limit) : list;
      },
      newChoice() {
        if (!this.cfg.allowNew || this.limitReached || this.term.length < Math.max(this.cfg.minInput, 1)) {
          return null;
        }
        const text = this.term.toLocaleLowerCase();
        if ([...this.offered, ...this.chosen].some((item) => item.text.toLocaleLowerCase() === text)) {
          return null;
        }
        return {
          id: `${this.cfg.newPrefix}${this.term}`,
          text: this.term,
          subText: null,
          image: { type: "icon", name: "circle-plus" },
          disabled: false,
          locked: false,
          isNew: true
        };
      },
      choices() {
        return this.newChoice ? [...this.offered, this.newChoice] : this.offered;
      },
      comboboxOptions() {
        return this.choices;
      },
      // One size per list: larger once an option has a sub text (two lines).
      optionImageSize() {
        return this.choices.some((item) => item.subText) ? 40 : 32;
      },
      rows() {
        const rows = [];
        let group = null;
        this.choices.forEach((item, index) => {
          if (index >= this.renderLimit) {
            return;
          }
          if (item.group && item.group !== group) {
            group = item.group;
            rows.push({ key: `group:${group}`, header: group });
          }
          rows.push({ key: `${item.isNew ? "new" : "item"}:${item.id}`, item, index });
        });
        return rows;
      },
      feedback() {
        if (this.limitReached) {
          return this.msg("maximumSelected", { n: this.cfg.maxSelection });
        }
        if (this.choices.length) {
          return null;
        }
        if (this.loading) {
          return this.msg("loading");
        }
        if (this.failed) {
          return this.msg("error");
        }
        if (this.remote && this.term.length < this.cfg.minInput) {
          return this.msg("inputTooShort", { n: this.cfg.minInput });
        }
        if (this.remote && this.term === "") {
          return null;
        }
        return this.msg("noResults");
      },
      inputText() {
        if (this.mode === "multiple" || this.editing) {
          return this.query;
        }
        return this.chosen.length ? this.chosen[0].text : "";
      },
      inputPlaceholder() {
        if (this.mode === "multiple") {
          return this.chosen.length ? this.cfg.placeholderMore : this.cfg.placeholder || this.emptyText;
        }
        if (this.editing && this.chosen.length) {
          return this.chosen[0].text;
        }
        return this.cfg.placeholder || this.emptyText;
      },
      accessibleName() {
        return this.cfg.label || this.cfg.placeholder || null;
      },
      activeDescendant() {
        return this.open && this.activeIndex >= 0 && this.activeIndex < this.choices.length ? this.optionId(this.activeIndex) : null;
      },
      clearable() {
        if (this.isDisabled || !this.chosen.some((item) => !item.locked)) {
          return false;
        }
        return this.mode !== "select" || this.cfg.allowClear;
      },
      hiddenName() {
        return this.cfg.multiple && this.chosen.length ? `${this.cfg.name}[]` : this.cfg.name;
      },
      hiddenValues() {
        return this.chosen.length ? this.chosenIds : [""];
      },
      rootClass() {
        return {
          "is-open": this.open,
          "has-selection": this.chosen.length > 0,
          "is-disabled": this.isDisabled,
          "is-loading": this.loading,
          "is-multiple": this.mode === "multiple",
          "is-single": this.mode === "single",
          "is-select": this.mode === "select",
          "is-listed": this.cfg.listed,
          "is-required": this.cfg.required,
          "is-drop-up": this.dropUp,
          "is-editing": this.editing
        };
      }
    },
    watch: {
      modelValue(value) {
        if (this.select) {
          return;
        }
        const ids = toIds(value).slice(0, this.mode === "multiple" ? void 0 : 1);
        if (!sameIds(ids, this.chosenIds)) {
          this.chosen = this.lockedFirst(this.itemsFor(ids));
        }
      },
      isDisabled(disabled) {
        if (disabled) {
          this.close();
        }
      },
      activeIndex(index) {
        if (index >= this.renderLimit) {
          this.renderLimit = index + RENDER_STEP;
        }
      },
      term() {
        this.renderLimit = RENDER_STEP;
      },
      choices(choices) {
        if (this.activeIndex >= choices.length) {
          this.activeIndex = choices.length - 1;
        }
        if (this.open && this.editing && this.activeIndex < 0 && choices.length) {
          this.activeIndex = 0;
        }
      },
      comboboxOptions() {
        this.$nextTick(() => this.placeDrawer());
      },
      feedback() {
        this.$nextTick(() => this.placeDrawer());
      }
    },
    created() {
      this.known = /* @__PURE__ */ new Map();
      this.pendingEnter = null;
      this.cache = /* @__PURE__ */ new Map();
      this.seq = 0;
      this.timer = null;
      this.initNative();
      this.sourceImpl = createSource({
        source: this.cfg.source,
        url: this.cfg.url,
        options: this.cfg.options,
        itemKey: this.cfg.itemKey,
        params: this.cfg.sourceParams
      });
      this.remote = this.sourceImpl.remote;
      this.remember(this.staticItems);
      this.remember(this.cfg.initialItems.map((item) => this.withFlags(normalizeItem(item))));
      this.remember(this.cfg.defaultResults.map((item) => this.withFlags(normalizeItem(item))));
      if (!this.select) {
        const ids = toIds(this.modelValue).slice(0, this.mode === "multiple" ? void 0 : 1);
        this.chosen = this.lockedFirst(this.itemsFor(ids));
      }
    },
    mounted() {
      var _a;
      this.mountNative();
      if (this.cfg.autofocus) {
        (_a = this.$refs.input) == null ? void 0 : _a.focus();
      }
    },
    beforeUnmount() {
      clearTimeout(this.timer);
      this.unmountNative();
    },
    methods: {
      // Native mode: binds to the `<select>` of `selectId` (picker/nativeSelect.js). Runs in
      // `created()` before the source is created, so `cfg` holds the select's settings there.
      initNative() {
        if (!this.selectId) {
          return;
        }
        this.select = document.getElementById(this.selectId);
        if (!this.select) {
          vue$1.log.error(`PickerInput: no <select id="${this.selectId}">`);
          return;
        }
        const { modelValue, initialItems, disabled, invalid, ...config } = readConfig(this.select);
        this.nativeConfig = config;
        this.initialValues = [...this.select.options].map((option) => option.value);
        this.nativeDisabled = this.select.disabled;
        this.nativeInvalid = this.select.classList.contains("is-invalid");
        this.nativeDescribedBy = this.select.getAttribute("aria-describedby");
        this.chosen = this.nativeItems(initialItems);
      },
      // The items read from the `<select>`, their images as descriptions.
      nativeItems(items) {
        return this.lockedFirst(items.map((item) => this.withFlags({ ...item, image: toImage(item.image) })));
      },
      mountNative() {
        var _a;
        const select = this.select;
        if (!select) {
          return;
        }
        this.nativeAttributes = Object.fromEntries(["tabindex", "aria-hidden", "autofocus"].map((name) => [name, select.getAttribute(name)]));
        select.classList.add("visually-hidden");
        select.setAttribute("tabindex", "-1");
        select.setAttribute("aria-hidden", "true");
        select.removeAttribute("autofocus");
        this.onNativeChange = () => {
          if (this.writing) {
            return;
          }
          if (!this.hasSource) {
            this.nativeConfig = { ...this.nativeConfig, options: [...select.options].map(readOption) };
            this.remember(this.staticItems);
          }
          this.chosen = this.nativeItems(readSelection(select));
          this.remember(this.chosen);
        };
        this.onNativeReset = () => {
          this.resetTimer = setTimeout(() => {
            if (this.hasSource) {
              pruneAfterReset(select, this.initialValues);
            }
            this.onNativeChange();
          }, 0);
        };
        if (window.jQuery) {
          window.jQuery(select).on("change.picker", this.onNativeChange);
        } else {
          select.addEventListener("change", this.onNativeChange);
        }
        this.nativeForm = select.form;
        (_a = this.nativeForm) == null ? void 0 : _a.addEventListener("reset", this.onNativeReset);
        this.observer = new MutationObserver((mutations) => {
          this.nativeDisabled = select.disabled;
          this.nativeInvalid = select.classList.contains("is-invalid");
          this.nativeDescribedBy = select.getAttribute("aria-describedby");
          if (mutations.some((mutation) => mutation.type === "childList")) {
            this.followNativeOptions();
          }
        });
        this.observer.observe(select, { attributes: true, attributeFilter: ["disabled", "class", "aria-describedby"], childList: true, subtree: true });
        const label = labelOf(select);
        if (label) {
          this.labelEl = label;
          this.onLabelClick = (event) => {
            var _a2, _b;
            event.preventDefault();
            if (!((_a2 = this.$refs.root) == null ? void 0 : _a2.contains(event.target))) {
              (_b = this.$refs.input) == null ? void 0 : _b.focus();
            }
          };
          label.addEventListener("click", this.onLabelClick);
        }
      },
      unmountNative() {
        var _a, _b, _c;
        const select = this.select;
        if (!select) {
          return;
        }
        if (window.jQuery) {
          window.jQuery(select).off("change.picker", this.onNativeChange);
        } else {
          select.removeEventListener("change", this.onNativeChange);
        }
        (_a = this.nativeForm) == null ? void 0 : _a.removeEventListener("reset", this.onNativeReset);
        clearTimeout(this.resetTimer);
        (_b = this.observer) == null ? void 0 : _b.disconnect();
        (_c = this.labelEl) == null ? void 0 : _c.removeEventListener("click", this.onLabelClick);
        if (select.isConnected) {
          select.classList.remove("visually-hidden");
          Object.entries(this.nativeAttributes).forEach(([name, value]) => value === null ? select.removeAttribute(name) : select.setAttribute(name, value));
          delete select.dataset.pickerBound;
        }
      },
      followNativeOptions() {
        if (!this.select || this.writing) {
          return;
        }
        if (!this.hasSource) {
          this.nativeConfig = { ...this.nativeConfig, options: [...this.select.options].map(readOption) };
          this.remember(this.staticItems);
        }
        this.followNativeSelection();
      },
      // Reads the selection of the `<select>` when it differs from the chosen items as a set (an
      // enhanced select keeps its option order, the chips the chosen one).
      followNativeSelection() {
        const items = readSelection(this.select);
        if (items.length !== this.chosenIds.length || items.some((item) => !this.chosenIds.includes(item.id))) {
          this.chosen = this.nativeItems(items);
          this.remember(this.chosen);
        }
      },
      writeNative(items) {
        if (!this.select) {
          return;
        }
        this.writing = true;
        try {
          writeSelection(this.select, items, { prune: this.hasSource });
        } finally {
          this.writing = false;
        }
        this.followNativeSelection();
      },
      msg(key, params = {}) {
        const given = this.cfg.messages[key];
        return typeof given === "string" && given !== "" ? format(given, params) : DEFAULT_MESSAGES[key](params);
      },
      withFlags(item) {
        return {
          ...item,
          locked: item.locked || this.cfg.lockedIds.map(String).includes(item.id),
          disabled: item.disabled || this.cfg.disabledIds.map(String).includes(item.id)
        };
      },
      remember(items) {
        items.forEach((item) => this.known.set(item.id, item));
      },
      itemsFor(ids) {
        return ids.map((id) => this.known.get(id) || this.chosen.find((item) => item.id === id) || this.withFlags(normalizeItem({ id, text: id })));
      },
      lockedFirst(items) {
        return [...items.filter((item) => item.locked), ...items.filter((item) => !item.locked)];
      },
      isChosen(id) {
        return this.chosenIds.includes(id);
      },
      highlight(item) {
        if (item.isNew) {
          return [{ text: this.msg("add", { text: item.text }), match: false }];
        }
        const term = this.editing ? this.term : "";
        const at = term === "" ? -1 : item.text.toLocaleLowerCase().indexOf(term.toLocaleLowerCase());
        if (at < 0) {
          return [{ text: item.text, match: false }];
        }
        return [
          { text: item.text.slice(0, at), match: false },
          { text: item.text.slice(at, at + term.length), match: true },
          { text: item.text.slice(at + term.length), match: false }
        ].filter((part) => part.text !== "");
      },
      commit(items) {
        this.chosen = items;
        this.remember(items);
        this.writeNative(items);
        this.$emit("update:modelValue", this.cfg.multiple ? [...this.chosenIds] : this.chosenIds[0] ?? "");
      },
      choose(item) {
        var _a, _b, _c;
        if (!item || item.disabled) {
          return;
        }
        const chosenItem = item.isNew ? { ...item, isNew: false, image: ((_a = this.sourceImpl) == null ? void 0 : _a.newImage) ?? null } : item;
        if (this.mode === "multiple") {
          if (!this.isChosen(item.id)) {
            this.commit([...this.chosen, chosenItem]);
          }
          this.query = "";
          this.runSearch();
          (_b = this.$refs.input) == null ? void 0 : _b.focus();
          return;
        }
        this.close();
        if (this.chosenIds[0] !== item.id && !((_c = this.chosen[0]) == null ? void 0 : _c.locked)) {
          this.commit([chosenItem]);
        }
      },
      remove(id) {
        var _a;
        const item = this.chosen.find((entry) => entry.id === id);
        if (!item || item.locked || this.isDisabled) {
          return;
        }
        this.commit(this.chosen.filter((entry) => entry.id !== id));
        (_a = this.$refs.input) == null ? void 0 : _a.focus();
      },
      clear() {
        var _a;
        this.query = "";
        this.editing = false;
        this.commit(this.chosen.filter((item) => item.locked));
        (_a = this.$refs.input) == null ? void 0 : _a.focus();
      },
      runSearch() {
        if (!this.remote) {
          return;
        }
        clearTimeout(this.timer);
        this.failed = false;
        const term = this.term;
        if (term === "") {
          this.suggest();
          return;
        }
        if (term.length < this.cfg.minInput) {
          this.seq++;
          this.loading = false;
          this.setResults(null, []);
          return;
        }
        if (this.cache.has(term)) {
          this.seq++;
          this.loading = false;
          this.setResults(term, this.cache.get(term));
          return;
        }
        const seq = ++this.seq;
        this.loading = true;
        this.setResults(null, []);
        this.timer = setTimeout(() => this.load(term, seq), SEARCH_DEBOUNCE_MS$1);
      },
      // What the list shows before anything is typed (remote sources).
      suggest() {
        this.seq++;
        this.loading = false;
        if (this.cfg.defaultResults.length) {
          this.setResults("", this.cfg.defaultResults.map((item) => this.withFlags(normalizeItem(item))));
          return;
        }
        if (this.remoteLimit("") === 0) {
          this.setResults("", []);
          return;
        }
        if (this.cache.has("")) {
          this.setResults("", this.cache.get(""));
          return;
        }
        this.load("", ++this.seq);
      },
      // How many items of the answer to `term` are listed: the suggestions for the empty term
      // (`suggested`, else the remote limit when no input is required), `maxResults` (8) else.
      remoteLimit(term) {
        const max = this.cfg.maxResults ?? DEFAULT_REMOTE_RESULTS;
        if (term !== "") {
          return max;
        }
        if (this.cfg.defaultResults.length) {
          return Infinity;
        }
        if (this.cfg.suggested > 0) {
          return this.cfg.suggested;
        }
        return this.cfg.minInput === 0 ? max : 0;
      },
      // Enter pressed on the new item before the answer came: an offered item of the typed
      // name is chosen, else the new item.
      chooseTypedText() {
        this.pendingEnter = null;
        const text = this.term.toLocaleLowerCase();
        this.choose(this.offered.find((item) => item.text.toLocaleLowerCase() === text) ?? this.newChoice);
      },
      setResults(term, list) {
        this.resultsTerm = term;
        this.results = list;
      },
      load(term, seq) {
        this.loading = true;
        new Promise((resolve) => {
          resolve(this.sourceImpl.search(term));
        }).then((items) => {
          if (seq !== this.seq) {
            return;
          }
          const list = items.map((item) => this.withFlags(item));
          this.cache.set(term, list);
          this.remember(list);
          this.setResults(term, list);
          if (this.pendingEnter === term) {
            this.chooseTypedText();
          }
        }).catch((error) => {
          if (seq !== this.seq) {
            return;
          }
          this.failed = true;
          this.setResults(null, []);
          vue$1.log.error(error);
          if (this.pendingEnter === term) {
            this.chooseTypedText();
          }
        }).finally(() => {
          if (seq === this.seq) {
            this.loading = false;
          }
        });
      },
      // Whether the listed results answer another term than the current one.
      resultsStale() {
        return this.remote && this.resultsTerm !== this.term && !this.loading;
      },
      // Near the end of the rendered rows: the next ones.
      onListScroll(event) {
        const list = event.target;
        if (this.renderLimit < this.choices.length && list.scrollTop + list.clientHeight >= list.scrollHeight - 100) {
          this.renderLimit += RENDER_STEP;
        }
      },
      onFocus() {
        if (this.resultsStale()) {
          this.runSearch();
        }
      },
      // The input of a single picker / searchable select shows the chosen label: typing
      // replaces it instead of being appended to it — the browser inserts into an emptied input.
      onBeforeInput(event) {
        if (this.mode !== "multiple" && this.searchable && !this.editing && event.target.value !== "") {
          event.target.value = "";
        }
      },
      // Leaving the closed control drops typed text (after Escape, say) — the value shows again.
      onBlur() {
        if (!this.open) {
          this.close();
        }
      },
      onFieldClick() {
        var _a;
        if (this.isDisabled) {
          return;
        }
        (_a = this.$refs.input) == null ? void 0 : _a.focus();
        if (this.open && this.mode === "select") {
          this.hide();
          return;
        }
        this.show(-1);
      },
      onInput(event) {
        this.pendingEnter = null;
        this.query = event.target.value;
        this.editing = true;
        this.runSearch();
        this.show(0);
        this.activeIndex = 0;
      },
      show(activeIndex = -1) {
        if (this.isDisabled || this.open) {
          return;
        }
        this.open = true;
        this.activeIndex = activeIndex;
        this.bindOutside();
        if (this.resultsStale()) {
          this.runSearch();
        }
      },
      hide() {
        if (!this.open) {
          return;
        }
        this.open = false;
        this.activeIndex = -1;
        this.unbindOutside();
      },
      close() {
        this.pendingEnter = null;
        clearTimeout(this.timer);
        if (this.loading) {
          this.seq++;
          this.loading = false;
        }
        this.query = "";
        this.editing = false;
        this.hide();
      },
      typeAhead(char) {
        const count = this.choices.length;
        if (!count) {
          return;
        }
        if (!this.open) {
          this.show(-1);
        }
        for (let step = 1; step <= count; step++) {
          const index = (this.activeIndex + step + count) % count;
          if (this.choices[index].text.toLocaleLowerCase().startsWith(char.toLocaleLowerCase())) {
            this.moveTo(index);
            return;
          }
        }
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
          case "PageDown":
          case "PageUp":
            if (this.open && this.choices.length) {
              event.preventDefault();
              this.movePage(event.key === "PageDown" ? 1 : -1);
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
            event.preventDefault();
            if (!this.open) {
              this.show(0);
            } else {
              const item = this.choices[this.activeIndex];
              if ((item == null ? void 0 : item.isNew) && this.remote && !this.failed && this.resultsTerm !== this.term) {
                this.pendingEnter = this.term;
                break;
              }
              this.choose(item);
            }
            break;
          case "Escape":
            if (this.open || this.query !== "") {
              event.preventDefault();
              event.stopPropagation();
              if (this.open) {
                this.hide();
              } else {
                this.close();
              }
            }
            break;
          case "Backspace":
            if (this.mode === "multiple" && this.query === "") {
              const lastRemovable = [...this.chosen].reverse().find((item) => !item.locked);
              if (lastRemovable) {
                event.preventDefault();
                this.remove(lastRemovable.id);
              }
            }
            break;
          case "Tab":
            this.close();
            break;
          default:
            if (!this.searchable && event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
              this.typeAhead(event.key);
            }
        }
      }
    }
  };
  const _hoisted_1$c = ["data-value", "title"];
  const _hoisted_2$a = { class: "picker-chip-label" };
  const _hoisted_3$a = ["aria-label", "title", "onClick"];
  const _hoisted_4$8 = ["id", "aria-expanded", "aria-controls", "aria-activedescendant", "aria-label", "aria-describedby", "aria-busy", "aria-invalid", "aria-required", "placeholder", "value", "readonly", "disabled", "autofocus", "maxlength"];
  const _hoisted_5$6 = {
    key: 0,
    class: "spinner-border spinner-border-sm select-menu-spinner",
    "aria-hidden": "true"
  };
  const _hoisted_6$5 = {
    key: 1,
    class: "ti ti-chevron-down select-menu-chevron",
    "aria-hidden": "true"
  };
  const _hoisted_7$3 = ["aria-label", "title"];
  const _hoisted_8$3 = ["id", "aria-label", "aria-multiselectable", "aria-busy"];
  const _hoisted_9$3 = {
    key: 0,
    class: "dropdown-header",
    role: "presentation"
  };
  const _hoisted_10$3 = ["id", "aria-selected", "aria-disabled", "onClick", "onMousemove"];
  const _hoisted_11$3 = { class: "picker-option-text" };
  const _hoisted_12$3 = { class: "picker-option-label" };
  const _hoisted_13 = { key: 0 };
  const _hoisted_14 = {
    key: 0,
    class: "picker-option-subtext"
  };
  const _hoisted_15 = {
    key: 0,
    class: "dropdown-item-text select-menu-feedback",
    role: "presentation"
  };
  const _hoisted_16 = {
    key: 3,
    class: "list-unstyled picker-list"
  };
  const _hoisted_17 = ["data-value", "title"];
  const _hoisted_18 = { class: "picker-list-item-text" };
  const _hoisted_19 = { class: "picker-list-item-label" };
  const _hoisted_20 = {
    key: 0,
    class: "picker-list-item-subtext"
  };
  const _hoisted_21 = ["aria-label", "title", "onClick"];
  const _hoisted_22 = ["name", "value"];
  const _hoisted_23 = {
    class: "visually-hidden",
    role: "status"
  };
  function _sfc_render$e(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_PickerImage = vue.resolveComponent("PickerImage");
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        ref: "root",
        class: vue.normalizeClass(["select-menu picker", $options.rootClass])
      },
      [
        vue.createElementVNode(
          "div",
          {
            ref: "field",
            class: vue.normalizeClass(["form-control picker-field", { "is-invalid": $options.isInvalid }]),
            onClick: _cache[5] || (_cache[5] = (...args) => $options.onFieldClick && $options.onFieldClick(...args))
          },
          [
            $options.mode === "multiple" && !$options.cfg.listed ? (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              { key: 0 },
              vue.renderList($data.chosen, (item) => {
                return vue.openBlock(), vue.createElementBlock("span", {
                  key: item.id,
                  class: vue.normalizeClass(["picker-chip", { "is-locked": item.locked }]),
                  "data-value": item.id,
                  title: item.locked ? $options.msg("locked") : null
                }, [
                  item.image ? (vue.openBlock(), vue.createBlock(_component_PickerImage, {
                    key: 0,
                    class: "picker-chip-image",
                    image: item.image,
                    size: 22
                  }, null, 8, ["image"])) : vue.createCommentVNode("v-if", true),
                  vue.createElementVNode(
                    "span",
                    _hoisted_2$a,
                    vue.toDisplayString(item.text),
                    1
                    /* TEXT */
                  ),
                  !item.locked && !$options.isDisabled ? (vue.openBlock(), vue.createElementBlock("button", {
                    key: 1,
                    type: "button",
                    class: "picker-chip-remove",
                    "aria-label": $options.msg("remove", { label: item.text }),
                    title: $options.msg("remove", { label: item.text }),
                    onClick: vue.withModifiers(($event) => $options.remove(item.id), ["stop"])
                  }, [..._cache[9] || (_cache[9] = [
                    vue.createElementVNode(
                      "i",
                      {
                        class: "ti ti-circle-x",
                        "aria-hidden": "true"
                      },
                      null,
                      -1
                      /* CACHED */
                    )
                  ])], 8, _hoisted_3$a)) : vue.createCommentVNode("v-if", true)
                ], 10, _hoisted_1$c);
              }),
              128
              /* KEYED_FRAGMENT */
            )) : vue.createCommentVNode("v-if", true),
            $options.mode !== "multiple" && $data.chosen.length && !$data.editing && $data.chosen[0].image ? (vue.openBlock(), vue.createBlock(_component_PickerImage, {
              key: 1,
              class: "picker-value-image",
              image: $data.chosen[0].image,
              size: 24
            }, null, 8, ["image"])) : vue.createCommentVNode("v-if", true),
            vue.createElementVNode("input", {
              id: $options.inputId,
              ref: "input",
              type: "text",
              class: "picker-input",
              role: "combobox",
              autocomplete: "off",
              spellcheck: "false",
              autocapitalize: "off",
              "aria-autocomplete": "list",
              "aria-expanded": _ctx.open ? "true" : "false",
              "aria-controls": $data.listboxId,
              "aria-activedescendant": $options.activeDescendant,
              "aria-label": $options.accessibleName,
              "aria-describedby": $data.select ? $data.nativeDescribedBy : $props.describedBy,
              "aria-busy": $data.loading ? "true" : null,
              "aria-invalid": $options.isInvalid ? "true" : null,
              "aria-required": $options.cfg.required ? "true" : null,
              placeholder: $options.inputPlaceholder,
              value: $options.inputText,
              readonly: !$options.searchable,
              disabled: $options.isDisabled,
              autofocus: $options.cfg.autofocus || null,
              maxlength: $options.cfg.maxInput > 0 ? $options.cfg.maxInput : null,
              onBeforeinput: _cache[0] || (_cache[0] = (...args) => $options.onBeforeInput && $options.onBeforeInput(...args)),
              onInput: _cache[1] || (_cache[1] = (...args) => $options.onInput && $options.onInput(...args)),
              onKeydown: _cache[2] || (_cache[2] = (...args) => $options.onKeydown && $options.onKeydown(...args)),
              onFocus: _cache[3] || (_cache[3] = (...args) => $options.onFocus && $options.onFocus(...args)),
              onBlur: _cache[4] || (_cache[4] = (...args) => $options.onBlur && $options.onBlur(...args))
            }, null, 40, _hoisted_4$8)
          ],
          2
          /* CLASS */
        ),
        $data.loading ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_5$6)) : $options.mode === "select" ? (vue.openBlock(), vue.createElementBlock("i", _hoisted_6$5)) : vue.createCommentVNode("v-if", true),
        $options.clearable ? (vue.openBlock(), vue.createElementBlock("button", {
          key: 2,
          type: "button",
          class: "select-menu-clear",
          "aria-label": $options.msg("clear"),
          title: $options.msg("clear"),
          onClick: _cache[6] || (_cache[6] = vue.withModifiers((...args) => $options.clear && $options.clear(...args), ["stop"]))
        }, [..._cache[10] || (_cache[10] = [
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
        ])], 8, _hoisted_7$3)) : vue.createCommentVNode("v-if", true),
        vue.createElementVNode("ul", {
          id: $data.listboxId,
          ref: "listbox",
          class: vue.normalizeClass(["dropdown-menu select-menu-drawer", { show: _ctx.open }]),
          "data-bs-popper": "static",
          role: "listbox",
          "aria-label": $options.accessibleName,
          "aria-multiselectable": $options.mode === "multiple" ? "true" : null,
          "aria-busy": $data.loading ? "true" : null,
          onScroll: _cache[8] || (_cache[8] = (...args) => $options.onListScroll && $options.onListScroll(...args))
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($options.rows, (row) => {
              return vue.openBlock(), vue.createElementBlock(
                vue.Fragment,
                {
                  key: row.key
                },
                [
                  row.header ? (vue.openBlock(), vue.createElementBlock(
                    "li",
                    _hoisted_9$3,
                    vue.toDisplayString(row.header),
                    1
                    /* TEXT */
                  )) : (vue.openBlock(), vue.createElementBlock("li", {
                    key: 1,
                    id: _ctx.optionId(row.index),
                    class: vue.normalizeClass(["dropdown-item select-menu-option picker-option", { active: $options.isChosen(row.item.id), "is-focused": row.index === _ctx.activeIndex, disabled: row.item.disabled, "is-new": row.item.isNew }]),
                    role: "option",
                    "aria-selected": $options.isChosen(row.item.id) ? "true" : "false",
                    "aria-disabled": row.item.disabled ? "true" : null,
                    onPointerdown: _cache[7] || (_cache[7] = vue.withModifiers(() => {
                    }, ["prevent"])),
                    onClick: ($event) => $options.choose(row.item),
                    onMousemove: ($event) => _ctx.activeIndex = row.index
                  }, [
                    row.item.image ? (vue.openBlock(), vue.createBlock(_component_PickerImage, {
                      key: 0,
                      class: "picker-option-image",
                      image: row.item.image,
                      size: $options.optionImageSize
                    }, null, 8, ["image", "size"])) : vue.createCommentVNode("v-if", true),
                    vue.createElementVNode("span", _hoisted_11$3, [
                      vue.createElementVNode("span", _hoisted_12$3, [
                        (vue.openBlock(true), vue.createElementBlock(
                          vue.Fragment,
                          null,
                          vue.renderList($options.highlight(row.item), (part, index) => {
                            return vue.openBlock(), vue.createElementBlock(
                              vue.Fragment,
                              { key: index },
                              [
                                part.match ? (vue.openBlock(), vue.createElementBlock(
                                  "mark",
                                  _hoisted_13,
                                  vue.toDisplayString(part.text),
                                  1
                                  /* TEXT */
                                )) : (vue.openBlock(), vue.createElementBlock(
                                  vue.Fragment,
                                  { key: 1 },
                                  [
                                    vue.createTextVNode(
                                      vue.toDisplayString(part.text),
                                      1
                                      /* TEXT */
                                    )
                                  ],
                                  64
                                  /* STABLE_FRAGMENT */
                                ))
                              ],
                              64
                              /* STABLE_FRAGMENT */
                            );
                          }),
                          128
                          /* KEYED_FRAGMENT */
                        ))
                      ]),
                      row.item.subText ? (vue.openBlock(), vue.createElementBlock(
                        "span",
                        _hoisted_14,
                        vue.toDisplayString(row.item.subText),
                        1
                        /* TEXT */
                      )) : vue.createCommentVNode("v-if", true)
                    ])
                  ], 42, _hoisted_10$3))
                ],
                64
                /* STABLE_FRAGMENT */
              );
            }),
            128
            /* KEYED_FRAGMENT */
          )),
          $options.feedback ? (vue.openBlock(), vue.createElementBlock(
            "li",
            _hoisted_15,
            vue.toDisplayString($options.feedback),
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true)
        ], 42, _hoisted_8$3),
        $options.cfg.listed && $data.chosen.length ? (vue.openBlock(), vue.createElementBlock("ul", _hoisted_16, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($data.chosen, (item) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                key: item.id,
                class: "picker-list-item",
                "data-value": item.id,
                title: item.locked ? $options.msg("locked") : null
              }, [
                item.image ? (vue.openBlock(), vue.createBlock(_component_PickerImage, {
                  key: 0,
                  class: "picker-list-item-image",
                  image: item.image,
                  size: 24
                }, null, 8, ["image"])) : vue.createCommentVNode("v-if", true),
                vue.createElementVNode("span", _hoisted_18, [
                  vue.createElementVNode(
                    "span",
                    _hoisted_19,
                    vue.toDisplayString(item.text),
                    1
                    /* TEXT */
                  ),
                  item.subText ? (vue.openBlock(), vue.createElementBlock(
                    "span",
                    _hoisted_20,
                    vue.toDisplayString(item.subText),
                    1
                    /* TEXT */
                  )) : vue.createCommentVNode("v-if", true)
                ]),
                !item.locked && !$options.isDisabled ? (vue.openBlock(), vue.createElementBlock("button", {
                  key: 1,
                  type: "button",
                  class: "picker-list-item-remove",
                  "aria-label": $options.msg("remove", { label: item.text }),
                  title: $options.msg("remove", { label: item.text }),
                  onClick: ($event) => $options.remove(item.id)
                }, [..._cache[11] || (_cache[11] = [
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
                ])], 8, _hoisted_21)) : vue.createCommentVNode("v-if", true)
              ], 8, _hoisted_17);
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ])) : vue.createCommentVNode("v-if", true),
        $options.cfg.name && !$data.select ? (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          { key: 4 },
          vue.renderList($options.hiddenValues, (value, index) => {
            return vue.openBlock(), vue.createElementBlock("input", {
              key: index,
              type: "hidden",
              name: $options.hiddenName,
              value
            }, null, 8, _hoisted_22);
          }),
          128
          /* KEYED_FRAGMENT */
        )) : vue.createCommentVNode("v-if", true),
        vue.createElementVNode(
          "span",
          _hoisted_23,
          vue.toDisplayString(_ctx.open ? $options.feedback || "" : ""),
          1
          /* TEXT */
        )
      ],
      2
      /* CLASS */
    );
  }
  const C20 = /* @__PURE__ */ _export_sfc(_sfc_main$e, [["render", _sfc_render$e]]);
  const _sfc_main$d = {
    name: "PickerField",
    components: { PickerInput: C20 },
    mixins: [fieldMixin],
    inheritAttrs: false,
    props: {
      modelValue: { type: [String, Number, Array], default: "" }
    },
    emits: ["update:modelValue"],
    methods: {
      onUpdate(value) {
        this.clearOwnError();
        this.$emit("update:modelValue", value);
      },
      focus() {
        var _a, _b;
        (_b = (_a = this.$refs.picker) == null ? void 0 : _a.$refs.input) == null ? void 0 : _b.focus();
      }
    }
  };
  const _hoisted_1$b = ["for"];
  const _hoisted_2$9 = ["id"];
  const _hoisted_3$9 = ["id"];
  function _sfc_render$d(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_PickerInput = vue.resolveComponent("PickerInput");
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
        }, vue.toDisplayString(_ctx.label), 9, _hoisted_1$b)) : vue.createCommentVNode("v-if", true),
        vue.createVNode(_component_PickerInput, vue.mergeProps({ ref: "picker" }, _ctx.$attrs, {
          id: _ctx.fieldId,
          name: _ctx.fieldName,
          label: _ctx.label || "",
          placeholder: _ctx.placeholder || "",
          required: _ctx.required,
          disabled: _ctx.isDisabled,
          invalid: _ctx.hasError,
          "described-by": _ctx.describedBy,
          "model-value": $props.modelValue,
          "onUpdate:modelValue": $options.onUpdate
        }), null, 16, ["id", "name", "label", "placeholder", "required", "disabled", "invalid", "described-by", "model-value", "onUpdate:modelValue"]),
        _ctx.hint ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 1,
          id: _ctx.hintId,
          class: "form-text text-muted"
        }, vue.toDisplayString(_ctx.hint), 9, _hoisted_2$9)) : vue.createCommentVNode("v-if", true),
        _ctx.hasError ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 2,
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
        ], 8, _hoisted_3$9)) : vue.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C18 = /* @__PURE__ */ _export_sfc(_sfc_main$d, [["render", _sfc_render$d]]);
  const SEARCH_DEBOUNCE_MS = TEXT_DEBOUNCE_MS;
  const MAX_SUGGESTIONS = 8;
  let uid$1 = 0;
  const idOf = (value) => value === null || value === void 0 ? "" : String(value);
  const ID_PATTERN = /^[1-9]\d*$/;
  const _sfc_main$c = {
    name: "PickerFilterControl",
    mixins: [comboboxMixin],
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
        results: [],
        searching: false,
        // The suggestions for an empty field are being loaded
        loadingSuggestions: false,
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
      // Where the list's suggestions for an empty field load from (see "Suggestions without a text").
      suggestionsUrl() {
        var _a;
        return ((_a = this.filter.props) == null ? void 0 : _a.suggestionsUrl) || null;
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
      comboboxOptions() {
        return this.results;
      },
      activeDescendant() {
        return this.open && this.activeIndex >= 0 && this.activeIndex < this.results.length ? this.optionId(this.activeIndex) : null;
      },
      feedback() {
        if (this.results.length) {
          return null;
        }
        return this.searching || this.loadingSuggestions ? vue$1.i18n.t("base", "Loading...") : vue$1.i18n.t("base", "No results found!");
      }
    },
    watch: {
      comboboxOptions() {
        this.$nextTick(() => this.placeDrawer());
      },
      valueKey() {
        this.show();
        if (this.pendingFocus) {
          this.pendingFocus = false;
          this.$nextTick(() => {
            var _a;
            this.quietFocus = true;
            (_a = this.$refs.input || this.$refs.remove) == null ? void 0 : _a.focus();
            this.quietFocus = false;
          });
        }
      }
    },
    created() {
      this.suggested = null;
      this.suggestSeq = 0;
      this.searchSeq = 0;
      this.resolveSeq = 0;
      this.searchTimer = null;
      this.pendingFocus = false;
      this.quietFocus = false;
      this.show();
    },
    beforeUnmount() {
      clearTimeout(this.searchTimer);
      this.searchSeq++;
      this.resolveSeq++;
      this.suggestSeq++;
    },
    methods: {
      idOf,
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
        if (this.term === "") {
          this.suggest();
        }
      },
      onFocus() {
        if (!this.quietFocus && this.term === "") {
          this.suggest();
        }
      },
      // Lists the suggestions for an empty field, the chosen ones left out (loaded first, when
      // that is still to do); none: closed.
      suggest() {
        if (!this.suggestionsUrl) {
          this.close();
          return;
        }
        if (this.suggested === null) {
          this.loadSuggestions();
          return;
        }
        const chosen = this.ids;
        this.results = this.suggested.filter((item) => !chosen.includes(idOf(item.id)));
        if (!this.results.length) {
          this.close();
          return;
        }
        this.activeIndex = -1;
        this.openListbox();
      },
      loadSuggestions() {
        if (this.loadingSuggestions) {
          return;
        }
        const seq = ++this.suggestSeq;
        this.loadingSuggestions = true;
        this.results = [];
        this.openListbox();
        vue$1.client.get(this.suggestionsUrl).then((response) => {
          if (seq === this.suggestSeq) {
            this.suggested = response.results || [];
            this.suggested.forEach((item) => this.known[idOf(item.id)] = item);
          }
        }).catch((response) => {
          if (seq === this.suggestSeq) {
            vue$1.log.error(response);
            this.suggested = [];
          }
        }).finally(() => {
          if (seq !== this.suggestSeq) {
            return;
          }
          this.loadingSuggestions = false;
          if (this.open && this.term === "") {
            this.suggest();
          }
        });
      },
      openListbox() {
        if (!this.open) {
          this.open = true;
          this.bindOutside();
        }
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
        if (this.open) {
          return;
        }
        if (this.term === "") {
          this.suggest();
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
      // Overrides the mixin's: an index below the first clamps to it instead of being ignored.
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
          case "PageDown":
          case "PageUp":
            if (this.open && this.results.length) {
              event.preventDefault();
              this.movePage(event.key === "PageDown" ? 1 : -1);
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
      }
    }
  };
  const _hoisted_1$a = ["title"];
  const _hoisted_2$8 = { class: "picker-chip-label" };
  const _hoisted_3$8 = ["aria-label", "title", "onClick"];
  const _hoisted_4$7 = ["title", "aria-label"];
  const _hoisted_5$5 = { class: "picker-chip-label" };
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
        class: vue.normalizeClass(["select-menu picker picker-filter", [$props.block, { "is-open": _ctx.open, "has-selection": $options.hasValue, "is-disabled": $data.resolving, "is-loading": $data.resolving, "is-multiple": $props.multiple, "keeps-placeholder": $options.keepsPlaceholder, "is-drop-up": _ctx.dropUp }]]),
        style: vue.normalizeStyle($options.keepsPlaceholder ? { "--hh-picker-placeholder-chars": $options.placeholder.length } : null)
      },
      [
        vue.createElementVNode(
          "div",
          {
            ref: "field",
            class: "form-control picker-field",
            onClick: _cache[4] || (_cache[4] = (...args) => $options.onFieldClick && $options.onFieldClick(...args))
          },
          [
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
                      }, [..._cache[6] || (_cache[6] = [
                        vue.createElementVNode(
                          "i",
                          {
                            class: "ti ti-circle-x",
                            "aria-hidden": "true"
                          },
                          null,
                          -1
                          /* CACHED */
                        )
                      ])], 8, _hoisted_3$8)
                    ], 10, _hoisted_1$a);
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
                _hoisted_5$5,
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
              }, [..._cache[7] || (_cache[7] = [
                vue.createElementVNode(
                  "i",
                  {
                    class: "ti ti-circle-x",
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
              spellcheck: "false",
              autocapitalize: "off",
              "aria-autocomplete": "list",
              "aria-expanded": _ctx.open ? "true" : "false",
              "aria-controls": $data.listboxId,
              "aria-activedescendant": $options.activeDescendant,
              "aria-label": $options.accessibleName,
              "aria-busy": $data.resolving || $data.searching || $data.loadingSuggestions ? "true" : null,
              placeholder: $props.multiple && $options.hasValue && !$props.keepPlaceholder ? "" : $options.placeholder,
              value: $data.query,
              disabled: $data.resolving,
              onInput: _cache[1] || (_cache[1] = (...args) => $options.onInput && $options.onInput(...args)),
              onKeydown: _cache[2] || (_cache[2] = (...args) => $options.onKeydown && $options.onKeydown(...args)),
              onFocus: _cache[3] || (_cache[3] = (...args) => $options.onFocus && $options.onFocus(...args))
            }, null, 40, _hoisted_7$2)) : vue.createCommentVNode("v-if", true)
          ],
          512
          /* NEED_PATCH */
        ),
        $data.resolving ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_8$2)) : vue.createCommentVNode("v-if", true),
        vue.createElementVNode("ul", {
          id: $data.listboxId,
          ref: "listbox",
          class: vue.normalizeClass(["dropdown-menu select-menu-drawer", { show: _ctx.open }]),
          "data-bs-popper": "static",
          role: "listbox",
          "aria-label": $options.accessibleName,
          "aria-busy": $data.searching || $data.loadingSuggestions ? "true" : null
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($data.results, (result, index) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                id: _ctx.optionId(index),
                key: $options.idOf(result.id),
                class: vue.normalizeClass(["dropdown-item select-menu-option picker-filter-option", [`${$props.block}-option`, { "is-focused": index === _ctx.activeIndex }]]),
                role: "option",
                "aria-selected": index === _ctx.activeIndex ? "true" : "false",
                onPointerdown: _cache[5] || (_cache[5] = vue.withModifiers(() => {
                }, ["prevent"])),
                onClick: ($event) => $options.choose(result),
                onMousemove: ($event) => _ctx.activeIndex = index
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
                    _cache[8] || (_cache[8] = vue.createElementVNode(
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
          vue.toDisplayString(_ctx.open ? $options.feedback || "" : ""),
          1
          /* TEXT */
        )
      ],
      6
      /* CLASS, STYLE */
    );
  }
  const C19 = /* @__PURE__ */ _export_sfc(_sfc_main$c, [["render", _sfc_render$c]]);
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
  const _hoisted_1$9 = ["aria-valuenow", "aria-label"];
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
    ], 8, _hoisted_1$9);
  }
  const C21 = /* @__PURE__ */ _export_sfc(_sfc_main$b, [["render", _sfc_render$b]]);
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
  const _sfc_main$a = {
    props: {
      handlers: { type: Array, default: () => [] }
    }
  };
  function _sfc_render$a(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(true), vue.createElementBlock(
      vue.Fragment,
      null,
      vue.renderList($props.handlers, (handler, index) => {
        return vue.openBlock(), vue.createElementBlock("li", { key: index }, [
          vue.createElementVNode(
            "a",
            vue.mergeProps({
              class: "dropdown-item",
              href: "#"
            }, { ref_for: true }, handler.attributes, { "data-action-process": "file-handler" }),
            [
              handler.icon ? (vue.openBlock(), vue.createElementBlock(
                "i",
                {
                  key: 0,
                  class: vue.normalizeClass("dropdown-item-icon ti ti-" + handler.icon),
                  "aria-hidden": "true"
                },
                null,
                2
                /* CLASS */
              )) : vue.createCommentVNode("v-if", true),
              vue.createTextVNode(
                vue.toDisplayString(handler.label),
                1
                /* TEXT */
              )
            ],
            16
            /* FULL_PROPS */
          )
        ]);
      }),
      128
      /* KEYED_FRAGMENT */
    );
  }
  const FileHandlerItems = /* @__PURE__ */ _export_sfc(_sfc_main$a, [["render", _sfc_render$a]]);
  const RICHTEXT_COMPONENT_DATA = "humhub-ui-richtexteditor";
  let instanceCounter = 0;
  const editorOptions = {
    created(el, binding) {
      Object.entries(binding.value).forEach(([key, value]) => {
        if (value !== null) {
          jQuery(el).data(key, value);
        }
      });
    }
  };
  const _sfc_main$9 = {
    components: { FileHandlerItems },
    directives: { editorOptions },
    mixins: [fieldMixin],
    props: {
      instanceKey: { type: String, default: null },
      preset: { type: String, default: null },
      include: { type: Array, default: () => [] },
      exclude: { type: Array, default: () => [] },
      pluginOptions: { type: Object, default: () => ({}) },
      mentioningUrl: { type: String, default: null },
      layout: { type: String, default: "block" },
      autofocus: { type: Boolean, default: false },
      backupInterval: { type: Number, default: 3 },
      // `true` or `(files) => Promise<file[]>`, see "Files in the text"
      upload: { type: [Boolean, Function], default: false },
      // File handlers (`{label, icon, attributes}`), see "Files in the text"
      handlers: { type: Array, default: () => [] }
    },
    emits: ["scroll-active", "scroll-inactive"],
    data() {
      return {
        // The PHP widget's `AbstractRichTextEditor::BACKUP_COOKIE_KEY`
        backupKey: "RichTextEditor.backup",
        pickerAccept: null,
        editorId: this.instanceKey ? "richtext-" + this.instanceKey.replace(/[^A-Za-z0-9_-]/g, "-") : "richtext-" + ++instanceCounter
      };
    },
    computed: {
      editorExclude() {
        const exclude = [...this.exclude];
        if (this.layout === "block") {
          exclude.push("resizeNav");
        }
        if (!this.upload && !exclude.includes("upload")) {
          exclude.push("upload");
        }
        if (!this.handlers.length && !exclude.includes("file_handler")) {
          exclude.push("file_handler");
        }
        return exclude;
      }
    },
    mounted() {
      jQuery(this.$refs.editor).on("scrollActive.richtextField", () => this.$emit("scroll-active")).on("scrollInactive.richtextField", () => this.$emit("scroll-inactive"));
      if (this.instanceKey && this.backupInterval > 0) {
        this.backupTimer = setInterval(() => {
          const editor = this.getEditorInstance();
          if (editor) {
            editor.backup();
          }
        }, this.backupInterval * 1e3);
      }
    },
    beforeUnmount() {
      clearInterval(this.backupTimer);
      jQuery(this.$refs.editor).off(".richtextField");
      const editor = this.getEditorInstance();
      if (editor && editor.editor && typeof editor.editor.destroy === "function") {
        editor.editor.destroy();
      }
    },
    methods: {
      getEditorInstance() {
        return this.$refs.editor ? jQuery(this.$refs.editor).data(RICHTEXT_COMPONENT_DATA) : null;
      },
      /** @returns {string} the editor's markdown */
      getValue() {
        const editor = this.getEditorInstance();
        return editor ? editor.editor.serialize() : "";
      },
      /** Replaces the editor's content (e.g. to edit an existing text). */
      setValue(markdown) {
        const editor = this.getEditorInstance();
        if (editor) {
          editor.editor.init(markdown || "");
        }
      },
      /** Empties the editor and drops its draft. */
      clear() {
        const editor = this.getEditorInstance();
        if (editor) {
          editor.$.trigger("clear");
        }
      },
      focus() {
        const editor = this.getEditorInstance();
        if (editor) {
          editor.focus();
        }
      },
      /**
       * The editor's `upload` option: the given files (pasted), or the ones the user picks;
       * resolves with none when the picker is cancelled.
       */
      uploadOption(files, { accept = null } = {}) {
        if (files && files.length) {
          return this.uploadFiles(files);
        }
        return new Promise((resolve) => {
          this.resolvePick = resolve;
          this.pickerAccept = accept;
          this.$nextTick(() => {
            this.$refs.picker.value = "";
            this.$refs.picker.click();
          });
        }).then((picked) => picked.length ? this.uploadFiles(picked) : []);
      },
      uploadFiles(files) {
        if (typeof this.upload === "function") {
          return this.upload(files);
        }
        return uploadFiles(files).then((response) => {
          (response.errors || []).forEach((error) => {
            vue$1.log.error(error.fileName + ": " + (error.messages || []).join(" "), true);
          });
          return response.results || [];
        });
      },
      onPicked(event) {
        if (this.resolvePick) {
          this.resolvePick(Array.from(event.target.files || []));
          this.resolvePick = null;
        }
      },
      /** @returns {Element} the field's root node */
      getElement() {
        return this.$el;
      }
    }
  };
  const _hoisted_1$8 = ["id", "data-preset", "data-include", "data-exclude", "data-plugin-options", "data-mentioning-url", "data-placeholder", "data-focus", "data-backup-cookie-key"];
  const _hoisted_2$7 = ["accept"];
  const _hoisted_3$7 = {
    key: 1,
    ref: "handlers",
    class: "d-none"
  };
  const _hoisted_4$6 = ["id", "name", "title", "aria-describedby"];
  const _hoisted_5$4 = ["id"];
  function _sfc_render$9(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FileHandlerItems = vue.resolveComponent("FileHandlerItems");
    const _directive_editor_options = vue.resolveDirective("editor-options");
    const _directive_additions = vue.resolveDirective("additions");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: "richtext-field",
        onInput: _cache[2] || (_cache[2] = (...args) => _ctx.clearOwnError && _ctx.clearOwnError(...args))
      },
      [
        vue.withDirectives(vue.createElementVNode("div", {
          id: $data.editorId,
          ref: "editor",
          class: vue.normalizeClass(["ProsemirrorEditor", $props.layout === "block" ? "plainMenu" : "focusMenu"]),
          "data-ui-widget": "ui.richtext.prosemirror.RichTextEditor",
          "data-ui-init": "",
          "data-preset": $props.preset,
          "data-include": JSON.stringify($props.include),
          "data-exclude": JSON.stringify($options.editorExclude),
          "data-plugin-options": JSON.stringify($props.pluginOptions),
          "data-mentioning-url": $props.mentioningUrl,
          "data-placeholder": _ctx.placeholder,
          "data-focus": $props.autofocus ? "true" : null,
          "data-backup-interval": "0",
          "data-backup-cookie-key": $data.backupKey
        }, null, 10, _hoisted_1$8), [
          [_directive_editor_options, {
            upload: $props.upload ? $options.uploadOption : null,
            fileHandlers: $props.handlers.length ? () => _ctx.$refs.handlers : null
          }]
        ]),
        $props.upload ? (vue.openBlock(), vue.createElementBlock("input", {
          key: 0,
          ref: "picker",
          type: "file",
          class: "d-none",
          multiple: "",
          accept: $data.pickerAccept,
          onChange: _cache[0] || (_cache[0] = (...args) => $options.onPicked && $options.onPicked(...args)),
          onCancel: _cache[1] || (_cache[1] = (...args) => $options.onPicked && $options.onPicked(...args))
        }, null, 40, _hoisted_2$7)) : vue.createCommentVNode("v-if", true),
        vue.createCommentVNode(" The file handlers the editor's menu offers (its `fileHandlers` option) "),
        $props.handlers.length ? (vue.openBlock(), vue.createElementBlock(
          "ul",
          _hoisted_3$7,
          [
            vue.createVNode(_component_FileHandlerItems, { handlers: $props.handlers }, null, 8, ["handlers"])
          ],
          512
          /* NEED_PATCH */
        )) : vue.createCommentVNode("v-if", true),
        vue.createElementVNode("textarea", {
          id: $data.editorId + "_input",
          name: _ctx.fieldName,
          class: "d-none",
          title: _ctx.placeholder,
          "aria-describedby": _ctx.describedBy
        }, null, 8, _hoisted_4$6),
        _ctx.hasError ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 2,
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
        ], 8, _hoisted_5$4)) : vue.createCommentVNode("v-if", true)
      ],
      32
      /* NEED_HYDRATION */
    )), [
      [_directive_additions]
    ]);
  }
  const C22 = /* @__PURE__ */ _export_sfc(_sfc_main$9, [["render", _sfc_render$9]]);
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
  const _sfc_main$8 = {
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
  const _hoisted_1$7 = { key: 0 };
  function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return $props.message && $data.ready ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$7, [
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
  const C23 = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["render", _sfc_render$8]]);
  const _sfc_main$7 = {
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
  const _hoisted_3$6 = {
    key: 0,
    value: ""
  };
  const _hoisted_4$5 = ["value"];
  const _hoisted_5$3 = ["id"];
  const _hoisted_6$3 = ["id"];
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
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
            _hoisted_3$6,
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
              }, vue.toDisplayString(option.label), 9, _hoisted_4$5);
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
  const C24 = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$7]]);
  const _sfc_main$6 = {
    name: "SelectionMenu",
    components: { DropdownMenu: C7 },
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
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
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
  const C25 = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
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
  const _sfc_main$5 = {
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
  const _hoisted_1$5 = {
    key: 0,
    class: "toast-container position-fixed bottom-0 start-50 translate-middle-x p-3"
  };
  const _hoisted_2$5 = ["role", "aria-live"];
  const _hoisted_3$5 = { class: "toast-body" };
  const _hoisted_4$4 = ["aria-expanded", "aria-label", "title"];
  const _hoisted_5$2 = ["aria-label"];
  const _hoisted_6$2 = {
    key: 0,
    class: "toast-details"
  };
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    return $data.entry ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$5, [
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
        vue.createElementVNode("div", _hoisted_3$5, [
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
          ], 8, _hoisted_4$4)) : vue.createCommentVNode("v-if", true),
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
      ], 42, _hoisted_2$5)
    ])) : vue.createCommentVNode("v-if", true);
  }
  const C26 = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
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
  const C27 = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
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
  const C29 = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = {
    name: "TileGrid",
    components: { ProgressFrame: C21 },
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
  const C30 = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const UPLOAD_BY_TYPE_ACTION = "file.uploadByType";
  let handlerField = null;
  document.addEventListener("click", (event) => {
    if (event.target instanceof Element && event.target.closest('a[data-action-process="file-handler"]')) {
      handlerField = null;
    }
  }, true);
  const _sfc_main$1 = {
    components: { FileHandlerItems },
    mixins: [fieldMixin],
    props: {
      modelValue: { type: Array, default: () => [] },
      max: { type: Number, default: 0 },
      accept: { type: String, default: null },
      multiple: { type: Boolean, default: true },
      title: { type: String, default: null },
      handlers: { type: Array, default: () => [] },
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
      vue$1.events.on("humhub:file:created", this.onFileCreated);
    },
    beforeUnmount() {
      this.$el.removeEventListener("humhub:file:attach", this.onAttachEvent);
      vue$1.events.off("humhub:file:created", this.onFileCreated);
      if (handlerField === this) {
        handlerField = null;
      }
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
        if (!entry) {
          return;
        }
        if (entry.getAttribute("data-action-click") !== UPLOAD_BY_TYPE_ACTION) {
          handlerField = this;
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
      /** A file a module's handler created (see the class docblock, "Legacy file handlers"). */
      onFileCreated(event, ...created) {
        if (handlerField !== this) {
          return;
        }
        handlerField = null;
        const files = created.flat().filter(Boolean).map((file) => ({ ...file, fileName: file.fileName || file.name }));
        if (files.length) {
          this.emitFiles([...this.files, ...files]);
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
  const _hoisted_6 = {
    key: 0,
    class: "progress mt-2",
    style: { "height": "6px" }
  };
  const _hoisted_7 = ["aria-valuenow"];
  const _hoisted_8 = {
    key: 1,
    class: "files"
  };
  const _hoisted_9 = ["data-preview-guid"];
  const _hoisted_10 = { class: "file-preview-content" };
  const _hoisted_11 = ["aria-label", "onClick", "onKeydown"];
  const _hoisted_12 = ["id"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FileHandlerItems = vue.resolveComponent("FileHandlerItems");
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
            $props.handlers.length ? (vue.openBlock(), vue.createElementBlock(
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
                  onClick: _cache[5] || (_cache[5] = (...args) => $options.onHandlerClick && $options.onHandlerClick(...args))
                }, [
                  vue.createVNode(_component_FileHandlerItems, { handlers: $props.handlers }, null, 8, ["handlers"])
                ])
              ],
              64
              /* STABLE_FRAGMENT */
            )) : vue.createCommentVNode("v-if", true)
          ])
        ], 8, ["to", "disabled"])),
        $data.progress !== null ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6, [
          vue.createElementVNode("div", {
            class: "progress-bar progress-bar-info",
            role: "progressbar",
            "aria-valuenow": $data.progress,
            "aria-valuemin": "0",
            "aria-valuemax": "100",
            style: vue.normalizeStyle({ width: $data.progress + "%" })
          }, null, 12, _hoisted_7)
        ])) : vue.createCommentVNode("v-if", true),
        $options.files.length ? (vue.openBlock(), vue.createElementBlock("ul", _hoisted_8, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($options.files, (file) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                key: file.guid,
                class: vue.normalizeClass(["file-preview-item mime", file.mimeIcon]),
                "data-preview-guid": file.guid
              }, [
                vue.createElementVNode("span", _hoisted_10, [
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
                  ])], 40, _hoisted_11)
                ])
              ], 10, _hoisted_9);
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
        ], 8, _hoisted_12)) : vue.createCommentVNode("v-if", true)
      ],
      32
      /* NEED_HYDRATION */
    )), [
      [_directive_additions]
    ]);
  }
  const C32 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
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
  const C33 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("CardSkeleton", C0);
  vue$1.register("CheckboxField", C1);
  vue$1.register("ColorField", C2);
  vue$1.register("ColorInput", C3);
  vue$1.register("ContentCard", C4);
  vue$1.register("DataTable", C5);
  vue$1.register("DropZone", C6);
  vue$1.register("DropdownMenu", C7);
  vue$1.register("EntityBrowser", C8);
  vue$1.register("EntityGrid", C9);
  vue$1.register("ExtensionSlot", C10);
  vue$1.register("FilterBar", C11);
  vue$1.register("FilterPicker", C12);
  vue$1.register("FilterSelect", C13);
  vue$1.register("HumHubForm", C14);
  vue$1.register("ImageAltTextModal", C15);
  vue$1.register("PageHeader", C16);
  vue$1.register("PathBar", C17);
  vue$1.register("PickerField", C18);
  vue$1.register("PickerFilterControl", C19);
  vue$1.register("PickerInput", C20);
  vue$1.register("ProgressFrame", C21);
  vue$1.register("RichTextField", C22);
  vue$1.register("RichTextOutput", C23);
  vue$1.register("SelectField", C24);
  vue$1.register("SelectionMenu", C25);
  vue$1.register("StatusBar", C26);
  vue$1.register("SubmitButton", C27);
  vue$1.register("TextField", C28);
  vue$1.register("TextareaField", C29);
  vue$1.register("TileGrid", C30);
  vue$1.register("UiModal", C31);
  vue$1.register("UploadField", C32);
  vue$1.register("ViewSwitch", C33);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.core.vue.js.map
