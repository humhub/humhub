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
  const _sfc_main$6 = {
    props: {
      // Part of the serialized user shape (`UserSerializer::short()`) and deliberately
      // unused: consumers spread that whole shape onto this component
      // (`v-bind="comment.author"`), and an undeclared `id` falls through to the DOM as an
      // `id` ATTRIBUTE — which puts the same element id on every avatar of a list.
      id: { type: Number, default: null },
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
        return this.imageAlt || vue$1.i18n.t("base", "Profile picture of {displayName}", { displayName: this.displayName });
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
  const _hoisted_1$5 = ["src", "alt", "data-contentcontainer-id", "data-guid"];
  const _hoisted_2$3 = ["aria-label", "title"];
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
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
        }, null, 12, _hoisted_1$5),
        $options.hasOnlineIndicator ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          class: vue.normalizeClass(["tt user-online-status", $props.online ? "user-is-online" : "user-is-offline"]),
          "aria-label": $options.onlineLabel,
          title: $options.onlineLabel
        }, null, 10, _hoisted_2$3)) : vue.createCommentVNode("v-if", true)
      ]),
      _: 1
      /* STABLE */
    }, 8, ["href", "class"]);
  }
  const __vite_glob_0_2 = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  const MAX_TAGS = 5;
  const _sfc_main$5 = {
    name: "PeopleCard",
    i18nCategories: ["UserModule.base"],
    // `ExtensionSlot` is a core component, the actions are registered by their modules — all
    // resolved through the global registry.
    components: { UserImage: __vite_glob_0_2 },
    props: {
      user: { type: Object, required: true },
      state: { type: Object, default: void 0 },
      buttons: { type: Object, default: () => ({}) },
      icons: { type: Object, default: () => ({}) },
      followEnabled: { type: Boolean, default: false },
      friendshipEnabled: { type: Boolean, default: false }
    },
    emits: ["filter-tag", "follow-change", "friendship-change"],
    computed: {
      showFooter() {
        return this.state !== null && !(this.state && this.state.isSelf);
      },
      actionContext() {
        return {
          user: this.user,
          state: this.state,
          buttons: this.buttons,
          icons: this.icons,
          followEnabled: this.followEnabled,
          friendshipEnabled: this.friendshipEnabled,
          onFollowChange: (payload) => this.$emit("follow-change", payload),
          onFriendshipChange: (payload) => this.$emit("friendship-change", payload)
        };
      },
      onlineStatus() {
        return this.state && typeof this.state.isOnline === "boolean" ? this.state.isOnline : null;
      },
      stat() {
        const friends = this.user.friendCount;
        if (this.friendshipEnabled && friends !== null && friends !== void 0) {
          return {
            kind: "friends",
            icon: "ti-users",
            count: friends,
            label: vue$1.i18n.t("UserModule.base", "{count} Friends", { count: friends })
          };
        }
        const followers = this.user.followerCount;
        if (followers !== null && followers !== void 0) {
          return {
            kind: "followers",
            icon: "ti-user-check",
            count: followers,
            label: vue$1.i18n.t("UserModule.base", "{count} Followers", { count: followers })
          };
        }
        return null;
      },
      details() {
        return (this.user.details || []).filter((detail) => String(detail).trim() !== "");
      },
      tags() {
        return (this.user.tags || []).map((tag) => String(tag).trim()).filter((tag) => tag !== "").slice(0, MAX_TAGS);
      },
      coverStyle() {
        return this.user.bannerUrl ? { backgroundImage: `url(${JSON.stringify(this.user.bannerUrl)})` } : {};
      }
    },
    methods: {
      filterByLabel(tag) {
        return vue$1.i18n.t("UserModule.base", "Filter by {tag}", { tag });
      },
      openStat() {
        if (this.stat.kind === "friends") {
          vue$1.modal.load(vue$1.url("friendship/list/popup", { userId: this.user.id }));
        } else {
          vue$1.modal.load(vue$1.url("user/profile/follower-list", { cguid: this.user.guid }));
        }
      }
    }
  };
  const _hoisted_1$4 = { class: "c-entity-card c-people-card" };
  const _hoisted_2$2 = {
    key: 0,
    class: "c-entity-card__pill"
  };
  const _hoisted_3$2 = ["title", "aria-label"];
  const _hoisted_4$2 = { class: "c-entity-card__header" };
  const _hoisted_5$1 = ["href"];
  const _hoisted_6$1 = {
    key: 0,
    class: "c-entity-card__subtitle"
  };
  const _hoisted_7$1 = { class: "c-entity-card__body" };
  const _hoisted_8$1 = ["title"];
  const _hoisted_9$1 = {
    key: 0,
    class: "c-entity-card__tags"
  };
  const _hoisted_10 = ["title", "aria-label", "onClick"];
  const _hoisted_11 = {
    key: 1,
    class: "c-entity-card__footer"
  };
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_UserImage = vue.resolveComponent("UserImage");
    const _component_ExtensionSlot = vue.resolveComponent("ExtensionSlot");
    return vue.openBlock(), vue.createElementBlock("article", _hoisted_1$4, [
      vue.createElementVNode(
        "div",
        {
          class: "c-entity-card__cover",
          style: vue.normalizeStyle($options.coverStyle)
        },
        [
          vue.createVNode(_component_UserImage, {
            class: "c-entity-card__avatar",
            guid: $props.user.guid,
            "display-name": $props.user.displayName,
            url: $props.user.url,
            "image-url": $props.user.imageUrl,
            "content-container-id": $props.user.contentContainerId,
            online: $options.onlineStatus,
            size: 80,
            link: "",
            "aria-hidden": "true",
            tabindex: "-1"
          }, null, 8, ["guid", "display-name", "url", "image-url", "content-container-id", "online"]),
          $options.stat ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$2, [
            vue.createElementVNode("button", {
              type: "button",
              class: vue.normalizeClass(["c-entity-card__stat", `c-entity-card__stat--${$options.stat.kind}`]),
              title: $options.stat.label,
              "aria-label": $options.stat.label,
              onClick: _cache[0] || (_cache[0] = (...args) => $options.openStat && $options.openStat(...args))
            }, [
              vue.createElementVNode(
                "i",
                {
                  class: vue.normalizeClass(["ti", $options.stat.icon]),
                  "aria-hidden": "true"
                },
                null,
                2
                /* CLASS */
              ),
              vue.createElementVNode(
                "span",
                null,
                vue.toDisplayString($options.stat.count),
                1
                /* TEXT */
              )
            ], 10, _hoisted_3$2)
          ])) : vue.createCommentVNode("v-if", true)
        ],
        4
        /* STYLE */
      ),
      vue.createElementVNode("div", _hoisted_4$2, [
        vue.createElementVNode("a", {
          class: "c-entity-card__title",
          href: $props.user.url
        }, vue.toDisplayString($props.user.displayName), 9, _hoisted_5$1),
        $props.user.title ? (vue.openBlock(), vue.createElementBlock(
          "p",
          _hoisted_6$1,
          vue.toDisplayString($props.user.title),
          1
          /* TEXT */
        )) : vue.createCommentVNode("v-if", true),
        vue.createVNode(_component_ExtensionSlot, {
          name: "user.card-subtitle",
          context: { user: $props.user }
        }, null, 8, ["context"])
      ]),
      vue.createElementVNode("div", _hoisted_7$1, [
        (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          null,
          vue.renderList($options.details, (detail, index) => {
            return vue.openBlock(), vue.createElementBlock("p", {
              key: index,
              class: "c-people-card__detail",
              title: detail
            }, vue.toDisplayString(detail), 9, _hoisted_8$1);
          }),
          128
          /* KEYED_FRAGMENT */
        ))
      ]),
      $options.tags.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_9$1, [
        (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          null,
          vue.renderList($options.tags, (tag) => {
            return vue.openBlock(), vue.createElementBlock("button", {
              key: tag,
              type: "button",
              class: "c-entity-card__tag",
              title: $options.filterByLabel(tag),
              "aria-label": $options.filterByLabel(tag),
              onClick: ($event) => _ctx.$emit("filter-tag", tag)
            }, vue.toDisplayString(tag), 9, _hoisted_10);
          }),
          128
          /* KEYED_FRAGMENT */
        ))
      ])) : vue.createCommentVNode("v-if", true),
      $options.showFooter ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_11, [
        $props.state === void 0 ? (vue.openBlock(), vue.createElementBlock(
          "button",
          {
            key: 0,
            type: "button",
            class: vue.normalizeClass(["c-entity-card__action c-entity-card__placeholder", $props.buttons.placeholderClass]),
            disabled: "",
            "aria-hidden": "true",
            tabindex: "-1"
          },
          " ",
          2
          /* CLASS */
        )) : (vue.openBlock(), vue.createBlock(_component_ExtensionSlot, {
          key: 1,
          name: "user.card-actions",
          context: $options.actionContext
        }, null, 8, ["context"]))
      ])) : vue.createCommentVNode("v-if", true)
    ]);
  }
  const PeopleCard = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const _sfc_main$4 = {
    name: "PeopleCardSkeleton"
  };
  const _hoisted_1$3 = {
    class: "c-card-skeleton c-entity-card-skeleton c-people-card-skeleton",
    "aria-hidden": "true"
  };
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$3, [..._cache[0] || (_cache[0] = [
      vue.createStaticVNode('<div class="c-entity-card-skeleton__cover"><span class="c-card-skeleton__block c-entity-card-skeleton__avatar"></span></div><div class="c-card-skeleton__header"><span class="c-card-skeleton__block c-card-skeleton__title"></span><span class="c-card-skeleton__block c-card-skeleton__version"></span></div><div class="c-card-skeleton__body"><span class="c-card-skeleton__block c-card-skeleton__line"></span><span class="c-card-skeleton__block c-card-skeleton__line c-card-skeleton__line--short"></span></div><div class="c-entity-card-skeleton__tags"><span class="c-card-skeleton__block c-entity-card-skeleton__tag"></span><span class="c-card-skeleton__block c-entity-card-skeleton__tag"></span><span class="c-card-skeleton__block c-entity-card-skeleton__tag"></span></div><div class="c-card-skeleton__footer"><span class="c-card-skeleton__block c-card-skeleton__action"></span></div>', 5)
    ])]);
  }
  const PeopleCardSkeleton = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const fetchStates = (ids) => {
    if (!ids.length) {
      return Promise.resolve({});
    }
    return vue$1.client.get(vue$1.apiUrl("user/states", { ids })).then((response) => response.results || {});
  };
  const fetchFollow = (userId) => vue$1.client.get(followUrl(userId)).then(normalizeFollow);
  const follow = (userId) => vue$1.client.put(followUrl(userId)).then(normalizeFollow);
  const unfollow = (userId) => vue$1.client.del(followUrl(userId)).then(normalizeFollow);
  const followUrl = (userId) => vue$1.apiUrl(`user/${userId}/follow`);
  const normalizeFollow = (response) => ({
    isFollowing: !!response.isFollowing,
    followerCount: response.followerCount ?? null,
    canFollow: !!response.canFollow
  });
  const STATE_FRIENDS = "friends";
  const _sfc_main$3 = {
    name: "PeopleDirectory",
    i18nCategories: ["UserModule.base", "FriendshipModule.base", "base"],
    // `CardDirectory` is a core component, resolved through the global registry (CoreVueAsset).
    components: { PeopleCard, PeopleCardSkeleton },
    props: {
      filters: { type: Array, default: () => [] },
      actions: { type: Array, default: () => [] },
      buttons: { type: Object, default: () => ({}) },
      icons: { type: Object, default: () => ({}) },
      followEnabled: { type: Boolean, default: false },
      friendshipEnabled: { type: Boolean, default: false }
    },
    computed: {
      listUrl() {
        return vue$1.apiUrl("user", { purpose: "directory" });
      },
      labels() {
        return {
          title: vue$1.i18n.t("UserModule.base", "People")
        };
      }
    },
    methods: {
      itemStates(ids) {
        return fetchStates(ids);
      },
      filterByTag(tag) {
        if (!this.$refs.directory.setFilter("tag", [tag], { add: true })) {
          this.$refs.directory.setFilter("q", tag);
        }
      },
      onFollowChange(payload) {
        if (!payload) {
          return;
        }
        const directory = this.$refs.directory;
        directory.replaceState(payload.userId, {
          isFollowing: payload.isFollowing,
          canFollow: payload.canFollow
        });
        const item = directory.items.find((candidate) => candidate.id === payload.userId);
        if (item && payload.followerCount !== null && item.followerCount !== payload.followerCount) {
          directory.replaceItem(item.id, { ...item, followerCount: payload.followerCount });
        }
      },
      onFriendshipChange(payload) {
        var _a, _b;
        if (!payload) {
          return;
        }
        const directory = this.$refs.directory;
        const previous = ((_b = (_a = directory.states[payload.userId]) == null ? void 0 : _a.friendship) == null ? void 0 : _b.state) ?? null;
        directory.replaceState(payload.userId, {
          friendship: { state: payload.state, isFollowing: payload.isFollowing }
        });
        const wasFriend = previous === STATE_FRIENDS;
        const isFriend = payload.state === STATE_FRIENDS;
        const item = directory.items.find((candidate) => candidate.id === payload.userId);
        if (item && wasFriend !== isFriend && item.friendCount !== null && item.friendCount !== void 0) {
          directory.replaceItem(item.id, { ...item, friendCount: Math.max(0, item.friendCount + (isFriend ? 1 : -1)) });
        }
      }
    }
  };
  const _hoisted_1$2 = { class: "c-people-directory" };
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_PeopleCard = vue.resolveComponent("PeopleCard");
    const _component_PeopleCardSkeleton = vue.resolveComponent("PeopleCardSkeleton");
    const _component_CardDirectory = vue.resolveComponent("CardDirectory");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
      vue.createVNode(_component_CardDirectory, {
        ref: "directory",
        url: $options.listUrl,
        title: $options.labels.title,
        filters: $props.filters,
        actions: $props.actions,
        "item-states": $options.itemStates,
        "page-size": 24,
        "id-prefix": "people-filter"
      }, {
        card: vue.withCtx(({ item, state }) => [
          vue.createVNode(_component_PeopleCard, {
            user: item,
            state,
            buttons: $props.buttons,
            icons: $props.icons,
            "follow-enabled": $props.followEnabled,
            "friendship-enabled": $props.friendshipEnabled,
            onFilterTag: $options.filterByTag,
            onFollowChange: $options.onFollowChange,
            onFriendshipChange: $options.onFriendshipChange
          }, null, 8, ["user", "state", "buttons", "icons", "follow-enabled", "friendship-enabled", "onFilterTag", "onFollowChange", "onFriendshipChange"])
        ]),
        skeleton: vue.withCtx(() => [
          vue.createVNode(_component_PeopleCardSkeleton)
        ]),
        _: 1
        /* STABLE */
      }, 8, ["url", "title", "filters", "actions", "item-states"])
    ]);
  }
  const __vite_glob_0_0 = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const FOLLOW_CHANGED = "user:follow-changed";
  const FRIENDSHIP_CHANGED = "user:friendship-changed";
  const _sfc_main$2 = {
    i18nCategories: ["UserModule.base", "SpaceModule.base"],
    props: {
      userId: { type: Number, required: true },
      // Used in the unfollow confirmation, as escaped markup (see `userNameHtml`).
      userName: { type: String, default: "" },
      // `{isFollowing, followerCount, canFollow}`, inlined by the widget. Fetched when absent.
      initial: { type: Object, default: null },
      followClass: { type: String, default: "btn btn-primary" },
      followingClass: { type: String, default: "btn btn-outline-primary" },
      // Server-rendered icon markup (the icon provider is pluggable, see `Icon`): the check of
      // the "Following" state, and an optional icon of the "Follow" state.
      checkIconHtml: { type: String, default: "" },
      followIconHtml: { type: String, default: "" }
    },
    emits: ["change"],
    data() {
      return {
        loaded: !!this.initial,
        isFollowing: this.initial ? !!this.initial.isFollowing : false,
        // `null` while following is disabled - never turned into a 0.
        followerCount: this.initial ? this.initial.followerCount ?? null : null,
        canFollow: this.initial ? !!this.initial.canFollow : false,
        busy: false,
        hovered: false,
        focused: false
      };
    },
    computed: {
      visible() {
        return this.loaded && (this.canFollow || this.isFollowing);
      },
      label() {
        if (!this.isFollowing) {
          return vue$1.i18n.t("UserModule.base", "Follow");
        }
        return this.hovered || this.focused ? vue$1.i18n.t("UserModule.base", "Unfollow") : vue$1.i18n.t("UserModule.base", "Following");
      },
      payload() {
        return {
          userId: this.userId,
          isFollowing: this.isFollowing,
          followerCount: this.followerCount,
          canFollow: this.canFollow
        };
      },
      userNameHtml() {
        return `<strong>${this.escape(this.userName)}</strong>`;
      }
    },
    created() {
      this.dispatching = false;
      if (!this.loaded) {
        this.load();
      }
    },
    mounted() {
      vue$1.events.on(FOLLOW_CHANGED, this.onFollowChanged);
      vue$1.events.on(FRIENDSHIP_CHANGED, this.onFriendshipChanged);
    },
    beforeUnmount() {
      vue$1.events.off(FOLLOW_CHANGED, this.onFollowChanged);
      vue$1.events.off(FRIENDSHIP_CHANGED, this.onFriendshipChanged);
    },
    methods: {
      load() {
        return fetchFollow(this.userId).then((state) => {
          this.apply(state);
          this.loaded = true;
        }).catch((response) => {
          vue$1.log.error(response, true);
        });
      },
      toggle() {
        if (this.busy) {
          return Promise.resolve();
        }
        if (!this.isFollowing) {
          return this.mutate(() => follow(this.userId));
        }
        return vue$1.modal.confirm({
          body: vue$1.i18n.t(
            "SpaceModule.base",
            "Would you like to unfollow {userName}?",
            { userName: this.userNameHtml }
          )
        }).then((confirmed) => {
          this.resetHover();
          return confirmed ? this.mutate(() => unfollow(this.userId)) : null;
        });
      },
      mutate(request) {
        if (this.busy) {
          return Promise.resolve();
        }
        this.busy = true;
        return request().then((state) => {
          this.busy = false;
          this.resetHover();
          this.apply(state);
          this.dispatching = true;
          try {
            vue$1.events.trigger(FOLLOW_CHANGED, [this.payload]);
          } finally {
            this.dispatching = false;
          }
        }).catch((response) => {
          this.busy = false;
          vue$1.log.error(response, true);
        });
      },
      // Losing the focus also ends the hover: a dropdown entry is hidden with its menu, which
      // takes the focus with it, without a `mouseleave`.
      onBlur() {
        this.focused = false;
        this.hovered = false;
      },
      resetHover() {
        this.hovered = false;
        this.focused = false;
      },
      apply(state) {
        this.isFollowing = !!state.isFollowing;
        this.followerCount = state.followerCount ?? null;
        this.canFollow = !!state.canFollow;
        this.$emit("change", this.payload);
      },
      onFollowChanged(event, payload) {
        if (this.dispatching || !payload || payload.userId !== this.userId) {
          return;
        }
        this.apply(payload);
        this.loaded = true;
      },
      onFriendshipChanged(event, payload) {
        if (!payload || payload.userId !== this.userId || !!payload.isFollowing === this.isFollowing) {
          return;
        }
        this.load();
      },
      escape(value) {
        const element = document.createElement("div");
        element.textContent = String(value);
        return element.innerHTML;
      }
    }
  };
  const _hoisted_1$1 = ["disabled", "aria-pressed", "aria-busy"];
  const _hoisted_2$1 = {
    key: 0,
    class: "spinner-border spinner-border-sm",
    "aria-hidden": "true"
  };
  const _hoisted_3$1 = ["innerHTML"];
  const _hoisted_4$1 = ["innerHTML"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    return $options.visible ? (vue.openBlock(), vue.createElementBlock("button", {
      key: 0,
      type: "button",
      class: vue.normalizeClass($data.isFollowing ? $props.followingClass : $props.followClass),
      disabled: $data.busy,
      "aria-pressed": $data.isFollowing ? "true" : "false",
      "aria-busy": $data.busy ? "true" : null,
      onClick: _cache[0] || (_cache[0] = (...args) => $options.toggle && $options.toggle(...args)),
      onMouseenter: _cache[1] || (_cache[1] = ($event) => $data.hovered = true),
      onMouseleave: _cache[2] || (_cache[2] = ($event) => $data.hovered = false),
      onFocus: _cache[3] || (_cache[3] = ($event) => $data.focused = true),
      onBlur: _cache[4] || (_cache[4] = (...args) => $options.onBlur && $options.onBlur(...args))
    }, [
      $data.busy ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$1)) : $data.isFollowing ? (vue.openBlock(), vue.createElementBlock("span", {
        key: 1,
        innerHTML: $props.checkIconHtml
      }, null, 8, _hoisted_3$1)) : $props.followIconHtml ? (vue.openBlock(), vue.createElementBlock("span", {
        key: 2,
        innerHTML: $props.followIconHtml
      }, null, 8, _hoisted_4$1)) : vue.createCommentVNode("v-if", true),
      vue.createTextVNode(
        vue.toDisplayString($options.label),
        1
        /* TEXT */
      )
    ], 42, _hoisted_1$1)) : vue.createCommentVNode("v-if", true);
  }
  const __vite_glob_0_1 = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = {
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
          let users;
          if (response.results) {
            users = response.results;
            this.total = response.total ?? users.length;
            const currentPage = response.page ?? page;
            const pages = response.pages ?? currentPage;
            this.hasMore = currentPage < pages;
            this.nextPage = this.hasMore ? currentPage + 1 : null;
          } else {
            users = response.users ?? [];
            this.total = response.total ?? users.length;
            this.hasMore = !!response.hasMore;
            this.nextPage = response.nextPage ?? null;
          }
          this.users = page === 1 ? users : this.users.concat(users);
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
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
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
  const __vite_glob_0_3 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = {
    name: "PeopleCardFollowAction",
    components: { UserFollowButton: __vite_glob_0_1 },
    // The whole context is spread onto the component; what it does not use must not become
    // attributes of the button.
    inheritAttrs: false,
    props: {
      user: { type: Object, required: true },
      state: { type: Object, required: true },
      buttons: { type: Object, default: () => ({}) },
      icons: { type: Object, default: () => ({}) },
      followEnabled: { type: Boolean, default: false }
    },
    emits: ["follow-change"],
    computed: {
      initial() {
        return {
          isFollowing: !!this.state.isFollowing,
          followerCount: this.user.followerCount ?? null,
          canFollow: !!this.state.canFollow
        };
      }
    }
  };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_UserFollowButton = vue.resolveComponent("UserFollowButton");
    return $props.followEnabled || $props.state.isFollowing ? (vue.openBlock(), vue.createBlock(_component_UserFollowButton, {
      key: 0,
      class: "c-entity-card__action",
      "user-id": $props.user.id,
      "user-name": $props.user.displayName,
      initial: $options.initial,
      "follow-class": $props.buttons.followClass,
      "following-class": $props.buttons.followingClass,
      "check-icon-html": $props.icons.check || "",
      onChange: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("follow-change", $event))
    }, null, 8, ["user-id", "user-name", "initial", "follow-class", "following-class", "check-icon-html"])) : vue.createCommentVNode("v-if", true);
  }
  const PeopleCardFollowAction = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  Object.entries(/* @__PURE__ */ Object.assign({ "./PeopleDirectory.vue": __vite_glob_0_0, "./UserFollowButton.vue": __vite_glob_0_1, "./UserImage.vue": __vite_glob_0_2, "./UserList.vue": __vite_glob_0_3 })).forEach(([path, component]) => {
    vue$1.register(path.slice("./".length, -".vue".length), component);
  });
  vue$1.register("PeopleCardFollowAction", PeopleCardFollowAction);
  vue$1.registerSlotComponent("user.card-actions", "PeopleCardFollowAction", { id: "follow", sortOrder: 200 });
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.user.vue.js.map
