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
  const _sfc_main$1 = {
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
  const _hoisted_1$1 = ["src", "alt", "data-contentcontainer-id", "data-guid"];
  const _hoisted_2$1 = ["aria-label", "title"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
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
        }, null, 12, _hoisted_1$1),
        $options.hasOnlineIndicator ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          class: vue.normalizeClass(["tt user-online-status", $props.online ? "user-is-online" : "user-is-offline"]),
          "aria-label": $options.onlineLabel,
          title: $options.onlineLabel
        }, null, 10, _hoisted_2$1)) : vue.createCommentVNode("v-if", true)
      ]),
      _: 1
      /* STABLE */
    }, 8, ["href", "class"]);
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = {
    name: "UserList",
    // INERT for every consumer of this component today: the mounter (humhub.vue.js's
    // mountElement()) only ever reads `i18nCategories` off the TOP-LEVEL component it
    // mounts as an island, and `UserList` is always nested (e.g. inside `LikeButton`'s
    // user-list modal) rather than mounted directly - see
    // docs/develop/ui-js-vuejs-components.md, "Mounting and lifecycle", "i18n
    // preloading". Left declared (rather than removed) because it becomes live and
    // correct the moment some future caller mounts `<user-list>` directly as its own
    // island; until then, every current top-level consumer must declare
    // `UserModule.base` itself (`LikeButton.vue` does, alongside `base` for the same
    // reason - see its own `i18nCategories` comment).
    i18nCategories: ["UserModule.base"],
    props: {
      url: { type: String, required: true },
      pageSize: { type: Number, default: null }
    },
    // `user-click`: fired on every row click (in addition to, not instead of, that row's
    // own native navigation - the `<a href>` is never `preventDefault()`-ed here), with the
    // clicked user as payload. Mirrors legacy `UserListBox`'s `data-modal-close="1"` rows
    // (`userListBox.php`) closing whatever modal hosts the list on click - `LikeButton.vue`
    // listens for this to close its own `UiModal` the same way.
    emits: ["user-click"],
    data() {
      return {
        users: [],
        total: 0,
        hasMore: false,
        nextPage: null,
        busy: false,
        error: false
      };
    },
    computed: {
      emptyLabel() {
        return vue$1.i18n.t("UserModule.base", "No users found.");
      },
      errorLabel() {
        return vue$1.i18n.t("UserModule.base", "Could not load the user list.");
      },
      loadMoreLabel() {
        return vue$1.i18n.t("base", "Show more");
      }
    },
    created() {
      this.load(1);
    },
    methods: {
      requestUrl(page) {
        const params = [`page=${page}`];
        if (this.pageSize) {
          params.push(`limit=${this.pageSize}`);
        }
        const separator = this.url.includes("?") ? "&" : "?";
        return `${this.url}${separator}${params.join("&")}`;
      },
      async load(page) {
        this.busy = true;
        this.error = false;
        try {
          const response = await vue$1.client.get(this.requestUrl(page));
          const users = response.users ?? [];
          this.users = page === 1 ? users : this.users.concat(users);
          this.total = response.total ?? this.users.length;
          this.hasMore = !!response.hasMore;
          this.nextPage = response.nextPage ?? null;
        } catch (e) {
          this.error = true;
          vue$1.log.error(e);
        } finally {
          this.busy = false;
        }
      },
      loadMore() {
        if (this.busy || !this.hasMore || !this.nextPage) {
          return;
        }
        this.load(this.nextPage);
      }
    }
  };
  const _hoisted_1 = { class: "user-list" };
  const _hoisted_2 = { key: 0 };
  const _hoisted_3 = {
    key: 1,
    class: "hh-list"
  };
  const _hoisted_4 = ["href", "onClick"];
  const _hoisted_5 = { class: "flex-shrink-0 me-2" };
  const _hoisted_6 = { class: "flex-grow-1" };
  const _hoisted_7 = { class: "mt-0" };
  const _hoisted_8 = {
    key: 2,
    class: "text-danger"
  };
  const _hoisted_9 = {
    key: 3,
    class: "pagination-container text-center"
  };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_UserImage = vue.resolveComponent("UserImage");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      !$data.busy && !$data.error && $data.users.length === 0 ? (vue.openBlock(), vue.createElementBlock(
        "p",
        _hoisted_2,
        vue.toDisplayString($options.emptyLabel),
        1
        /* TEXT */
      )) : vue.createCommentVNode("v-if", true),
      $data.users.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3, [
        (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          null,
          vue.renderList($data.users, (user) => {
            return vue.openBlock(), vue.createElementBlock("a", {
              key: user.guid,
              href: user.url,
              class: "d-flex",
              onClick: ($event) => _ctx.$emit("user-click", user)
            }, [
              vue.createElementVNode("div", _hoisted_5, [
                vue.createVNode(
                  _component_UserImage,
                  vue.mergeProps({ ref_for: true }, user, {
                    size: 50,
                    link: false,
                    class: "m-0"
                  }),
                  null,
                  16
                  /* FULL_PROPS */
                )
              ]),
              vue.createElementVNode("div", _hoisted_6, [
                vue.createElementVNode(
                  "h4",
                  _hoisted_7,
                  vue.toDisplayString(user.displayName),
                  1
                  /* TEXT */
                )
              ])
            ], 8, _hoisted_4);
          }),
          128
          /* KEYED_FRAGMENT */
        ))
      ])) : vue.createCommentVNode("v-if", true),
      $data.error ? (vue.openBlock(), vue.createElementBlock(
        "p",
        _hoisted_8,
        vue.toDisplayString($options.errorLabel),
        1
        /* TEXT */
      )) : vue.createCommentVNode("v-if", true),
      $data.hasMore ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_9, [
        vue.createElementVNode(
          "a",
          {
            href: "#",
            class: vue.normalizeClass({ disabled: $data.busy }),
            onClick: _cache[0] || (_cache[0] = vue.withModifiers((...args) => $options.loadMore && $options.loadMore(...args), ["prevent"]))
          },
          vue.toDisplayString($options.loadMoreLabel),
          3
          /* TEXT, CLASS */
        )
      ])) : vue.createCommentVNode("v-if", true)
    ]);
  }
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("UserImage", C0);
  vue$1.register("UserList", C1);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.user.vue.js.map
