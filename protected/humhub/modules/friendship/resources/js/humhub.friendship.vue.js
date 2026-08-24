/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from friendship/vue/ via `grunt build-vue --module=friendship`.
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
  const STATE_NONE = "none";
  const STATE_REQUEST_SENT = "requestSent";
  const STATE_REQUEST_RECEIVED = "requestReceived";
  const STATE_FRIENDS = "friends";
  const _sfc_main = {
    i18nCategories: ["FriendshipModule.base", "base"],
    props: {
      userId: { type: Number, required: true },
      // Used in the confirmation dialogs, as trusted markup — see `userNameHtml`.
      userName: { type: String, default: "" },
      // FriendshipSerializer::state(), inlined by the widget. Fetched on mount when absent.
      initial: { type: Object, default: null },
      // Presentation, see the docblock.
      buttonClass: { type: String, default: "btn btn-accent" },
      stateClass: { type: String, default: "btn btn-accent active" },
      togglerClass: { type: String, default: "btn btn-accent active" },
      groupClass: { type: String, default: "btn-group" },
      // Server-rendered icon markup (the icon provider is pluggable, see `Icon`).
      checkIconHtml: { type: String, default: "" },
      plusIconHtml: { type: String, default: "" },
      clockIconHtml: { type: String, default: "" },
      timesIconHtml: { type: String, default: "" }
    },
    data() {
      return {
        state: this.initial ? this.initial.state : null,
        isFollowing: this.initial ? !!this.initial.isFollowing : false,
        busy: false
      };
    },
    computed: {
      endpoint() {
        return vue$1.apiUrl(`user/${this.userId}/friendship`);
      },
      isNone() {
        return this.state === STATE_NONE;
      },
      isRequestSent() {
        return this.state === STATE_REQUEST_SENT;
      },
      isRequestReceived() {
        return this.state === STATE_REQUEST_RECEIVED;
      },
      isFriends() {
        return this.state === STATE_FRIENDS;
      },
      // The display name as the legacy confirmation messages carried it: bold, inside an
      // otherwise translator-authored sentence. `i18n.t()` leaves markup in the message
      // alone (`ignoreTag`), and `modal.confirm()` renders the result as HTML — hence the
      // encoding here, on the one value that is not developer-controlled.
      userNameHtml() {
        return `<strong>${this.escape(this.userName)}</strong>`;
      },
      friendsLabel() {
        return vue$1.i18n.t("FriendshipModule.base", "Friends");
      },
      acceptLabel() {
        return vue$1.i18n.t("FriendshipModule.base", "Accept Friend Request");
      },
      denyLabel() {
        return vue$1.i18n.t("FriendshipModule.base", "Deny friend request");
      },
      pendingLabel() {
        return vue$1.i18n.t("FriendshipModule.base", "Pending");
      },
      toggleDropdownLabel() {
        return vue$1.i18n.t("base", "Toggle Dropdown");
      }
    },
    created() {
      if (this.state === null) {
        this.load();
      }
    },
    methods: {
      load() {
        vue$1.client.get(this.endpoint).then((response) => {
          this.apply(response);
        }).catch((response) => {
          vue$1.log.error(response, true);
          this.apply({ state: STATE_NONE });
        });
      },
      request() {
        return this.confirmThen(
          vue$1.i18n.t(
            "FriendshipModule.base",
            "Would you like to send a friendship request to {userName}?",
            { userName: this.userNameHtml }
          ),
          () => this.affirm()
        );
      },
      accept() {
        return this.confirmThen(
          vue$1.i18n.t("FriendshipModule.base", "Would you like to accept the friendship request?"),
          () => this.affirm()
        );
      },
      deny() {
        return this.confirmThen(
          vue$1.i18n.t("FriendshipModule.base", "Would you like to withdraw the friendship request?"),
          () => this.remove()
        );
      },
      withdraw() {
        return this.confirmThen(
          vue$1.i18n.t("FriendshipModule.base", "Would you like to withdraw your friendship request?"),
          () => this.remove()
        );
      },
      end() {
        return this.confirmThen(
          vue$1.i18n.t(
            "FriendshipModule.base",
            "Would you like to end your friendship with {userName}?",
            { userName: this.userNameHtml }
          ),
          () => this.remove()
        );
      },
      confirmThen(body, action) {
        if (this.busy) {
          return Promise.resolve();
        }
        return vue$1.modal.confirm({ body }).then((confirmed) => confirmed ? action() : null);
      },
      /**
       * POST: sends the request, or accepts the one this user sent — the server decides
       * which from the current state.
       */
      affirm() {
        return this.mutate(() => vue$1.client.post(this.endpoint));
      },
      /**
       * DELETE: withdraws, denies or ends.
       */
      remove() {
        return this.mutate(() => vue$1.client.del(this.endpoint));
      },
      /**
       * Every transition answers the new state, so there is nothing to derive here: apply
       * it, then align what depends on it.
       */
      mutate(request) {
        if (this.busy) {
          return Promise.resolve();
        }
        this.busy = true;
        return request().then((response) => {
          this.busy = false;
          this.apply(response);
        }).catch((response) => {
          this.busy = false;
          vue$1.log.error(response, true);
        });
      },
      apply(state) {
        this.state = state.state;
        this.isFollowing = !!state.isFollowing;
        this.syncFollowButtons();
      },
      /**
       * The server-rendered follow/unfollow pair of this user: exactly one of them is
       * shown. Absent (profile header, guests) means nothing to do.
       */
      syncFollowButtons() {
        const selector = `[data-content-container-id="${this.userId}"]`;
        this.toggle(document.querySelectorAll(`${selector}.followButton`), !this.isFollowing);
        this.toggle(document.querySelectorAll(`${selector}.unfollowButton`), this.isFollowing);
      },
      toggle(elements, visible) {
        elements.forEach((element) => {
          element.classList.toggle("d-none", !visible);
        });
      },
      escape(value) {
        const element = document.createElement("div");
        element.textContent = String(value);
        return element.innerHTML;
      }
    }
  };
  const _hoisted_1 = ["innerHTML"];
  const _hoisted_2 = ["innerHTML"];
  const _hoisted_3 = { class: "sr-only" };
  const _hoisted_4 = { class: "dropdown-menu" };
  const _hoisted_5 = ["innerHTML"];
  const _hoisted_6 = ["innerHTML"];
  const _hoisted_7 = ["innerHTML"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    return $options.isNone ? (vue.openBlock(), vue.createElementBlock(
      "a",
      {
        key: 0,
        href: "#",
        class: vue.normalizeClass([$props.buttonClass, { disabled: $data.busy }]),
        onClick: _cache[0] || (_cache[0] = vue.withModifiers((...args) => $options.request && $options.request(...args), ["prevent"]))
      },
      [
        vue.createElementVNode("span", { innerHTML: $props.plusIconHtml }, null, 8, _hoisted_1),
        vue.createTextVNode(
          vue.toDisplayString($options.friendsLabel),
          1
          /* TEXT */
        )
      ],
      2
      /* CLASS */
    )) : $options.isRequestReceived ? (vue.openBlock(), vue.createElementBlock(
      "div",
      {
        key: 1,
        class: vue.normalizeClass($props.groupClass)
      },
      [
        vue.createElementVNode(
          "a",
          {
            href: "#",
            class: vue.normalizeClass([$props.stateClass, { disabled: $data.busy }]),
            onClick: _cache[1] || (_cache[1] = vue.withModifiers((...args) => $options.accept && $options.accept(...args), ["prevent"]))
          },
          [
            vue.createElementVNode("span", { innerHTML: $props.clockIconHtml }, null, 8, _hoisted_2),
            vue.createTextVNode(
              vue.toDisplayString($options.acceptLabel),
              1
              /* TEXT */
            )
          ],
          2
          /* CLASS */
        ),
        vue.createElementVNode(
          "button",
          {
            type: "button",
            class: vue.normalizeClass(["dropdown-toggle", $props.togglerClass]),
            "data-bs-toggle": "dropdown",
            "aria-haspopup": "true",
            "aria-expanded": "false"
          },
          [
            vue.createElementVNode(
              "span",
              _hoisted_3,
              vue.toDisplayString($options.toggleDropdownLabel),
              1
              /* TEXT */
            )
          ],
          2
          /* CLASS */
        ),
        vue.createElementVNode("ul", _hoisted_4, [
          vue.createElementVNode("li", null, [
            vue.createElementVNode("a", {
              href: "#",
              class: "dropdown-item",
              onClick: _cache[2] || (_cache[2] = vue.withModifiers((...args) => $options.deny && $options.deny(...args), ["prevent"]))
            }, [
              vue.createElementVNode("span", { innerHTML: $props.timesIconHtml }, null, 8, _hoisted_5),
              vue.createTextVNode(
                "  " + vue.toDisplayString($options.denyLabel),
                1
                /* TEXT */
              )
            ])
          ])
        ])
      ],
      2
      /* CLASS */
    )) : $options.isRequestSent ? (vue.openBlock(), vue.createElementBlock(
      "a",
      {
        key: 2,
        href: "#",
        class: vue.normalizeClass([$props.stateClass, { disabled: $data.busy }]),
        onClick: _cache[3] || (_cache[3] = vue.withModifiers((...args) => $options.withdraw && $options.withdraw(...args), ["prevent"]))
      },
      [
        vue.createElementVNode("span", { innerHTML: $props.clockIconHtml }, null, 8, _hoisted_6),
        vue.createTextVNode(
          vue.toDisplayString($options.pendingLabel),
          1
          /* TEXT */
        )
      ],
      2
      /* CLASS */
    )) : $options.isFriends ? (vue.openBlock(), vue.createElementBlock(
      "a",
      {
        key: 3,
        href: "#",
        class: vue.normalizeClass([$props.stateClass, { disabled: $data.busy }]),
        onClick: _cache[4] || (_cache[4] = vue.withModifiers((...args) => $options.end && $options.end(...args), ["prevent"]))
      },
      [
        vue.createElementVNode("span", { innerHTML: $props.checkIconHtml }, null, 8, _hoisted_7),
        vue.createTextVNode(
          vue.toDisplayString($options.friendsLabel),
          1
          /* TEXT */
        )
      ],
      2
      /* CLASS */
    )) : vue.createCommentVNode("v-if", true);
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("FriendshipButton", C0);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.friendship.vue.js.map
