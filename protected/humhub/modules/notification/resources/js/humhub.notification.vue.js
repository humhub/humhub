/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from notification/vue/ via `grunt build-vue --module=notification`.
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
  const _sfc_main$4 = {
    props: {
      // Serialized notification (NotificationSerializer::notification()).
      notification: { type: Object, required: true }
    },
    computed: {
      absoluteTime() {
        return this.notification.createdAt ? new Date(this.notification.createdAt).toLocaleString() : "";
      }
    }
  };
  const _hoisted_1$4 = ["href", "data-notification-id", "data-notification-group"];
  const _hoisted_2$4 = { class: "flex-shrink-0 me-3 pt-1 img-profile-space" };
  const _hoisted_3$4 = { class: "flex-grow-1" };
  const _hoisted_4$4 = ["innerHTML"];
  const _hoisted_5$3 = ["datetime", "title"];
  const _hoisted_6$3 = { class: "flex-shrink-0 ms-2 order-last text-center" };
  const _hoisted_7$3 = {
    key: 0,
    class: "badge badge-new"
  };
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_UserImage = vue.resolveComponent("UserImage");
    const _component_SpaceImage = vue.resolveComponent("SpaceImage");
    return vue.openBlock(), vue.createElementBlock("a", {
      class: vue.normalizeClass(["d-flex", { new: $props.notification.isNew }]),
      href: $props.notification.url,
      "data-notification-id": $props.notification.id,
      "data-notification-group": $props.notification.groupKey || ""
    }, [
      vue.createElementVNode("div", _hoisted_2$4, [
        $props.notification.originator ? (vue.openBlock(), vue.createBlock(
          _component_UserImage,
          vue.mergeProps({ key: 0 }, $props.notification.originator, {
            width: 32,
            link: false
          }),
          null,
          16
          /* FULL_PROPS */
        )) : vue.createCommentVNode("v-if", true),
        $props.notification.space ? (vue.openBlock(), vue.createBlock(
          _component_SpaceImage,
          vue.mergeProps({ key: 1 }, $props.notification.space, {
            width: 20,
            link: false,
            class: "img-space"
          }),
          null,
          16
          /* FULL_PROPS */
        )) : vue.createCommentVNode("v-if", true)
      ]),
      vue.createElementVNode("div", _hoisted_3$4, [
        vue.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- server-rendered sentence, see docblock "),
        vue.createElementVNode("span", {
          innerHTML: $props.notification.html
        }, null, 8, _hoisted_4$4),
        _cache[0] || (_cache[0] = vue.createElementVNode(
          "br",
          null,
          null,
          -1
          /* CACHED */
        )),
        vue.createElementVNode("time", {
          class: "tt time timeago",
          "data-ui-addition": "timeago",
          datetime: $props.notification.createdAt,
          title: $options.absoluteTime
        }, vue.toDisplayString($options.absoluteTime), 9, _hoisted_5$3)
      ]),
      vue.createElementVNode("div", _hoisted_6$3, [
        $props.notification.isNew ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_7$3)) : vue.createCommentVNode("v-if", true)
      ])
    ], 10, _hoisted_1$4);
  }
  const NotificationEntry = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const fetchNotifications = ({ cursor = null, limit = null, categories = null, seen = null } = {}) => {
    const params = {};
    if (cursor) {
      params.cursor = cursor;
    }
    if (limit) {
      params.limit = limit;
    }
    if (Array.isArray(categories)) {
      params.categories = categories.length ? categories : [""];
    }
    if (seen) {
      params.seen = seen;
    }
    return vue$1.client.get(vue$1.apiUrl("notification", params)).then(normalizeWindow);
  };
  const markAllAsSeen = () => vue$1.client.post(vue$1.apiUrl("notification/mark-as-seen"));
  const normalizeWindow = (response) => ({
    results: response && response.results || [],
    unseenCount: Number(response && response.unseenCount || 0),
    nextCursor: response && response.nextCursor || null
  });
  const _sfc_main$3 = {
    // Internal building block of this module's islands (a `vue/components/` file is not
    // auto-registered platform-wide - see docs/develop/ui-js-vuejs-components.md), so it is
    // imported rather than resolved by tag.
    components: { NotificationEntry },
    props: {
      // Optional first page from the server (inlined by the widget), so the first paint of
      // the overview page costs no request.
      initial: { type: Object, default: null },
      // Filters, forwarded to the endpoint (see notificationApi.js).
      categories: { type: Array, default: null },
      seen: { type: String, default: null },
      pageSize: { type: Number, default: 6 },
      showMoreButton: { type: Boolean, default: false },
      // Scroll-paging (the dropdown): distance from the bottom, in pixels, that triggers the
      // next page.
      scrollThreshold: { type: Number, default: 20 },
      emptyText: { type: String, default: null }
    },
    emits: ["loaded"],
    data() {
      return {
        items: this.initial ? [...this.initial.results || []] : [],
        nextCursor: this.initial ? this.initial.nextCursor || null : null,
        loading: false,
        // Nothing fetched yet AND nothing handed over: the first `reload()` is the initial
        // load rather than a refresh.
        loaded: !!this.initial
      };
    },
    computed: {
      hasMore() {
        return this.nextCursor !== null;
      },
      emptyLabel() {
        return this.emptyText || vue$1.i18n.t("NotificationModule.base", "There are no notifications yet.");
      },
      showMoreLabel() {
        return vue$1.i18n.t("NotificationModule.base", "Show all notifications");
      },
      loadingLabel() {
        return vue$1.i18n.t("base", "Loading...");
      }
    },
    methods: {
      /** Fetches the first page, replacing what is listed. */
      reload() {
        return this.fetch(null, true);
      },
      /** Appends the next page. */
      loadMore() {
        if (this.loading || !this.hasMore) {
          return Promise.resolve();
        }
        return this.fetch(this.nextCursor, false);
      },
      fetch(cursor, replace) {
        this.loading = true;
        return fetchNotifications({
          cursor,
          limit: this.pageSize,
          categories: this.categories,
          seen: this.seen
        }).then((response) => {
          this.items = replace ? response.results : [...this.items, ...response.results];
          this.nextCursor = response.nextCursor;
          this.loaded = true;
          this.$emit("loaded", response);
        }).catch((response) => {
          vue$1.log.error(response, true);
        }).finally(() => {
          this.loading = false;
        });
      },
      /**
       * Inserts a live-arrived notification at the top. Already-listed entries are replaced
       * in place (a grouped notification whose group grew keeps its position rather than
       * appearing twice).
       */
      prepend(entry) {
        const index = this.indexOf(entry);
        if (index === -1) {
          this.items = [entry, ...this.items];
          return;
        }
        const items = [...this.items];
        items.splice(index, 1);
        this.items = [entry, ...items];
      },
      /** @returns {boolean} whether this id or group is already listed. */
      has(entry) {
        return this.indexOf(entry) !== -1;
      },
      indexOf(entry) {
        return this.items.findIndex((item) => item.id === entry.id || !!entry.groupKey && item.groupKey === entry.groupKey);
      },
      onScroll() {
        const element = this.$refs.list;
        if (!element || this.loading || !this.hasMore) {
          return;
        }
        if (element.scrollTop + element.clientHeight >= element.scrollHeight - this.scrollThreshold) {
          this.loadMore();
        }
      }
    }
  };
  const _hoisted_1$3 = {
    key: 0,
    class: "info"
  };
  const _hoisted_2$3 = {
    key: 1,
    class: "text-center p-2"
  };
  const _hoisted_3$3 = {
    class: "visually-hidden",
    role: "status"
  };
  const _hoisted_4$3 = {
    key: 2,
    class: "text-center p-2"
  };
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_NotificationEntry = vue.resolveComponent("NotificationEntry");
    const _directive_additions = vue.resolveDirective("additions");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock(
      "div",
      {
        ref: "list",
        class: "hh-list",
        onScroll: _cache[1] || (_cache[1] = (...args) => $options.onScroll && $options.onScroll(...args))
      },
      [
        (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          null,
          vue.renderList($data.items, (entry) => {
            return vue.openBlock(), vue.createBlock(_component_NotificationEntry, {
              key: entry.id,
              notification: entry
            }, null, 8, ["notification"]);
          }),
          128
          /* KEYED_FRAGMENT */
        )),
        !$data.items.length && !$data.loading ? (vue.openBlock(), vue.createElementBlock(
          "div",
          _hoisted_1$3,
          vue.toDisplayString($options.emptyLabel),
          1
          /* TEXT */
        )) : vue.createCommentVNode("v-if", true),
        $data.loading ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$3, [
          _cache[2] || (_cache[2] = vue.createElementVNode(
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
            _hoisted_3$3,
            vue.toDisplayString($options.loadingLabel),
            1
            /* TEXT */
          )
        ])) : vue.createCommentVNode("v-if", true),
        $props.showMoreButton && $options.hasMore && !$data.loading ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$3, [
          vue.createElementVNode(
            "button",
            {
              type: "button",
              class: "btn btn-light btn-sm",
              onClick: _cache[0] || (_cache[0] = (...args) => $options.loadMore && $options.loadMore(...args))
            },
            vue.toDisplayString($options.showMoreLabel),
            1
            /* TEXT */
          )
        ])) : vue.createCommentVNode("v-if", true)
      ],
      32
      /* NEED_HYDRATION */
    )), [
      [_directive_additions]
    ]);
  }
  const NotificationList = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const LIVE_EVENT = "humhub:modules:notification:live:NewNotification";
  const UPDATE_TITLE_EVENT = "humhub:modules:notification:UpdateTitleNotificationCount";
  const UPDATE_COUNT_EVENT = "humhub:notification:updateCount";
  const SET_COUNT_EVENT$1 = "humhub:notification:setCount";
  const _sfc_main$2 = {
    components: { NotificationList },
    // Preloaded for this island AND for the shared components it nests (UserImage's own alt
    // phrase from `base`, `UserModule.base`), since the mounter only preloads the categories of
    // the top-level island component.
    i18nCategories: ["NotificationModule.base", "UserModule.base", "base"],
    props: {
      // First page, inlined by the widget: {results, unseenCount, nextCursor}.
      initial: { type: Object, default: null },
      overviewUrl: { type: String, required: true },
      settingsUrl: { type: String, required: true },
      // Server-rendered icon markup (the icon provider is pluggable, see `Icon`).
      bellIconHtml: { type: String, default: "" },
      checkIconHtml: { type: String, default: "" },
      cogIconHtml: { type: String, default: "" },
      pageSize: { type: Number, default: 6 }
    },
    data() {
      return {
        unseenCount: this.initial ? Number(this.initial.unseenCount || 0) : 0,
        open: false,
        animate: false
      };
    },
    computed: {
      openLabel() {
        return vue$1.i18n.t("NotificationModule.base", "Open the notification dropdown menu");
      },
      headerLabel() {
        return vue$1.i18n.t("NotificationModule.base", "Notifications");
      },
      markSeenLabel() {
        return vue$1.i18n.t("NotificationModule.base", "Mark all as seen");
      },
      settingsLabel() {
        return vue$1.i18n.t("NotificationModule.base", "Notification Settings");
      },
      showAllLabel() {
        return vue$1.i18n.t("NotificationModule.base", "Show all notifications");
      }
    },
    mounted() {
      this.$refs.toggle.addEventListener("show.bs.dropdown", this.onShow);
      this.$refs.toggle.addEventListener("hidden.bs.dropdown", this.onHidden);
      vue$1.events.on(LIVE_EVENT, this.onLiveNotification);
      vue$1.events.on(UPDATE_TITLE_EVENT, this.updateTitle);
      vue$1.events.on(SET_COUNT_EVENT$1, this.onSetCount);
      this.updateTitle();
    },
    beforeUnmount() {
      this.$refs.toggle.removeEventListener("show.bs.dropdown", this.onShow);
      this.$refs.toggle.removeEventListener("hidden.bs.dropdown", this.onHidden);
      vue$1.events.off(LIVE_EVENT, this.onLiveNotification);
      vue$1.events.off(UPDATE_TITLE_EVENT, this.updateTitle);
      vue$1.events.off(SET_COUNT_EVENT$1, this.onSetCount);
    },
    methods: {
      onShow() {
        this.open = true;
        this.$refs.list.reload();
      },
      onHidden() {
        this.open = false;
      },
      onLoaded(response) {
        this.setCount(response.unseenCount);
      },
      onSetCount(event, count) {
        this.setCount(count);
      },
      /**
       * Live events carry ids only. Anything already listed is not news; anything else bumps
       * the count, and refreshes the list if the user is looking at it.
       */
      onLiveNotification(event, liveEvents) {
        const fresh = (liveEvents || []).filter((liveEvent) => {
          const data = liveEvent.data || {};
          return !this.$refs.list.has({
            id: Number(data.notificationId),
            groupKey: data.notificationGroup || null
          });
        });
        if (!fresh.length) {
          return;
        }
        if (this.open) {
          this.$refs.list.reload();
          return;
        }
        this.setCount(this.unseenCount + fresh.length);
      },
      markAsSeen() {
        return markAllAsSeen().then(() => {
          this.setCount(0);
          vue$1.events.trigger(SET_COUNT_EVENT$1, [0]);
          if (this.open) {
            this.$refs.list.reload();
          }
        }).catch((response) => {
          vue$1.log.error(response, true);
        });
      },
      setCount(count) {
        const next = Number(count) || 0;
        if (next === this.unseenCount) {
          return;
        }
        if (next > this.unseenCount) {
          this.animate = false;
          this.$nextTick(() => {
            this.animate = true;
          });
        }
        this.unseenCount = next;
        vue$1.events.trigger(UPDATE_COUNT_EVENT, [next]);
        this.updateTitle();
      },
      /**
       * `(3) Dashboard - HumHub` - own unread count plus the mail module's unread messages,
       * the exact arithmetic `humhub.notification.js` did.
       */
      updateTitle() {
        const base = vue$1.pageTitle() || document.title;
        let count = this.unseenCount;
        const mail = window.humhub && window.humhub.modules && window.humhub.modules.mail;
        if (mail && mail.notification && typeof mail.notification.getNewMessageCount === "function") {
          count += Number(mail.notification.getNewMessageCount()) || 0;
        }
        document.title = count > 0 ? "(" + count + ") " + base : base;
      }
    }
  };
  const _hoisted_1$2 = ["aria-label"];
  const _hoisted_2$2 = ["innerHTML"];
  const _hoisted_3$2 = {
    key: 0,
    id: "badge-notifications",
    class: "text-bg-danger badge badge-notifications"
  };
  const _hoisted_4$2 = {
    id: "dropdown-notifications",
    class: "dropdown-menu"
  };
  const _hoisted_5$2 = { class: "dropdown-header" };
  const _hoisted_6$2 = { class: "dropdown-header-actions" };
  const _hoisted_7$2 = ["aria-label", "title"];
  const _hoisted_8$2 = ["innerHTML"];
  const _hoisted_9$2 = ["href", "aria-label", "title"];
  const _hoisted_10$2 = ["innerHTML"];
  const _hoisted_11$1 = { class: "dropdown-footer" };
  const _hoisted_12$1 = ["href"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_NotificationList = vue.resolveComponent("NotificationList");
    return vue.openBlock(), vue.createElementBlock(
      vue.Fragment,
      null,
      [
        vue.createElementVNode("a", {
          ref: "toggle",
          href: "#",
          id: "icon-notifications",
          "data-bs-toggle": "dropdown",
          "aria-label": $options.openLabel,
          onClick: _cache[0] || (_cache[0] = vue.withModifiers(() => {
          }, ["prevent"]))
        }, [
          vue.createElementVNode("span", {
            class: vue.normalizeClass({ "animated swing": $data.animate }),
            innerHTML: $props.bellIconHtml
          }, null, 10, _hoisted_2$2)
        ], 8, _hoisted_1$2),
        $data.unseenCount > 0 ? (vue.openBlock(), vue.createElementBlock(
          "span",
          _hoisted_3$2,
          vue.toDisplayString($data.unseenCount),
          1
          /* TEXT */
        )) : vue.createCommentVNode("v-if", true),
        vue.createElementVNode("ul", _hoisted_4$2, [
          vue.createElementVNode("li", null, [
            vue.createElementVNode("div", _hoisted_5$2, [
              _cache[2] || (_cache[2] = vue.createElementVNode(
                "div",
                { class: "arrow" },
                null,
                -1
                /* CACHED */
              )),
              vue.createTextVNode(
                " " + vue.toDisplayString($options.headerLabel) + " ",
                1
                /* TEXT */
              ),
              vue.createElementVNode("div", _hoisted_6$2, [
                $data.unseenCount > 0 ? (vue.openBlock(), vue.createElementBlock("button", {
                  key: 0,
                  type: "button",
                  id: "mark-seen-link",
                  class: "btn-light btn btn-icon-only btn-sm",
                  "aria-label": $options.markSeenLabel,
                  title: $options.markSeenLabel,
                  onClick: _cache[1] || (_cache[1] = (...args) => $options.markAsSeen && $options.markAsSeen(...args))
                }, [
                  vue.createElementVNode("span", { innerHTML: $props.checkIconHtml }, null, 8, _hoisted_8$2)
                ], 8, _hoisted_7$2)) : vue.createCommentVNode("v-if", true),
                vue.createElementVNode("a", {
                  class: "btn-light btn btn-icon-only btn-sm",
                  href: $props.settingsUrl,
                  "aria-label": $options.settingsLabel,
                  title: $options.settingsLabel
                }, [
                  vue.createElementVNode("span", { innerHTML: $props.cogIconHtml }, null, 8, _hoisted_10$2)
                ], 8, _hoisted_9$2)
              ])
            ])
          ]),
          vue.createElementVNode("li", null, [
            vue.createVNode(_component_NotificationList, {
              ref: "list",
              class: "dropdown-item",
              initial: $props.initial,
              "page-size": $props.pageSize,
              onLoaded: $options.onLoaded
            }, null, 8, ["initial", "page-size", "onLoaded"])
          ]),
          vue.createElementVNode("li", null, [
            vue.createElementVNode("div", _hoisted_11$1, [
              vue.createElementVNode("a", {
                class: "btn btn-light col-lg-12",
                href: $props.overviewUrl
              }, vue.toDisplayString($options.showAllLabel), 9, _hoisted_12$1)
            ])
          ])
        ])
      ],
      64
      /* STABLE_FRAGMENT */
    );
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const _sfc_main$1 = {
    props: {
      // [{id, title}] - the categories the server offers (localized).
      categories: { type: Array, default: () => [] },
      // Currently selected category ids.
      selected: { type: Array, default: () => [] },
      // '' (all), 'unseen' or 'seen'.
      seen: { type: String, default: "" },
      // Server-rendered icon markup per option: {all, unseen, seen}.
      icons: { type: Object, default: () => ({}) }
    },
    emits: ["change"],
    computed: {
      seenOptions() {
        return [
          { value: "", label: vue$1.i18n.t("NotificationModule.base", "All"), icon: this.icons.all },
          { value: "unseen", label: vue$1.i18n.t("NotificationModule.base", "Unseen"), icon: this.icons.unseen },
          { value: "seen", label: vue$1.i18n.t("NotificationModule.base", "Seen"), icon: this.icons.seen }
        ];
      },
      seenFilterLabel() {
        return vue$1.i18n.t("NotificationModule.base", "Filter");
      },
      allLabel() {
        return vue$1.i18n.t("NotificationModule.base", "All");
      },
      allSelected() {
        return this.categories.length > 0 && this.selected.length === this.categories.length;
      }
    },
    methods: {
      selectSeen(value) {
        this.emitChange({ seen: value });
      },
      toggleAll(checked) {
        this.emitChange({ categories: checked ? this.categories.map((category) => category.id) : [] });
      },
      toggleCategory(id, checked) {
        const categories = checked ? [...this.selected, id] : this.selected.filter((candidate) => candidate !== id);
        this.emitChange({ categories });
      },
      emitChange(changed) {
        this.$emit("change", {
          categories: changed.categories !== void 0 ? changed.categories : [...this.selected],
          seen: changed.seen !== void 0 ? changed.seen : this.seen
        });
      }
    }
  };
  const _hoisted_1$1 = { class: "form-checkboxes-normal" };
  const _hoisted_2$1 = ["aria-label"];
  const _hoisted_3$1 = ["aria-pressed", "onClick"];
  const _hoisted_4$1 = ["innerHTML"];
  const _hoisted_5$1 = { style: { "padding-left": "5px" } };
  const _hoisted_6$1 = { class: "form-check" };
  const _hoisted_7$1 = ["checked"];
  const _hoisted_8$1 = {
    class: "form-check-label",
    for: "notification-filter-all"
  };
  const _hoisted_9$1 = ["id", "checked", "onChange"];
  const _hoisted_10$1 = ["for"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, [
      vue.createElementVNode("div", {
        class: "btn-group w-100 mb-3",
        role: "group",
        "aria-label": $options.seenFilterLabel
      }, [
        (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          null,
          vue.renderList($options.seenOptions, (option) => {
            return vue.openBlock(), vue.createElementBlock("button", {
              key: option.value || "all",
              type: "button",
              class: vue.normalizeClass(["btn btn-sm", option.value === $props.seen ? "btn-primary" : "btn-light"]),
              "aria-pressed": option.value === $props.seen ? "true" : "false",
              onClick: ($event) => $options.selectSeen(option.value)
            }, [
              option.icon ? (vue.openBlock(), vue.createElementBlock("span", {
                key: 0,
                innerHTML: option.icon
              }, null, 8, _hoisted_4$1)) : vue.createCommentVNode("v-if", true),
              vue.createTextVNode(
                " " + vue.toDisplayString(option.label),
                1
                /* TEXT */
              )
            ], 10, _hoisted_3$1);
          }),
          128
          /* KEYED_FRAGMENT */
        ))
      ], 8, _hoisted_2$1),
      vue.createElementVNode("div", _hoisted_5$1, [
        vue.createElementVNode("div", _hoisted_6$1, [
          vue.createElementVNode("input", {
            id: "notification-filter-all",
            class: "form-check-input",
            type: "checkbox",
            checked: $options.allSelected,
            onChange: _cache[0] || (_cache[0] = ($event) => $options.toggleAll($event.target.checked))
          }, null, 40, _hoisted_7$1),
          vue.createElementVNode(
            "label",
            _hoisted_8$1,
            vue.toDisplayString($options.allLabel),
            1
            /* TEXT */
          )
        ]),
        (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          null,
          vue.renderList($props.categories, (category) => {
            return vue.openBlock(), vue.createElementBlock("div", {
              key: category.id,
              class: "form-check"
            }, [
              vue.createElementVNode("input", {
                id: "notification-filter-" + category.id,
                class: "form-check-input",
                type: "checkbox",
                checked: $props.selected.includes(category.id),
                onChange: ($event) => $options.toggleCategory(category.id, $event.target.checked)
              }, null, 40, _hoisted_9$1),
              vue.createElementVNode("label", {
                class: "form-check-label",
                for: "notification-filter-" + category.id
              }, vue.toDisplayString(category.title), 9, _hoisted_10$1)
            ]);
          }),
          128
          /* KEYED_FRAGMENT */
        ))
      ])
    ]);
  }
  const NotificationFilter = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const SET_COUNT_EVENT = "humhub:notification:setCount";
  const _sfc_main = {
    components: { NotificationFilter, NotificationList },
    i18nCategories: ["NotificationModule.base", "UserModule.base", "base"],
    props: {
      // First page, inlined by the controller: {results, unseenCount, nextCursor}.
      initial: { type: Object, default: null },
      // [{id, title}] of every category the caller can filter by (localized).
      categories: { type: Array, default: () => [] },
      // Server-rendered icon markup: {check, cog, all, unseen, seen}.
      icons: { type: Object, default: () => ({}) },
      settingsUrl: { type: String, required: true },
      pageSize: { type: Number, default: 20 }
    },
    data() {
      return {
        // Everything selected initially, like the server-rendered filter's own default.
        selectedCategories: this.categories.map((category) => category.id),
        seen: "",
        unseenCount: this.initial ? Number(this.initial.unseenCount || 0) : 0
      };
    },
    computed: {
      // No filter at all while every category is selected: it would only narrow the list to
      // classes the modules currently register (see the endpoint's own docblock).
      requestCategories() {
        return this.selectedCategories.length === this.categories.length ? null : this.selectedCategories;
      },
      headingLabel() {
        return vue$1.i18n.t("NotificationModule.base", "<strong>Notification</strong> Overview");
      },
      filterLabel() {
        return vue$1.i18n.t("NotificationModule.base", "Filter");
      },
      markSeenLabel() {
        return vue$1.i18n.t("NotificationModule.base", "Mark all as seen");
      },
      settingsLabel() {
        return vue$1.i18n.t("NotificationModule.base", "Notification Settings");
      },
      emptyLabel() {
        return vue$1.i18n.t("NotificationModule.base", "No notifications found!");
      },
      sidebarLabel() {
        return vue$1.i18n.t("base", "Sidebar");
      }
    },
    mounted() {
      vue$1.events.on(SET_COUNT_EVENT, this.onSetCount);
    },
    beforeUnmount() {
      vue$1.events.off(SET_COUNT_EVENT, this.onSetCount);
    },
    methods: {
      /** The menu marked everything as seen - this list's unread markers are stale. */
      onSetCount(event, count) {
        if (Number(count) === 0 && this.unseenCount !== 0) {
          this.unseenCount = 0;
          this.$refs.list.reload();
        }
      },
      onFilterChange({ categories, seen }) {
        this.selectedCategories = categories;
        this.seen = seen;
        this.$nextTick(() => this.$refs.list.reload());
      },
      onLoaded(response) {
        this.unseenCount = Number(response.unseenCount) || 0;
      },
      markAsSeen() {
        return markAllAsSeen().then(() => {
          this.unseenCount = 0;
          vue$1.events.trigger(SET_COUNT_EVENT, [0]);
          return this.$refs.list.reload();
        }).catch((response) => {
          vue$1.log.error(response, true);
        });
      }
    }
  };
  const _hoisted_1 = { class: "row" };
  const _hoisted_2 = { class: "col-lg-9 layout-content-container" };
  const _hoisted_3 = { class: "panel panel-default" };
  const _hoisted_4 = { class: "panel-heading" };
  const _hoisted_5 = ["innerHTML"];
  const _hoisted_6 = { class: "float-end" };
  const _hoisted_7 = ["aria-label", "title"];
  const _hoisted_8 = ["innerHTML"];
  const _hoisted_9 = ["href", "aria-label", "title"];
  const _hoisted_10 = ["innerHTML"];
  const _hoisted_11 = { class: "panel-body" };
  const _hoisted_12 = ["aria-label"];
  const _hoisted_13 = { class: "panel panel-default" };
  const _hoisted_14 = { class: "panel-heading" };
  const _hoisted_15 = { class: "panel-body" };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_NotificationList = vue.resolveComponent("NotificationList");
    const _component_NotificationFilter = vue.resolveComponent("NotificationFilter");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      vue.createElementVNode("div", _hoisted_2, [
        vue.createElementVNode("div", _hoisted_3, [
          vue.createElementVNode("div", _hoisted_4, [
            vue.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- translated heading with markup, as server-side "),
            vue.createElementVNode("span", { innerHTML: $options.headingLabel }, null, 8, _hoisted_5),
            vue.createElementVNode("div", _hoisted_6, [
              $data.unseenCount > 0 ? (vue.openBlock(), vue.createElementBlock("button", {
                key: 0,
                type: "button",
                id: "notification_overview_markseen",
                class: "btn-light btn btn-icon-only btn-sm",
                "aria-label": $options.markSeenLabel,
                title: $options.markSeenLabel,
                onClick: _cache[0] || (_cache[0] = (...args) => $options.markAsSeen && $options.markAsSeen(...args))
              }, [
                vue.createElementVNode("span", {
                  innerHTML: $props.icons.check
                }, null, 8, _hoisted_8)
              ], 8, _hoisted_7)) : vue.createCommentVNode("v-if", true),
              vue.createElementVNode("a", {
                class: "btn-light btn btn-icon-only btn-sm",
                href: $props.settingsUrl,
                "aria-label": $options.settingsLabel,
                title: $options.settingsLabel
              }, [
                vue.createElementVNode("span", {
                  innerHTML: $props.icons.cog
                }, null, 8, _hoisted_10)
              ], 8, _hoisted_9)
            ])
          ]),
          vue.createElementVNode("div", _hoisted_11, [
            vue.createVNode(_component_NotificationList, {
              ref: "list",
              id: "notification_overview_list",
              initial: $props.initial,
              "page-size": $props.pageSize,
              categories: $options.requestCategories,
              seen: $data.seen || null,
              "show-more-button": true,
              "empty-text": $options.emptyLabel,
              onLoaded: $options.onLoaded
            }, null, 8, ["initial", "page-size", "categories", "seen", "empty-text", "onLoaded"])
          ])
        ])
      ]),
      vue.createElementVNode("aside", {
        class: "col-lg-3 layout-sidebar-container",
        "aria-label": $options.sidebarLabel
      }, [
        vue.createElementVNode("div", _hoisted_13, [
          vue.createElementVNode("div", _hoisted_14, [
            vue.createElementVNode(
              "strong",
              null,
              vue.toDisplayString($options.filterLabel),
              1
              /* TEXT */
            ),
            _cache[1] || (_cache[1] = vue.createElementVNode(
              "hr",
              { style: { "margin-bottom": "0" } },
              null,
              -1
              /* CACHED */
            ))
          ]),
          vue.createElementVNode("div", _hoisted_15, [
            vue.createVNode(_component_NotificationFilter, {
              categories: $props.categories,
              selected: $data.selectedCategories,
              seen: $data.seen,
              icons: $props.icons,
              onChange: $options.onFilterChange
            }, null, 8, ["categories", "selected", "seen", "icons", "onChange"])
          ])
        ])
      ], 8, _hoisted_12)
    ]);
  }
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("NotificationMenu", C0);
  vue$1.register("NotificationOverview", C1);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.notification.vue.js.map
