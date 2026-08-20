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
  const _sfc_main$3 = {
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
  const _hoisted_1$2 = { class: "nav nav-pills preferences" };
  const _hoisted_2$1 = { class: "nav-item dropdown" };
  const _hoisted_3 = ["aria-label"];
  const _hoisted_4 = ["onClick"];
  const _hoisted_5 = ["onClick"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("ul", _hoisted_1$2, [
      vue.createElementVNode("li", _hoisted_2$1, [
        vue.createElementVNode("a", {
          href: "#",
          class: vue.normalizeClass($props.toggleClass),
          "data-bs-toggle": "dropdown",
          role: "button",
          "aria-haspopup": "true",
          "aria-expanded": "false",
          "aria-label": $props.toggleAriaLabel
        }, null, 10, _hoisted_3),
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
                  ], 8, _hoisted_4)) : (vue.openBlock(), vue.createElementBlock("a", {
                    key: 2,
                    href: "#",
                    class: "dropdown-item",
                    onClick: vue.withModifiers(($event) => $options.onEntryClick(entry), ["prevent"])
                  }, vue.toDisplayString($options.resolveLabel(entry)), 9, _hoisted_5))
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
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = {
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
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
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
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const FORM_TOKEN = "__VUEFORM__";
  const RICHTEXT_SELECTOR = '[data-ui-widget="ui.richtext.prosemirror.RichTextEditor"]';
  const RICHTEXT_COMPONENT_DATA = "humhub-ui-richtexteditor";
  const UPLOAD_SELECTOR = ".vueform-upload";
  const UPLOAD_COMPONENT_DATA = "humhub-file-upload";
  let instanceCounter = 0;
  const _sfc_main$1 = {
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
  const _hoisted_1$1 = ["innerHTML"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", { innerHTML: $options.processedShell }, null, 8, _hoisted_1$1)), [
      [_directive_additions]
    ]);
  }
  const C2 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const OEMBED_URL_ENTITY_MAP = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" };
  const _sfc_main = {
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
  const _hoisted_1 = { key: 0 };
  const _hoisted_2 = ["data-oembed", "innerHTML"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return $props.message ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
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
            }, null, 8, _hoisted_2);
          }),
          128
          /* KEYED_FRAGMENT */
        ))
      ])) : vue.createCommentVNode("v-if", true)
    ])), [
      [_directive_additions]
    ]) : vue.createCommentVNode("v-if", true);
  }
  const C3 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("DropdownMenu", C0);
  vue$1.register("ExtensionSlot", C1);
  vue$1.register("LegacyFormWrapper", C2);
  vue$1.register("RichTextOutput", C3);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.core.vue.js.map
