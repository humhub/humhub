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
      likeUrl: { type: String, required: true },
      unlikeUrl: { type: String, required: true },
      userListUrl: { type: String, required: true },
      likeCount: { type: Number, default: 0 },
      currentUserLiked: { type: Boolean, default: false },
      title: { type: String, default: "" },
      // Server-rendered translations: avoids a cold-cache i18n XHR round trip
      // before the island can render its labels (FOUC). Both messages remain
      // PHP-extractable via Yii::t('LikeModule.base', ...) in likeLink.php.
      likeLabel: { type: String, required: true },
      unlikeLabel: { type: String, required: true }
    },
    data() {
      return {
        liked: this.currentUserLiked,
        count: this.likeCount,
        busy: false
      };
    },
    methods: {
      toggle(url) {
        if (this.busy) {
          return;
        }
        this.busy = true;
        vue$1.client.post(url).then((response) => {
          this.liked = response.currentUserLiked;
          this.count = response.likeCounter;
          if (this.liked) {
            jQuery(this.$el).trigger("humhub:like:liked");
          }
          this.busy = false;
        }).catch((e) => {
          vue$1.log.error(e, true);
          this.busy = false;
        });
      }
    }
  };
  const _hoisted_1 = { class: "likeLinkContainer" };
  const _hoisted_2 = ["href"];
  const _hoisted_3 = ["title"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("span", _hoisted_1, [
      !$data.liked ? (vue.openBlock(), vue.createElementBlock(
        "a",
        {
          key: 0,
          href: "#",
          class: "like likeAnchor",
          onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => $options.toggle($props.likeUrl), ["prevent"]))
        },
        vue.toDisplayString($props.likeLabel),
        1
        /* TEXT */
      )) : (vue.openBlock(), vue.createElementBlock(
        "a",
        {
          key: 1,
          href: "#",
          class: "unlike likeAnchor",
          onClick: _cache[1] || (_cache[1] = vue.withModifiers(($event) => $options.toggle($props.unlikeUrl), ["prevent"]))
        },
        vue.toDisplayString($props.unlikeLabel),
        1
        /* TEXT */
      )),
      $data.count > 0 ? (vue.openBlock(), vue.createElementBlock("a", {
        key: 2,
        href: $props.userListUrl,
        "data-bs-target": "#globalModal"
      }, [
        vue.createElementVNode("span", {
          class: "likeCount",
          title: $props.title
        }, "(" + vue.toDisplayString($data.count) + ")", 9, _hoisted_3)
      ], 8, _hoisted_2)) : vue.createCommentVNode("v-if", true)
    ]);
  }
  const LikeButton = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("LikeButton", LikeButton);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.like.vue.js.map
