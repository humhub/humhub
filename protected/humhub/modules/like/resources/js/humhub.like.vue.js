/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from like/vue/ via `grunt build-vue --module=like`.
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
    i18nCategories: ["LikeModule.base"],
    props: {
      recordId: { type: Number, required: true },
      likeCount: { type: Number, default: null },
      currentUserLiked: { type: Boolean, default: null }
    },
    data() {
      return {
        liked: this.currentUserLiked,
        count: this.likeCount,
        busy: false
      };
    },
    computed: {
      // Guests never get a liked state from the server (LikeLink always sends
      // `currentUserLiked: false` for them) — the like/unlike anchors and the
      // user-list link are member-only, so ready() must not wait on `liked`.
      ready() {
        return this.guest ? this.count !== null : this.liked !== null && this.count !== null;
      },
      guest() {
        return vue$1.getConfig("user").isGuest === true;
      },
      loginUrl() {
        return vue$1.getConfig("user").loginUrl;
      },
      likeLabel() {
        return vue$1.i18n.t("LikeModule.base", "Like");
      },
      unlikeLabel() {
        return vue$1.i18n.t("LikeModule.base", "Unlike");
      },
      userListUrl() {
        return vue$1.url("/like/like/user-list", { recordId: this.recordId });
      }
    },
    created() {
      if (!this.ready) {
        this.load();
      }
    },
    methods: {
      load() {
        vue$1.client.get(vue$1.url("/like/like/info", { recordId: this.recordId })).then((response) => {
          this.liked = response.currentUserLiked;
          this.count = response.likeCounter;
        }).catch((e) => {
          vue$1.log.error(e, true);
          this.liked = false;
          this.count = 0;
        });
      },
      toggle() {
        if (this.busy) {
          return;
        }
        this.busy = true;
        vue$1.client.post(vue$1.url(this.liked ? "/like/like/unlike" : "/like/like/like", { recordId: this.recordId })).then((response) => {
          this.liked = response.currentUserLiked;
          this.count = response.likeCounter;
          this.busy = false;
          if (this.liked) {
            jQuery(this.$el).trigger("humhub:like:liked");
          }
        }).catch((e) => {
          this.busy = false;
          vue$1.log.error(e, true);
        });
      }
    }
  };
  const _hoisted_1 = {
    key: 0,
    class: "likeLinkContainer"
  };
  const _hoisted_2 = ["href"];
  const _hoisted_3 = {
    key: 0,
    class: "likeCount"
  };
  const _hoisted_4 = ["href"];
  const _hoisted_5 = { class: "likeCount" };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    return $options.ready ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_1, [
      $options.guest ? (vue.openBlock(), vue.createElementBlock(
        vue.Fragment,
        { key: 0 },
        [
          vue.createElementVNode("a", {
            href: $options.loginUrl,
            "data-bs-target": "#globalModal"
          }, vue.toDisplayString($options.likeLabel), 9, _hoisted_2),
          $data.count > 0 ? (vue.openBlock(), vue.createElementBlock(
            "span",
            _hoisted_3,
            "(" + vue.toDisplayString($data.count) + ")",
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true)
        ],
        64
        /* STABLE_FRAGMENT */
      )) : (vue.openBlock(), vue.createElementBlock(
        vue.Fragment,
        { key: 1 },
        [
          !$data.liked ? (vue.openBlock(), vue.createElementBlock(
            "a",
            {
              key: 0,
              href: "#",
              class: "like likeAnchor",
              onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => $options.toggle(), ["prevent"]))
            },
            vue.toDisplayString($options.likeLabel),
            1
            /* TEXT */
          )) : (vue.openBlock(), vue.createElementBlock(
            "a",
            {
              key: 1,
              href: "#",
              class: "unlike likeAnchor",
              onClick: _cache[1] || (_cache[1] = vue.withModifiers(($event) => $options.toggle(), ["prevent"]))
            },
            vue.toDisplayString($options.unlikeLabel),
            1
            /* TEXT */
          )),
          $data.count > 0 ? (vue.openBlock(), vue.createElementBlock("a", {
            key: 2,
            href: $options.userListUrl,
            "data-bs-target": "#globalModal"
          }, [
            vue.createElementVNode(
              "span",
              _hoisted_5,
              "(" + vue.toDisplayString($data.count) + ")",
              1
              /* TEXT */
            )
          ], 8, _hoisted_4)) : vue.createCommentVNode("v-if", true)
        ],
        64
        /* STABLE_FRAGMENT */
      ))
    ])) : vue.createCommentVNode("v-if", true);
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("LikeButton", C0);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.like.vue.js.map
