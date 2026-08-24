/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from space/vue/ via `grunt build-vue --module=space`.
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
  const STATE_INVITED = "invited";
  const STATE_APPLICANT = "applicant";
  const STATE_MEMBER = "member";
  const _sfc_main$1 = {
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
        isFollowing: this.initial ? !!this.initial.isFollowing : false,
        busy: false,
        showRequest: false,
        requestSent: false,
        message: ""
      };
    },
    computed: {
      endpoint() {
        return vue$1.apiUrl(`space/${this.spaceId}/membership`);
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
        return vue$1.i18n.t("SpaceModule.base", "Join");
      },
      acceptInviteLabel() {
        return vue$1.i18n.t("SpaceModule.base", "Accept Invite");
      },
      declineInviteLabel() {
        return vue$1.i18n.t("SpaceModule.base", "Decline Invite");
      },
      pendingLabel() {
        return vue$1.i18n.t("SpaceModule.base", "Pending");
      },
      memberLabel() {
        return vue$1.i18n.t("SpaceModule.base", "Member");
      },
      ownerLabel() {
        return vue$1.i18n.t("SpaceModule.base", "Owner");
      },
      toggleDropdownLabel() {
        return vue$1.i18n.t("base", "Toggle Dropdown");
      },
      requestTitle() {
        return vue$1.i18n.t("SpaceModule.base", "<strong>Request</strong> Membership");
      },
      requestIntroLabel() {
        return vue$1.i18n.t(
          "SpaceModule.base",
          "Access to this Space is restricted. Please introduce yourself to become a member."
        );
      },
      requestSentLabel() {
        return vue$1.i18n.t("SpaceModule.base", "Your request was successfully submitted to the space administrators.");
      },
      messageLabel() {
        return vue$1.i18n.t("SpaceModule.base", "Your Message");
      },
      messagePlaceholder() {
        return vue$1.i18n.t("SpaceModule.base", "I want to become a member because...");
      },
      sendLabel() {
        return vue$1.i18n.t("SpaceModule.base", "Send");
      },
      cancelLabel() {
        return vue$1.i18n.t("base", "Cancel");
      },
      closeLabel() {
        return vue$1.i18n.t("base", "Close");
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
       * POST: joins, applies or accepts the invite — which one follows from the state and
       * the space's join policy, and the server decides it (see the API controller).
       */
      affirm(data) {
        return this.mutate(() => vue$1.client.post(this.endpoint, data ? { data } : void 0));
      },
      /**
       * DELETE: leaves, withdraws the application or declines the invite.
       */
      remove() {
        return this.mutate(() => vue$1.client.del(this.endpoint));
      },
      withdraw() {
        return this.confirm({
          body: vue$1.i18n.t(
            "SpaceModule.base",
            "Would you like to withdraw your request to join Space {spaceName}?",
            { spaceName: this.spaceNameHtml }
          )
        }).then((confirmed) => confirmed ? this.remove() : null);
      },
      leave() {
        return this.confirm({
          header: vue$1.i18n.t("SpaceModule.base", "<strong>Leave</strong> Space"),
          body: vue$1.i18n.t(
            "SpaceModule.base",
            "Would you like to end your membership in Space {spaceName}?",
            { spaceName: this.spaceNameHtml }
          ),
          confirmText: vue$1.i18n.t("SpaceModule.base", "Leave")
        }).then((confirmed) => confirmed ? this.remove() : null);
      },
      confirm(options) {
        if (this.busy) {
          return Promise.resolve(false);
        }
        return vue$1.modal.confirm(options);
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
       * it, then align what depends on it.
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
          vue$1.log.error(response, true);
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
        this.isFollowing = !!state.isFollowing;
        this.syncFollowButtons();
      },
      /**
       * The server-rendered follow/unfollow pair of this space: hidden for a member,
       * otherwise exactly one of them is shown. Absent (guests, invisible spaces) means
       * nothing to do.
       */
      syncFollowButtons() {
        const selector = `[data-content-container-id="${this.spaceId}"]`;
        const follow = document.querySelectorAll(`${selector}.followButton`);
        const unfollow = document.querySelectorAll(`${selector}.unfollowButton`);
        if (this.state === STATE_MEMBER) {
          this.toggle(follow, false);
          this.toggle(unfollow, false);
          return;
        }
        this.toggle(follow, !this.isFollowing);
        this.toggle(unfollow, this.isFollowing);
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
  const _hoisted_1$1 = { class: "sr-only" };
  const _hoisted_2$1 = { class: "dropdown-menu" };
  const _hoisted_3 = ["innerHTML"];
  const _hoisted_4 = ["innerHTML"];
  const _hoisted_5 = ["href"];
  const _hoisted_6 = ["innerHTML"];
  const _hoisted_7 = ["id", "innerHTML"];
  const _hoisted_8 = ["aria-label"];
  const _hoisted_9 = {
    key: 0,
    class: "text-center"
  };
  const _hoisted_10 = ["disabled"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_TextareaField = vue.resolveComponent("TextareaField");
    const _component_HumHubForm = vue.resolveComponent("HumHubForm");
    const _component_UiModal = vue.resolveComponent("UiModal");
    return vue.openBlock(), vue.createElementBlock(
      vue.Fragment,
      null,
      [
        $options.isNone && $data.canJoin ? (vue.openBlock(), vue.createElementBlock(
          "a",
          {
            key: 0,
            href: "#",
            class: vue.normalizeClass([$props.buttonClass, { disabled: $data.busy }]),
            onClick: _cache[0] || (_cache[0] = vue.withModifiers((...args) => $options.onJoinClick && $options.onJoinClick(...args), ["prevent"]))
          },
          vue.toDisplayString($options.joinLabel),
          3
          /* TEXT, CLASS */
        )) : $options.isInvited ? (vue.openBlock(), vue.createElementBlock(
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
                class: vue.normalizeClass([$props.buttonClass, { disabled: $data.busy }]),
                onClick: _cache[1] || (_cache[1] = vue.withModifiers(($event) => $options.affirm(), ["prevent"]))
              },
              vue.toDisplayString($options.acceptInviteLabel),
              3
              /* TEXT, CLASS */
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
                  _hoisted_1$1,
                  vue.toDisplayString($options.toggleDropdownLabel),
                  1
                  /* TEXT */
                )
              ],
              2
              /* CLASS */
            ),
            vue.createElementVNode("ul", _hoisted_2$1, [
              vue.createElementVNode("li", null, [
                vue.createElementVNode(
                  "a",
                  {
                    href: "#",
                    class: "dropdown-item",
                    onClick: _cache[2] || (_cache[2] = vue.withModifiers(($event) => $options.remove(), ["prevent"]))
                  },
                  vue.toDisplayString($options.declineInviteLabel),
                  1
                  /* TEXT */
                )
              ])
            ])
          ],
          2
          /* CLASS */
        )) : $options.isApplicant ? (vue.openBlock(), vue.createElementBlock(
          "a",
          {
            key: 2,
            href: "#",
            class: vue.normalizeClass([$props.pendingClass, { disabled: $data.busy }]),
            onClick: _cache[3] || (_cache[3] = vue.withModifiers((...args) => $options.withdraw && $options.withdraw(...args), ["prevent"]))
          },
          [
            vue.createElementVNode("span", { innerHTML: $props.clockIconHtml }, null, 8, _hoisted_3),
            vue.createTextVNode(
              vue.toDisplayString($options.pendingLabel),
              1
              /* TEXT */
            )
          ],
          2
          /* CLASS */
        )) : $options.isMember && $props.showMemberState ? (vue.openBlock(), vue.createElementBlock(
          vue.Fragment,
          { key: 3 },
          [
            $data.canLeave ? (vue.openBlock(), vue.createElementBlock(
              "a",
              {
                key: 0,
                href: "#",
                class: vue.normalizeClass([$props.memberClass, { disabled: $data.busy }]),
                onClick: _cache[4] || (_cache[4] = vue.withModifiers((...args) => $options.leave && $options.leave(...args), ["prevent"]))
              },
              [
                vue.createElementVNode("span", { innerHTML: $props.checkIconHtml }, null, 8, _hoisted_4),
                vue.createTextVNode(
                  vue.toDisplayString($options.memberLabel),
                  1
                  /* TEXT */
                )
              ],
              2
              /* CLASS */
            )) : (vue.openBlock(), vue.createElementBlock("a", {
              key: 1,
              href: $props.spaceUrl,
              class: vue.normalizeClass($props.memberClass)
            }, [
              vue.createElementVNode("span", {
                innerHTML: $data.isOwner ? $props.userIconHtml : $props.checkIconHtml
              }, null, 8, _hoisted_6),
              vue.createTextVNode(
                vue.toDisplayString($data.isOwner ? $options.ownerLabel : $options.memberLabel),
                1
                /* TEXT */
              )
            ], 10, _hoisted_5))
          ],
          64
          /* STABLE_FRAGMENT */
        )) : vue.createCommentVNode("v-if", true),
        vue.createVNode(_component_UiModal, {
          show: $data.showRequest,
          "onUpdate:show": _cache[10] || (_cache[10] = ($event) => $data.showRequest = $event),
          onOpened: $options.focusMessage
        }, {
          header: vue.withCtx(({ titleId }) => [
            vue.createElementVNode("h5", {
              class: "modal-title",
              id: titleId,
              innerHTML: $options.requestTitle
            }, null, 8, _hoisted_7),
            vue.createElementVNode("button", {
              type: "button",
              class: "btn-close",
              "aria-label": $options.closeLabel,
              onClick: _cache[5] || (_cache[5] = ($event) => $data.showRequest = false)
            }, null, 8, _hoisted_8)
          ]),
          footer: vue.withCtx(() => [
            $data.requestSent ? (vue.openBlock(), vue.createElementBlock(
              "button",
              {
                key: 0,
                type: "button",
                class: "btn btn-light",
                onClick: _cache[7] || (_cache[7] = ($event) => $data.showRequest = false)
              },
              vue.toDisplayString($options.closeLabel),
              1
              /* TEXT */
            )) : (vue.openBlock(), vue.createElementBlock(
              vue.Fragment,
              { key: 1 },
              [
                vue.createElementVNode(
                  "button",
                  {
                    type: "button",
                    class: "btn btn-light",
                    onClick: _cache[8] || (_cache[8] = ($event) => $data.showRequest = false)
                  },
                  vue.toDisplayString($options.cancelLabel),
                  1
                  /* TEXT */
                ),
                vue.createElementVNode("button", {
                  type: "button",
                  class: "btn btn-primary",
                  disabled: $data.busy,
                  onClick: _cache[9] || (_cache[9] = (...args) => $options.sendRequest && $options.sendRequest(...args))
                }, vue.toDisplayString($options.sendLabel), 9, _hoisted_10)
              ],
              64
              /* STABLE_FRAGMENT */
            ))
          ]),
          default: vue.withCtx(() => [
            $data.requestSent ? (vue.openBlock(), vue.createElementBlock(
              "div",
              _hoisted_9,
              vue.toDisplayString($options.requestSentLabel),
              1
              /* TEXT */
            )) : (vue.openBlock(), vue.createBlock(_component_HumHubForm, {
              key: 1,
              ref: "requestForm",
              "model-name": "RequestMembershipForm",
              busy: $data.busy,
              onSubmit: $options.sendRequest
            }, {
              default: vue.withCtx(() => [
                vue.createElementVNode(
                  "p",
                  null,
                  vue.toDisplayString($options.requestIntroLabel),
                  1
                  /* TEXT */
                ),
                vue.createVNode(_component_TextareaField, {
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
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = {
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
  const _hoisted_1 = ["data-contentcontainer-id"];
  const _hoisted_2 = ["src", "alt", "data-contentcontainer-id"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent($props.link ? "a" : "span"), {
      href: $props.link ? $props.url : void 0
    }, {
      default: vue.withCtx(() => [
        vue.createElementVNode("div", {
          class: vue.normalizeClass(["space-acronym d-inline-flex justify-content-center align-items-center", [$options.acronymIdClass, { "d-none-space-image": $options.hasImage }]]),
          style: vue.normalizeStyle($options.acronymStyle),
          "data-contentcontainer-id": $props.contentContainerId
        }, [
          vue.createElementVNode(
            "span",
            null,
            vue.toDisplayString($options.acronym),
            1
            /* TEXT */
          )
        ], 14, _hoisted_1),
        $options.hasImage ? (vue.openBlock(), vue.createElementBlock("img", {
          key: 0,
          class: vue.normalizeClass(["rounded profile-user-photo", $options.imageIdClass]),
          style: vue.normalizeStyle($options.sizeStyle),
          src: $props.imageUrl,
          alt: $props.name,
          "data-contentcontainer-id": $props.contentContainerId
        }, null, 14, _hoisted_2)) : vue.createCommentVNode("v-if", true)
      ]),
      _: 1
      /* STABLE */
    }, 8, ["href"]);
  }
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("MembershipButton", C0);
  vue$1.register("SpaceImage", C1);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.space.vue.js.map
