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
  const _sfc_main$j = {
    name: "CardSkeleton"
  };
  const _hoisted_1$i = {
    class: "c-card-skeleton",
    "aria-hidden": "true"
  };
  function _sfc_render$j(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$i, [..._cache[0] || (_cache[0] = [
      vue.createStaticVNode('<div class="c-card-skeleton__cover"><span class="c-card-skeleton__block c-card-skeleton__image"></span></div><div class="c-card-skeleton__header"><span class="c-card-skeleton__block c-card-skeleton__title"></span><span class="c-card-skeleton__block c-card-skeleton__version"></span></div><div class="c-card-skeleton__body"><span class="c-card-skeleton__block c-card-skeleton__line"></span><span class="c-card-skeleton__block c-card-skeleton__line c-card-skeleton__line--short"></span></div><div class="c-card-skeleton__footer"><span class="c-card-skeleton__block c-card-skeleton__action"></span><span class="c-card-skeleton__block c-card-skeleton__icon"></span></div>', 4)
    ])]);
  }
  const C2 = /* @__PURE__ */ _export_sfc(_sfc_main$j, [["render", _sfc_render$j]]);
  const _sfc_main$i = {
    name: "CardGrid",
    components: { CardSkeleton: C2 },
    props: {
      items: { type: Array, required: true },
      itemKey: { type: String, default: "id" },
      loading: { type: Boolean, default: false },
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
  const _hoisted_2$d = ["data-id"];
  const _hoisted_3$b = {
    key: 1,
    class: "c-card-grid__message c-card-grid__message--error"
  };
  const _hoisted_4$9 = {
    role: "alert",
    class: "c-card-grid__message-text"
  };
  const _hoisted_5$7 = {
    key: 2,
    class: "c-card-grid__message c-card-grid__message--empty"
  };
  const _hoisted_6$6 = {
    role: "status",
    class: "c-card-grid__message-text"
  };
  const _hoisted_7$4 = {
    key: 0,
    class: "c-card-grid__error cards-error"
  };
  const _hoisted_8$4 = {
    role: "alert",
    class: "c-card-grid__message-text"
  };
  const _hoisted_9$3 = {
    key: 1,
    ref: "sentinel",
    class: "c-card-grid__more cards-more"
  };
  const _hoisted_10$3 = ["aria-label"];
  function _sfc_render$i(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_CardSkeleton = vue.resolveComponent("CardSkeleton");
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: vue.normalizeClass(["c-card-grid", { "is-loading": $props.loading && $props.items.length > 0 }])
      },
      [
        vue.createElementVNode("div", {
          class: "c-card-grid__cells",
          "aria-busy": $props.loading ? "true" : "false"
        }, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($props.items, (item, index) => {
              return vue.openBlock(), vue.createElementBlock("div", {
                key: item[$props.itemKey],
                class: vue.normalizeClass(["c-card-grid__cell", $props.cardClass]),
                style: vue.normalizeStyle({ "--card-stagger-index": $options.staggerIndex(index) }),
                "data-id": item[$props.itemKey]
              }, [
                vue.renderSlot(_ctx.$slots, "card", {
                  item,
                  index: $options.staggerIndex(index)
                })
              ], 14, _hoisted_2$d);
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
                      class: vue.normalizeClass(["c-card-grid__cell c-card-grid__cell--skeleton", $props.cardClass]),
                      style: vue.normalizeStyle({ "--card-stagger-index": n - 1 })
                    },
                    [
                      vue.createVNode(_component_CardSkeleton)
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
                  _hoisted_4$9,
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
              ])) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$7, [
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
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main$i, [["render", _sfc_render$i]]);
  let uid$2 = 0;
  const _sfc_main$h = {
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
      const key = ++uid$2;
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
        return empty ? String(empty.label) : "";
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
  const _hoisted_1$g = ["id", "aria-expanded", "aria-controls", "aria-label", "aria-busy", "disabled"];
  const _hoisted_2$c = { class: "c-select__value" };
  const _hoisted_3$a = ["aria-label"];
  const _hoisted_4$8 = {
    key: 1,
    class: "ti ti-chevron-up c-select__chevron",
    "aria-hidden": "true"
  };
  const _hoisted_5$6 = ["disabled", "aria-hidden", "aria-label", "title"];
  const _hoisted_6$5 = ["id", "aria-label", "aria-activedescendant"];
  const _hoisted_7$3 = ["id", "aria-selected", "onClick", "onMousemove"];
  const _hoisted_8$3 = {
    key: 0,
    class: "c-select__feedback",
    role: "presentation"
  };
  function _sfc_render$h(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        ref: "root",
        class: vue.normalizeClass(["c-select", { "is-open": $data.open, "has-selection": $options.hasSelection, "is-disabled": $options.isDisabled, "is-loading": $props.loading }])
      },
      [
        vue.createElementVNode("button", {
          id: $options.buttonId,
          ref: "button",
          type: "button",
          class: "c-select__button",
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
            _hoisted_2$c,
            vue.toDisplayString($options.displayLabel),
            1
            /* TEXT */
          )
        ], 40, _hoisted_1$g),
        $props.loading ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          class: "spinner-border spinner-border-sm c-select__spinner",
          role: "status",
          "aria-label": $options.loadingLabel
        }, null, 8, _hoisted_3$a)) : (vue.openBlock(), vue.createElementBlock("i", _hoisted_4$8)),
        vue.createElementVNode("button", {
          type: "button",
          class: "c-select__clear",
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
        ])], 8, _hoisted_5$6),
        vue.createElementVNode("ul", {
          id: $data.listboxId,
          ref: "listbox",
          class: vue.normalizeClass(["c-select__drawer", { "is-open": $data.open }]),
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
                class: vue.normalizeClass(["c-select__option", { "is-selected": option.value === $props.modelValue, "is-active": index === $data.activeIndex }]),
                role: "option",
                "aria-selected": option.value === $props.modelValue ? "true" : "false",
                onClick: ($event) => $options.select(option),
                onMousemove: ($event) => $data.activeIndex = index
              }, vue.toDisplayString(option.label), 43, _hoisted_7$3);
            }),
            128
            /* KEYED_FRAGMENT */
          )),
          !$options.choices.length ? (vue.openBlock(), vue.createElementBlock(
            "li",
            _hoisted_8$3,
            vue.toDisplayString($options.emptyLabel),
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true)
        ], 42, _hoisted_6$5)
      ],
      2
      /* CLASS */
    );
  }
  const C7 = /* @__PURE__ */ _export_sfc(_sfc_main$h, [["render", _sfc_render$h]]);
  const isMultiple = (filter) => filter.type === "tags" && filter.multiple === true;
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
  const readValues = (filters, search, base = defaultValues(filters)) => {
    const params = new URLSearchParams(search);
    const values = { ...base };
    for (const filter of filters) {
      if (params.has(filter.key)) {
        values[filter.key] = parseValue(filter, params.get(filter.key));
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
  const requestParams = (filters, values) => {
    const params = {};
    for (const filter of filters) {
      const serialized = serializeValue(filter, values[filter.key]);
      if (filter.type === "checkbox" || serialized !== "") {
        params[filter.key] = serialized;
      }
    }
    return params;
  };
  const TEXT_DEBOUNCE_MS = 300;
  const ANIMATION_MS = 300;
  let uid$1 = 0;
  const reducedMotion = () => typeof window.matchMedia === "function" && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const same = (filters, a, b) => filters.every((filter) => JSON.stringify(a == null ? void 0 : a[filter.key]) === JSON.stringify(b == null ? void 0 : b[filter.key]));
  const _sfc_main$g = {
    name: "FilterBar",
    components: { FilterSelect: C7 },
    props: {
      filters: { type: Array, required: true },
      modelValue: { type: Object, default: () => ({}) },
      syncUrl: { type: Boolean, default: true },
      idPrefix: { type: String, default: "filter" }
    },
    emits: ["update:modelValue"],
    data() {
      const base = { ...defaultValues(this.filters), ...this.modelValue };
      const initial = this.syncUrl ? readValues(this.filters, window.location.search, base) : base;
      return {
        draft: initial,
        remoteOptions: {},
        loading: {},
        open: false,
        collapsing: false,
        barId: `filter-bar-${++uid$1}`
      };
    },
    computed: {
      visibleFilters() {
        return this.filters.filter((filter) => !filter.hidden);
      },
      // The item that stays visible while the bar is collapsed: the first search field.
      keepIndex() {
        return this.visibleFilters.findIndex((filter) => filter.type === "text");
      },
      collapsible() {
        return this.visibleFilters.length > (this.keepIndex === -1 ? 0 : 1);
      },
      // The funnel toggle follows the kept search field (or leads the bar without one).
      toggleAfter() {
        return Math.max(this.keepIndex, 0);
      },
      resettable() {
        return this.visibleFilters.some((filter) => !isDefault(filter, this.draft[filter.key]));
      },
      resetLabel() {
        return vue$1.i18n.t("base", "Clear all filters");
      },
      toggleLabel() {
        return this.open ? vue$1.i18n.t("base", "Hide filters") : vue$1.i18n.t("base", "Show filters");
      }
    },
    watch: {
      draft: "onDraftChange",
      modelValue(value) {
        if (!same(this.filters, value, this.applied)) {
          clearTimeout(this.debounceTimer);
          this.applied = { ...defaultValues(this.filters), ...value };
          this.draft = { ...this.applied };
          this.writeUrl();
        }
      }
    },
    created() {
      this.debounceTimer = null;
      this.applied = { ...this.draft };
      if (!same(this.filters, this.applied, this.modelValue)) {
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
      setFilter(key, value) {
        if (!this.filters.some((filter) => filter.key === key)) {
          return false;
        }
        this.update(key, value);
        return true;
      },
      reset() {
        this.draft = defaultValues(this.filters);
      },
      reloadOptions() {
        this.filters.filter((filter) => filter.optionsUrl).forEach((filter) => this.loadOptions(filter));
      },
      inputId(filter) {
        return `${this.idPrefix}-${filter.key}`;
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
        const changed = this.filters.filter((filter) => JSON.stringify(values[filter.key]) !== JSON.stringify(this.applied[filter.key]));
        if (!changed.length) {
          return;
        }
        if (changed.some((filter) => !filter.hidden)) {
          const stale = this.filters.filter((filter) => filter.hidden && !isDefault(filter, values[filter.key]));
          if (stale.length) {
            this.draft = { ...values, ...Object.fromEntries(stale.map((filter) => [filter.key, defaultValue(filter)])) };
            return;
          }
        }
        if (changed.every((filter) => filter.type === "text")) {
          this.debounceTimer = setTimeout(() => this.apply(), TEXT_DEBOUNCE_MS);
        } else {
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
        const path = pathname + writeQuery(this.filters, this.applied, search) + hash;
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
      isTagActive(filter, value) {
        const current = this.draft[filter.key];
        if (filter.multiple === true) {
          return value === "" ? current.length === 0 : current.includes(value);
        }
        return current === value;
      },
      toggleTag(filter, value) {
        const current = this.draft[filter.key];
        if (filter.multiple !== true) {
          this.update(filter.key, current === value ? "" : value);
          return;
        }
        if (value === "") {
          this.update(filter.key, []);
          return;
        }
        this.update(filter.key, current.includes(value) ? current.filter((v) => v !== value) : [...current, value]);
      },
      toggleElement() {
        const toggle = this.$refs.toggle;
        return Array.isArray(toggle) ? toggle[0] : toggle;
      },
      // CSS owns the breakpoint (a container query), so "does the bar collapse right now?" is
      // answered by whether it shows the toggle, not by a width kept in step with the SCSS.
      collapsesNow() {
        const toggle = this.toggleElement();
        return Boolean(toggle) && window.getComputedStyle(toggle).display !== "none";
      },
      onToggle() {
        if (this.collapsesNow()) {
          this.setOpen(!this.open);
        }
      },
      setOpen(open, animate = !reducedMotion()) {
        const bar = this.$refs.bar;
        const from = bar.getBoundingClientRect().height;
        const clear = this.$refs.clear || null;
        const flipping = animate && clear && clear.getClientRects().length > 0;
        const clearFrom = flipping ? clear.getBoundingClientRect() : null;
        this.collapsing = false;
        this.open = open;
        bar.classList.remove("is-collapsing");
        bar.classList.toggle("is-open", open);
        const clearTo = flipping ? clear.getBoundingClientRect() : null;
        clearTimeout(this.settleTimer);
        if (!animate) {
          bar.style.maxHeight = "";
          return;
        }
        this.collapsing = !open;
        bar.classList.toggle("is-collapsing", !open);
        if (flipping) {
          this.flipClear(clear, clearFrom, clearTo, open);
        }
        const to = open ? bar.scrollHeight : this.collapsedHeight();
        bar.style.maxHeight = `${from}px`;
        void bar.offsetHeight;
        bar.style.maxHeight = `${to}px`;
        this.settleTimer = setTimeout(() => {
          bar.style.maxHeight = open ? "none" : "";
          this.collapsing = false;
        }, ANIMATION_MS);
      },
      collapsedHeight() {
        const bar = this.$refs.bar;
        const keeper = bar.querySelector("[data-filter-bar-keep], [data-filter-bar-toggle]");
        const style = window.getComputedStyle(bar);
        const padding = (parseFloat(style.paddingTop) || 0) + (parseFloat(style.paddingBottom) || 0);
        return (keeper ? keeper.offsetHeight : 38) + padding;
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
      // and any inline height, so it cannot be stranded half-open.
      sync() {
        if (this.collapsesNow() || !this.open && !this.collapsing) {
          return;
        }
        clearTimeout(this.settleTimer);
        this.setOpen(false, false);
      }
    }
  };
  const _hoisted_1$f = { class: "c-filter-bar-container" };
  const _hoisted_2$b = ["id"];
  const _hoisted_3$9 = ["data-filter-bar-keep"];
  const _hoisted_4$7 = {
    key: 0,
    class: "c-search-field"
  };
  const _hoisted_5$5 = ["id", "value", "placeholder", "aria-label", "onInput"];
  const _hoisted_6$4 = ["aria-label"];
  const _hoisted_7$2 = {
    key: 0,
    class: "c-filter-tags__label",
    "aria-hidden": "true"
  };
  const _hoisted_8$2 = ["aria-pressed", "onClick"];
  const _hoisted_9$2 = {
    key: 3,
    class: "c-filter-check form-check"
  };
  const _hoisted_10$2 = ["id", "checked", "onChange"];
  const _hoisted_11$1 = ["for"];
  const _hoisted_12$1 = ["aria-expanded", "aria-controls", "aria-label", "title"];
  const _hoisted_13$1 = ["aria-label", "title"];
  function _sfc_render$g(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FilterSelect = vue.resolveComponent("FilterSelect");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$f, [
      vue.createElementVNode("form", {
        id: $data.barId,
        ref: "bar",
        class: vue.normalizeClass(["c-filter-bar", { "is-open": $data.open, "is-collapsing": $data.collapsing }]),
        role: "search",
        onSubmit: _cache[2] || (_cache[2] = vue.withModifiers(() => {
        }, ["prevent"]))
      }, [
        (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          null,
          vue.renderList($options.visibleFilters, (filter, index) => {
            return vue.openBlock(), vue.createElementBlock(
              vue.Fragment,
              {
                key: filter.key
              },
              [
                vue.createElementVNode("div", {
                  class: vue.normalizeClass(["c-filter-bar__item", `c-filter-bar__item--${filter.type}`, `form-search-filter-${filter.key}`, { "c-filter-bar__item--wide": filter.wide }]),
                  "data-filter-bar-keep": index === $options.keepIndex ? "" : null
                }, [
                  vue.renderSlot(_ctx.$slots, `filter-${filter.key}`, {
                    filter,
                    value: $data.draft[filter.key],
                    update: (value) => $options.update(filter.key, value)
                  }, () => [
                    filter.type === "text" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$7, [
                      _cache[3] || (_cache[3] = vue.createElementVNode(
                        "i",
                        {
                          class: "ti ti-search c-search-field__icon",
                          "aria-hidden": "true"
                        },
                        null,
                        -1
                        /* CACHED */
                      )),
                      vue.createElementVNode("input", {
                        id: $options.inputId(filter),
                        type: "text",
                        class: "c-search-field__input",
                        autocomplete: "off",
                        value: $data.draft[filter.key],
                        placeholder: filter.placeholder || filter.label || "",
                        "aria-label": filter.label || filter.placeholder || null,
                        onInput: ($event) => $options.update(filter.key, $event.target.value)
                      }, null, 40, _hoisted_5$5)
                    ])) : filter.type === "select" ? (vue.openBlock(), vue.createBlock(_component_FilterSelect, {
                      key: 1,
                      id: $options.inputId(filter),
                      "model-value": String($data.draft[filter.key] ?? ""),
                      options: $options.optionsOf(filter),
                      placeholder: filter.placeholder || "",
                      label: filter.label || "",
                      loading: $options.isLoading(filter),
                      "onUpdate:modelValue": ($event) => $options.update(filter.key, $event)
                    }, null, 8, ["id", "model-value", "options", "placeholder", "label", "loading", "onUpdate:modelValue"])) : filter.type === "tags" ? (vue.openBlock(), vue.createElementBlock("div", {
                      key: 2,
                      class: "c-filter-tags",
                      role: "group",
                      "aria-label": filter.label
                    }, [
                      filter.label ? (vue.openBlock(), vue.createElementBlock(
                        "span",
                        _hoisted_7$2,
                        vue.toDisplayString(filter.label),
                        1
                        /* TEXT */
                      )) : vue.createCommentVNode("v-if", true),
                      (vue.openBlock(true), vue.createElementBlock(
                        vue.Fragment,
                        null,
                        vue.renderList($options.optionsOf(filter), (option) => {
                          return vue.openBlock(), vue.createElementBlock("button", {
                            key: option.value,
                            type: "button",
                            class: vue.normalizeClass(["c-filter-tags__tag", { active: $options.isTagActive(filter, option.value) }]),
                            "aria-pressed": $options.isTagActive(filter, option.value) ? "true" : "false",
                            onClick: ($event) => $options.toggleTag(filter, option.value)
                          }, vue.toDisplayString(option.label), 11, _hoisted_8$2);
                        }),
                        128
                        /* KEYED_FRAGMENT */
                      ))
                    ], 8, _hoisted_6$4)) : filter.type === "checkbox" ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_9$2, [
                      vue.createElementVNode("input", {
                        id: $options.inputId(filter),
                        class: "form-check-input",
                        type: "checkbox",
                        checked: $data.draft[filter.key],
                        onChange: ($event) => $options.update(filter.key, $event.target.checked)
                      }, null, 40, _hoisted_10$2),
                      vue.createElementVNode("label", {
                        class: "form-check-label",
                        for: $options.inputId(filter)
                      }, vue.toDisplayString(filter.label), 9, _hoisted_11$1)
                    ])) : vue.createCommentVNode("v-if", true)
                  ])
                ], 10, _hoisted_3$9),
                $options.collapsible && index === $options.toggleAfter ? (vue.openBlock(), vue.createElementBlock("button", {
                  key: 0,
                  ref_for: true,
                  ref: "toggle",
                  type: "button",
                  class: vue.normalizeClass(["btn c-icon-button c-icon-button--ghost c-filter-bar__toggle", { "is-active": $data.open }]),
                  "data-filter-bar-toggle": "",
                  "aria-expanded": $data.open ? "true" : "false",
                  "aria-controls": $data.barId,
                  "aria-label": $options.toggleLabel,
                  title: $options.toggleLabel,
                  onClick: _cache[0] || (_cache[0] = (...args) => $options.onToggle && $options.onToggle(...args))
                }, [..._cache[4] || (_cache[4] = [
                  vue.createElementVNode(
                    "i",
                    {
                      class: "ti ti-filter",
                      "aria-hidden": "true"
                    },
                    null,
                    -1
                    /* CACHED */
                  )
                ])], 10, _hoisted_12$1)) : vue.createCommentVNode("v-if", true)
              ],
              64
              /* STABLE_FRAGMENT */
            );
          }),
          128
          /* KEYED_FRAGMENT */
        )),
        vue.createVNode(vue.Transition, { name: "c-filter-bar-clear" }, {
          default: vue.withCtx(() => [
            $options.resettable ? (vue.openBlock(), vue.createElementBlock("button", {
              key: 0,
              ref: "clear",
              type: "button",
              class: "btn c-icon-button c-icon-button--ghost c-filter-bar__clear",
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
            ])], 8, _hoisted_13$1)) : vue.createCommentVNode("v-if", true)
          ]),
          _: 1
          /* STABLE */
        })
      ], 42, _hoisted_2$b)
    ]);
  }
  const C6 = /* @__PURE__ */ _export_sfc(_sfc_main$g, [["render", _sfc_render$g]]);
  let uid = 0;
  const _sfc_main$f = {
    name: "PageToolbar",
    props: {
      title: { type: String, default: "" },
      titleTag: { type: String, default: "h1" }
    },
    data() {
      return {
        titleId: `page-toolbar-title-${++uid}`
      };
    }
  };
  const _hoisted_1$e = ["aria-labelledby"];
  const _hoisted_2$a = {
    key: 0,
    class: "c-page-toolbar__header"
  };
  const _hoisted_3$8 = {
    key: 1,
    class: "c-page-toolbar__actions"
  };
  function _sfc_render$f(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("section", {
      class: "c-page-toolbar",
      "aria-labelledby": $props.title ? $data.titleId : null
    }, [
      $props.title || _ctx.$slots.actions ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$a, [
        $props.title ? (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent($props.titleTag), {
          key: 0,
          id: $data.titleId,
          class: "c-page-toolbar__title"
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
        _ctx.$slots.actions ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$8, [
          vue.renderSlot(_ctx.$slots, "actions")
        ])) : vue.createCommentVNode("v-if", true)
      ])) : vue.createCommentVNode("v-if", true),
      vue.renderSlot(_ctx.$slots, "default")
    ], 8, _hoisted_1$e);
  }
  const C10 = /* @__PURE__ */ _export_sfc(_sfc_main$f, [["render", _sfc_render$f]]);
  const _sfc_main$e = {
    name: "CardDirectory",
    components: { CardGrid: C1, FilterBar: C6, PageToolbar: C10 },
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
      idPrefix: { type: String, default: "filter" }
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
        error: null,
        failedPage: null,
        pageStarts: [0]
      };
    },
    computed: {
      hasMore() {
        return this.page < this.pages;
      }
    },
    created() {
      this.requestSeq = 0;
      this.started = false;
    },
    mounted() {
      this.started = true;
      this.fetch(1);
    },
    beforeUnmount() {
      this.requestSeq++;
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
      setFilter(key, value) {
        return this.$refs.filterBar ? this.$refs.filterBar.setFilter(key, value) : false;
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
        this.error = null;
        const query = new URLSearchParams({
          ...requestParams(this.filters, this.values),
          page: String(page),
          pageSize: String(this.pageSize)
        }).toString();
        const url = this.url + (this.url.includes("?") ? "&" : "?") + query;
        return vue$1.client.get(url).then((response) => {
          if (seq !== this.requestSeq) {
            return;
          }
          const results = response.results || [];
          this.pageStarts = page === 1 ? [0] : [...this.pageStarts, this.items.length];
          this.items = page === 1 ? results : [...this.items, ...results];
          this.page = response.page || page;
          this.pages = response.pages || 0;
          this.total = response.total || 0;
          this.meta = Object.fromEntries(this.metaKeys.map((key) => [key, response[key]]));
          this.loading = false;
          this.failedPage = null;
          this.$emit("loaded", { total: this.total, meta: this.meta, values: { ...this.values } });
        }).catch((response) => {
          if (seq !== this.requestSeq) {
            return;
          }
          this.loading = false;
          this.failedPage = page;
          if (page === 1) {
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
      }
    }
  };
  const _hoisted_1$d = { class: "c-card-directory" };
  const _hoisted_2$9 = {
    key: 0,
    class: "c-card-directory__notice"
  };
  function _sfc_render$e(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_FilterBar = vue.resolveComponent("FilterBar");
    const _component_PageToolbar = vue.resolveComponent("PageToolbar");
    const _component_CardGrid = vue.resolveComponent("CardGrid");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$d, [
      vue.createVNode(_component_PageToolbar, {
        title: $props.title,
        "title-tag": $props.titleTag
      }, vue.createSlots({
        default: vue.withCtx(() => [
          $props.filters.length ? (vue.openBlock(), vue.createBlock(_component_FilterBar, {
            key: 0,
            ref: "filterBar",
            "model-value": $data.values,
            filters: $props.filters,
            "sync-url": $props.syncUrl,
            "id-prefix": $props.idPrefix,
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
          ]), 1032, ["model-value", "filters", "sync-url", "id-prefix", "onUpdate:modelValue"])) : vue.createCommentVNode("v-if", true)
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
      ]), 1032, ["title", "title-tag"]),
      _ctx.$slots.notice ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$9, [
        vue.renderSlot(_ctx.$slots, "notice", {
          meta: $data.meta,
          total: $data.total
        })
      ])) : vue.createCommentVNode("v-if", true),
      vue.createVNode(_component_CardGrid, {
        items: $data.items,
        "item-key": $props.itemKey,
        loading: $data.loading,
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
            index
          })
        ]),
        _: 2
        /* DYNAMIC */
      }, [
        _ctx.$slots.empty ? {
          name: "empty",
          fn: vue.withCtx(() => [
            vue.renderSlot(_ctx.$slots, "empty")
          ]),
          key: "0"
        } : void 0
      ]), 1032, ["items", "item-key", "loading", "error", "has-more", "skeleton-count", "card-class", "page-starts", "onLoadMore", "onRetry"])
    ]);
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$e, [["render", _sfc_render$e]]);
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
  const _hoisted_2$8 = ["id", "name", "disabled", "aria-required", "aria-invalid", "aria-describedby"];
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
          }, null, 10, _hoisted_2$8), [
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
  const C3 = /* @__PURE__ */ _export_sfc(_sfc_main$d, [["render", _sfc_render$d]]);
  const _sfc_main$c = {
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
      this.$refs.toggle.removeEventListener("show.bs.dropdown", this.onShow);
      this.$refs.toggle.removeEventListener("hidden.bs.dropdown", this.onHidden);
      this.disposeOwnDropdown();
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
  const _hoisted_1$b = { class: "nav-item dropdown" };
  const _hoisted_2$7 = ["aria-label", "title"];
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
            "aria-label": $props.toggleAriaLabel,
            title: $props.toggleTitle
          }, [
            vue.renderSlot(_ctx.$slots, "toggle")
          ], 10, _hoisted_2$7),
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
                              class: vue.normalizeClass("ti ti-" + entry.icon),
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
  const C4 = /* @__PURE__ */ _export_sfc(_sfc_main$c, [["render", _sfc_render$c]]);
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
  const C5 = /* @__PURE__ */ _export_sfc(_sfc_main$b, [["render", _sfc_render$b]]);
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
  const C8 = /* @__PURE__ */ _export_sfc(_sfc_main$a, [["render", _sfc_render$a]]);
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
  const C9 = /* @__PURE__ */ _export_sfc(_sfc_main$9, [["render", _sfc_render$9]]);
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
  const C11 = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["render", _sfc_render$8]]);
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
  const _sfc_main$7 = {
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
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
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
  const C12 = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$7]]);
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
  const C13 = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  const TRANSITION_MS = 220;
  const AUTOCLOSE = {
    info: 6e3,
    success: 2e3,
    warn: 1e4,
    error: 0
  };
  const ICONS = {
    info: "ti ti-info-circle info",
    success: "ti ti-circle-check success",
    warn: "ti ti-alert-triangle warning",
    error: "ti ti-alert-circle error"
  };
  const TONES = {
    info: "status-bar-info",
    success: "status-bar-success",
    warn: "status-bar-warning",
    error: "status-bar-error"
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
      toneClass() {
        return TONES[this.entry.level] || TONES.info;
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
  const _hoisted_1$5 = { class: "status-bar-header" };
  const _hoisted_2$5 = {
    key: 0,
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
        vue.createElementVNode(
          "div",
          {
            class: vue.normalizeClass(["status-bar-content", $options.toneClass]),
            role: "status",
            "aria-live": "polite"
          },
          [
            vue.createElementVNode("div", _hoisted_1$5, [
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
                  onClick: _cache[0] || (_cache[0] = (...args) => $options.toggleDetails && $options.toggleDetails(...args))
                },
                vue.toDisplayString($data.entry.message),
                3
                /* TEXT, CLASS */
              ),
              $options.hasDetails ? (vue.openBlock(), vue.createElementBlock("a", {
                key: 0,
                class: "showMore",
                onClick: _cache[1] || (_cache[1] = (...args) => $options.toggleDetails && $options.toggleDetails(...args))
              }, [
                vue.createElementVNode(
                  "i",
                  {
                    class: vue.normalizeClass($data.detailsOpen ? "ti ti-chevron-down" : "ti ti-chevron-up")
                  },
                  null,
                  2
                  /* CLASS */
                )
              ])) : vue.createCommentVNode("v-if", true),
              vue.createElementVNode("a", {
                class: "status-bar-close",
                onClick: _cache[2] || (_cache[2] = (...args) => $options.close && $options.close(...args))
              }, "×")
            ]),
            $data.detailsOpen ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$5, [
              vue.createElementVNode(
                "pre",
                null,
                vue.toDisplayString($options.detailsText),
                1
                /* TEXT */
              )
            ])) : vue.createCommentVNode("v-if", true)
          ],
          2
          /* CLASS */
        )
      ],
      2
      /* CLASS */
    )) : vue.createCommentVNode("v-if", true);
  }
  const C14 = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
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
  const C15 = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
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
  const C16 = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
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
  const C17 = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
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
            class: vue.normalizeClass(["modal-dialog", [$options.sizeClass, $props.dialogClass]])
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
  const C18 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
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
  const C19 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("CardDirectory", C0);
  vue$1.register("CardGrid", C1);
  vue$1.register("CardSkeleton", C2);
  vue$1.register("CheckboxField", C3);
  vue$1.register("DropdownMenu", C4);
  vue$1.register("ExtensionSlot", C5);
  vue$1.register("FilterBar", C6);
  vue$1.register("FilterSelect", C7);
  vue$1.register("HumHubForm", C8);
  vue$1.register("LegacyFormWrapper", C9);
  vue$1.register("PageToolbar", C10);
  vue$1.register("RichTextField", C11);
  vue$1.register("RichTextOutput", C12);
  vue$1.register("SelectField", C13);
  vue$1.register("StatusBar", C14);
  vue$1.register("SubmitButton", C15);
  vue$1.register("TextField", C16);
  vue$1.register("TextareaField", C17);
  vue$1.register("UiModal", C18);
  vue$1.register("UploadField", C19);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.core.vue.js.map
