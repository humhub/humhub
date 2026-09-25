/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from space/vue/ via `grunt build-vue --module=space`.
 * See docs/develop/ui-js-vuejs.md
 */
(function(vue, vue$1) {
  "use strict";
  const fetchSpaces = ({ q = null, scope = null, purpose = null, page = null, pageSize = null } = {}) => {
    const params = {};
    if (q) {
      params.q = q;
    }
    if (scope) {
      params.scope = scope;
    }
    if (purpose) {
      params.purpose = purpose;
    }
    if (page) {
      params.page = page;
    }
    if (pageSize) {
      params.pageSize = pageSize;
    }
    return vue.client.get(vue.apiUrl("space", params)).then(normalizePage);
  };
  const fetchStates = (ids) => {
    if (!ids.length) {
      return Promise.resolve({});
    }
    return vue.client.get(vue.apiUrl("space/states", { ids })).then((response) => response.results || {});
  };
  const fetchFollow = (spaceId) => vue.client.get(followUrl(spaceId)).then(normalizeFollow);
  const follow = (spaceId) => vue.client.put(followUrl(spaceId)).then(normalizeFollow);
  const unfollow = (spaceId) => vue.client.del(followUrl(spaceId)).then(normalizeFollow);
  const followUrl = (spaceId) => vue.apiUrl(`space/${spaceId}/follow`);
  const normalizeFollow = (response) => ({
    isFollowing: !!response.isFollowing,
    followerCount: response.followerCount ?? null,
    canFollow: !!response.canFollow
  });
  const normalizePage = (response) => ({
    results: Array.isArray(response.results) ? response.results : [],
    total: response.total || 0,
    page: response.page || 1,
    pageSize: response.pageSize || 0,
    pages: response.pages || 0
  });
  const _export_sfc = (sfc, props) => {
    const target = sfc.__vccOpts || sfc;
    for (const [key, val] of props) {
      target[key] = val;
    }
    return target;
  };
  const FOLLOW_CHANGED = "space:follow-changed";
  const MEMBERSHIP_CHANGED$2 = "space:membership-changed";
  const STATE_MEMBER$1 = "member";
  const _sfc_main$8 = {
    i18nCategories: ["SpaceModule.base"],
    props: {
      spaceId: { type: Number, required: true },
      // Used in the unfollow confirmation, as escaped markup (see `spaceNameHtml`).
      spaceName: { type: String, default: "" },
      // `{isFollowing, followerCount, canFollow}`, inlined by the widget. Fetched when absent.
      initial: { type: Object, default: null },
      // Whether the viewer is a member of the space (members cannot follow).
      isMember: { type: Boolean, default: false },
      followClass: { type: String, default: "btn btn-secondary" },
      followingClass: { type: String, default: "btn btn-secondary active" },
      // Server-rendered icon markup (the icon provider is pluggable, see `Icon`).
      checkIconHtml: { type: String, default: "" }
    },
    emits: ["change"],
    data() {
      return {
        loaded: !!this.initial,
        isFollowing: this.initial ? !!this.initial.isFollowing : false,
        // `null` where the space does not show its followers - never turned into a 0.
        followerCount: this.initial ? this.initial.followerCount ?? null : null,
        canFollow: this.initial ? !!this.initial.canFollow : false,
        member: this.isMember,
        busy: false,
        hovered: false,
        focused: false
      };
    },
    computed: {
      visible() {
        return this.loaded && !this.member && (this.canFollow || this.isFollowing);
      },
      label() {
        if (!this.isFollowing) {
          return vue.i18n.t("SpaceModule.base", "Follow");
        }
        return this.hovered || this.focused ? vue.i18n.t("SpaceModule.base", "Unfollow") : vue.i18n.t("SpaceModule.base", "Following");
      },
      payload() {
        return {
          spaceId: this.spaceId,
          isFollowing: this.isFollowing,
          followerCount: this.followerCount,
          canFollow: this.canFollow
        };
      },
      spaceNameHtml() {
        return `<strong>${this.escape(this.spaceName)}</strong>`;
      }
    },
    watch: {
      isMember(value) {
        this.member = value;
      }
    },
    created() {
      this.dispatching = false;
      if (!this.loaded) {
        this.load();
      }
    },
    mounted() {
      vue.events.on(FOLLOW_CHANGED, this.onFollowChanged);
      vue.events.on(MEMBERSHIP_CHANGED$2, this.onMembershipChanged);
    },
    beforeUnmount() {
      vue.events.off(FOLLOW_CHANGED, this.onFollowChanged);
      vue.events.off(MEMBERSHIP_CHANGED$2, this.onMembershipChanged);
    },
    methods: {
      load() {
        return fetchFollow(this.spaceId).then((state) => {
          this.apply(state);
          this.loaded = true;
        }).catch((response) => {
          vue.log.error(response, true);
        });
      },
      toggle() {
        if (this.busy) {
          return Promise.resolve();
        }
        if (!this.isFollowing) {
          return this.mutate(() => follow(this.spaceId));
        }
        return vue.modal.confirm({
          body: vue.i18n.t(
            "SpaceModule.base",
            "Would you like to unfollow Space {spaceName}?",
            { spaceName: this.spaceNameHtml }
          )
        }).then((confirmed) => confirmed ? this.mutate(() => unfollow(this.spaceId)) : null);
      },
      mutate(request) {
        if (this.busy) {
          return Promise.resolve();
        }
        this.busy = true;
        return request().then((state) => {
          this.busy = false;
          this.hovered = false;
          this.focused = false;
          this.apply(state);
          this.dispatching = true;
          try {
            vue.events.trigger(FOLLOW_CHANGED, [this.payload]);
          } finally {
            this.dispatching = false;
          }
        }).catch((response) => {
          this.busy = false;
          vue.log.error(response, true);
        });
      },
      apply(state) {
        this.isFollowing = !!state.isFollowing;
        this.followerCount = state.followerCount ?? null;
        this.canFollow = !!state.canFollow;
        this.$emit("change", this.payload);
      },
      onFollowChanged(event, payload) {
        if (this.dispatching || !payload || payload.spaceId !== this.spaceId) {
          return;
        }
        this.apply(payload);
        this.loaded = true;
      },
      onMembershipChanged(event, payload) {
        if (!payload || payload.spaceId !== this.spaceId) {
          return;
        }
        if (payload.state === STATE_MEMBER$1) {
          this.member = true;
          return;
        }
        if (this.member) {
          this.member = false;
          this.load();
        }
      },
      escape(value) {
        const element = document.createElement("div");
        element.textContent = String(value);
        return element.innerHTML;
      }
    }
  };
  const _hoisted_1$8 = ["disabled", "aria-pressed", "aria-busy"];
  const _hoisted_2$6 = {
    key: 0,
    class: "spinner-border spinner-border-sm",
    "aria-hidden": "true"
  };
  const _hoisted_3$5 = ["innerHTML"];
  function _sfc_render$8(_ctx, _cache, $props, $setup, $data, $options) {
    return $options.visible ? (vue$1.openBlock(), vue$1.createElementBlock("button", {
      key: 0,
      type: "button",
      class: vue$1.normalizeClass($data.isFollowing ? $props.followingClass : $props.followClass),
      disabled: $data.busy,
      "aria-pressed": $data.isFollowing ? "true" : "false",
      "aria-busy": $data.busy ? "true" : null,
      onClick: _cache[0] || (_cache[0] = (...args) => $options.toggle && $options.toggle(...args)),
      onMouseenter: _cache[1] || (_cache[1] = ($event) => $data.hovered = true),
      onMouseleave: _cache[2] || (_cache[2] = ($event) => $data.hovered = false),
      onFocus: _cache[3] || (_cache[3] = ($event) => $data.focused = true),
      onBlur: _cache[4] || (_cache[4] = ($event) => $data.focused = false)
    }, [
      $data.busy ? (vue$1.openBlock(), vue$1.createElementBlock("span", _hoisted_2$6)) : $data.isFollowing ? (vue$1.openBlock(), vue$1.createElementBlock("span", {
        key: 1,
        innerHTML: $props.checkIconHtml
      }, null, 8, _hoisted_3$5)) : vue$1.createCommentVNode("v-if", true),
      vue$1.createTextVNode(
        vue$1.toDisplayString($options.label),
        1
        /* TEXT */
      )
    ], 42, _hoisted_1$8)) : vue$1.createCommentVNode("v-if", true);
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["render", _sfc_render$8]]);
  const STATE_NONE = "none";
  const STATE_INVITED = "invited";
  const STATE_APPLICANT = "applicant";
  const STATE_MEMBER = "member";
  const MEMBERSHIP_CHANGED$1 = "space:membership-changed";
  const _sfc_main$7 = {
    // `base` covers the modal's own Cancel/Close labels, `SpaceModule.base` everything else.
    i18nCategories: ["SpaceModule.base", "base"],
    props: {
      spaceId: { type: Number, required: true },
      // Used in the confirmation dialogs ("... membership in Space <name>?"), as trusted
      // markup — see `spaceNameHtml`.
      spaceName: { type: String, default: "" },
      // Where the non-leavable member state links to, i.e. the space itself.
      spaceUrl: { type: String, default: "#" },
      // MembershipSerializer::state(), inlined by the widget. Fetched on mount when absent.
      initial: { type: Object, default: null },
      // Presentation, see the docblock.
      buttonClass: { type: String, default: "btn btn-accent" },
      pendingClass: { type: String, default: "btn btn-accent active" },
      memberClass: { type: String, default: "btn btn-accent active" },
      togglerClass: { type: String, default: "btn btn-accent" },
      groupClass: { type: String, default: "btn-group" },
      showMemberState: { type: Boolean, default: false },
      reloadOnJoin: { type: Boolean, default: false },
      // Server-rendered icon markup (the icon provider is pluggable, see `Icon`).
      checkIconHtml: { type: String, default: "" },
      clockIconHtml: { type: String, default: "" },
      userIconHtml: { type: String, default: "" }
    },
    data() {
      return {
        state: this.initial ? this.initial.state : null,
        canJoin: this.initial ? !!this.initial.canJoin : false,
        needsApproval: this.initial ? !!this.initial.needsApproval : false,
        canLeave: this.initial ? !!this.initial.canLeave : false,
        isOwner: this.initial ? !!this.initial.isOwner : false,
        busy: false,
        showRequest: false,
        requestSent: false,
        message: ""
      };
    },
    computed: {
      endpoint() {
        return vue.apiUrl(`space/${this.spaceId}/membership`);
      },
      isNone() {
        return this.state === STATE_NONE;
      },
      isInvited() {
        return this.state === STATE_INVITED;
      },
      isApplicant() {
        return this.state === STATE_APPLICANT;
      },
      isMember() {
        return this.state === STATE_MEMBER;
      },
      // The space name as the legacy confirmation messages carried it: bold, inside an
      // otherwise translator-authored sentence. `i18n.t()` leaves markup in the message
      // alone (`ignoreTag`), so both halves are rendered as HTML by `modal.confirm()` -
      // hence the encoding here, on the one value that is not developer-controlled.
      spaceNameHtml() {
        return `<strong>${this.escape(this.spaceName)}</strong>`;
      },
      joinLabel() {
        return vue.i18n.t("SpaceModule.base", "Join");
      },
      acceptInviteLabel() {
        return vue.i18n.t("SpaceModule.base", "Accept Invite");
      },
      declineInviteLabel() {
        return vue.i18n.t("SpaceModule.base", "Decline Invite");
      },
      pendingLabel() {
        return vue.i18n.t("SpaceModule.base", "Pending");
      },
      memberLabel() {
        return vue.i18n.t("SpaceModule.base", "Member");
      },
      ownerLabel() {
        return vue.i18n.t("SpaceModule.base", "Owner");
      },
      toggleDropdownLabel() {
        return vue.i18n.t("base", "Toggle Dropdown");
      },
      requestTitle() {
        return vue.i18n.t("SpaceModule.base", "<strong>Request</strong> Membership");
      },
      requestIntroLabel() {
        return vue.i18n.t(
          "SpaceModule.base",
          "Access to this Space is restricted. Please introduce yourself to become a member."
        );
      },
      requestSentLabel() {
        return vue.i18n.t("SpaceModule.base", "Your request was successfully submitted to the space administrators.");
      },
      messageLabel() {
        return vue.i18n.t("SpaceModule.base", "Your Message");
      },
      messagePlaceholder() {
        return vue.i18n.t("SpaceModule.base", "I want to become a member because...");
      },
      sendLabel() {
        return vue.i18n.t("SpaceModule.base", "Send");
      },
      cancelLabel() {
        return vue.i18n.t("base", "Cancel");
      },
      closeLabel() {
        return vue.i18n.t("base", "Close");
      }
    },
    created() {
      if (this.state === null) {
        this.load();
      }
    },
    methods: {
      load() {
        vue.client.get(this.endpoint).then((response) => {
          this.apply(response);
        }).catch((response) => {
          vue.log.error(response, true);
          this.apply({ state: STATE_NONE });
        });
      },
      /**
       * Joining a space that approves memberships means introducing yourself first.
       */
      onJoinClick() {
        if (this.needsApproval) {
          this.openRequest();
          return;
        }
        this.affirm();
      },
      openRequest() {
        this.message = "";
        this.requestSent = false;
        this.showRequest = true;
      },
      focusMessage() {
        if (this.$refs.messageField) {
          this.$refs.messageField.focus();
        }
      },
      /**
       * PUT: joins, applies or accepts the invite — which one follows from the state and
       * the space's join policy, and the server decides it (see the API controller).
       */
      affirm(data) {
        return this.mutate(() => vue.client.put(this.endpoint, data ? { data } : void 0));
      },
      /**
       * DELETE: leaves, withdraws the application or declines the invite.
       */
      remove() {
        return this.mutate(() => vue.client.del(this.endpoint));
      },
      withdraw() {
        return this.confirm({
          body: vue.i18n.t(
            "SpaceModule.base",
            "Would you like to withdraw your request to join Space {spaceName}?",
            { spaceName: this.spaceNameHtml }
          )
        }).then((confirmed) => confirmed ? this.remove() : null);
      },
      leave() {
        return this.confirm({
          header: vue.i18n.t("SpaceModule.base", "<strong>Leave</strong> Space"),
          body: vue.i18n.t(
            "SpaceModule.base",
            "Would you like to end your membership in Space {spaceName}?",
            { spaceName: this.spaceNameHtml }
          ),
          confirmText: vue.i18n.t("SpaceModule.base", "Leave")
        }).then((confirmed) => confirmed ? this.remove() : null);
      },
      confirm(options) {
        if (this.busy) {
          return Promise.resolve(false);
        }
        return vue.modal.confirm(options);
      },
      sendRequest() {
        if (this.busy) {
          return Promise.resolve();
        }
        this.$refs.requestForm.clearErrors();
        return this.affirm({ message: this.message }).then(() => {
          if (this.isApplicant) {
            this.requestSent = true;
          }
        });
      },
      /**
       * Every transition answers the new state, so there is nothing to derive here: apply
       * it, then tell whoever depends on it (the follow button, the page on joining).
       */
      mutate(request) {
        if (this.busy) {
          return Promise.resolve();
        }
        this.busy = true;
        const wasMember = this.isMember;
        return request().then((response) => {
          this.busy = false;
          this.apply(response);
          vue.events.trigger(MEMBERSHIP_CHANGED$1, [{ spaceId: this.spaceId, state: this.state }]);
          if (!wasMember && this.isMember && this.reloadOnJoin) {
            this.reloadPage();
          }
        }).catch((response) => {
          this.busy = false;
          const errors = response ? response.errors : null;
          if (response && response.status === 422 && errors && this.$refs.requestForm) {
            this.$refs.requestForm.setErrors({ errors });
            return;
          }
          vue.log.error(response, true);
        });
      },
      /** Own method so a test can watch for it instead of navigating. */
      reloadPage() {
        window.location.reload();
      },
      apply(state) {
        this.state = state.state;
        this.canJoin = !!state.canJoin;
        this.needsApproval = !!state.needsApproval;
        this.canLeave = !!state.canLeave;
        this.isOwner = !!state.isOwner;
      },
      escape(value) {
        const element = document.createElement("div");
        element.textContent = String(value);
        return element.innerHTML;
      }
    }
  };
  const _hoisted_1$7 = { class: "visually-hidden" };
  const _hoisted_2$5 = { class: "dropdown-menu" };
  const _hoisted_3$4 = ["innerHTML"];
  const _hoisted_4$3 = ["innerHTML"];
  const _hoisted_5$3 = ["href"];
  const _hoisted_6$3 = ["innerHTML"];
  const _hoisted_7$3 = ["id", "innerHTML"];
  const _hoisted_8$3 = ["aria-label"];
  const _hoisted_9$2 = {
    key: 0,
    class: "text-center"
  };
  const _hoisted_10$2 = ["disabled"];
  function _sfc_render$7(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_TextareaField = vue$1.resolveComponent("TextareaField");
    const _component_HumHubForm = vue$1.resolveComponent("HumHubForm");
    const _component_UiModal = vue$1.resolveComponent("UiModal");
    return vue$1.openBlock(), vue$1.createElementBlock(
      vue$1.Fragment,
      null,
      [
        $options.isNone && $data.canJoin ? (vue$1.openBlock(), vue$1.createElementBlock(
          "a",
          {
            key: 0,
            href: "#",
            class: vue$1.normalizeClass([$props.buttonClass, { disabled: $data.busy }]),
            onClick: _cache[0] || (_cache[0] = vue$1.withModifiers((...args) => $options.onJoinClick && $options.onJoinClick(...args), ["prevent"]))
          },
          vue$1.toDisplayString($options.joinLabel),
          3
          /* TEXT, CLASS */
        )) : $options.isInvited ? (vue$1.openBlock(), vue$1.createElementBlock(
          "div",
          {
            key: 1,
            class: vue$1.normalizeClass($props.groupClass)
          },
          [
            vue$1.createElementVNode(
              "a",
              {
                href: "#",
                class: vue$1.normalizeClass([$props.buttonClass, { disabled: $data.busy }]),
                onClick: _cache[1] || (_cache[1] = vue$1.withModifiers(($event) => $options.affirm(), ["prevent"]))
              },
              vue$1.toDisplayString($options.acceptInviteLabel),
              3
              /* TEXT, CLASS */
            ),
            vue$1.createElementVNode(
              "button",
              {
                type: "button",
                class: vue$1.normalizeClass(["dropdown-toggle", $props.togglerClass]),
                "data-bs-toggle": "dropdown",
                "aria-haspopup": "true",
                "aria-expanded": "false"
              },
              [
                vue$1.createElementVNode(
                  "span",
                  _hoisted_1$7,
                  vue$1.toDisplayString($options.toggleDropdownLabel),
                  1
                  /* TEXT */
                )
              ],
              2
              /* CLASS */
            ),
            vue$1.createElementVNode("ul", _hoisted_2$5, [
              vue$1.createElementVNode("li", null, [
                vue$1.createElementVNode(
                  "a",
                  {
                    href: "#",
                    class: "dropdown-item",
                    onClick: _cache[2] || (_cache[2] = vue$1.withModifiers(($event) => $options.remove(), ["prevent"]))
                  },
                  vue$1.toDisplayString($options.declineInviteLabel),
                  1
                  /* TEXT */
                )
              ])
            ])
          ],
          2
          /* CLASS */
        )) : $options.isApplicant ? (vue$1.openBlock(), vue$1.createElementBlock(
          "a",
          {
            key: 2,
            href: "#",
            class: vue$1.normalizeClass([$props.pendingClass, { disabled: $data.busy }]),
            onClick: _cache[3] || (_cache[3] = vue$1.withModifiers((...args) => $options.withdraw && $options.withdraw(...args), ["prevent"]))
          },
          [
            vue$1.createElementVNode("span", { innerHTML: $props.clockIconHtml }, null, 8, _hoisted_3$4),
            vue$1.createTextVNode(
              vue$1.toDisplayString($options.pendingLabel),
              1
              /* TEXT */
            )
          ],
          2
          /* CLASS */
        )) : $options.isMember && $props.showMemberState ? (vue$1.openBlock(), vue$1.createElementBlock(
          vue$1.Fragment,
          { key: 3 },
          [
            $data.canLeave ? (vue$1.openBlock(), vue$1.createElementBlock(
              "a",
              {
                key: 0,
                href: "#",
                class: vue$1.normalizeClass([$props.memberClass, { disabled: $data.busy }]),
                onClick: _cache[4] || (_cache[4] = vue$1.withModifiers((...args) => $options.leave && $options.leave(...args), ["prevent"]))
              },
              [
                vue$1.createElementVNode("span", { innerHTML: $props.checkIconHtml }, null, 8, _hoisted_4$3),
                vue$1.createTextVNode(
                  vue$1.toDisplayString($options.memberLabel),
                  1
                  /* TEXT */
                )
              ],
              2
              /* CLASS */
            )) : (vue$1.openBlock(), vue$1.createElementBlock("a", {
              key: 1,
              href: $props.spaceUrl,
              class: vue$1.normalizeClass($props.memberClass)
            }, [
              vue$1.createElementVNode("span", {
                innerHTML: $data.isOwner ? $props.userIconHtml : $props.checkIconHtml
              }, null, 8, _hoisted_6$3),
              vue$1.createTextVNode(
                vue$1.toDisplayString($data.isOwner ? $options.ownerLabel : $options.memberLabel),
                1
                /* TEXT */
              )
            ], 10, _hoisted_5$3))
          ],
          64
          /* STABLE_FRAGMENT */
        )) : vue$1.createCommentVNode("v-if", true),
        vue$1.createVNode(_component_UiModal, {
          show: $data.showRequest,
          "onUpdate:show": _cache[10] || (_cache[10] = ($event) => $data.showRequest = $event),
          onOpened: $options.focusMessage
        }, {
          header: vue$1.withCtx(({ titleId }) => [
            vue$1.createElementVNode("h5", {
              class: "modal-title",
              id: titleId,
              innerHTML: $options.requestTitle
            }, null, 8, _hoisted_7$3),
            vue$1.createElementVNode("button", {
              type: "button",
              class: "btn-close",
              "aria-label": $options.closeLabel,
              onClick: _cache[5] || (_cache[5] = ($event) => $data.showRequest = false)
            }, null, 8, _hoisted_8$3)
          ]),
          footer: vue$1.withCtx(() => [
            $data.requestSent ? (vue$1.openBlock(), vue$1.createElementBlock(
              "button",
              {
                key: 0,
                type: "button",
                class: "btn btn-light",
                onClick: _cache[7] || (_cache[7] = ($event) => $data.showRequest = false)
              },
              vue$1.toDisplayString($options.closeLabel),
              1
              /* TEXT */
            )) : (vue$1.openBlock(), vue$1.createElementBlock(
              vue$1.Fragment,
              { key: 1 },
              [
                vue$1.createElementVNode(
                  "button",
                  {
                    type: "button",
                    class: "btn btn-light",
                    onClick: _cache[8] || (_cache[8] = ($event) => $data.showRequest = false)
                  },
                  vue$1.toDisplayString($options.cancelLabel),
                  1
                  /* TEXT */
                ),
                vue$1.createElementVNode("button", {
                  type: "button",
                  class: "btn btn-primary",
                  disabled: $data.busy,
                  onClick: _cache[9] || (_cache[9] = (...args) => $options.sendRequest && $options.sendRequest(...args))
                }, vue$1.toDisplayString($options.sendLabel), 9, _hoisted_10$2)
              ],
              64
              /* STABLE_FRAGMENT */
            ))
          ]),
          default: vue$1.withCtx(() => [
            $data.requestSent ? (vue$1.openBlock(), vue$1.createElementBlock(
              "div",
              _hoisted_9$2,
              vue$1.toDisplayString($options.requestSentLabel),
              1
              /* TEXT */
            )) : (vue$1.openBlock(), vue$1.createBlock(_component_HumHubForm, {
              key: 1,
              ref: "requestForm",
              "model-name": "RequestMembershipForm",
              busy: $data.busy,
              onSubmit: $options.sendRequest
            }, {
              default: vue$1.withCtx(() => [
                vue$1.createElementVNode(
                  "p",
                  null,
                  vue$1.toDisplayString($options.requestIntroLabel),
                  1
                  /* TEXT */
                ),
                vue$1.createVNode(_component_TextareaField, {
                  ref: "messageField",
                  attribute: "message",
                  label: $options.messageLabel,
                  placeholder: $options.messagePlaceholder,
                  required: "",
                  modelValue: $data.message,
                  "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => $data.message = $event)
                }, null, 8, ["label", "placeholder", "modelValue"])
              ]),
              _: 1
              /* STABLE */
            }, 8, ["busy", "onSubmit"]))
          ]),
          _: 1
          /* STABLE */
        }, 8, ["show", "onOpened"])
      ],
      64
      /* STABLE_FRAGMENT */
    );
  }
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["render", _sfc_render$7]]);
  const DESCRIPTION_LENGTH = 60;
  const _sfc_main$6 = {
    props: {
      // Serialized space (SpaceSerializer::list()).
      space: { type: Object, required: true },
      // 'member', 'following', 'archived' or 'none' - what this space is to the caller.
      relation: { type: String, default: "none" },
      // Items the caller has not seen since their last visit, 0 for none and for a space
      // they are not a member of.
      newItems: { type: Number, default: 0 },
      // Whether the keyboard selection currently rests on this entry.
      selected: { type: Boolean, default: false }
    },
    computed: {
      relationAttribute() {
        return { ["data-space-" + this.relation]: "" };
      },
      badge() {
        if (this.relation === "following") {
          return {
            // The solid star Font Awesome drew: Tabler's plain `star` is an outline.
            icon: "ti ti-star-filled",
            title: vue.i18n.t("SpaceModule.chooser", "You are following this space")
          };
        }
        if (this.space.archived) {
          return {
            icon: "ti ti-history",
            title: vue.i18n.t("SpaceModule.chooser", "This space is archived")
          };
        }
        return null;
      },
      newItemsTitle() {
        return vue.i18n.t(
          "SpaceModule.chooser",
          "{n,plural,=1{# new entry} other{# new entries}} since your last visit",
          { n: this.newItems }
        );
      },
      shortDescription() {
        const description = this.space.description || "";
        return description.length > DESCRIPTION_LENGTH ? description.slice(0, DESCRIPTION_LENGTH) + "..." : description;
      }
    }
  };
  const _hoisted_1$6 = ["href", "data-space-guid"];
  const _hoisted_2$4 = { class: "flex-shrink-0 me-2" };
  const _hoisted_3$3 = { class: "flex-grow-1" };
  const _hoisted_4$2 = { class: "space-name" };
  const _hoisted_5$2 = ["title"];
  const _hoisted_6$2 = ["data-message-count", "title"];
  const _hoisted_7$2 = { class: "space-description" };
  const _hoisted_8$2 = {
    key: 2,
    class: "space-tags d-none"
  };
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SpaceImage = vue$1.resolveComponent("SpaceImage");
    return vue$1.openBlock(), vue$1.createElementBlock("a", vue$1.mergeProps({
      href: $props.space.url,
      class: ["dropdown-item d-flex", { selected: $props.selected }],
      "data-space-chooser-item": "",
      "data-space-guid": $props.space.guid
    }, $options.relationAttribute), [
      vue$1.createElementVNode("div", _hoisted_2$4, [
        vue$1.createVNode(
          _component_SpaceImage,
          vue$1.mergeProps($props.space, {
            width: 24,
            link: false
          }),
          null,
          16
          /* FULL_PROPS */
        )
      ]),
      vue$1.createElementVNode("div", _hoisted_3$3, [
        vue$1.createElementVNode(
          "strong",
          _hoisted_4$2,
          vue$1.toDisplayString($props.space.name),
          1
          /* TEXT */
        ),
        $options.badge ? (vue$1.openBlock(), vue$1.createElementBlock("i", {
          key: 0,
          class: vue$1.normalizeClass(["fa badge-space float-end type tt", $options.badge.icon]),
          title: $options.badge.title,
          "aria-hidden": "true"
        }, null, 10, _hoisted_5$2)) : vue$1.createCommentVNode("v-if", true),
        $props.newItems > 0 ? (vue$1.openBlock(), vue$1.createElementBlock("div", {
          key: 1,
          "data-message-count": $props.newItems,
          class: "badge badge-space messageCount float-end tt",
          title: $options.newItemsTitle
        }, vue$1.toDisplayString($props.newItems), 9, _hoisted_6$2)) : vue$1.createCommentVNode("v-if", true),
        _cache[0] || (_cache[0] = vue$1.createElementVNode(
          "br",
          null,
          null,
          -1
          /* CACHED */
        )),
        vue$1.createElementVNode(
          "p",
          _hoisted_7$2,
          vue$1.toDisplayString($options.shortDescription),
          1
          /* TEXT */
        ),
        $props.space.tags && $props.space.tags.length ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_8$2, [
          (vue$1.openBlock(true), vue$1.createElementBlock(
            vue$1.Fragment,
            null,
            vue$1.renderList($props.space.tags, (tag) => {
              return vue$1.openBlock(), vue$1.createElementBlock(
                "span",
                {
                  key: tag,
                  class: "badge badge-light"
                },
                vue$1.toDisplayString(tag),
                1
                /* TEXT */
              );
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ])) : vue$1.createCommentVNode("v-if", true)
      ])
    ], 16, _hoisted_1$6);
  }
  const SpaceChooserItem = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  const LIVE_NEW_CONTENT = "humhub:modules:content:live:NewContent";
  const RELATION_EVENTS = [
    "space:follow-changed",
    "space:membership-changed",
    "humhub:space:archived",
    "humhub:space:unarchived"
  ];
  const SEARCH_DEBOUNCE_MS = 300;
  const MIN_KEYWORD_LENGTH = 2;
  const _sfc_main$5 = {
    components: { SpaceChooserItem },
    i18nCategories: ["SpaceModule.chooser", "base"],
    props: {
      // Entries per page.
      pageSize: { type: Number, default: 25 },
      // Where "Create Space" opens, empty when the caller may not create one.
      createSpaceUrl: { type: String, default: "" },
      // Where the directory link points, empty when the caller may not access it.
      directoryUrl: { type: String, default: "" },
      // Rendered icons - the icon provider is pluggable, so a client cannot build them.
      directoryIconHtml: { type: String, default: "" },
      resetIconHtml: { type: String, default: "" }
    },
    data() {
      return {
        keyword: "",
        spaces: [],
        states: {},
        page: 1,
        pages: 0,
        loading: false,
        loaded: false,
        selected: -1,
        searchTimer: null,
        observer: null
      };
    },
    computed: {
      hasMore() {
        return this.page < this.pages;
      },
      searchLabel() {
        return vue.i18n.t("SpaceModule.chooser", "Search");
      },
      searchTitle() {
        return vue.i18n.t("SpaceModule.chooser", "Search for spaces");
      },
      createLabel() {
        return vue.i18n.t("SpaceModule.chooser", "Create Space");
      },
      loadingLabel() {
        return vue.i18n.t("base", "Loading...");
      },
      // The strings the legacy chooser used, so no translation is lost with the rewrite.
      emptyLabel() {
        return this.searching ? vue.i18n.t("SpaceModule.chooser", "No Spaces found.") : vue.i18n.t("SpaceModule.chooser", "You are not a member of or following any Spaces.");
      },
      minKeywordLabel() {
        return vue.i18n.t(
          "SpaceModule.chooser",
          "Please enter at least {count} characters to search Spaces.",
          { count: MIN_KEYWORD_LENGTH }
        );
      },
      /**
       * A single character is not searched for: the legacy chooser asked for two before it
       * queried, and the hint that says so is part of the menu.
       */
      searching() {
        return this.keyword.trim().length >= MIN_KEYWORD_LENGTH;
      },
      tooShort() {
        const length = this.keyword.trim().length;
        return length > 0 && length < MIN_KEYWORD_LENGTH;
      }
    },
    watch: {
      keyword() {
        this.selected = -1;
        clearTimeout(this.searchTimer);
        this.searchTimer = setTimeout(() => this.load(1), SEARCH_DEBOUNCE_MS);
      }
    },
    mounted() {
      this.dropdown = this.$refs.root.closest(".dropdown");
      if (this.dropdown) {
        this.dropdown.addEventListener("show.bs.dropdown", this.onShow);
      }
      if (this.menuElement() && this.menuElement().classList.contains("show")) {
        this.onShow();
      }
      vue.events.on(LIVE_NEW_CONTENT, this.onNewContent);
      RELATION_EVENTS.forEach((name) => vue.events.on(name, this.onRelationChanged));
    },
    beforeUnmount() {
      this.disconnectObserver();
      if (this.dropdown) {
        this.dropdown.removeEventListener("show.bs.dropdown", this.onShow);
      }
      vue.events.off(LIVE_NEW_CONTENT, this.onNewContent);
      RELATION_EVENTS.forEach((name) => vue.events.off(name, this.onRelationChanged));
      clearTimeout(this.searchTimer);
    },
    methods: {
      /**
       * The dropdown menu itself, which is the element the island is mounted on (see
       * `space\widgets\Chooser`): Bootstrap marks it `show` while it is open.
       */
      menuElement() {
        return this.$refs.root ? this.$refs.root.closest(".dropdown-menu") : null;
      },
      onShow() {
        if (!this.loaded) {
          this.load(1);
        }
        this.$nextTick(() => this.$refs.search && this.$refs.search.focus({ preventScroll: true }));
      },
      /**
       * Loads a page: the caller's own spaces while the field is empty, every space they may
       * see once they type. A page beyond the first appends, so scrolling extends the list.
       */
      load(page) {
        this.loading = true;
        const keyword = this.searching ? this.keyword.trim() : "";
        fetchSpaces({
          q: keyword || null,
          scope: keyword ? null : "mine",
          purpose: "chooser",
          page,
          pageSize: this.pageSize
        }).then((result) => {
          if (keyword !== this.keyword.trim()) {
            return;
          }
          this.spaces = page > 1 ? [...this.spaces, ...result.results] : result.results;
          this.page = result.page;
          this.pages = result.pages;
          this.loaded = true;
          return this.loadStates(result.results);
        }).catch((error) => {
          vue.log.error(error, true);
        }).finally(() => {
          this.loading = false;
          this.armObserver();
        });
      },
      /**
       * What the caller is to the spaces just loaded — membership, following, and what is new
       * in them. Asked for the spaces displayed, never for "all of mine".
       */
      loadStates(spaces) {
        const ids = spaces.map((space) => space.id);
        if (!ids.length) {
          return Promise.resolve();
        }
        return fetchStates(ids).then((states) => {
          this.states = { ...this.states, ...states };
        });
      },
      /**
       * (Re)observes the sentinel at the end of the list, which is what asks for the next
       * page - the menu's list scrolls (`#space-menu-dropdown .hh-list` is `max-height:
       * 200px; overflow: auto`). Re-arming after every page makes the callback run against
       * the current state, so a page too short to fill the list keeps loading.
       */
      armObserver() {
        if (!window.IntersectionObserver) {
          return;
        }
        this.$nextTick(() => {
          this.disconnectObserver();
          const sentinel = this.$refs.sentinel;
          if (!sentinel) {
            return;
          }
          this.observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting) && !this.loading) {
              this.load(this.page + 1);
            }
          }, { root: this.$refs.list, rootMargin: "1px" });
          this.observer.observe(sentinel);
        });
      },
      disconnectObserver() {
        if (this.observer) {
          this.observer.disconnect();
          this.observer = null;
        }
      },
      relationOf(space) {
        const state = this.states[space.id];
        if (state && state.isMember) {
          return "member";
        }
        if (state && state.isFollowing) {
          return "following";
        }
        return space.archived ? "archived" : "none";
      },
      newItemsOf(space) {
        const state = this.states[space.id];
        return state ? state.newItems : 0;
      },
      /**
       * Counts what arrives while the menu is open, the way the legacy chooser did: content
       * of the caller's own making, silent content and profile content do not count.
       */
      onNewContent(event, liveEvents) {
        (liveEvents || []).forEach((liveEvent) => {
          const data = liveEvent.data || {};
          if (data.uguid || data.silent || data.originator === this.currentUserGuid()) {
            return;
          }
          const space = this.spaces.find((candidate) => candidate.guid === data.sguid);
          const state = space ? this.states[space.id] : null;
          if (state && state.isMember) {
            this.states = {
              ...this.states,
              [space.id]: { ...state, newItems: state.newItems + 1 }
            };
          }
        });
      },
      /**
       * Following, unfollowing, joining, leaving and archiving change what the list should show. Rather than
       * patching entries in place, the list is marked stale and re-read the next time the
       * menu opens — it is closed while any of this happens, and the server decides the
       * order anyway.
       */
      onRelationChanged() {
        this.loaded = false;
        if (this.dropdown && this.dropdown.classList.contains("show")) {
          this.load(1);
        }
      },
      currentUserGuid() {
        return window.humhub && window.humhub.modules && window.humhub.modules.user && typeof window.humhub.modules.user.guid === "function" ? window.humhub.modules.user.guid() : null;
      },
      move(offset) {
        if (!this.spaces.length) {
          return;
        }
        const next = this.selected + offset;
        this.selected = Math.max(0, Math.min(this.spaces.length - 1, next));
        this.$nextTick(() => {
          const item = this.$refs.list.querySelectorAll("[data-space-chooser-item]")[this.selected];
          if (item && item.scrollIntoView) {
            item.scrollIntoView({ block: "nearest" });
          }
        });
      },
      open() {
        const space = this.spaces[this.selected] || this.spaces[0];
        if (space) {
          window.location.href = space.url;
        }
      },
      reset() {
        this.keyword = "";
        this.selected = -1;
      }
    }
  };
  const _hoisted_1$5 = { ref: "root" };
  const _hoisted_2$3 = ["placeholder", "title"];
  const _hoisted_3$2 = {
    key: 0,
    id: "space-directory-link",
    class: "input-group-text"
  };
  const _hoisted_4$1 = ["href", "innerHTML"];
  const _hoisted_5$1 = ["innerHTML"];
  const _hoisted_6$1 = {
    id: "space-menu-spaces",
    ref: "list",
    class: "hh-list"
  };
  const _hoisted_7$1 = {
    key: 0,
    class: "text-center p-2"
  };
  const _hoisted_8$1 = {
    class: "visually-hidden",
    role: "status"
  };
  const _hoisted_9$1 = {
    key: 1,
    class: "dropdown-item disabled"
  };
  const _hoisted_10$1 = {
    key: 2,
    class: "dropdown-item disabled"
  };
  const _hoisted_11$1 = {
    key: 3,
    ref: "sentinel",
    class: "stream-end"
  };
  const _hoisted_12$1 = {
    key: 0,
    class: "dropdown-footer"
  };
  const _hoisted_13$1 = ["data-action-url"];
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SpaceChooserItem = vue$1.resolveComponent("SpaceChooserItem");
    const _directive_additions = vue$1.resolveDirective("additions");
    return vue$1.openBlock(), vue$1.createElementBlock(
      "div",
      _hoisted_1$5,
      [
        vue$1.createElementVNode(
          "form",
          {
            class: "dropdown-header dropdown-controls",
            onSubmit: _cache[6] || (_cache[6] = vue$1.withModifiers(() => {
            }, ["prevent"]))
          },
          [
            vue$1.createElementVNode(
              "div",
              {
                class: vue$1.normalizeClass({ "input-group": !!$props.directoryUrl })
              },
              [
                vue$1.withDirectives(vue$1.createElementVNode("input", {
                  id: "space-menu-search",
                  ref: "search",
                  "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.keyword = $event),
                  type: "text",
                  class: "form-control",
                  autocomplete: "off",
                  placeholder: $options.searchLabel,
                  title: $options.searchTitle,
                  onKeydown: [
                    _cache[1] || (_cache[1] = vue$1.withKeys(vue$1.withModifiers(($event) => $options.move(1), ["prevent"]), ["down"])),
                    _cache[2] || (_cache[2] = vue$1.withKeys(vue$1.withModifiers(($event) => $options.move(-1), ["prevent"]), ["up"])),
                    _cache[3] || (_cache[3] = vue$1.withKeys(vue$1.withModifiers((...args) => $options.open && $options.open(...args), ["prevent"]), ["enter"])),
                    _cache[4] || (_cache[4] = vue$1.withKeys(vue$1.withModifiers((...args) => $options.reset && $options.reset(...args), ["prevent"]), ["esc"]))
                  ]
                }, null, 40, _hoisted_2$3), [
                  [vue$1.vModelText, $data.keyword]
                ]),
                $props.directoryUrl ? (vue$1.openBlock(), vue$1.createElementBlock("span", _hoisted_3$2, [
                  vue$1.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- server-rendered icon, see docblock "),
                  vue$1.createElementVNode("a", {
                    href: $props.directoryUrl,
                    innerHTML: $props.directoryIconHtml
                  }, null, 8, _hoisted_4$1)
                ])) : vue$1.createCommentVNode("v-if", true),
                vue$1.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- server-rendered icon, see docblock "),
                vue$1.withDirectives(vue$1.createElementVNode("div", {
                  id: "space-search-reset",
                  class: "search-reset",
                  onClick: _cache[5] || (_cache[5] = (...args) => $options.reset && $options.reset(...args)),
                  innerHTML: $props.resetIconHtml
                }, null, 8, _hoisted_5$1), [
                  [vue$1.vShow, $data.keyword]
                ])
              ],
              2
              /* CLASS */
            )
          ],
          32
          /* NEED_HYDRATION */
        ),
        _cache[8] || (_cache[8] = vue$1.createElementVNode(
          "hr",
          { class: "dropdown-divider" },
          null,
          -1
          /* CACHED */
        )),
        vue$1.withDirectives((vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_6$1, [
          (vue$1.openBlock(true), vue$1.createElementBlock(
            vue$1.Fragment,
            null,
            vue$1.renderList($data.spaces, (space, index) => {
              return vue$1.openBlock(), vue$1.createBlock(_component_SpaceChooserItem, {
                key: space.id,
                space,
                relation: $options.relationOf(space),
                "new-items": $options.newItemsOf(space),
                selected: index === $data.selected
              }, null, 8, ["space", "relation", "new-items", "selected"]);
            }),
            128
            /* KEYED_FRAGMENT */
          )),
          $data.loading ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_7$1, [
            _cache[7] || (_cache[7] = vue$1.createElementVNode(
              "span",
              {
                class: "spinner-border spinner-border-sm",
                "aria-hidden": "true"
              },
              null,
              -1
              /* CACHED */
            )),
            vue$1.createElementVNode(
              "span",
              _hoisted_8$1,
              vue$1.toDisplayString($options.loadingLabel),
              1
              /* TEXT */
            )
          ])) : vue$1.createCommentVNode("v-if", true),
          $options.tooShort ? (vue$1.openBlock(), vue$1.createElementBlock(
            "div",
            _hoisted_9$1,
            vue$1.toDisplayString($options.minKeywordLabel),
            1
            /* TEXT */
          )) : !$data.loading && !$data.spaces.length ? (vue$1.openBlock(), vue$1.createElementBlock(
            "div",
            _hoisted_10$1,
            vue$1.toDisplayString($options.emptyLabel),
            1
            /* TEXT */
          )) : vue$1.createCommentVNode("v-if", true),
          $options.hasMore && !$data.loading ? (vue$1.openBlock(), vue$1.createElementBlock(
            "div",
            _hoisted_11$1,
            null,
            512
            /* NEED_PATCH */
          )) : vue$1.createCommentVNode("v-if", true)
        ])), [
          [_directive_additions]
        ]),
        $props.createSpaceUrl ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_12$1, [
          vue$1.createElementVNode("a", {
            href: "#",
            class: "btn btn-accent col-lg-12",
            "data-action-click": "ui.modal.load",
            "data-action-url": $props.createSpaceUrl
          }, vue$1.toDisplayString($options.createLabel), 9, _hoisted_13$1)
        ])) : vue$1.createCommentVNode("v-if", true)
      ],
      512
      /* NEED_PATCH */
    );
  }
  const C2 = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const SPACE_CHANGED = "humhub:space:changed";
  const READY = "humhub:ready";
  const _sfc_main$4 = {
    i18nCategories: ["SpaceModule.chooser"],
    props: {
      // Rendered image of the space currently shown, empty outside a space.
      initialImageHtml: { type: String, default: "" },
      // Rendered icon of the "My spaces" state.
      noSpaceIconHtml: { type: String, default: "" }
    },
    data() {
      return {
        imageHtml: this.initialImageHtml
      };
    },
    computed: {
      noSpaceLabel() {
        return vue.i18n.t("SpaceModule.chooser", "My spaces");
      }
    },
    mounted() {
      vue.events.on(SPACE_CHANGED, this.onSpaceChanged);
      vue.events.on(READY, this.onReady);
    },
    beforeUnmount() {
      vue.events.off(SPACE_CHANGED, this.onSpaceChanged);
      vue.events.off(READY, this.onReady);
    },
    methods: {
      onSpaceChanged(event, space) {
        this.imageHtml = space && space.image || "";
      },
      /**
       * A pjax navigation that leaves the space section behind: the platform's own `space`
       * module knows whether the page shown is a space page.
       */
      onReady() {
        const space = window.humhub && window.humhub.modules && window.humhub.modules.space;
        if (space && typeof space.isSpacePage === "function" && !space.isSpacePage()) {
          this.imageHtml = "";
        }
      }
    }
  };
  const _hoisted_1$4 = ["innerHTML"];
  const _hoisted_2$2 = {
    key: 1,
    class: "no-space"
  };
  const _hoisted_3$1 = ["innerHTML"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    return vue$1.openBlock(), vue$1.createElementBlock(
      vue$1.Fragment,
      null,
      [
        vue$1.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- server-rendered image or icon, see docblock "),
        $data.imageHtml ? (vue$1.openBlock(), vue$1.createElementBlock("span", {
          key: 0,
          class: "current-space",
          innerHTML: $data.imageHtml
        }, null, 8, _hoisted_1$4)) : (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_2$2, [
          vue$1.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- server-rendered icon, see docblock "),
          vue$1.createElementVNode("span", { innerHTML: $props.noSpaceIconHtml }, null, 8, _hoisted_3$1),
          _cache[0] || (_cache[0] = vue$1.createElementVNode(
            "br",
            null,
            null,
            -1
            /* CACHED */
          )),
          vue$1.createTextVNode(
            " " + vue$1.toDisplayString($options.noSpaceLabel),
            1
            /* TEXT */
          )
        ]))
      ],
      2112
      /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */
    );
  }
  const C3 = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = {
    props: {
      // Serialized space shape (SpaceSerializer::short()).
      id: { type: [Number, String], default: null },
      name: { type: String, default: "" },
      url: { type: String, default: null },
      color: { type: String, default: null },
      imageUrl: { type: String, default: null },
      contentContainerId: { type: [Number, String], default: null },
      // Display options.
      width: { type: Number, default: 50 },
      height: { type: Number, default: null },
      link: { type: Boolean, default: false },
      acronymCount: { type: Number, default: 2 }
    },
    computed: {
      hasImage() {
        return !!this.imageUrl;
      },
      resolvedHeight() {
        return this.height === null ? this.width : this.height;
      },
      sizeStyle() {
        return { width: this.width + "px", height: this.resolvedHeight + "px" };
      },
      acronymStyle() {
        return {
          ...this.sizeStyle,
          // Same fallback the widget uses when a space has no colour of its own.
          backgroundColor: this.color || "var(--background3)",
          borderRadius: this.borderRadius + "px"
        };
      },
      // Mirrors Image::getDynamicStyles()'s width buckets.
      borderRadius() {
        if (this.width < 35) {
          return 2;
        }
        if (this.width < 140 && this.width > 40) {
          return 3;
        }
        return 4;
      },
      acronymIdClass() {
        return this.id === null ? null : "space-profile-acronym-" + this.id;
      },
      imageIdClass() {
        return this.id === null ? null : "space-profile-image-" + this.id;
      },
      acronym() {
        const words = String(this.name || "").replace(/[^\p{L}\d\s]+/gu, "").split(/\s+/).filter((word) => word.length > 0);
        return words.map((word) => word.slice(0, 1)).join("").toUpperCase().slice(0, this.acronymCount);
      }
    }
  };
  const _hoisted_1$3 = ["data-contentcontainer-id"];
  const _hoisted_2$1 = ["src", "alt", "data-contentcontainer-id"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    return vue$1.openBlock(), vue$1.createBlock(vue$1.resolveDynamicComponent($props.link ? "a" : "span"), {
      href: $props.link ? $props.url : void 0
    }, {
      default: vue$1.withCtx(() => [
        vue$1.createElementVNode("div", {
          class: vue$1.normalizeClass(["space-acronym d-inline-flex justify-content-center align-items-center", [$options.acronymIdClass, { "d-none-space-image": $options.hasImage }]]),
          style: vue$1.normalizeStyle($options.acronymStyle),
          "data-contentcontainer-id": $props.contentContainerId
        }, [
          vue$1.createElementVNode(
            "span",
            null,
            vue$1.toDisplayString($options.acronym),
            1
            /* TEXT */
          )
        ], 14, _hoisted_1$3),
        $options.hasImage ? (vue$1.openBlock(), vue$1.createElementBlock("img", {
          key: 0,
          class: vue$1.normalizeClass(["rounded profile-user-photo", $options.imageIdClass]),
          style: vue$1.normalizeStyle($options.sizeStyle),
          src: $props.imageUrl,
          alt: $props.name,
          "data-contentcontainer-id": $props.contentContainerId
        }, null, 14, _hoisted_2$1)) : vue$1.createCommentVNode("v-if", true)
      ]),
      _: 1
      /* STABLE */
    }, 8, ["href"]);
  }
  const C5 = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const MAX_TAGS = 5;
  const _sfc_main$2 = {
    name: "SpaceCard",
    i18nCategories: ["SpaceModule.base"],
    // `ExtensionSlot` is a core component, resolved through the global registry (CoreVueAsset).
    components: { FollowButton: C0, MembershipButton: C1, SpaceImage: C5 },
    props: {
      space: { type: Object, required: true },
      state: { type: Object, default: void 0 },
      buttons: { type: Object, default: () => ({}) },
      icons: { type: Object, default: () => ({}) }
    },
    emits: ["filter-tag", "follow-change"],
    computed: {
      loaded() {
        return this.state !== void 0 && this.state !== null;
      },
      followerCount() {
        return this.loaded && this.state.followerCount !== void 0 ? this.state.followerCount : this.space.followerCount;
      },
      memberCount() {
        return this.loaded && this.state.memberCount !== void 0 ? this.state.memberCount : this.space.memberCount;
      },
      showFollowers() {
        return this.followerCount !== null && this.followerCount !== void 0 && (!this.loaded || this.state.canViewFollowers !== false);
      },
      showMembers() {
        return this.memberCount !== null && this.memberCount !== void 0;
      },
      canOpenFollowers() {
        return this.loaded && !!this.state.canViewFollowers;
      },
      canOpenMembers() {
        return this.loaded && !!this.state.canViewMembers;
      },
      followInitial() {
        return {
          isFollowing: !!this.state.isFollowing,
          followerCount: this.followerCount ?? null,
          canFollow: !!this.state.canFollow
        };
      },
      tags() {
        return (this.space.tags || []).map((tag) => String(tag).trim()).filter((tag) => tag !== "").slice(0, MAX_TAGS);
      },
      coverStyle() {
        return this.space.bannerUrl ? { backgroundImage: `url(${JSON.stringify(this.space.bannerUrl)})` } : {};
      },
      followersLabel() {
        return vue.i18n.t("SpaceModule.base", "{count} Followers", { count: this.followerCount });
      },
      membersLabel() {
        return vue.i18n.t("SpaceModule.base", "{count} Members", { count: this.memberCount });
      },
      labels() {
        return {
          archived: vue.i18n.t("SpaceModule.base", "Archived")
        };
      }
    },
    methods: {
      actionClass(classes) {
        return `${classes || ""} c-space-card__action`.trim();
      },
      filterByLabel(tag) {
        return vue.i18n.t("SpaceModule.base", "Filter by {tag}", { tag });
      },
      openFollowers() {
        vue.modal.load(vue.url("space/space/follower-list", { cguid: this.space.guid }));
      },
      openMembers() {
        vue.modal.load(vue.url("space/membership/members-list", { cguid: this.space.guid }));
      }
    }
  };
  const _hoisted_1$2 = {
    key: 0,
    class: "c-space-card__pill"
  };
  const _hoisted_2 = ["title", "aria-label"];
  const _hoisted_3 = ["title"];
  const _hoisted_4 = { "aria-hidden": "true" };
  const _hoisted_5 = { class: "visually-hidden" };
  const _hoisted_6 = ["title", "aria-label"];
  const _hoisted_7 = ["title"];
  const _hoisted_8 = { "aria-hidden": "true" };
  const _hoisted_9 = { class: "visually-hidden" };
  const _hoisted_10 = { class: "c-space-card__header" };
  const _hoisted_11 = ["href"];
  const _hoisted_12 = { class: "c-space-card__body" };
  const _hoisted_13 = {
    key: 0,
    class: "c-space-card__text"
  };
  const _hoisted_14 = {
    key: 1,
    class: "c-space-card__archived"
  };
  const _hoisted_15 = {
    key: 0,
    class: "c-space-card__tags"
  };
  const _hoisted_16 = ["title", "aria-label", "onClick"];
  const _hoisted_17 = {
    key: 1,
    class: "c-space-card__footer"
  };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SpaceImage = vue$1.resolveComponent("SpaceImage");
    const _component_ExtensionSlot = vue$1.resolveComponent("ExtensionSlot");
    const _component_MembershipButton = vue$1.resolveComponent("MembershipButton");
    const _component_FollowButton = vue$1.resolveComponent("FollowButton");
    return vue$1.openBlock(), vue$1.createElementBlock(
      "article",
      {
        class: vue$1.normalizeClass(["c-space-card", { "is-archived": $props.space.archived }])
      },
      [
        vue$1.createElementVNode(
          "div",
          {
            class: "c-space-card__cover",
            style: vue$1.normalizeStyle($options.coverStyle)
          },
          [
            vue$1.createVNode(_component_SpaceImage, {
              class: "c-space-card__avatar",
              id: $props.space.id,
              name: $props.space.name,
              url: $props.space.url,
              color: $props.space.color,
              "image-url": $props.space.imageUrl,
              "content-container-id": $props.space.contentContainerId,
              width: 80,
              link: "",
              "aria-hidden": "true",
              tabindex: "-1"
            }, null, 8, ["id", "name", "url", "color", "image-url", "content-container-id"]),
            $options.showFollowers || $options.showMembers ? (vue$1.openBlock(), vue$1.createElementBlock("span", _hoisted_1$2, [
              $options.showFollowers ? (vue$1.openBlock(), vue$1.createElementBlock(
                vue$1.Fragment,
                { key: 0 },
                [
                  $options.canOpenFollowers ? (vue$1.openBlock(), vue$1.createElementBlock("button", {
                    key: 0,
                    type: "button",
                    class: "c-space-card__stat c-space-card__stat--followers",
                    title: $options.followersLabel,
                    "aria-label": $options.followersLabel,
                    onClick: _cache[0] || (_cache[0] = (...args) => $options.openFollowers && $options.openFollowers(...args))
                  }, [
                    _cache[3] || (_cache[3] = vue$1.createElementVNode(
                      "i",
                      {
                        class: "ti ti-user-check",
                        "aria-hidden": "true"
                      },
                      null,
                      -1
                      /* CACHED */
                    )),
                    vue$1.createElementVNode(
                      "span",
                      null,
                      vue$1.toDisplayString($options.followerCount),
                      1
                      /* TEXT */
                    )
                  ], 8, _hoisted_2)) : (vue$1.openBlock(), vue$1.createElementBlock("span", {
                    key: 1,
                    class: "c-space-card__stat c-space-card__stat--followers",
                    title: $options.followersLabel
                  }, [
                    _cache[4] || (_cache[4] = vue$1.createElementVNode(
                      "i",
                      {
                        class: "ti ti-user-check",
                        "aria-hidden": "true"
                      },
                      null,
                      -1
                      /* CACHED */
                    )),
                    vue$1.createElementVNode(
                      "span",
                      _hoisted_4,
                      vue$1.toDisplayString($options.followerCount),
                      1
                      /* TEXT */
                    ),
                    vue$1.createElementVNode(
                      "span",
                      _hoisted_5,
                      vue$1.toDisplayString($options.followersLabel),
                      1
                      /* TEXT */
                    )
                  ], 8, _hoisted_3))
                ],
                64
                /* STABLE_FRAGMENT */
              )) : vue$1.createCommentVNode("v-if", true),
              $options.showMembers ? (vue$1.openBlock(), vue$1.createElementBlock(
                vue$1.Fragment,
                { key: 1 },
                [
                  $options.canOpenMembers ? (vue$1.openBlock(), vue$1.createElementBlock("button", {
                    key: 0,
                    type: "button",
                    class: "c-space-card__stat c-space-card__stat--members",
                    title: $options.membersLabel,
                    "aria-label": $options.membersLabel,
                    onClick: _cache[1] || (_cache[1] = (...args) => $options.openMembers && $options.openMembers(...args))
                  }, [
                    _cache[5] || (_cache[5] = vue$1.createElementVNode(
                      "i",
                      {
                        class: "ti ti-users-group",
                        "aria-hidden": "true"
                      },
                      null,
                      -1
                      /* CACHED */
                    )),
                    vue$1.createElementVNode(
                      "span",
                      null,
                      vue$1.toDisplayString($options.memberCount),
                      1
                      /* TEXT */
                    )
                  ], 8, _hoisted_6)) : (vue$1.openBlock(), vue$1.createElementBlock("span", {
                    key: 1,
                    class: "c-space-card__stat c-space-card__stat--members",
                    title: $options.membersLabel
                  }, [
                    _cache[6] || (_cache[6] = vue$1.createElementVNode(
                      "i",
                      {
                        class: "ti ti-users-group",
                        "aria-hidden": "true"
                      },
                      null,
                      -1
                      /* CACHED */
                    )),
                    vue$1.createElementVNode(
                      "span",
                      _hoisted_8,
                      vue$1.toDisplayString($options.memberCount),
                      1
                      /* TEXT */
                    ),
                    vue$1.createElementVNode(
                      "span",
                      _hoisted_9,
                      vue$1.toDisplayString($options.membersLabel),
                      1
                      /* TEXT */
                    )
                  ], 8, _hoisted_7))
                ],
                64
                /* STABLE_FRAGMENT */
              )) : vue$1.createCommentVNode("v-if", true)
            ])) : vue$1.createCommentVNode("v-if", true)
          ],
          4
          /* STYLE */
        ),
        vue$1.createElementVNode("div", _hoisted_10, [
          vue$1.createElementVNode("a", {
            class: "c-space-card__title",
            href: $props.space.url
          }, vue$1.toDisplayString($props.space.name), 9, _hoisted_11),
          vue$1.createVNode(_component_ExtensionSlot, {
            name: "space.card-subtitle",
            context: { space: $props.space }
          }, null, 8, ["context"])
        ]),
        vue$1.createElementVNode("div", _hoisted_12, [
          $props.space.description ? (vue$1.openBlock(), vue$1.createElementBlock(
            "p",
            _hoisted_13,
            vue$1.toDisplayString($props.space.description),
            1
            /* TEXT */
          )) : vue$1.createCommentVNode("v-if", true),
          $props.space.archived ? (vue$1.openBlock(), vue$1.createElementBlock("p", _hoisted_14, [
            _cache[7] || (_cache[7] = vue$1.createElementVNode(
              "i",
              {
                class: "ti ti-archive",
                "aria-hidden": "true"
              },
              null,
              -1
              /* CACHED */
            )),
            vue$1.createElementVNode(
              "span",
              null,
              vue$1.toDisplayString($options.labels.archived),
              1
              /* TEXT */
            )
          ])) : vue$1.createCommentVNode("v-if", true)
        ]),
        $options.tags.length ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_15, [
          (vue$1.openBlock(true), vue$1.createElementBlock(
            vue$1.Fragment,
            null,
            vue$1.renderList($options.tags, (tag) => {
              return vue$1.openBlock(), vue$1.createElementBlock("button", {
                key: tag,
                type: "button",
                class: "c-space-card__tag",
                title: $options.filterByLabel(tag),
                "aria-label": $options.filterByLabel(tag),
                onClick: ($event) => _ctx.$emit("filter-tag", tag)
              }, vue$1.toDisplayString(tag), 9, _hoisted_16);
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ])) : vue$1.createCommentVNode("v-if", true),
        $props.state !== null ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_17, [
          $props.state === void 0 ? (vue$1.openBlock(), vue$1.createElementBlock(
            "button",
            {
              key: 0,
              type: "button",
              class: vue$1.normalizeClass(["c-space-card__action c-space-card__placeholder", $props.buttons.placeholderClass]),
              disabled: "",
              "aria-hidden": "true",
              tabindex: "-1"
            },
            " ",
            2
            /* CLASS */
          )) : (vue$1.openBlock(), vue$1.createElementBlock(
            vue$1.Fragment,
            { key: 1 },
            [
              vue$1.createVNode(_component_MembershipButton, {
                "space-id": $props.space.id,
                "space-name": $props.space.name,
                "space-url": $props.space.url,
                initial: $props.state.membership || null,
                "button-class": $options.actionClass($props.buttons.buttonClass),
                "pending-class": $options.actionClass($props.buttons.pendingClass),
                "member-class": $options.actionClass($props.buttons.memberClass),
                "toggler-class": $props.buttons.togglerClass,
                "group-class": $options.actionClass($props.buttons.groupClass),
                "show-member-state": "",
                "check-icon-html": $props.icons.check || "",
                "clock-icon-html": $props.icons.clock || "",
                "user-icon-html": $props.icons.user || ""
              }, null, 8, ["space-id", "space-name", "space-url", "initial", "button-class", "pending-class", "member-class", "toggler-class", "group-class", "check-icon-html", "clock-icon-html", "user-icon-html"]),
              vue$1.createVNode(_component_FollowButton, {
                class: vue$1.normalizeClass("c-space-card__action"),
                "space-id": $props.space.id,
                "space-name": $props.space.name,
                initial: $options.followInitial,
                "is-member": !!$props.state.isMember,
                "follow-class": $props.buttons.followClass,
                "following-class": $props.buttons.followingClass,
                "check-icon-html": $props.icons.check || "",
                onChange: _cache[2] || (_cache[2] = ($event) => _ctx.$emit("follow-change", $event))
              }, null, 8, ["space-id", "space-name", "initial", "is-member", "follow-class", "following-class", "check-icon-html"])
            ],
            64
            /* STABLE_FRAGMENT */
          ))
        ])) : vue$1.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const SpaceCard = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = {
    name: "SpaceCardSkeleton"
  };
  const _hoisted_1$1 = {
    class: "c-card-skeleton c-space-card-skeleton",
    "aria-hidden": "true"
  };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    return vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_1$1, [..._cache[0] || (_cache[0] = [
      vue$1.createStaticVNode('<div class="c-space-card-skeleton__cover"><span class="c-card-skeleton__block c-space-card-skeleton__avatar"></span></div><div class="c-card-skeleton__header"><span class="c-card-skeleton__block c-card-skeleton__title"></span></div><div class="c-card-skeleton__body"><span class="c-card-skeleton__block c-card-skeleton__line"></span><span class="c-card-skeleton__block c-card-skeleton__line"></span><span class="c-card-skeleton__block c-card-skeleton__line c-card-skeleton__line--short"></span></div><div class="c-space-card-skeleton__tags"><span class="c-card-skeleton__block c-space-card-skeleton__tag"></span><span class="c-card-skeleton__block c-space-card-skeleton__tag"></span><span class="c-card-skeleton__block c-space-card-skeleton__tag"></span></div><div class="c-card-skeleton__footer"><span class="c-card-skeleton__block c-card-skeleton__action"></span></div>', 5)
    ])]);
  }
  const SpaceCardSkeleton = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const MEMBERSHIP_CHANGED = "space:membership-changed";
  const _sfc_main = {
    name: "SpaceDirectory",
    i18nCategories: ["SpaceModule.base", "base"],
    // `CardDirectory` is a core component, resolved through the global registry (CoreVueAsset).
    components: { SpaceCard, SpaceCardSkeleton },
    props: {
      filters: { type: Array, default: () => [] },
      actions: { type: Array, default: () => [] },
      buttons: { type: Object, default: () => ({}) },
      icons: { type: Object, default: () => ({}) }
    },
    computed: {
      listUrl() {
        return vue.apiUrl("space", { purpose: "directory" });
      },
      labels() {
        return {
          title: vue.i18n.t("SpaceModule.base", "Spaces")
        };
      }
    },
    mounted() {
      vue.events.on(MEMBERSHIP_CHANGED, this.onMembershipChanged);
    },
    beforeUnmount() {
      vue.events.off(MEMBERSHIP_CHANGED, this.onMembershipChanged);
    },
    methods: {
      itemStates(ids) {
        return fetchStates(ids);
      },
      filterByTag(tag) {
        this.$refs.directory.setFilter("q", tag);
      },
      onFollowChange(payload) {
        if (!payload) {
          return;
        }
        const directory = this.$refs.directory;
        directory.replaceState(payload.spaceId, {
          isFollowing: payload.isFollowing,
          followerCount: payload.followerCount,
          canFollow: payload.canFollow
        });
        const item = directory.items.find((candidate) => candidate.id === payload.spaceId);
        if (item && item.followerCount !== payload.followerCount) {
          directory.replaceItem(item.id, { ...item, followerCount: payload.followerCount });
        }
      },
      onMembershipChanged(event, payload) {
        const directory = this.$refs.directory;
        if (!payload || !directory || !directory.items.some((item) => item.id === payload.spaceId)) {
          return;
        }
        fetchStates([payload.spaceId]).then((states) => {
          var _a;
          const state = states[payload.spaceId];
          if (state) {
            (_a = this.$refs.directory) == null ? void 0 : _a.replaceState(payload.spaceId, state);
          }
        }).catch((response) => {
          vue.log.error(response);
        });
      }
    }
  };
  const _hoisted_1 = { class: "c-space-directory" };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SpaceCard = vue$1.resolveComponent("SpaceCard");
    const _component_SpaceCardSkeleton = vue$1.resolveComponent("SpaceCardSkeleton");
    const _component_CardDirectory = vue$1.resolveComponent("CardDirectory");
    return vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_1, [
      vue$1.createVNode(_component_CardDirectory, {
        ref: "directory",
        url: $options.listUrl,
        title: $options.labels.title,
        filters: $props.filters,
        actions: $props.actions,
        "item-states": $options.itemStates,
        "page-size": 24,
        "id-prefix": "space-filter"
      }, {
        card: vue$1.withCtx(({ item, state }) => [
          vue$1.createVNode(_component_SpaceCard, {
            space: item,
            state,
            buttons: $props.buttons,
            icons: $props.icons,
            onFilterTag: $options.filterByTag,
            onFollowChange: $options.onFollowChange
          }, null, 8, ["space", "state", "buttons", "icons", "onFilterTag", "onFollowChange"])
        ]),
        skeleton: vue$1.withCtx(() => [
          vue$1.createVNode(_component_SpaceCardSkeleton)
        ]),
        _: 1
        /* STABLE */
      }, 8, ["url", "title", "filters", "actions", "item-states"])
    ]);
  }
  const C4 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue.register("FollowButton", C0);
  vue.register("MembershipButton", C1);
  vue.register("SpaceChooser", C2);
  vue.register("SpaceChooserToggle", C3);
  vue.register("SpaceDirectory", C4);
  vue.register("SpaceImage", C5);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.space.vue.js.map
