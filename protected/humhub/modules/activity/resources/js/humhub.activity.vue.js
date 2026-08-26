/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from activity/vue/ via `grunt build-vue --module=activity`.
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
      // Serialized activity (ActivitySerializer::activity()).
      activity: { type: Object, required: true },
      // Whether the space badge is rendered - false inside a container.
      showSpace: { type: Boolean, default: true }
    },
    computed: {
      absoluteTime() {
        return this.activity.createdAt ? new Date(this.activity.createdAt).toLocaleString() : "";
      }
    }
  };
  const _hoisted_1$1 = ["data-activity-id"];
  const _hoisted_2$1 = { class: "d-flex activity-box-entry" };
  const _hoisted_3$1 = { class: "flex-shrink-0 me-3 pt-1 img-profile-space" };
  const _hoisted_4$1 = { class: "flex-grow-1 text-break" };
  const _hoisted_5$1 = ["innerHTML"];
  const _hoisted_6$1 = ["datetime", "title"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_UserImage = vue.resolveComponent("UserImage");
    const _component_SpaceImage = vue.resolveComponent("SpaceImage");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: "activity-entry",
      "data-activity-id": $props.activity.id
    }, [
      (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent($props.activity.url ? "a" : "div"), {
        href: $props.activity.url || null
      }, {
        default: vue.withCtx(() => [
          vue.createElementVNode("div", _hoisted_2$1, [
            vue.createElementVNode("div", _hoisted_3$1, [
              $props.activity.user ? (vue.openBlock(), vue.createBlock(
                _component_UserImage,
                vue.mergeProps({ key: 0 }, $props.activity.user, {
                  width: 32,
                  link: false
                }),
                null,
                16
                /* FULL_PROPS */
              )) : vue.createCommentVNode("v-if", true),
              $props.showSpace && $props.activity.space ? (vue.openBlock(), vue.createBlock(
                _component_SpaceImage,
                vue.mergeProps({ key: 1 }, $props.activity.space, {
                  width: 20,
                  link: false,
                  class: "img-space"
                }),
                null,
                16
                /* FULL_PROPS */
              )) : vue.createCommentVNode("v-if", true)
            ]),
            vue.createElementVNode("div", _hoisted_4$1, [
              vue.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- server-rendered sentence, see docblock "),
              vue.createElementVNode("span", {
                innerHTML: $props.activity.message
              }, null, 8, _hoisted_5$1),
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
                datetime: $props.activity.createdAt,
                title: $options.absoluteTime
              }, vue.toDisplayString($options.absoluteTime), 9, _hoisted_6$1)
            ])
          ])
        ]),
        _: 1
        /* STABLE */
      }, 8, ["href"]))
    ], 8, _hoisted_1$1);
  }
  const ActivityEntry = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const fetchActivities = ({ cursor = null, limit = null, containerGuid = null } = {}) => {
    const params = {};
    if (cursor) {
      params.cursor = cursor;
    }
    if (limit) {
      params.limit = limit;
    }
    if (containerGuid) {
      params.containerGuid = containerGuid;
    }
    return vue$1.client.get(vue$1.apiUrl("activity", params)).then(normalizeWindow);
  };
  const normalizeWindow = (response) => ({
    results: Array.isArray(response.results) ? response.results : [],
    nextCursor: response.nextCursor ?? null
  });
  const _sfc_main = {
    components: { ActivityEntry },
    i18nCategories: ["ActivityModule.base", "base"],
    props: {
      // First page as `ActivityWindowService::window()` returns it: {results, nextCursor}.
      initial: { type: Object, default: () => ({ results: [], nextCursor: null }) },
      // Guid of the container the box is scoped to, empty on the dashboard.
      containerGuid: { type: String, default: "" },
      // Entries requested per page after the first.
      pageSize: { type: Number, default: 10 },
      // Rendered `PanelMenu` widget.
      panelMenuHtml: { type: String, default: "" }
    },
    data() {
      return {
        entries: Array.isArray(this.initial.results) ? [...this.initial.results] : [],
        cursor: this.initial.nextCursor ?? null,
        loading: false,
        observer: null
      };
    },
    computed: {
      hasMore() {
        return this.cursor !== null;
      },
      headingLabel() {
        return vue$1.i18n.t("ActivityModule.base", "<strong>Latest</strong> activities");
      },
      emptyLabel() {
        return vue$1.i18n.t("ActivityModule.base", "There are no activities yet.");
      },
      loadingLabel() {
        return vue$1.i18n.t("base", "Loading...");
      }
    },
    mounted() {
      this.armObserver();
    },
    beforeUnmount() {
      this.disconnectObserver();
    },
    methods: {
      /**
       * (Re)observes the sentinel. Re-arming rather than observing once is deliberate: the
       * callback then runs against the CURRENT intersection state, so a short page that
       * leaves the sentinel in view keeps the list loading instead of stalling.
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
            if (entries.some((entry) => entry.isIntersecting)) {
              this.loadMore();
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
      loadMore() {
        if (this.loading || !this.cursor) {
          return;
        }
        this.loading = true;
        fetchActivities({
          cursor: this.cursor,
          limit: this.pageSize,
          containerGuid: this.containerGuid || null
        }).then(({ results, nextCursor }) => {
          this.entries.push(...results);
          this.cursor = nextCursor;
        }).catch((error) => {
          vue$1.log.error(error, true);
        }).finally(() => {
          this.loading = false;
          this.armObserver();
        });
      }
    }
  };
  const _hoisted_1 = {
    id: "panel-activities",
    class: "panel panel-default panel-activities"
  };
  const _hoisted_2 = ["innerHTML"];
  const _hoisted_3 = ["innerHTML"];
  const _hoisted_4 = { class: "panel-body p-0 pb-1 collapse show" };
  const _hoisted_5 = {
    id: "activity-box-content",
    ref: "list",
    class: "hh-list activities"
  };
  const _hoisted_6 = {
    key: 0,
    class: "p-3 m-0"
  };
  const _hoisted_7 = {
    key: 1,
    class: "text-center p-2"
  };
  const _hoisted_8 = {
    class: "visually-hidden",
    role: "status"
  };
  const _hoisted_9 = {
    key: 2,
    ref: "sentinel",
    class: "stream-end"
  };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_ActivityEntry = vue.resolveComponent("ActivityEntry");
    const _directive_additions = vue.resolveDirective("additions");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      vue.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- server-rendered PanelMenu, see docblock "),
      $props.panelMenuHtml ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", {
        key: 0,
        innerHTML: $props.panelMenuHtml
      }, null, 8, _hoisted_2)), [
        [_directive_additions]
      ]) : vue.createCommentVNode("v-if", true),
      vue.createCommentVNode(" eslint-disable-next-line vue/no-v-html -- localized heading with markup, see docblock "),
      vue.createElementVNode("div", {
        class: "panel-heading",
        innerHTML: $options.headingLabel
      }, null, 8, _hoisted_3),
      vue.createElementVNode("div", _hoisted_4, [
        vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_5, [
          _cache[1] || (_cache[1] = vue.createElementVNode(
            "hr",
            { class: "m-0" },
            null,
            -1
            /* CACHED */
          )),
          !$data.entries.length && !$data.loading ? (vue.openBlock(), vue.createElementBlock(
            "p",
            _hoisted_6,
            vue.toDisplayString($options.emptyLabel),
            1
            /* TEXT */
          )) : vue.createCommentVNode("v-if", true),
          (vue.openBlock(true), vue.createElementBlock(
            vue.Fragment,
            null,
            vue.renderList($data.entries, (entry) => {
              return vue.openBlock(), vue.createBlock(_component_ActivityEntry, {
                key: entry.key,
                activity: entry,
                "show-space": !$props.containerGuid
              }, null, 8, ["activity", "show-space"]);
            }),
            128
            /* KEYED_FRAGMENT */
          )),
          $data.loading ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_7, [
            _cache[0] || (_cache[0] = vue.createElementVNode(
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
          $options.hasMore ? (vue.openBlock(), vue.createElementBlock(
            "div",
            _hoisted_9,
            null,
            512
            /* NEED_PATCH */
          )) : vue.createCommentVNode("v-if", true)
        ])), [
          [_directive_additions]
        ])
      ])
    ]);
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("ActivityBox", C0);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.activity.vue.js.map
