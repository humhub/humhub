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
  const _sfc_main$5 = {
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
  const _hoisted_1$5 = ["href", "data-notification-id", "data-notification-group"];
  const _hoisted_2$5 = { class: "flex-shrink-0 me-3 pt-1 img-profile-space" };
  const _hoisted_3$5 = { class: "flex-grow-1" };
  const _hoisted_4$5 = ["innerHTML"];
  const _hoisted_5$4 = ["datetime", "title"];
  const _hoisted_6$4 = { class: "flex-shrink-0 ms-2 order-last text-center" };
  const _hoisted_7$4 = {
    key: 0,
    class: "badge badge-new"
  };
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_UserImage = vue.resolveComponent("UserImage");
    const _component_SpaceImage = vue.resolveComponent("SpaceImage");
    return vue.openBlock(), vue.createElementBlock("a", {
      class: vue.normalizeClass(["d-flex", { new: $props.notification.isNew }]),
      href: $props.notification.url,
      "data-notification-id": $props.notification.id,
      "data-notification-group": $props.notification.groupKey || ""
    }, [
      vue.createElementVNode("div", _hoisted_2$5, [
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
      vue.createElementVNode("div", _hoisted_3$5, [
        vue.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- server-rendered sentence, see docblock "),
        vue.createElementVNode("span", {
          innerHTML: $props.notification.html
        }, null, 8, _hoisted_4$5),
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
        }, vue.toDisplayString($options.absoluteTime), 9, _hoisted_5$4)
      ]),
      vue.createElementVNode("div", _hoisted_6$4, [
        $props.notification.isNew ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_7$4)) : vue.createCommentVNode("v-if", true)
      ])
    ], 10, _hoisted_1$5);
  }
  const NotificationEntry = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
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
    return vue$1.client.get(vue$1.apiUrl("notification", params)).then(normalizePage);
  };
  const markAsSeen = (ids = null) => {
    if (Array.isArray(ids) && !ids.length) {
      return Promise.resolve(null);
    }
    const url = vue$1.apiUrl("notification/mark-as-seen");
    const request = Array.isArray(ids) ? vue$1.client.post(url, { data: { ids } }) : vue$1.client.post(url);
    return request.then((response) => ({ unseenCount: Number(response && response.unseenCount || 0) }));
  };
  const normalizePage = (response) => ({
    results: response && response.results || [],
    unseenCount: Number(response && response.unseenCount || 0),
    nextCursor: response && response.nextCursor || null
  });
  const _sfc_main$4 = {
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
  const _hoisted_1$4 = {
    key: 0,
    class: "info"
  };
  const _hoisted_2$4 = {
    key: 1,
    class: "text-center p-2"
  };
  const _hoisted_3$4 = {
    class: "visually-hidden",
    role: "status"
  };
  const _hoisted_4$4 = {
    key: 2,
    class: "text-center p-2"
  };
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
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
          _hoisted_1$4,
          vue.toDisplayString($options.emptyLabel),
          1
          /* TEXT */
        )) : vue.createCommentVNode("v-if", true),
        $data.loading ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$4, [
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
            _hoisted_3$4,
            vue.toDisplayString($options.loadingLabel),
            1
            /* TEXT */
          )
        ])) : vue.createCommentVNode("v-if", true),
        $props.showMoreButton && $options.hasMore && !$data.loading ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_4$4, [
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
  const NotificationList = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const LIVE_EVENT = "humhub:modules:notification:live:NewNotification";
  const UPDATE_TITLE_EVENT = "humhub:modules:notification:UpdateTitleNotificationCount";
  const UPDATE_COUNT_EVENT = "humhub:notification:updateCount";
  const SET_COUNT_EVENT$1 = "humhub:notification:setCount";
  const LIVE_RELOAD_DELAY = 500;
  const _sfc_main$3 = {
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
        animate: false,
        liveReloadTimer: null
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
      clearTimeout(this.liveReloadTimer);
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
       * Live events carry ids only. Anything already listed is not news; anything else
       * refreshes the list - and with it the count, see "Live updates".
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
        clearTimeout(this.liveReloadTimer);
        if (this.open) {
          this.$refs.list.reload();
          return;
        }
        this.liveReloadTimer = setTimeout(() => {
          this.liveReloadTimer = null;
          this.$refs.list.reload();
        }, LIVE_RELOAD_DELAY);
      },
      markAsSeen() {
        return markAsSeen().then(() => {
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
  const _hoisted_1$3 = ["aria-label"];
  const _hoisted_2$3 = ["innerHTML"];
  const _hoisted_3$3 = {
    key: 0,
    id: "badge-notifications",
    class: "text-bg-danger badge badge-notifications"
  };
  const _hoisted_4$3 = {
    id: "dropdown-notifications",
    class: "dropdown-menu"
  };
  const _hoisted_5$3 = { class: "dropdown-header" };
  const _hoisted_6$3 = { class: "dropdown-header-actions" };
  const _hoisted_7$3 = ["aria-label", "title"];
  const _hoisted_8$3 = ["innerHTML"];
  const _hoisted_9$3 = ["href", "aria-label", "title"];
  const _hoisted_10$3 = ["innerHTML"];
  const _hoisted_11$2 = { class: "dropdown-footer" };
  const _hoisted_12$2 = ["href"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
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
          }, null, 10, _hoisted_2$3)
        ], 8, _hoisted_1$3),
        $data.unseenCount > 0 ? (vue.openBlock(), vue.createElementBlock(
          "span",
          _hoisted_3$3,
          vue.toDisplayString($data.unseenCount),
          1
          /* TEXT */
        )) : vue.createCommentVNode("v-if", true),
        vue.createElementVNode("ul", _hoisted_4$3, [
          vue.createElementVNode("li", null, [
            vue.createElementVNode("div", _hoisted_5$3, [
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
              vue.createElementVNode("div", _hoisted_6$3, [
                $data.unseenCount > 0 ? (vue.openBlock(), vue.createElementBlock("button", {
                  key: 0,
                  type: "button",
                  id: "mark-seen-link",
                  class: "btn-light btn btn-icon-only btn-sm",
                  "aria-label": $options.markSeenLabel,
                  title: $options.markSeenLabel,
                  onClick: _cache[1] || (_cache[1] = (...args) => $options.markAsSeen && $options.markAsSeen(...args))
                }, [
                  vue.createElementVNode("span", { innerHTML: $props.checkIconHtml }, null, 8, _hoisted_8$3)
                ], 8, _hoisted_7$3)) : vue.createCommentVNode("v-if", true),
                vue.createElementVNode("a", {
                  class: "btn-light btn btn-icon-only btn-sm",
                  href: $props.settingsUrl,
                  "aria-label": $options.settingsLabel,
                  title: $options.settingsLabel
                }, [
                  vue.createElementVNode("span", { innerHTML: $props.cogIconHtml }, null, 8, _hoisted_10$3)
                ], 8, _hoisted_9$3)
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
            vue.createElementVNode("div", _hoisted_11$2, [
              vue.createElementVNode("a", {
                class: "btn btn-light col-lg-12",
                href: $props.overviewUrl
              }, vue.toDisplayString($options.showAllLabel), 9, _hoisted_12$2)
            ])
          ])
        ])
      ],
      64
      /* STABLE_FRAGMENT */
    );
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = {
    props: {
      // [{id, title}] - the notification categories the server offers (localized).
      filters: { type: Array, default: () => [] },
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
        return this.filters.length > 0 && this.selected.length === this.filters.length;
      }
    },
    methods: {
      selectSeen(value) {
        this.emitChange({ seen: value });
      },
      toggleAll(checked) {
        this.emitChange({ categories: checked ? this.filters.map((filter) => filter.id) : [] });
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
  const _hoisted_1$2 = { class: "form-checkboxes-normal" };
  const _hoisted_2$2 = ["aria-label"];
  const _hoisted_3$2 = ["aria-pressed", "onClick"];
  const _hoisted_4$2 = ["innerHTML"];
  const _hoisted_5$2 = { style: { "padding-left": "5px" } };
  const _hoisted_6$2 = { class: "form-check" };
  const _hoisted_7$2 = ["checked"];
  const _hoisted_8$2 = {
    class: "form-check-label",
    for: "notification-filter-all"
  };
  const _hoisted_9$2 = ["id", "checked", "onChange"];
  const _hoisted_10$2 = ["for"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
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
              }, null, 8, _hoisted_4$2)) : vue.createCommentVNode("v-if", true),
              vue.createTextVNode(
                " " + vue.toDisplayString(option.label),
                1
                /* TEXT */
              )
            ], 10, _hoisted_3$2);
          }),
          128
          /* KEYED_FRAGMENT */
        ))
      ], 8, _hoisted_2$2),
      vue.createElementVNode("div", _hoisted_5$2, [
        vue.createElementVNode("div", _hoisted_6$2, [
          vue.createElementVNode("input", {
            id: "notification-filter-all",
            class: "form-check-input",
            type: "checkbox",
            checked: $options.allSelected,
            onChange: _cache[0] || (_cache[0] = ($event) => $options.toggleAll($event.target.checked))
          }, null, 40, _hoisted_7$2),
          vue.createElementVNode(
            "label",
            _hoisted_8$2,
            vue.toDisplayString($options.allLabel),
            1
            /* TEXT */
          )
        ]),
        (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          null,
          vue.renderList($props.filters, (filter) => {
            return vue.openBlock(), vue.createElementBlock("div", {
              key: filter.id,
              class: "form-check"
            }, [
              vue.createElementVNode("input", {
                id: "notification-filter-" + filter.id,
                class: "form-check-input",
                type: "checkbox",
                checked: $props.selected.includes(filter.id),
                onChange: ($event) => $options.toggleCategory(filter.id, $event.target.checked)
              }, null, 40, _hoisted_9$2),
              vue.createElementVNode("label", {
                class: "form-check-label",
                for: "notification-filter-" + filter.id
              }, vue.toDisplayString(filter.title), 9, _hoisted_10$2)
            ]);
          }),
          128
          /* KEYED_FRAGMENT */
        ))
      ])
    ]);
  }
  const NotificationFilter = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const SET_COUNT_EVENT = "humhub:notification:setCount";
  const _sfc_main$1 = {
    components: { NotificationFilter, NotificationList },
    i18nCategories: ["NotificationModule.base", "UserModule.base", "base"],
    props: {
      // First page, inlined by the controller: {results, unseenCount, nextCursor}.
      initial: { type: Object, default: null },
      // [{id, title}] of every notification category the caller can filter by (localized).
      filters: { type: Array, default: () => [] },
      // Server-rendered icon markup: {check, cog, all, unseen, seen}.
      icons: { type: Object, default: () => ({}) },
      settingsUrl: { type: String, required: true },
      pageSize: { type: Number, default: 20 }
    },
    data() {
      return {
        // Everything selected initially, like the server-rendered filter's own default.
        selectedCategories: this.filters.map((filter) => filter.id),
        seen: "",
        unseenCount: this.initial ? Number(this.initial.unseenCount || 0) : 0
      };
    },
    computed: {
      // No filter at all while every category is selected: it would only narrow the list to
      // classes the modules currently register (see the endpoint's own docblock).
      requestCategories() {
        return this.selectedCategories.length === this.filters.length ? null : this.selectedCategories;
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
        return markAsSeen().then(() => {
          this.unseenCount = 0;
          vue$1.events.trigger(SET_COUNT_EVENT, [0]);
          return this.$refs.list.reload();
        }).catch((response) => {
          vue$1.log.error(response, true);
        });
      }
    }
  };
  const _hoisted_1$1 = { class: "row" };
  const _hoisted_2$1 = { class: "col-lg-9 layout-content-container" };
  const _hoisted_3$1 = { class: "panel panel-default" };
  const _hoisted_4$1 = { class: "panel-heading" };
  const _hoisted_5$1 = ["innerHTML"];
  const _hoisted_6$1 = { class: "float-end" };
  const _hoisted_7$1 = ["aria-label", "title"];
  const _hoisted_8$1 = ["innerHTML"];
  const _hoisted_9$1 = ["href", "aria-label", "title"];
  const _hoisted_10$1 = ["innerHTML"];
  const _hoisted_11$1 = { class: "panel-body" };
  const _hoisted_12$1 = ["aria-label"];
  const _hoisted_13$1 = { class: "panel panel-default" };
  const _hoisted_14$1 = { class: "panel-heading" };
  const _hoisted_15$1 = { class: "panel-body" };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_NotificationList = vue.resolveComponent("NotificationList");
    const _component_NotificationFilter = vue.resolveComponent("NotificationFilter");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, [
      vue.createElementVNode("div", _hoisted_2$1, [
        vue.createElementVNode("div", _hoisted_3$1, [
          vue.createElementVNode("div", _hoisted_4$1, [
            vue.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- translated heading with markup, as server-side "),
            vue.createElementVNode("span", { innerHTML: $options.headingLabel }, null, 8, _hoisted_5$1),
            vue.createElementVNode("div", _hoisted_6$1, [
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
                }, null, 8, _hoisted_8$1)
              ], 8, _hoisted_7$1)) : vue.createCommentVNode("v-if", true),
              vue.createElementVNode("a", {
                class: "btn-light btn btn-icon-only btn-sm",
                href: $props.settingsUrl,
                "aria-label": $options.settingsLabel,
                title: $options.settingsLabel
              }, [
                vue.createElementVNode("span", {
                  innerHTML: $props.icons.cog
                }, null, 8, _hoisted_10$1)
              ], 8, _hoisted_9$1)
            ])
          ]),
          vue.createElementVNode("div", _hoisted_11$1, [
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
        vue.createElementVNode("div", _hoisted_13$1, [
          vue.createElementVNode("div", _hoisted_14$1, [
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
          vue.createElementVNode("div", _hoisted_15$1, [
            vue.createVNode(_component_NotificationFilter, {
              filters: $props.filters,
              selected: $data.selectedCategories,
              seen: $data.seen,
              icons: $props.icons,
              onChange: $options.onFilterChange
            }, null, 8, ["filters", "selected", "seen", "icons", "onChange"])
          ])
        ])
      ], 8, _hoisted_12$1)
    ]);
  }
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const clone = (value) => JSON.parse(JSON.stringify(value));
  const SPACES_DEBOUNCE_MS = 600;
  const mergePatch = (target, patch) => {
    Object.entries(patch.categories || {}).forEach(([categoryId, switches]) => {
      target.categories = target.categories || {};
      target.categories[categoryId] = { ...target.categories[categoryId] || {}, ...switches };
    });
    if (patch.spaces !== void 0) {
      target.spaces = patch.spaces;
    }
    return target;
  };
  const _sfc_main = {
    name: "NotificationSettings",
    i18nCategories: ["NotificationModule.base", "base"],
    props: {
      initial: { type: Object, required: true },
      settingsUrl: { type: String, required: true },
      resetUrl: { type: String, default: null },
      resetAllUrl: { type: String, default: null },
      scope: { type: String, default: "user" }
    },
    data() {
      return {
        ...this.stateOf(this.initial),
        expanded: {},
        saveState: "idle",
        resetting: false,
        spacesInputId: "notificationsettings-spaces"
      };
    },
    created() {
      this.knownSpaces = this.spacesOf(this.initial);
      this.saved = this.snapshot();
      this.inFlight = null;
      this.pending = null;
      this.spacesTimer = null;
    },
    beforeUnmount() {
      clearTimeout(this.spacesTimer);
    },
    computed: {
      labels() {
        return {
          title: vue$1.i18n.t("NotificationModule.base", "Notifications"),
          intro: vue$1.i18n.t("NotificationModule.base", "Pick a profile for where your notifications reach you. Any change below turns it into “Custom”."),
          profile: vue$1.i18n.t("NotificationModule.base", "Profile"),
          categories: vue$1.i18n.t("NotificationModule.base", "Categories"),
          fromModules: vue$1.i18n.t("NotificationModule.base", "From modules"),
          alwaysOn: vue$1.i18n.t("NotificationModule.base", "Always on"),
          on: vue$1.i18n.t("NotificationModule.base", "on"),
          off: vue$1.i18n.t("NotificationModule.base", "off"),
          spaces: vue$1.i18n.t("NotificationModule.base", "From these Spaces"),
          spacesPlaceholder: vue$1.i18n.t("NotificationModule.base", "Add a Space…"),
          saving: vue$1.i18n.t("NotificationModule.base", "Saving…"),
          saved: vue$1.i18n.t("base", "Saved"),
          notSaved: vue$1.i18n.t("NotificationModule.base", "Not saved"),
          autosave: vue$1.i18n.t("NotificationModule.base", "Changes are saved right away."),
          reset: vue$1.i18n.t("NotificationModule.base", "Reset to defaults"),
          resetConfirm: vue$1.i18n.t("NotificationModule.base", "Do you want to reset your notification settings to the defaults?"),
          resetAll: vue$1.i18n.t("NotificationModule.base", "Reset all users"),
          resetAllConfirm: vue$1.i18n.t("NotificationModule.base", "Do you want to reset the settings concerning notifications for all users?")
        };
      },
      profiles() {
        return [
          { id: "everything", title: vue$1.i18n.t("NotificationModule.base", "Everything"), description: vue$1.i18n.t("NotificationModule.base", "Every category on all channels.") },
          { id: "recommended", title: vue$1.i18n.t("NotificationModule.base", "Recommended"), description: vue$1.i18n.t("NotificationModule.base", "The defaults of this network.") },
          { id: "important", title: vue$1.i18n.t("NotificationModule.base", "Important only"), description: vue$1.i18n.t("NotificationModule.base", "Only what is addressed to you by e-mail and push.") },
          { id: "custom", title: vue$1.i18n.t("NotificationModule.base", "Custom"), description: vue$1.i18n.t("NotificationModule.base", "Your own choice below.") }
        ];
      },
      showProfiles() {
        return this.scope === "user" && this.defaults !== null;
      },
      // The profile the current switches equal, else `custom`.
      profile() {
        const current = this.switches();
        const match = ["recommended", "everything", "important"].find((id) => this.categories.every((category) => Object.keys(category.channels).every((channelId) => current[category.id][channelId] === this.profileValue(id, category, channelId))));
        return match || "custom";
      },
      statusLabel() {
        return { saving: this.labels.saving, saved: this.labels.saved, error: this.labels.notSaved }[this.saveState] || "";
      },
      statusIcon() {
        return { saving: "ti-loader-2 c-notification-settings__spin", saved: "ti-check", error: "ti-alert-circle" }[this.saveState] || "";
      },
      spacesFilter() {
        return { key: "spaces", label: this.labels.spaces, placeholder: this.labels.spacesPlaceholder, multiple: true };
      }
    },
    methods: {
      // The editable state of a payload: a copy, so a reset can replace it wholesale.
      stateOf(payload) {
        var _a, _b;
        const selected = ((_a = payload.spaces) == null ? void 0 : _a.selected) || [];
        return {
          channels: clone(payload.channels || []),
          categories: clone(payload.categories || []).map((category) => ({ ...category, channels: category.channels || {} })),
          defaults: payload.defaults ? clone(payload.defaults) : null,
          spaces: { enabled: ((_b = payload.spaces) == null ? void 0 : _b.enabled) !== false },
          spaceIds: selected.map((space) => String(space.id))
        };
      },
      applyState(payload) {
        this.knownSpaces = this.spacesOf(payload);
        Object.assign(this, this.stateOf(payload));
        this.saved = this.snapshot();
      },
      spacesOf(payload) {
        var _a;
        return Object.fromEntries((((_a = payload.spaces) == null ? void 0 : _a.selected) || []).map((space) => [String(space.id), space]));
      },
      // `{<category>: {<channel>: bool}}` of the current switches.
      switches() {
        return Object.fromEntries(this.categories.map((category) => [category.id, { ...category.channels }]));
      },
      snapshot() {
        return { categories: this.switches(), spaceIds: [...this.spaceIds] };
      },
      channelsOf(category) {
        return this.channels.filter((channel) => Object.prototype.hasOwnProperty.call(category.channels, channel.id));
      },
      // What the profile sets the channel of the category to.
      profileValue(profileId, category, channelId) {
        var _a, _b;
        if (category.fixed) {
          return true;
        }
        switch (profileId) {
          case "everything":
            return true;
          case "recommended":
            return ((_b = (_a = this.defaults) == null ? void 0 : _a[category.id]) == null ? void 0 : _b[channelId]) !== false;
          case "important":
            return channelId === "web";
          default:
            return category.channels[channelId];
        }
      },
      isExpanded(category) {
        return !category.fixed && !!this.expanded[category.id];
      },
      toggle(category) {
        if (!category.fixed) {
          this.expanded[category.id] = !this.expanded[category.id];
        }
      },
      panelId(category) {
        return `notification-settings-category-${category.id}`;
      },
      category(id) {
        return this.categories.find((category) => category.id === id);
      },
      setSwitch(category, channelId, value) {
        category.channels[channelId] = !!value;
        this.enqueue({ categories: { [category.id]: { [channelId]: !!value } } });
      },
      setSpaces(ids) {
        this.spaceIds = ids.map(String);
        clearTimeout(this.spacesTimer);
        this.spacesTimer = setTimeout(() => {
          this.spacesTimer = null;
          this.enqueue({ spaces: this.spaceIds.map(Number) });
        }, SPACES_DEBOUNCE_MS);
      },
      selectProfile(profileId) {
        if (profileId === "custom" || profileId === this.profile) {
          return;
        }
        const patch = { categories: {} };
        this.categories.filter((category) => !category.fixed).forEach((category) => {
          Object.keys(category.channels).forEach((channelId) => {
            const value = this.profileValue(profileId, category, channelId);
            category.channels[channelId] = value;
            patch.categories[category.id] = patch.categories[category.id] || {};
            patch.categories[category.id][channelId] = profileId === "recommended" ? null : value;
          });
        });
        if (Object.keys(patch.categories).length) {
          this.enqueue(patch);
        }
      },
      // Arrow keys move the choice within the radio group, as with native radio buttons.
      onProfileKeydown(event, profileId) {
        const steps = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 };
        if (!steps[event.key]) {
          return;
        }
        event.preventDefault();
        const ids = this.profiles.map((option) => option.id);
        const next = ids[(ids.indexOf(profileId) + steps[event.key] + ids.length) % ids.length];
        this.selectProfile(next);
        this.$nextTick(() => {
          var _a;
          return (_a = this.$el.querySelector(`[data-profile="${next}"]`)) == null ? void 0 : _a.focus();
        });
      },
      enqueue(patch) {
        this.pending = mergePatch(this.pending || {}, patch);
        this.saveState = "saving";
        this.flush();
      },
      flush() {
        var _a;
        if (this.inFlight || !this.pending) {
          return Promise.resolve();
        }
        const patch = this.pending;
        this.pending = null;
        (_a = this.$refs.form) == null ? void 0 : _a.clearErrors();
        this.inFlight = this.request("patch", this.settingsUrl, patch).then((response) => {
          this.inFlight = null;
          this.remember(patch, response);
          if (this.pending) {
            return this.flush();
          }
          this.saveState = "saved";
          return null;
        }).catch((response) => {
          var _a2;
          this.inFlight = null;
          this.revert(patch);
          this.saveState = "error";
          if (response && response.status === 422) {
            (_a2 = this.$refs.form) == null ? void 0 : _a2.setErrors(response);
          } else {
            vue$1.log.error(response);
          }
          return this.pending ? this.flush() : null;
        });
        return this.inFlight;
      },
      // A saved patch becomes the state failed requests return to; without further changes
      // waiting, the answer is the state.
      remember(patch, response) {
        Object.entries(patch.categories || {}).forEach(([categoryId, switches]) => {
          Object.keys(switches).forEach((channelId) => {
            var _a, _b, _c;
            const answered = (_b = (_a = ((response == null ? void 0 : response.categories) || []).find((category) => category.id === categoryId)) == null ? void 0 : _a.channels) == null ? void 0 : _b[channelId];
            const value = answered !== void 0 ? answered : (_c = this.category(categoryId)) == null ? void 0 : _c.channels[channelId];
            this.saved.categories[categoryId] = { ...this.saved.categories[categoryId] || {}, [channelId]: value };
          });
        });
        if (patch.spaces !== void 0) {
          this.saved.spaceIds = patch.spaces.map(String);
        }
        if (!this.pending && !this.spacesTimer && response && Array.isArray(response.categories)) {
          const expanded = this.expanded;
          this.applyState(response);
          this.expanded = expanded;
        }
      },
      // Puts what a failed patch carried back to the last saved state — unless a later change
      // of the same switch waits to be sent.
      revert(patch) {
        var _a;
        Object.entries(patch.categories || {}).forEach(([categoryId, switches]) => {
          const category = this.category(categoryId);
          Object.keys(switches).forEach((channelId) => {
            var _a2, _b, _c, _d;
            if (category && ((_c = (_b = (_a2 = this.pending) == null ? void 0 : _a2.categories) == null ? void 0 : _b[categoryId]) == null ? void 0 : _c[channelId]) === void 0) {
              category.channels[channelId] = ((_d = this.saved.categories[categoryId]) == null ? void 0 : _d[channelId]) ?? category.channels[channelId];
            }
          });
        });
        if (patch.spaces !== void 0 && ((_a = this.pending) == null ? void 0 : _a.spaces) === void 0 && !this.spacesTimer) {
          this.spaceIds = [...this.saved.spaceIds];
        }
      },
      request(method, url, data) {
        const cfg = data === void 0 ? {} : { data: JSON.stringify(data), contentType: "application/json" };
        return vue$1.client[method](url, cfg);
      },
      reset() {
        return this.confirmAndPost(this.resetUrl, { body: this.labels.resetConfirm }, true);
      },
      resetAll() {
        return this.confirmAndPost(this.resetAllUrl, { body: this.labels.resetAllConfirm }, false);
      },
      confirmAndPost(url, options, replace) {
        if (this.resetting || !url) {
          return Promise.resolve();
        }
        return vue$1.modal.confirm(options).then((confirmed) => {
          if (!confirmed) {
            return null;
          }
          this.resetting = true;
          this.saveState = "saving";
          this.$refs.form.clearErrors();
          return vue$1.client.post(url).then((response) => {
            this.resetting = false;
            if (replace) {
              this.applyState(response);
            }
            this.saveState = "saved";
          }).catch((response) => {
            this.resetting = false;
            this.saveState = "error";
            vue$1.log.error(response);
          });
        });
      },
      searchSpaces(q, pageSize) {
        return vue$1.client.get(vue$1.apiUrl("space", { purpose: "picker", scope: "all", q, pageSize })).then((response) => response.results || []);
      },
      // The selection comes with the payload; an id it does not name (none, normally) is asked
      // for like `SpaceFilterControl` does.
      resolveSpaces(ids) {
        const known = ids.map((id) => this.knownSpaces[id]).filter(Boolean);
        const unknown = ids.filter((id) => !this.knownSpaces[id]);
        if (!unknown.length) {
          return Promise.resolve(known);
        }
        return vue$1.client.get(vue$1.apiUrl("space", { purpose: "picker", scope: "all", ids: unknown.join(","), pageSize: unknown.length })).then((response) => known.concat(response.results || []));
      },
      spaceLabel(space) {
        return space.name;
      },
      spaceImageProps(space) {
        return {
          id: space.id,
          name: space.name,
          url: space.url,
          color: space.color,
          imageUrl: space.imageUrl,
          contentContainerId: space.contentContainerId ?? null
        };
      }
    }
  };
  const _hoisted_1 = {
    key: 0,
    class: "panel panel-default c-notification-settings__card c-notification-settings__intro"
  };
  const _hoisted_2 = { class: "c-notification-settings__head" };
  const _hoisted_3 = { class: "c-notification-settings__title" };
  const _hoisted_4 = { class: "c-notification-settings__lead" };
  const _hoisted_5 = ["aria-label"];
  const _hoisted_6 = ["aria-checked", "tabindex", "data-profile", "onClick", "onKeydown"];
  const _hoisted_7 = { class: "c-notification-settings__profile-text" };
  const _hoisted_8 = { class: "c-notification-settings__profile-title" };
  const _hoisted_9 = { class: "c-notification-settings__profile-description" };
  const _hoisted_10 = { class: "c-notification-settings__head c-notification-settings__categories-head" };
  const _hoisted_11 = { class: "c-notification-settings__subtitle" };
  const _hoisted_12 = {
    key: 0,
    class: "c-notification-settings__divider"
  };
  const _hoisted_13 = ["data-category"];
  const _hoisted_14 = {
    class: "c-notification-settings__category-icon",
    "aria-hidden": "true"
  };
  const _hoisted_15 = { class: "c-notification-settings__category-text" };
  const _hoisted_16 = { class: "c-notification-settings__category-title" };
  const _hoisted_17 = {
    key: 0,
    class: "c-notification-settings__category-description"
  };
  const _hoisted_18 = { class: "c-notification-settings__badges" };
  const _hoisted_19 = ["data-channel"];
  const _hoisted_20 = { class: "visually-hidden" };
  const _hoisted_21 = ["title"];
  const _hoisted_22 = { class: "visually-hidden" };
  const _hoisted_23 = {
    key: 1,
    class: "c-notification-settings__category-state",
    "aria-hidden": "true"
  };
  const _hoisted_24 = ["id"];
  const _hoisted_25 = ["aria-label"];
  const _hoisted_26 = {
    key: 0,
    class: "c-notification-settings__spaces"
  };
  const _hoisted_27 = ["for"];
  const _hoisted_28 = { class: "c-notification-settings__footer" };
  const _hoisted_29 = { class: "c-notification-settings__autosave" };
  const _hoisted_30 = ["disabled"];
  const _hoisted_31 = ["disabled"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_CheckboxField = vue.resolveComponent("CheckboxField");
    const _component_SpaceImage = vue.resolveComponent("SpaceImage");
    const _component_PickerFilterControl = vue.resolveComponent("PickerFilterControl");
    const _component_HumHubForm = vue.resolveComponent("HumHubForm");
    return vue.openBlock(), vue.createBlock(_component_HumHubForm, {
      ref: "form",
      class: vue.normalizeClass(["c-notification-settings", `c-notification-settings--${$props.scope}`]),
      "model-name": "NotificationSettings"
    }, {
      default: vue.withCtx(() => [
        $options.showProfiles ? (vue.openBlock(), vue.createElementBlock("section", _hoisted_1, [
          vue.createElementVNode("div", _hoisted_2, [
            vue.createElementVNode(
              "h2",
              _hoisted_3,
              vue.toDisplayString($options.labels.title),
              1
              /* TEXT */
            ),
            vue.createElementVNode(
              "span",
              {
                class: vue.normalizeClass(["c-notification-settings__status", `is-${$data.saveState}`]),
                role: "status",
                "aria-live": "polite"
              },
              [
                $options.statusLabel ? (vue.openBlock(), vue.createElementBlock(
                  vue.Fragment,
                  { key: 0 },
                  [
                    vue.createElementVNode(
                      "i",
                      {
                        class: vue.normalizeClass(["ti", $options.statusIcon]),
                        "aria-hidden": "true"
                      },
                      null,
                      2
                      /* CLASS */
                    ),
                    vue.createTextVNode(
                      vue.toDisplayString($options.statusLabel),
                      1
                      /* TEXT */
                    )
                  ],
                  64
                  /* STABLE_FRAGMENT */
                )) : vue.createCommentVNode("v-if", true)
              ],
              2
              /* CLASS */
            )
          ]),
          vue.createElementVNode(
            "p",
            _hoisted_4,
            vue.toDisplayString($options.labels.intro),
            1
            /* TEXT */
          ),
          vue.createElementVNode("div", {
            class: "c-notification-settings__profiles",
            role: "radiogroup",
            "aria-label": $options.labels.profile
          }, [
            (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              null,
              vue.renderList($options.profiles, (option) => {
                return vue.openBlock(), vue.createElementBlock("button", {
                  key: option.id,
                  type: "button",
                  role: "radio",
                  class: vue.normalizeClass(["c-notification-settings__profile", { "is-selected": $options.profile === option.id }]),
                  "aria-checked": $options.profile === option.id ? "true" : "false",
                  tabindex: $options.profile === option.id ? 0 : -1,
                  "data-profile": option.id,
                  onClick: ($event) => $options.selectProfile(option.id),
                  onKeydown: ($event) => $options.onProfileKeydown($event, option.id)
                }, [
                  _cache[2] || (_cache[2] = vue.createElementVNode(
                    "span",
                    {
                      class: "c-notification-settings__radio",
                      "aria-hidden": "true"
                    },
                    null,
                    -1
                    /* CACHED */
                  )),
                  vue.createElementVNode("span", _hoisted_7, [
                    vue.createElementVNode(
                      "span",
                      _hoisted_8,
                      vue.toDisplayString(option.title),
                      1
                      /* TEXT */
                    ),
                    vue.createElementVNode(
                      "span",
                      _hoisted_9,
                      vue.toDisplayString(option.description),
                      1
                      /* TEXT */
                    )
                  ])
                ], 42, _hoisted_6);
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ], 8, _hoisted_5)
        ])) : vue.createCommentVNode("v-if", true),
        vue.createElementVNode(
          "section",
          {
            class: vue.normalizeClass(["c-notification-settings__card c-notification-settings__categories", { "panel panel-default": $options.showProfiles }])
          },
          [
            vue.createElementVNode("div", _hoisted_10, [
              vue.createElementVNode(
                "h3",
                _hoisted_11,
                vue.toDisplayString($options.labels.categories),
                1
                /* TEXT */
              ),
              !$options.showProfiles ? (vue.openBlock(), vue.createElementBlock(
                "span",
                {
                  key: 0,
                  class: vue.normalizeClass(["c-notification-settings__status", `is-${$data.saveState}`]),
                  role: "status",
                  "aria-live": "polite"
                },
                [
                  $options.statusLabel ? (vue.openBlock(), vue.createElementBlock(
                    vue.Fragment,
                    { key: 0 },
                    [
                      vue.createElementVNode(
                        "i",
                        {
                          class: vue.normalizeClass(["ti", $options.statusIcon]),
                          "aria-hidden": "true"
                        },
                        null,
                        2
                        /* CLASS */
                      ),
                      vue.createTextVNode(
                        vue.toDisplayString($options.statusLabel),
                        1
                        /* TEXT */
                      )
                    ],
                    64
                    /* STABLE_FRAGMENT */
                  )) : vue.createCommentVNode("v-if", true)
                ],
                2
                /* CLASS */
              )) : vue.createCommentVNode("v-if", true)
            ]),
            (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              null,
              vue.renderList(_ctx.categories, (category, index) => {
                return vue.openBlock(), vue.createElementBlock(
                  vue.Fragment,
                  {
                    key: category.id
                  },
                  [
                    category.module && (index === 0 || !_ctx.categories[index - 1].module) ? (vue.openBlock(), vue.createElementBlock(
                      "h4",
                      _hoisted_12,
                      vue.toDisplayString($options.labels.fromModules),
                      1
                      /* TEXT */
                    )) : vue.createCommentVNode("v-if", true),
                    vue.createElementVNode("div", {
                      class: vue.normalizeClass(["c-notification-settings__category", { "is-expanded": $options.isExpanded(category), "is-fixed": category.fixed }]),
                      "data-category": category.id
                    }, [
                      (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(category.fixed ? "div" : "button"), {
                        type: category.fixed ? null : "button",
                        class: "c-notification-settings__category-head",
                        "aria-expanded": category.fixed ? null : $options.isExpanded(category) ? "true" : "false",
                        "aria-controls": category.fixed ? null : $options.panelId(category),
                        onClick: ($event) => $options.toggle(category)
                      }, {
                        default: vue.withCtx(() => [
                          vue.createElementVNode("span", _hoisted_14, [
                            vue.createElementVNode(
                              "i",
                              {
                                class: vue.normalizeClass(["ti", category.icon])
                              },
                              null,
                              2
                              /* CLASS */
                            )
                          ]),
                          vue.createElementVNode("span", _hoisted_15, [
                            vue.createElementVNode(
                              "span",
                              _hoisted_16,
                              vue.toDisplayString(category.title),
                              1
                              /* TEXT */
                            ),
                            category.description ? (vue.openBlock(), vue.createElementBlock(
                              "span",
                              _hoisted_17,
                              vue.toDisplayString(category.description),
                              1
                              /* TEXT */
                            )) : vue.createCommentVNode("v-if", true)
                          ]),
                          vue.createElementVNode("span", _hoisted_18, [
                            (vue.openBlock(true), vue.createElementBlock(
                              vue.Fragment,
                              null,
                              vue.renderList($options.channelsOf(category), (channel) => {
                                return vue.openBlock(), vue.createElementBlock("span", {
                                  key: channel.id,
                                  class: vue.normalizeClass(["c-notification-settings__badge", { "is-on": category.channels[channel.id] }]),
                                  "data-channel": channel.id
                                }, [
                                  vue.createTextVNode(
                                    vue.toDisplayString(channel.title),
                                    1
                                    /* TEXT */
                                  ),
                                  vue.createElementVNode(
                                    "span",
                                    _hoisted_20,
                                    ": " + vue.toDisplayString(category.channels[channel.id] ? $options.labels.on : $options.labels.off),
                                    1
                                    /* TEXT */
                                  )
                                ], 10, _hoisted_19);
                              }),
                              128
                              /* KEYED_FRAGMENT */
                            ))
                          ]),
                          category.fixed ? (vue.openBlock(), vue.createElementBlock("span", {
                            key: 0,
                            class: "c-notification-settings__category-state",
                            title: $options.labels.alwaysOn
                          }, [
                            _cache[3] || (_cache[3] = vue.createElementVNode(
                              "i",
                              {
                                class: "ti ti-lock",
                                "aria-hidden": "true"
                              },
                              null,
                              -1
                              /* CACHED */
                            )),
                            vue.createElementVNode(
                              "span",
                              _hoisted_22,
                              vue.toDisplayString($options.labels.alwaysOn),
                              1
                              /* TEXT */
                            )
                          ], 8, _hoisted_21)) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_23, [
                            vue.createElementVNode(
                              "i",
                              {
                                class: vue.normalizeClass(["ti", $options.isExpanded(category) ? "ti-chevron-up" : "ti-chevron-down"])
                              },
                              null,
                              2
                              /* CLASS */
                            )
                          ]))
                        ]),
                        _: 2
                        /* DYNAMIC */
                      }, 1032, ["type", "aria-expanded", "aria-controls", "onClick"])),
                      !category.fixed && $options.isExpanded(category) ? (vue.openBlock(), vue.createElementBlock("div", {
                        key: 0,
                        id: $options.panelId(category),
                        class: "c-notification-settings__category-body"
                      }, [
                        vue.createElementVNode("div", {
                          class: "c-notification-settings__channels",
                          role: "group",
                          "aria-label": category.title
                        }, [
                          (vue.openBlock(true), vue.createElementBlock(
                            vue.Fragment,
                            null,
                            vue.renderList($options.channelsOf(category), (channel) => {
                              return vue.openBlock(), vue.createBlock(_component_CheckboxField, {
                                key: channel.id,
                                "model-value": category.channels[channel.id],
                                attribute: `categories.${category.id}.${channel.id}`,
                                label: channel.title,
                                "onUpdate:modelValue": ($event) => $options.setSwitch(category, channel.id, $event)
                              }, null, 8, ["model-value", "attribute", "label", "onUpdate:modelValue"]);
                            }),
                            128
                            /* KEYED_FRAGMENT */
                          ))
                        ], 8, _hoisted_25),
                        category.id === "content" && _ctx.spaces.enabled ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_26, [
                          vue.createElementVNode("label", {
                            for: $data.spacesInputId,
                            class: "form-label c-notification-settings__spaces-label"
                          }, vue.toDisplayString($options.labels.spaces), 9, _hoisted_27),
                          vue.createVNode(_component_PickerFilterControl, {
                            "model-value": _ctx.spaceIds,
                            filter: $options.spacesFilter,
                            "input-id": $data.spacesInputId,
                            multiple: true,
                            "keep-placeholder": "",
                            search: $options.searchSpaces,
                            resolve: $options.resolveSpaces,
                            "item-label": $options.spaceLabel,
                            icon: "ti-users-group",
                            block: "c-space-filter",
                            "onUpdate:modelValue": $options.setSpaces
                          }, {
                            option: vue.withCtx(({ item }) => [
                              vue.createVNode(
                                _component_SpaceImage,
                                vue.mergeProps({ ref_for: true }, $options.spaceImageProps(item), {
                                  width: 24,
                                  link: false
                                }),
                                null,
                                16
                                /* FULL_PROPS */
                              )
                            ]),
                            chip: vue.withCtx(({ item }) => [
                              vue.createVNode(
                                _component_SpaceImage,
                                vue.mergeProps({ ref_for: true }, $options.spaceImageProps(item), {
                                  width: 18,
                                  link: false
                                }),
                                null,
                                16
                                /* FULL_PROPS */
                              )
                            ]),
                            _: 1
                            /* STABLE */
                          }, 8, ["model-value", "filter", "input-id", "search", "resolve", "item-label", "onUpdate:modelValue"])
                        ])) : vue.createCommentVNode("v-if", true)
                      ], 8, _hoisted_24)) : vue.createCommentVNode("v-if", true)
                    ], 10, _hoisted_13)
                  ],
                  64
                  /* STABLE_FRAGMENT */
                );
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ],
          2
          /* CLASS */
        ),
        vue.createElementVNode("div", _hoisted_28, [
          vue.createElementVNode(
            "span",
            _hoisted_29,
            vue.toDisplayString($options.labels.autosave),
            1
            /* TEXT */
          ),
          $props.scope === "user" && $props.resetUrl ? (vue.openBlock(), vue.createElementBlock("button", {
            key: 0,
            type: "button",
            class: "btn btn-light c-notification-settings__reset",
            disabled: $data.resetting,
            onClick: _cache[0] || (_cache[0] = (...args) => $options.reset && $options.reset(...args))
          }, vue.toDisplayString($options.labels.reset), 9, _hoisted_30)) : vue.createCommentVNode("v-if", true),
          $props.scope === "global" && $props.resetAllUrl ? (vue.openBlock(), vue.createElementBlock("button", {
            key: 1,
            type: "button",
            class: "btn btn-danger c-notification-settings__reset-all",
            disabled: $data.resetting,
            onClick: _cache[1] || (_cache[1] = (...args) => $options.resetAll && $options.resetAll(...args))
          }, vue.toDisplayString($options.labels.resetAll), 9, _hoisted_31)) : vue.createCommentVNode("v-if", true)
        ])
      ]),
      _: 1
      /* STABLE */
    }, 8, ["class"]);
  }
  const C2 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("NotificationMenu", C0);
  vue$1.register("NotificationOverview", C1);
  vue$1.register("NotificationSettings", C2);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.notification.vue.js.map
