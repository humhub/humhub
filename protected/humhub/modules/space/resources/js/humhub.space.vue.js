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
  const _sfc_main$4 = {
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
  const _hoisted_1$4 = { class: "sr-only" };
  const _hoisted_2$4 = { class: "dropdown-menu" };
  const _hoisted_3$3 = ["innerHTML"];
  const _hoisted_4$2 = ["innerHTML"];
  const _hoisted_5$2 = ["href"];
  const _hoisted_6$2 = ["innerHTML"];
  const _hoisted_7$2 = ["id", "innerHTML"];
  const _hoisted_8$2 = ["aria-label"];
  const _hoisted_9$1 = {
    key: 0,
    class: "text-center"
  };
  const _hoisted_10$1 = ["disabled"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
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
                  _hoisted_1$4,
                  vue.toDisplayString($options.toggleDropdownLabel),
                  1
                  /* TEXT */
                )
              ],
              2
              /* CLASS */
            ),
            vue.createElementVNode("ul", _hoisted_2$4, [
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
            vue.createElementVNode("span", { innerHTML: $props.clockIconHtml }, null, 8, _hoisted_3$3),
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
                vue.createElementVNode("span", { innerHTML: $props.checkIconHtml }, null, 8, _hoisted_4$2),
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
              }, null, 8, _hoisted_6$2),
              vue.createTextVNode(
                vue.toDisplayString($data.isOwner ? $options.ownerLabel : $options.memberLabel),
                1
                /* TEXT */
              )
            ], 10, _hoisted_5$2))
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
            }, null, 8, _hoisted_7$2),
            vue.createElementVNode("button", {
              type: "button",
              class: "btn-close",
              "aria-label": $options.closeLabel,
              onClick: _cache[5] || (_cache[5] = ($event) => $data.showRequest = false)
            }, null, 8, _hoisted_8$2)
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
                }, vue.toDisplayString($options.sendLabel), 9, _hoisted_10$1)
              ],
              64
              /* STABLE_FRAGMENT */
            ))
          ]),
          default: vue.withCtx(() => [
            $data.requestSent ? (vue.openBlock(), vue.createElementBlock(
              "div",
              _hoisted_9$1,
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
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const DESCRIPTION_LENGTH = 60;
  const _sfc_main$3 = {
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
            icon: "fa-star",
            title: vue$1.i18n.t("SpaceModule.chooser", "You are following this space")
          };
        }
        if (this.space.archived) {
          return {
            icon: "fa-history",
            title: vue$1.i18n.t("SpaceModule.chooser", "This space is archived")
          };
        }
        return null;
      },
      newItemsTitle() {
        return vue$1.i18n.t(
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
  const _hoisted_1$3 = ["href", "data-space-guid"];
  const _hoisted_2$3 = { class: "flex-shrink-0 me-2" };
  const _hoisted_3$2 = { class: "flex-grow-1" };
  const _hoisted_4$1 = { class: "space-name" };
  const _hoisted_5$1 = ["title"];
  const _hoisted_6$1 = ["data-message-count", "title"];
  const _hoisted_7$1 = { class: "space-description" };
  const _hoisted_8$1 = {
    key: 2,
    class: "space-tags d-none"
  };
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SpaceImage = vue.resolveComponent("SpaceImage");
    return vue.openBlock(), vue.createElementBlock("a", vue.mergeProps({
      href: $props.space.url,
      class: ["dropdown-item d-flex", { selected: $props.selected }],
      "data-space-chooser-item": "",
      "data-space-guid": $props.space.guid
    }, $options.relationAttribute), [
      vue.createElementVNode("div", _hoisted_2$3, [
        vue.createVNode(
          _component_SpaceImage,
          vue.mergeProps($props.space, {
            width: 24,
            link: false
          }),
          null,
          16
          /* FULL_PROPS */
        )
      ]),
      vue.createElementVNode("div", _hoisted_3$2, [
        vue.createElementVNode(
          "strong",
          _hoisted_4$1,
          vue.toDisplayString($props.space.name),
          1
          /* TEXT */
        ),
        $options.badge ? (vue.openBlock(), vue.createElementBlock("i", {
          key: 0,
          class: vue.normalizeClass(["fa badge-space float-end type tt", $options.badge.icon]),
          title: $options.badge.title,
          "aria-hidden": "true"
        }, null, 10, _hoisted_5$1)) : vue.createCommentVNode("v-if", true),
        $props.newItems > 0 ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 1,
          "data-message-count": $props.newItems,
          class: "badge badge-space messageCount float-end tt",
          title: $options.newItemsTitle
        }, vue.toDisplayString($props.newItems), 9, _hoisted_6$1)) : vue.createCommentVNode("v-if", true),
        _cache[0] || (_cache[0] = vue.createElementVNode(
          "br",
          null,
          null,
          -1
          /* CACHED */
        )),
        vue.createElementVNode(
          "p",
          _hoisted_7$1,
          vue.toDisplayString($options.shortDescription),
          1
          /* TEXT */
        ),
        $props.space.tags && $props.space.tags.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_8$1, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($props.space.tags, (tag) => {
              return vue.openBlock(), vue.createElementBlock(
                "span",
                {
                  key: tag,
                  class: "badge badge-light"
                },
                vue.toDisplayString(tag),
                1
                /* TEXT */
              );
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ])) : vue.createCommentVNode("v-if", true)
      ])
    ], 16, _hoisted_1$3);
  }
  const SpaceChooserItem = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const fetchSpaces = ({ q = null, scope = null, page = null, pageSize = null } = {}) => {
    const params = {};
    if (q) {
      params.q = q;
    }
    if (scope) {
      params.scope = scope;
    }
    if (page) {
      params.page = page;
    }
    if (pageSize) {
      params.pageSize = pageSize;
    }
    return vue$1.client.get(vue$1.apiUrl("space", params)).then(normalizePage);
  };
  const fetchStates = (guids) => {
    if (!guids.length) {
      return Promise.resolve({});
    }
    return vue$1.client.get(vue$1.apiUrl("space/states", { guids })).then((response) => response.results || {});
  };
  const normalizePage = (response) => ({
    results: Array.isArray(response.results) ? response.results : [],
    total: response.total || 0,
    page: response.page || 1,
    pageSize: response.pageSize || 0,
    pages: response.pages || 0
  });
  const LIVE_NEW_CONTENT = "humhub:modules:content:live:NewContent";
  const RELATION_EVENTS = [
    "humhub:space:followed",
    "humhub:space:unfollowed",
    "humhub:space:archived",
    "humhub:space:unarchived"
  ];
  const SEARCH_DEBOUNCE_MS = 300;
  const MIN_KEYWORD_LENGTH = 2;
  const _sfc_main$2 = {
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
        return vue$1.i18n.t("SpaceModule.chooser", "Search");
      },
      searchTitle() {
        return vue$1.i18n.t("SpaceModule.chooser", "Search for spaces");
      },
      createLabel() {
        return vue$1.i18n.t("SpaceModule.chooser", "Create Space");
      },
      loadingLabel() {
        return vue$1.i18n.t("base", "Loading...");
      },
      // The strings the legacy chooser used, so no translation is lost with the rewrite.
      emptyLabel() {
        return this.searching ? vue$1.i18n.t("SpaceModule.chooser", "No Spaces found.") : vue$1.i18n.t("SpaceModule.chooser", "You are not a member of or following any Spaces.");
      },
      minKeywordLabel() {
        return vue$1.i18n.t(
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
      vue$1.events.on(LIVE_NEW_CONTENT, this.onNewContent);
      RELATION_EVENTS.forEach((name) => vue$1.events.on(name, this.onRelationChanged));
    },
    beforeUnmount() {
      this.disconnectObserver();
      if (this.dropdown) {
        this.dropdown.removeEventListener("show.bs.dropdown", this.onShow);
      }
      vue$1.events.off(LIVE_NEW_CONTENT, this.onNewContent);
      RELATION_EVENTS.forEach((name) => vue$1.events.off(name, this.onRelationChanged));
      clearTimeout(this.searchTimer);
    },
    methods: {
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
          vue$1.log.error(error, true);
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
        const guids = spaces.map((space) => space.guid);
        if (!guids.length) {
          return Promise.resolve();
        }
        return fetchStates(guids).then((states) => {
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
        const state = this.states[space.guid];
        if (state && state.isMember) {
          return "member";
        }
        if (state && state.isFollowing) {
          return "following";
        }
        return space.archived ? "archived" : "none";
      },
      newItemsOf(space) {
        const state = this.states[space.guid];
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
          const state = this.states[data.sguid];
          if (state && state.isMember) {
            this.states = {
              ...this.states,
              [data.sguid]: { ...state, newItems: state.newItems + 1 }
            };
          }
        });
      },
      /**
       * Following, unfollowing and archiving change what the list should show. Rather than
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
  const _hoisted_1$2 = { ref: "root" };
  const _hoisted_2$2 = ["placeholder", "title"];
  const _hoisted_3$1 = {
    key: 0,
    id: "space-directory-link",
    class: "input-group-text"
  };
  const _hoisted_4 = ["href", "innerHTML"];
  const _hoisted_5 = ["innerHTML"];
  const _hoisted_6 = {
    id: "space-menu-spaces",
    ref: "list",
    class: "hh-list"
  };
  const _hoisted_7 = {
    key: 0,
    class: "text-center p-2"
  };
  const _hoisted_8 = {
    class: "visually-hidden",
    role: "status"
  };
  const _hoisted_9 = {
    key: 1,
    class: "dropdown-item disabled"
  };
  const _hoisted_10 = {
    key: 2,
    class: "dropdown-item disabled"
  };
  const _hoisted_11 = {
    key: 3,
    ref: "sentinel",
    class: "stream-end"
  };
  const _hoisted_12 = {
    key: 0,
    class: "dropdown-footer"
  };
  const _hoisted_13 = ["data-action-url"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_SpaceChooserItem = vue.resolveComponent("SpaceChooserItem");
    const _directive_additions = vue.resolveDirective("additions");
    return vue.openBlock(), vue.createElementBlock(
      "div",
      _hoisted_1$2,
      [
        vue.createElementVNode(
          "form",
          {
            class: "dropdown-header dropdown-controls",
            onSubmit: _cache[6] || (_cache[6] = vue.withModifiers(() => {
            }, ["prevent"]))
          },
          [
            vue.createElementVNode(
              "div",
              {
                class: vue.normalizeClass({ "input-group": !!$props.directoryUrl })
              },
              [
                vue.withDirectives(vue.createElementVNode("input", {
                  id: "space-menu-search",
                  ref: "search",
                  "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.keyword = $event),
                  type: "text",
                  class: "form-control",
                  autocomplete: "off",
                  placeholder: $options.searchLabel,
                  title: $options.searchTitle,
                  onKeydown: [
                    _cache[1] || (_cache[1] = vue.withKeys(vue.withModifiers(($event) => $options.move(1), ["prevent"]), ["down"])),
                    _cache[2] || (_cache[2] = vue.withKeys(vue.withModifiers(($event) => $options.move(-1), ["prevent"]), ["up"])),
                    _cache[3] || (_cache[3] = vue.withKeys(vue.withModifiers((...args) => $options.open && $options.open(...args), ["prevent"]), ["enter"])),
                    _cache[4] || (_cache[4] = vue.withKeys(vue.withModifiers((...args) => $options.reset && $options.reset(...args), ["prevent"]), ["esc"]))
                  ]
                }, null, 40, _hoisted_2$2), [
                  [vue.vModelText, $data.keyword]
                ]),
                $props.directoryUrl ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_3$1, [
                  vue.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- server-rendered icon, see docblock "),
                  vue.createElementVNode("a", {
                    href: $props.directoryUrl,
                    innerHTML: $props.directoryIconHtml
                  }, null, 8, _hoisted_4)
                ])) : vue.createCommentVNode("v-if", true),
                vue.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- server-rendered icon, see docblock "),
                vue.withDirectives(vue.createElementVNode("div", {
                  id: "space-search-reset",
                  class: "search-reset",
                  onClick: _cache[5] || (_cache[5] = (...args) => $options.reset && $options.reset(...args)),
                  innerHTML: $props.resetIconHtml
                }, null, 8, _hoisted_5), [
                  [vue.vShow, $data.keyword]
                ])
              ],
              2
              /* CLASS */
            )
          ],
          32
          /* NEED_HYDRATION */
        ),
        _cache[8] || (_cache[8] = vue.createElementVNode(
          "hr",
          { class: "dropdown-divider" },
          null,
          -1
          /* CACHED */
        )),
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_6, [
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($data.spaces, (space, index) => {
              return vue.openBlock(), vue.createBlock(_component_SpaceChooserItem, {
                key: space.guid,
                space,
                relation: $options.relationOf(space),
                "new-items": $options.newItemsOf(space),
                selected: index === $data.selected
              }, null, 8, ["space", "relation", "new-items", "selected"]);
            }),
            128
            /* KEYED_FRAGMENT */
          )),
          $data.loading ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_7, [
            _cache[7] || (_cache[7] = vue.createElementVNode(
              "span",
              {
                class: "spinner-border spinner-border-sm",
                "aria-hidden": "true"
              },
              null,
              -1
              /* CACHED */
            )),
            vue.createElementVNode(
              "span",
              _hoisted_8,
              vue.toDisplayString($options.loadingLabel),
              1
              /* TEXT */
            )
          ])) : vue.createCommentVNode("v-if", true),
          $options.tooShort ? (vue.openBlock(), vue.createElementBlock(
            "div",
            _hoisted_9,
            vue.toDisplayString($options.minKeywordLabel),
            1
            /* TEXT */
          )) : !$data.loading && !$data.spaces.length ? (vue.openBlock(), vue.createElementBlock(
            "div",
            _hoisted_10,
            vue.toDisplayString($options.emptyLabel),
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true),
          $options.hasMore && !$data.loading ? (vue.openBlock(), vue.createElementBlock(
            "div",
            _hoisted_11,
            null,
            512
            /* NEED_PATCH */
          )) : vue.createCommentVNode("v-if", true)
        ])), [
          [_directive_additions]
        ]),
        $props.createSpaceUrl ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_12, [
          vue.createElementVNode("a", {
            href: "#",
            class: "btn btn-accent col-lg-12",
            "data-action-click": "ui.modal.load",
            "data-action-url": $props.createSpaceUrl
          }, vue.toDisplayString($options.createLabel), 9, _hoisted_13)
        ])) : vue.createCommentVNode("v-if", true)
      ],
      512
      /* NEED_PATCH */
    );
  }
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const SPACE_CHANGED = "humhub:space:changed";
  const READY = "humhub:ready";
  const _sfc_main$1 = {
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
        return vue$1.i18n.t("SpaceModule.chooser", "My spaces");
      }
    },
    mounted() {
      vue$1.events.on(SPACE_CHANGED, this.onSpaceChanged);
      vue$1.events.on(READY, this.onReady);
    },
    beforeUnmount() {
      vue$1.events.off(SPACE_CHANGED, this.onSpaceChanged);
      vue$1.events.off(READY, this.onReady);
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
  const _hoisted_1$1 = ["innerHTML"];
  const _hoisted_2$1 = {
    key: 1,
    class: "no-space"
  };
  const _hoisted_3 = ["innerHTML"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      vue.Fragment,
      null,
      [
        vue.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- server-rendered image or icon, see docblock "),
        $data.imageHtml ? (vue.openBlock(), vue.createElementBlock("span", {
          key: 0,
          class: "current-space",
          innerHTML: $data.imageHtml
        }, null, 8, _hoisted_1$1)) : (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$1, [
          vue.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- server-rendered icon, see docblock "),
          vue.createElementVNode("span", { innerHTML: $props.noSpaceIconHtml }, null, 8, _hoisted_3),
          _cache[0] || (_cache[0] = vue.createElementVNode(
            "br",
            null,
            null,
            -1
            /* CACHED */
          )),
          vue.createTextVNode(
            " " + vue.toDisplayString($options.noSpaceLabel),
            1
            /* TEXT */
          )
        ]))
      ],
      2112
      /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */
    );
  }
  const C2 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
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
  const C3 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("MembershipButton", C0);
  vue$1.register("SpaceChooser", C1);
  vue$1.register("SpaceChooserToggle", C2);
  vue$1.register("SpaceImage", C3);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.space.vue.js.map
