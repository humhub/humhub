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
  const _sfc_main$4 = {
    props: {
      toggleAriaLabel: { type: String, required: true },
      alignEnd: { type: Boolean, default: true },
      toggleClass: { type: String, default: "nav-link dropdown-toggle" }
    }
  };
  const _hoisted_1$3 = { class: "nav nav-pills preferences" };
  const _hoisted_2$2 = { class: "nav-item dropdown" };
  const _hoisted_3$1 = ["aria-label"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("ul", _hoisted_1$3, [
      vue.createElementVNode("li", _hoisted_2$2, [
        vue.createElementVNode("a", {
          href: "#",
          class: vue.normalizeClass($props.toggleClass),
          "data-bs-toggle": "dropdown",
          role: "button",
          "aria-haspopup": "true",
          "aria-expanded": "false",
          "aria-label": $props.toggleAriaLabel
        }, null, 10, _hoisted_3$1),
        vue.createElementVNode(
          "ul",
          {
            class: vue.normalizeClass(["dropdown-menu", { "dropdown-menu-end": $props.alignEnd }])
          },
          [
            vue.renderSlot(_ctx.$slots, "default")
          ],
          2
          /* CLASS */
        )
      ])
    ]);
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = {
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
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
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
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const FORM_TOKEN = "__VUEFORM__";
  const RICHTEXT_SELECTOR = '[data-ui-widget="ui.richtext.prosemirror.RichTextEditor"]';
  const RICHTEXT_COMPONENT_DATA = "humhub-ui-richtexteditor";
  const UPLOAD_SELECTOR = ".main_comment_upload";
  const UPLOAD_COMPONENT_DATA = "humhub-file-upload";
  let instanceCounter = 0;
  const _sfc_main$2 = {
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
  const _hoisted_1$2 = ["innerHTML"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", { innerHTML: $options.processedShell }, null, 8, _hoisted_1$2)), [
      [_directive_additions]
    ]);
  }
  const C2 = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = {
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
      }
    }
  };
  const _hoisted_1$1 = { key: 0 };
  const _hoisted_2$1 = {
    key: 0,
    class: "richtext-oembed-container",
    style: { "display": "none" }
  };
  const _hoisted_3 = ["data-oembed", "innerHTML"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return $props.message ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, [
      vue.createElementVNode(
        "div",
        vue.normalizeProps(vue.guardReactiveProps($options.envelopeAttrs)),
        vue.toDisplayString($props.message),
        17
        /* TEXT, FULL_PROPS */
      ),
      $options.hasOembeds ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$1, [
        (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          null,
          vue.renderList($options.oembeds, (html, url) => {
            return vue.openBlock(), vue.createElementBlock("div", {
              key: url,
              "data-oembed": url,
              innerHTML: html
            }, null, 8, _hoisted_3);
          }),
          128
          /* KEYED_FRAGMENT */
        ))
      ])) : vue.createCommentVNode("v-if", true)
    ])), [
      [_directive_additions]
    ]) : vue.createCommentVNode("v-if", true);
  }
  const C3 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = {
    props: {
      guid: { type: String, required: true },
      displayName: { type: String, required: true },
      url: { type: String, required: true },
      imageUrl: { type: String, required: true },
      imageAlt: { type: String, default: null },
      contentContainerId: { type: Number, default: null },
      // Tri-state: null (default) renders no online-status indicator at all -
      // distinct from `false` (renders the "offline" variant).
      online: { type: Boolean, default: null },
      size: { type: Number, default: 25 },
      link: { type: Boolean, default: true }
    },
    computed: {
      resolvedAlt() {
        return this.imageAlt || this.displayName;
      },
      imageStyle() {
        return `width: ${this.size}px; height: ${this.size}px`;
      },
      hasOnlineIndicator() {
        return this.online !== null;
      },
      sizeBucketClass() {
        if (this.size < 28) {
          return "img-size-small";
        }
        if (this.size > 48) {
          return "img-size-large";
        }
        return "img-size-medium";
      },
      onlineLabel() {
        if (this.online === null) {
          return null;
        }
        return this.online ? vue$1.i18n.t("UserModule.base", "Online") : vue$1.i18n.t("UserModule.base", "Offline");
      }
    }
  };
  const _hoisted_1 = ["src", "alt", "data-contentcontainer-id", "data-guid"];
  const _hoisted_2 = ["aria-label", "title"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent($props.link ? "a" : "span"), {
      href: $props.link ? $props.url : void 0,
      class: vue.normalizeClass({ "has-online-status": $options.hasOnlineIndicator, [$options.sizeBucketClass]: $options.hasOnlineIndicator })
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("img", {
          class: "rounded",
          style: vue.normalizeStyle($options.imageStyle),
          src: $props.imageUrl,
          alt: $options.resolvedAlt,
          "data-contentcontainer-id": $props.contentContainerId,
          "data-guid": $props.guid
        }, null, 12, _hoisted_1),
        $options.hasOnlineIndicator ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          class: vue.normalizeClass(["tt user-online-status", $props.online ? "user-is-online" : "user-is-offline"]),
          "aria-label": $options.onlineLabel,
          title: $options.onlineLabel
        }, null, 10, _hoisted_2)) : vue.createCommentVNode("v-if", true)
      ]),
      _: 1
      /* STABLE */
    }, 8, ["href", "class"]);
  }
  const C4 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("DropdownMenu", C0);
  vue$1.register("ExtensionSlot", C1);
  vue$1.register("LegacyFormWrapper", C2);
  vue$1.register("RichTextOutput", C3);
  vue$1.register("UserImage", C4);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.core.vue.js.map
