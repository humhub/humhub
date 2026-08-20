/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from user/vue/ via `grunt build-vue --module=user`.
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
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("UserImage", C0);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.user.vue.js.map
