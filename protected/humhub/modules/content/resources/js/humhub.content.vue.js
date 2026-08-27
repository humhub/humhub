/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from content/vue/ via `grunt build-vue --module=content`.
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
  const _sfc_main = {
    i18nCategories: ["base"],
    props: {
      /** The content id whose menu this is — NOT the owning record's own primary key. */
      contentId: { type: Number, required: true },
      /**
       * Where this menu is being shown, one of the server's `StreamEntryOptions`
       * `VIEW_CONTEXT_*` values (`stream`, `detail`, `modal`, …). Selects the render-options
       * profile the server would have used in the same place, so a menu inside a module's
       * own UI is not offered stream-only actions.
       */
      viewContext: { type: String, default: null },
      /** The host island's own entries, in `DropdownMenu`'s entry shape. */
      entries: { type: Array, default: () => [] },
      /** Merged into what every entry's `condition`/`onClick`/`component` receives. */
      context: { type: Object, default: () => ({}) },
      /**
       * Core entries this host renders ITSELF and does not want from the server, by name
       * (`edit`, `delete`, `permalink`, `pin`, `move`, `archive`, `topics`, `visibility`,
       * `notifications`). Without this a host with its own Edit action gets a second,
       * server-rendered one next to it.
       */
      suppress: { type: Array, default: () => [] },
      toggleAriaLabel: { type: String, default: null },
      toggleClass: { type: String, default: "nav-link dropdown-toggle" },
      /**
       * Passed straight to `DropdownMenu`. The default is the platform's corner-controls
       * markup, which is positioned `absolute` platform-wide — a host rendering this menu
       * inline (a list row, a toolbar) has to say so. See `DropdownMenu`'s own docblock.
       */
      rootClass: { type: String, default: "nav nav-pills preferences" },
      alignEnd: { type: Boolean, default: true }
    },
    // Fires once the server's answer is in, so a host that wants to react to the caller's
    // permissions (rather than only gate menu entries on them) does not have to ask again.
    emits: ["loaded"],
    data() {
      return {
        loading: false,
        loaded: false,
        serverEntries: [],
        capabilities: {}
      };
    },
    computed: {
      resolvedToggleLabel() {
        return this.toggleAriaLabel || vue$1.i18n.t("base", "Toggle stream entry menu");
      },
      /**
       * Host entries first, then the server's — minus any the host already renders itself.
       * A host that provides its own `delete` entry means it, and the server's version of
       * the same id would otherwise appear twice.
       */
      mergedEntries() {
        const ownIds = this.entries.map((entry) => entry.id);
        return this.entries.concat(
          this.serverEntries.filter((entry) => ownIds.indexOf(entry.id) === -1)
        );
      },
      menuContext() {
        return { ...this.context, contentId: this.contentId, capabilities: this.capabilities };
      }
    },
    methods: {
      load() {
        if (this.loaded || this.loading) {
          return;
        }
        this.loading = true;
        const params = {};
        if (this.viewContext) {
          params.viewContext = this.viewContext;
        }
        if (this.suppress.length) {
          params.suppress = this.suppress.join(",");
        }
        vue$1.client.get(vue$1.apiUrl("content/" + this.contentId + "/controls", params)).then((response) => {
          this.serverEntries = response.entries || [];
          this.capabilities = response.capabilities || {};
          this.loaded = true;
          this.loading = false;
          this.$emit("loaded", this.capabilities);
        }).catch((e) => {
          this.loading = false;
          vue$1.log.error(e, true);
        });
      }
    }
  };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DropdownMenu = vue.resolveComponent("DropdownMenu");
    return vue.openBlock(), vue.createBlock(_component_DropdownMenu, {
      "toggle-aria-label": $options.resolvedToggleLabel,
      "toggle-class": $props.toggleClass,
      "root-class": $props.rootClass,
      "align-end": $props.alignEnd,
      "menu-id": "content.controls",
      entries: $options.mergedEntries,
      context: $options.menuContext,
      loading: $data.loading,
      onOpen: $options.load
    }, {
      toggle: vue.withCtx(() => [
        vue.renderSlot(_ctx.$slots, "toggle")
      ]),
      default: vue.withCtx(() => [
        vue.renderSlot(_ctx.$slots, "default", { capabilities: $data.capabilities })
      ]),
      _: 3
      /* FORWARDED */
    }, 8, ["toggle-aria-label", "toggle-class", "root-class", "align-end", "entries", "context", "loading", "onOpen"]);
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("ContentControls", C0);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.content.vue.js.map
