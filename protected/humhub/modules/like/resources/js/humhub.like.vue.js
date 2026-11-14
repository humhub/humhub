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
    // `UserModule.base` and `base` are needed here, not just `LikeModule.base`, because the
    // mounter (humhub.vue.js's mountElement()) only preloads `i18nCategories` off the
    // TOP-LEVEL island component - this one. `UserList` (nested inside the modal below, see
    // its own `i18nCategories`) and `UiModal`'s implicit reliance on `closeLabel` never get a
    // preload of their own; declaring both categories here is what keeps their strings out of
    // the English fallback on a cold cache. See CommentSection.vue's own `i18nCategories` for
    // the same pattern (a top-level island preloading categories its nested children need).
    i18nCategories: ["LikeModule.base", "UserModule.base", "base"],
    props: {
      recordId: { type: Number, required: true },
      likeCount: { type: Number, default: null },
      currentUserLiked: { type: Boolean, default: null }
    },
    data() {
      return {
        liked: this.currentUserLiked,
        count: this.likeCount,
        busy: false,
        showUserList: false
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
        return vue$1.apiUrl(`like/${this.recordId}/users`);
      },
      // Same message key the legacy HTML user-list action used for the modal title
      // (`Yii::t('LikeModule.base', "<strong>Users</strong> who like this")`) - kept
      // as trusted, translator-authored markup (rendered via v-html in the template,
      // same trust boundary the legacy `Modal::beginDialog(['title' => $title])` call
      // already had for this exact string - see `humhub\widgets\modal\Modal`'s "not
      // html encoded!" docblock on `$title`), not user input.
      userListTitle() {
        return vue$1.i18n.t("LikeModule.base", "<strong>Users</strong> who like this");
      },
      // The count opens the list of the users: "Users who like this (2)" rather than "(2)"
      userListLinkLabel() {
        return this.userListTitle.replace(/<[^>]*>/g, "") + " (" + this.count + ")";
      },
      // Used for both the header close button's aria-label and the footer Close
      // button's visible label below - 'base' is preloaded via `i18nCategories`
      // above precisely so this reads translated on a cold cache too.
      closeLabel() {
        return vue$1.i18n.t("base", "Close");
      }
    },
    created() {
      if (!this.ready) {
        this.load();
      }
    },
    methods: {
      load() {
        vue$1.client.get(vue$1.apiUrl(`like/${this.recordId}`)).then((response) => {
          this.liked = response.liked;
          this.count = response.total;
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
        const endpoint = vue$1.apiUrl(`like/${this.recordId}`);
        const request = this.liked ? vue$1.client.del(endpoint) : vue$1.client.put(endpoint);
        request.then((response) => {
          this.liked = response.liked;
          this.count = response.total;
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
  const _hoisted_3 = { class: "likeCount" };
  const _hoisted_4 = ["aria-label"];
  const _hoisted_5 = { class: "likeCount" };
  const _hoisted_6 = ["id", "innerHTML"];
  const _hoisted_7 = ["aria-label"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_UserList = vue.resolveComponent("UserList");
    const _component_UiModal = vue.resolveComponent("UiModal");
    return $options.ready ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_1, [
      $options.guest ? (vue.openBlock(), vue.createElementBlock(
        vue.Fragment,
        { key: 0 },
        [
          vue.createElementVNode("a", {
            href: $options.loginUrl,
            "data-bs-target": "#globalModal"
          }, [
            _cache[7] || (_cache[7] = vue.createElementVNode(
              "i",
              {
                class: "ti ti-thumb-up",
                "aria-hidden": "true"
              },
              null,
              -1
              /* CACHED */
            )),
            vue.createTextVNode(
              vue.toDisplayString($options.likeLabel),
              1
              /* TEXT */
            )
          ], 8, _hoisted_2),
          vue.createCommentVNode("\n                The `{{ ' ' }}` interpolation is load-bearing, not decorative: legacy\n                `likeLink.php` had this as whitespace (a newline+indent) BETWEEN the anchor\n                and this span, which the browser collapses to a single rendered space. Vue's\n                template compiler (`whitespace: 'condense'`, the default) strips a purely\n                static whitespace-only text node containing a newline entirely rather than\n                collapsing it — an interpolation is not static text, so it survives condensing\n                and keeps rendering \"Like (2)\" instead of \"Like(2)\".\n            "),
          $data.count > 0 ? (vue.openBlock(), vue.createElementBlock(
            vue.Fragment,
            { key: 0 },
            [
              _cache[8] || (_cache[8] = vue.createTextVNode(
                vue.toDisplayString(" "),
                -1
                /* CACHED */
              )),
              vue.createElementVNode(
                "span",
                _hoisted_3,
                "(" + vue.toDisplayString($data.count) + ")",
                1
                /* TEXT */
              )
            ],
            64
            /* STABLE_FRAGMENT */
          )) : vue.createCommentVNode("v-if", true)
        ],
        64
        /* STABLE_FRAGMENT */
      )) : (vue.openBlock(), vue.createElementBlock(
        vue.Fragment,
        { key: 1 },
        [
          vue.createCommentVNode('\n                One element for both states: it keeps the keyboard focus when its text changes (WCAG\n                2.4.3). A button to the screen reader, which a link with `href="#"` would not be.\n            '),
          vue.createElementVNode(
            "a",
            {
              href: "#",
              role: "button",
              class: vue.normalizeClass([$data.liked ? "unlike" : "like", "likeAnchor"]),
              onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => $options.toggle(), ["prevent"])),
              onKeydown: _cache[1] || (_cache[1] = vue.withKeys(vue.withModifiers(($event) => $options.toggle(), ["prevent"]), ["space"]))
            },
            [
              vue.createElementVNode(
                "i",
                {
                  class: vue.normalizeClass(["ti", $data.liked ? "ti-thumb-up-filled" : "ti-thumb-up"]),
                  "aria-hidden": "true"
                },
                null,
                2
                /* CLASS */
              ),
              vue.createTextVNode(
                vue.toDisplayString($data.liked ? $options.unlikeLabel : $options.likeLabel),
                1
                /* TEXT */
              )
            ],
            34
            /* CLASS, NEED_HYDRATION */
          ),
          vue.createCommentVNode(" See the guest branch's own comment above on the `{{ ' ' }}` interpolation. "),
          $data.count > 0 ? (vue.openBlock(), vue.createElementBlock(
            vue.Fragment,
            { key: 0 },
            [
              _cache[9] || (_cache[9] = vue.createTextVNode(
                vue.toDisplayString(" "),
                -1
                /* CACHED */
              )),
              vue.createElementVNode("a", {
                href: "#",
                "aria-label": $options.userListLinkLabel,
                "aria-haspopup": "dialog",
                onClick: _cache[2] || (_cache[2] = vue.withModifiers(($event) => $data.showUserList = true, ["prevent"]))
              }, [
                vue.createElementVNode(
                  "span",
                  _hoisted_5,
                  "(" + vue.toDisplayString($data.count) + ")",
                  1
                  /* TEXT */
                )
              ], 8, _hoisted_4)
            ],
            64
            /* STABLE_FRAGMENT */
          )) : vue.createCommentVNode("v-if", true)
        ],
        64
        /* STABLE_FRAGMENT */
      )),
      vue.createVNode(_component_UiModal, {
        show: $data.showUserList,
        "onUpdate:show": _cache[6] || (_cache[6] = ($event) => $data.showUserList = $event)
      }, {
        header: vue.withCtx(({ titleId }) => [
          vue.createElementVNode("h5", {
            class: "modal-title",
            id: titleId,
            innerHTML: $options.userListTitle
          }, null, 8, _hoisted_6),
          vue.createElementVNode("button", {
            type: "button",
            class: "btn-close",
            "aria-label": $options.closeLabel,
            onClick: _cache[3] || (_cache[3] = ($event) => $data.showUserList = false)
          }, null, 8, _hoisted_7)
        ]),
        footer: vue.withCtx(() => [
          vue.createElementVNode(
            "button",
            {
              type: "button",
              class: "btn btn-light",
              onClick: _cache[5] || (_cache[5] = ($event) => $data.showUserList = false)
            },
            vue.toDisplayString($options.closeLabel),
            1
            /* TEXT */
          )
        ]),
        default: vue.withCtx(() => [
          $data.showUserList ? (vue.openBlock(), vue.createBlock(_component_UserList, {
            key: 0,
            url: $options.userListUrl,
            onUserClick: _cache[4] || (_cache[4] = ($event) => $data.showUserList = false)
          }, null, 8, ["url"])) : vue.createCommentVNode("v-if", true)
        ]),
        _: 1
        /* STABLE */
      }, 8, ["show"])
    ])) : vue.createCommentVNode("v-if", true);
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("LikeButton", C0);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.like.vue.js.map
