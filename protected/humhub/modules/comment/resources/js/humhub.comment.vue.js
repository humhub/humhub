/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from comment/vue/ via `grunt build-vue --module=comment`.
 * See docs/develop/ui-js-vuejs.md
 */
(function(vue, vue$1) {
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
      permalink: { type: String, required: true },
      canEdit: { type: Boolean, default: false },
      canDelete: { type: Boolean, default: false },
      canAdminDelete: { type: Boolean, default: false }
    },
    emits: ["edit", "delete", "admin-delete"],
    computed: {
      toggleMenuLabel() {
        return vue.i18n.t("base", "Toggle comment menu");
      },
      permalinkLabel() {
        return vue.i18n.t("CommentModule.base", "Permalink");
      },
      permalinkTitle() {
        return vue.i18n.t("CommentModule.base", "<strong>Permalink</strong> to this comment");
      },
      editLabel() {
        return vue.i18n.t("CommentModule.base", "Edit");
      },
      deleteLabel() {
        return vue.i18n.t("CommentModule.base", "Delete");
      }
    },
    methods: {
      onEdit() {
        this.$emit("edit");
      },
      onDelete() {
        this.$emit(this.canAdminDelete ? "admin-delete" : "delete");
      }
    }
  };
  const _hoisted_1$3 = ["data-content-permalink", "data-content-permalink-title"];
  const _hoisted_2$3 = { key: 0 };
  const _hoisted_3$3 = { key: 1 };
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DropdownMenu = vue$1.resolveComponent("DropdownMenu");
    return vue$1.openBlock(), vue$1.createElementBlock(
      vue$1.Fragment,
      null,
      [
        _cache[2] || (_cache[2] = vue$1.createElementVNode(
          "div",
          { class: "comment-entry-loader float-end" },
          null,
          -1
          /* CACHED */
        )),
        vue$1.createVNode(_component_DropdownMenu, { "toggle-aria-label": $options.toggleMenuLabel }, {
          default: vue$1.withCtx(() => [
            vue$1.createElementVNode("li", null, [
              vue$1.createCommentVNode('\n                Plain anchor reusing the exact legacy attributes\n                (data-action-click="content.permalink" + the two\n                data-content-permalink* values) instead of a\n                Vue-owned click handler: humhub.action.js binds the\n                [data-action-click] delegate on `document` itself\n                (see bindAction(document, \'click\', ...) in\n                humhub.action.js), so it already fires for anchors\n                injected anywhere in the DOM, Vue-rendered islands\n                included, with zero extra wiring. The `content`\n                module (ui.content) is always loaded page-wide\n                wherever comments can appear, so this "just works".\n            '),
              vue$1.createElementVNode("a", {
                href: "#",
                class: "dropdown-item",
                "data-action-click": "content.permalink",
                "data-content-permalink": $props.permalink,
                "data-content-permalink-title": $options.permalinkTitle
              }, vue$1.toDisplayString($options.permalinkLabel), 9, _hoisted_1$3)
            ]),
            $props.canEdit ? (vue$1.openBlock(), vue$1.createElementBlock("li", _hoisted_2$3, [
              vue$1.createElementVNode(
                "a",
                {
                  href: "#",
                  class: "dropdown-item",
                  onClick: _cache[0] || (_cache[0] = vue$1.withModifiers((...args) => $options.onEdit && $options.onEdit(...args), ["prevent"]))
                },
                vue$1.toDisplayString($options.editLabel),
                1
                /* TEXT */
              )
            ])) : vue$1.createCommentVNode("v-if", true),
            $props.canDelete ? (vue$1.openBlock(), vue$1.createElementBlock("li", _hoisted_3$3, [
              vue$1.createElementVNode(
                "a",
                {
                  href: "#",
                  class: "dropdown-item",
                  onClick: _cache[1] || (_cache[1] = vue$1.withModifiers((...args) => $options.onDelete && $options.onDelete(...args), ["prevent"]))
                },
                vue$1.toDisplayString($options.deleteLabel),
                1
                /* TEXT */
              )
            ])) : vue$1.createCommentVNode("v-if", true)
          ]),
          _: 1
          /* STABLE */
        }, 8, ["toggle-aria-label"])
      ],
      64
      /* STABLE_FRAGMENT */
    );
  }
  const CommentControls = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = {
    props: {
      shellHtml: { type: String, required: true },
      contentId: { type: Number, required: true },
      parentCommentId: { type: Number, default: null },
      // When set, this form edits an existing comment instead of creating one.
      editCommentId: { type: Number, default: null },
      // Edit mode only: the raw markdown to prefill the editor with once booted.
      initialMessage: { type: String, default: null },
      // Server-rendered submit-icon HTML (see "Submit button" docblock section above).
      submitIconHtml: { type: String, default: null }
    },
    emits: ["created", "updated"],
    data() {
      return {
        busy: false,
        errors: {},
        // Resolved once in mounted() - see the "Submit button placement" docblock
        // section above. `null` until then/if the shell has no button-group
        // container, which Teleport's `disabled` prop treats as "render in place".
        teleportTarget: null
      };
    },
    computed: {
      hasErrors() {
        return Object.keys(this.errors).length > 0;
      },
      errorMessages() {
        return Object.values(this.errors).flat();
      },
      // Same key the legacy submit button's aria-label used (see the
      // "Submit button" docblock section above) - NOT a CommentModule.base
      // key. CommentSection preloads 'ContentModule.base' alongside its
      // own category for exactly this.
      sendLabel() {
        return vue.i18n.t("ContentModule.base", "Submit");
      }
    },
    mounted() {
      this.formEl = this.$refs.wrapper.$el.querySelector("form");
      if (this.formEl) {
        this.formEl.addEventListener("submit", this.onSubmit);
      }
      this.teleportTarget = this.$refs.wrapper.$el.querySelector(".richtext-create-buttons");
      if (this.initialMessage !== null) {
        this.$nextTick(() => {
          if (this.$refs.wrapper) {
            this.$refs.wrapper.setValue(this.initialMessage);
          }
        });
      }
    },
    beforeUnmount() {
      if (this.formEl) {
        this.formEl.removeEventListener("submit", this.onSubmit);
      }
    },
    methods: {
      onSubmit(event) {
        if (event) {
          event.preventDefault();
        }
        if (this.busy) {
          return;
        }
        const isEdit = this.editCommentId !== null;
        const endpoint = isEdit ? "/comment/comment/update" : "/comment/comment/create";
        const params = isEdit ? { id: this.editCommentId } : { contentId: this.contentId };
        if (!isEdit && this.parentCommentId !== null) {
          params.parentCommentId = this.parentCommentId;
        }
        this.busy = true;
        this.errors = {};
        vue.client.post(vue.url(endpoint, params), {
          data: {
            message: this.$refs.wrapper.getValue(),
            fileList: this.$refs.wrapper.getFileGuids()
          }
        }).then((comment) => {
          this.busy = false;
          if (!isEdit) {
            this.clear();
          }
          this.$emit(isEdit ? "updated" : "created", comment);
        }).catch((response) => {
          this.busy = false;
          const errors = response && (response.errors || response.error && response.error.errors);
          if (response && response.status === 422 && errors) {
            this.errors = errors;
          } else {
            vue.log.error(response, true);
          }
        });
      },
      /** Proxies to the wrapper so callers (reply toggle, section toggle) don't touch jQuery/legacy widgets. */
      focus() {
        if (this.$refs.wrapper) {
          this.$refs.wrapper.focus();
        }
      },
      /**
       * Proxies to the wrapper's clear() - blanks the editor/uploads AND resets the
       * unsaved-changes guard baseline (see this component's own "Unsaved-changes guard"
       * docblock section). Called both on a successful create/reply submit (below) and by
       * CommentEntry when a reply/edit form is discarded without submitting.
       */
      clear() {
        if (this.$refs.wrapper) {
          this.$refs.wrapper.clear();
        }
      }
    }
  };
  const _hoisted_1$2 = ["aria-label", "disabled"];
  const _hoisted_2$2 = ["innerHTML"];
  const _hoisted_3$2 = {
    key: 0,
    class: "invalid-feedback d-block"
  };
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_LegacyFormWrapper = vue$1.resolveComponent("LegacyFormWrapper");
    return vue$1.openBlock(), vue$1.createElementBlock(
      vue$1.Fragment,
      null,
      [
        vue$1.createVNode(_component_LegacyFormWrapper, {
          ref: "wrapper",
          "shell-html": $props.shellHtml
        }, null, 8, ["shell-html"]),
        (vue$1.openBlock(), vue$1.createBlock(vue$1.Teleport, {
          to: $data.teleportTarget,
          disabled: !$data.teleportTarget
        }, [
          vue$1.createElementVNode("button", {
            type: "button",
            class: vue$1.normalizeClass(["btn btn-accent btn-comment-submit btn-sm", { "btn-icon-only": $props.submitIconHtml }]),
            "aria-label": $options.sendLabel,
            disabled: $data.busy,
            onClick: _cache[0] || (_cache[0] = (...args) => $options.onSubmit && $options.onSubmit(...args))
          }, [
            $props.submitIconHtml ? (vue$1.openBlock(), vue$1.createElementBlock("span", {
              key: 0,
              innerHTML: $props.submitIconHtml
            }, null, 8, _hoisted_2$2)) : (vue$1.openBlock(), vue$1.createElementBlock(
              vue$1.Fragment,
              { key: 1 },
              [
                vue$1.createTextVNode(
                  vue$1.toDisplayString($options.sendLabel),
                  1
                  /* TEXT */
                )
              ],
              64
              /* STABLE_FRAGMENT */
            ))
          ], 10, _hoisted_1$2)
        ], 8, ["to", "disabled"])),
        $options.hasErrors ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_3$2, [
          (vue$1.openBlock(true), vue$1.createElementBlock(
            vue$1.Fragment,
            null,
            vue$1.renderList($options.errorMessages, (message, index) => {
              return vue$1.openBlock(), vue$1.createElementBlock(
                "div",
                { key: index },
                vue$1.toDisplayString(message),
                1
                /* TEXT */
              );
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ])) : vue$1.createCommentVNode("v-if", true)
      ],
      64
      /* STABLE_FRAGMENT */
    );
  }
  const CommentForm = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = {
    name: "CommentEntry",
    components: { CommentControls, CommentForm },
    inject: {
      commentRevisions: { default: () => ({}) },
      bumpCommentRevision: { default: () => () => {
      } },
      pruneCommentRevision: { default: () => () => {
      } },
      adjustTotal: { default: () => () => {
      } },
      registerKnownId: { default: () => () => {
      } },
      isKnownId: { default: () => () => false }
    },
    props: {
      comment: { type: Object, required: true },
      canComment: { type: Boolean, default: false },
      formShellHtml: { type: String, default: null },
      submitIconHtml: { type: String, default: null },
      pageSize: { type: Number, default: 10 },
      // A reply (one level deep) never gets its own reply toggle or further
      // nesting - the server enforces at most one level (see
      // CommentController::actionCreate()).
      isNested: { type: Boolean, default: false },
      // Permalink anchor target - a persistent CSS affordance (the legacy
      // `comment-current` class set via Comments widget's
      // `$highlightCommentId`), not the same thing as the *temporary*
      // flash `additions.highlight()` applies after an inline edit
      // (Comment.prototype.editSubmit in humhub.comment.js) - that flash
      // is an edit-flow concern, out of scope until P2-5 wires editing.
      highlighted: { type: Boolean, default: false }
    },
    emits: ["entry-removed", "entry-updated"],
    data() {
      return {
        replyOpen: false,
        editing: false,
        editMessage: null,
        childItems: this.comment.children ? [...this.comment.children.items] : [],
        childTotal: this.comment.children ? this.comment.children.total : 0,
        childRemainingNext: this.comment.children ? this.comment.children.total - this.comment.children.items.length : 0,
        // Initial value trusts the server's own preview flag verbatim
        // (CommentJsonService::serializeChildren()'s `total > count($items)`);
        // recomputed with the same formula after each load-more below,
        // rather than solely trusting `nextCount`, so a concurrent
        // create/delete between requests can't leave a stale gate.
        childHasMore: this.comment.children ? this.comment.children.hasMore : false,
        busyReplies: false,
        busyReveal: false,
        busyEdit: false
      };
    },
    computed: {
      showReplyToggle() {
        return !this.isNested && this.canComment;
      },
      blockedLabel() {
        return vue.i18n.t("CommentModule.base", "Comment of blocked user.");
      },
      showLabel() {
        return vue.i18n.t("CommentModule.base", "Show");
      },
      readMoreLabel() {
        return vue.i18n.t("CommentModule.base", "Read full comment...");
      },
      replyLabel() {
        return vue.i18n.t("CommentModule.base", "Reply");
      },
      cancelEditLabel() {
        return vue.i18n.t("CommentModule.base", "Cancel Edit");
      },
      // Same wording/category/placeholder as CommentList's own "show next"
      // link: the legacy nested Comments::widget() reused the exact same
      // ShowMore strings for children, there never was a distinct
      // "replies" message key.
      moreRepliesLabel() {
        return vue.i18n.t("CommentModule.base", "Show next {count} comments", { count: this.childRemainingNext });
      },
      // No server-formatted absolute time in the JSON payload (only ISO
      // `createdAt` - see plan §"Timestamps") - formatted client-side via
      // the browser locale/timezone (documented parity gap vs.
      // TimeAgo::getFullDateTime(), which uses the HumHub profile
      // timezone). This text is only ever visible for an instant: v-additions
      // runs the real `timeago` addition (registered selector-less in
      // humhub.ui.additions.js, dispatched per-element through the generic
      // `[data-ui-addition]` addition - see TimeAgo::renderTimeAgo()'s own
      // `data-ui-addition="timeago"` markup, reproduced above) on mount,
      // which immediately overwrites it with a live relative time.
      absoluteTime() {
        return new Date(this.comment.createdAt).toLocaleString();
      },
      // Same client-side-formatting choice as `absoluteTime` above (no server-formatted
      // string in the payload - see CommentJsonService's own `updatedAt` docblock note),
      // for the same documented parity gap vs. UpdatedIcon::getByDated()'s server/profile-
      // timezone-formatted tooltip. `null` (no `title` attribute) whenever the comment
      // isn't edited, since `updatedAt` is only ever set in that case.
      updatedAtTitle() {
        return this.comment.updatedAt ? new Date(this.comment.updatedAt).toLocaleString() : null;
      },
      // Normalizes a missing `online` field (e.g. a fixture predating this field) to the
      // same "no overlay" null the server sends when the online-status feature is
      // disabled or the viewer is looking at their own comment - see
      // CommentJsonService::serializeOnlineStatus().
      isOnline() {
        const online = this.comment.author && this.comment.author.online;
        return online === true || online === false ? online : null;
      },
      // Mirrors user\widgets\Image::run()'s aria-label/title pair for the online-status
      // overlay span - same 'UserModule.base' keys, preloaded by CommentSection.
      onlineLabel() {
        if (this.isOnline === null) {
          return null;
        }
        return this.isOnline ? vue.i18n.t("UserModule.base", "Online") : vue.i18n.t("UserModule.base", "Offline");
      }
    },
    mounted() {
      if (this.highlighted && typeof this.$el.scrollIntoView === "function") {
        this.$el.scrollIntoView({ block: "center" });
      }
    },
    methods: {
      revisionKey(comment) {
        return comment.id + ":" + (this.commentRevisions[comment.id] || 0);
      },
      reveal() {
        if (this.busyReveal) {
          return;
        }
        this.busyReveal = true;
        vue.client.get(vue.url("/comment/comment/info", { id: this.comment.id, showBlocked: 1 })).then((comment) => {
          this.$emit("entry-updated", { id: this.comment.id, comment });
        }).catch((e) => {
          vue.log.error(e, true);
        }).finally(() => {
          this.busyReveal = false;
        });
      },
      onEdit() {
        if (this.busyEdit) {
          return;
        }
        this.busyEdit = true;
        vue.client.get(vue.url("/comment/comment/update", { id: this.comment.id })).then((response) => {
          this.editMessage = response.message;
          this.editing = true;
        }).catch((e) => {
          vue.log.error(e, true);
        }).finally(() => {
          this.busyEdit = false;
        });
      },
      cancelEdit() {
        if (this.$refs.editForm) {
          this.$refs.editForm.clear();
        }
        this.editing = false;
        this.editMessage = null;
      },
      onEditSaved(comment) {
        this.editing = false;
        this.editMessage = null;
        this.$emit("entry-updated", { id: this.comment.id, comment });
      },
      onReplyCreated(comment) {
        if (this.isKnownId(comment.id)) {
          return;
        }
        this.childItems.push(comment);
        this.childTotal += 1;
        this.childHasMore = this.childTotal > this.childItems.length;
        this.adjustTotal(1);
        this.registerKnownId(comment.id);
      },
      onChildRemoved(id) {
        this.childItems = this.childItems.filter((child) => child.id !== id);
        this.childTotal = Math.max(0, this.childTotal - 1);
        this.childHasMore = this.childTotal > this.childItems.length;
        this.pruneCommentRevision(id);
      },
      onChildUpdated({ id, comment }) {
        const index = this.childItems.findIndex((child) => child.id === id);
        if (index !== -1) {
          this.childItems.splice(index, 1, comment);
        }
        this.bumpCommentRevision(id);
      },
      onDelete() {
        vue.modal.confirm({
          header: vue.i18n.t("CommentModule.base", "<strong>Confirm</strong> comment deleting"),
          body: vue.i18n.t("CommentModule.base", "Do you really want to delete this comment?"),
          confirmText: vue.i18n.t("CommentModule.base", "Delete"),
          cancelText: vue.i18n.t("CommentModule.base", "Cancel")
        }).then((confirmed) => {
          if (confirmed) {
            return this.performDelete();
          }
        }).catch((e) => {
          vue.log.error(e, true);
        });
      },
      onAdminDelete() {
        vue.client.get(vue.url("/comment/comment/get-admin-delete-modal", { id: this.comment.id })).then((response) => vue.modal.confirm(response).then((confirmed) => {
          if (!confirmed) {
            return;
          }
          const fields = {};
          jQuery("#globalModalConfirm form").serializeArray().forEach(({ name, value }) => {
            fields[name] = value;
          });
          return this.performDelete(fields);
        })).catch((e) => {
          vue.log.error(e, true);
        });
      },
      performDelete(extraFields) {
        const cfg = extraFields ? { data: extraFields } : void 0;
        return vue.client.post(vue.url("/comment/comment/delete", { id: this.comment.id }), cfg).then((response) => {
          if (!response || !response.success) {
            vue.log.error("Comment delete failed", response, true);
            return;
          }
          this.adjustTotal(-(1 + (this.isNested ? 0 : this.childTotal)));
          this.$emit("entry-removed", this.comment.id);
        }).catch((e) => {
          vue.log.error(e, true);
        });
      },
      loadMoreReplies() {
        if (this.busyReplies || this.childItems.length === 0) {
          return;
        }
        this.busyReplies = true;
        const cursor = this.childItems[this.childItems.length - 1].id;
        vue.client.get(vue.url("/comment/comment/list", {
          contentId: this.comment.contentId,
          parentCommentId: this.comment.id,
          commentId: cursor,
          direction: "next",
          pageSize: this.pageSize
        })).then((response) => {
          this.childItems = [...this.childItems, ...response.comments];
          this.childTotal = response.total;
          this.childRemainingNext = response.nextCount;
          this.childHasMore = this.childTotal > this.childItems.length;
        }).catch((e) => {
          vue.log.error(e, true);
        }).finally(() => {
          this.busyReplies = false;
        });
      },
      toggleReply() {
        if (this.replyOpen) {
          if (this.$refs.replyForm) {
            this.$refs.replyForm.clear();
          }
          this.replyOpen = false;
          return;
        }
        this.replyOpen = true;
        this.$nextTick(() => {
          if (this.$refs.replyForm) {
            this.$refs.replyForm.focus();
          }
        });
      }
    }
  };
  const _hoisted_1$1 = ["id"];
  const _hoisted_2$1 = { class: "flex-grow-1 overflow-hidden" };
  const _hoisted_3$1 = ["id"];
  const _hoisted_4 = { class: "flex-shrink-0 comment-header-image" };
  const _hoisted_5 = ["href"];
  const _hoisted_6 = ["src", "alt", "data-contentcontainer-id"];
  const _hoisted_7 = ["aria-label", "title"];
  const _hoisted_8 = { class: "flex-grow-1" };
  const _hoisted_9 = { class: "comment-heading" };
  const _hoisted_10 = ["href", "data-contentcontainer-id", "data-guid"];
  const _hoisted_11 = ["datetime", "title"];
  const _hoisted_12 = ["title"];
  const _hoisted_13 = ["id"];
  const _hoisted_14 = ["innerHTML"];
  const _hoisted_15 = { class: "wall-entry-controls" };
  const _hoisted_16 = ["data-count"];
  const _hoisted_17 = {
    key: 0,
    class: "nested-comments-root"
  };
  const _hoisted_18 = { class: "comment" };
  const _hoisted_19 = {
    key: 0,
    class: "showMore"
  };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_CommentControls = vue$1.resolveComponent("CommentControls");
    const _component_CommentForm = vue$1.resolveComponent("CommentForm");
    const _component_RichTextOutput = vue$1.resolveComponent("RichTextOutput");
    const _component_LikeButton = vue$1.resolveComponent("LikeButton");
    const _component_CommentEntry = vue$1.resolveComponent("CommentEntry", true);
    const _directive_additions = vue$1.resolveDirective("additions");
    return $props.comment.blocked ? (vue$1.openBlock(), vue$1.createElementBlock("div", {
      key: 0,
      id: "comment_" + $props.comment.id,
      class: "d-flex comment-blocked-user"
    }, [
      _cache[4] || (_cache[4] = vue$1.createElementVNode(
        "div",
        { class: "flex-shrink-0 me-2" },
        null,
        -1
        /* CACHED */
      )),
      vue$1.createElementVNode("div", _hoisted_2$1, [
        vue$1.createTextVNode(
          vue$1.toDisplayString($options.blockedLabel) + " ",
          1
          /* TEXT */
        ),
        vue$1.createElementVNode(
          "a",
          {
            href: "#",
            class: "text-primary",
            onClick: _cache[0] || (_cache[0] = vue$1.withModifiers((...args) => $options.reveal && $options.reveal(...args), ["prevent"]))
          },
          vue$1.toDisplayString($options.showLabel),
          1
          /* TEXT */
        )
      ])
    ], 8, _hoisted_1$1)) : vue$1.withDirectives((vue$1.openBlock(), vue$1.createElementBlock("div", {
      key: 1,
      id: "comment_" + $props.comment.id,
      class: vue$1.normalizeClass(["single-comment d-flex p-2", { "comment-current": $props.highlighted }])
    }, [
      vue$1.createVNode(_component_CommentControls, {
        permalink: $props.comment.permalink,
        "can-edit": $props.comment.canEdit,
        "can-delete": $props.comment.canDelete,
        "can-admin-delete": $props.comment.canAdminDelete,
        onEdit: $options.onEdit,
        onDelete: $options.onDelete,
        onAdminDelete: $options.onAdminDelete
      }, null, 8, ["permalink", "can-edit", "can-delete", "can-admin-delete", "onEdit", "onDelete", "onAdminDelete"]),
      vue$1.createElementVNode("div", _hoisted_4, [
        vue$1.createElementVNode("a", {
          href: $props.comment.author.url,
          class: vue$1.normalizeClass({ "has-online-status img-size-small": $options.isOnline !== null })
        }, [
          vue$1.createElementVNode("img", {
            class: "rounded",
            style: { "width": "25px", "height": "25px" },
            src: $props.comment.author.imageUrl,
            alt: $props.comment.author.imageAlt,
            "data-contentcontainer-id": $props.comment.author.contentContainerId
          }, null, 8, _hoisted_6),
          $options.isOnline !== null ? (vue$1.openBlock(), vue$1.createElementBlock("span", {
            key: 0,
            class: vue$1.normalizeClass(["tt user-online-status", $options.isOnline ? "user-is-online" : "user-is-offline"]),
            "aria-label": $options.onlineLabel,
            title: $options.onlineLabel
          }, null, 10, _hoisted_7)) : vue$1.createCommentVNode("v-if", true)
        ], 10, _hoisted_5)
      ]),
      vue$1.createElementVNode("div", _hoisted_8, [
        vue$1.createElementVNode("h4", _hoisted_9, [
          vue$1.createElementVNode("a", {
            href: $props.comment.author.url,
            "data-contentcontainer-id": $props.comment.author.contentContainerId,
            "data-guid": $props.comment.author.guid
          }, vue$1.toDisplayString($props.comment.author.displayName), 9, _hoisted_10),
          vue$1.createElementVNode("small", null, [
            _cache[6] || (_cache[6] = vue$1.createTextVNode(
              " · ",
              -1
              /* CACHED */
            )),
            vue$1.createElementVNode("time", {
              class: "tt time timeago",
              "data-ui-addition": "timeago",
              datetime: $props.comment.createdAt,
              title: $options.absoluteTime
            }, vue$1.toDisplayString($options.absoluteTime), 9, _hoisted_11),
            $props.comment.isEdited ? (vue$1.openBlock(), vue$1.createElementBlock(
              vue$1.Fragment,
              { key: 0 },
              [
                _cache[5] || (_cache[5] = vue$1.createTextVNode(
                  " · ",
                  -1
                  /* CACHED */
                )),
                vue$1.createElementVNode("i", {
                  class: "tt fa fa-clock-o text-body-secondary",
                  title: $options.updatedAtTitle,
                  "aria-hidden": "true"
                }, null, 8, _hoisted_12)
              ],
              64
              /* STABLE_FRAGMENT */
            )) : vue$1.createCommentVNode("v-if", true)
          ])
        ]),
        vue$1.createCommentVNode(" class comment_edit_content required since v1.2 "),
        vue$1.createElementVNode("div", {
          class: "content comment_edit_content",
          id: "comment_editarea_" + $props.comment.id
        }, [
          $data.editing && $props.formShellHtml ? (vue$1.openBlock(), vue$1.createElementBlock(
            vue$1.Fragment,
            { key: 0 },
            [
              vue$1.createVNode(_component_CommentForm, {
                ref: "editForm",
                "shell-html": $props.formShellHtml,
                "content-id": $props.comment.contentId,
                "edit-comment-id": $props.comment.id,
                "initial-message": $data.editMessage,
                "submit-icon-html": $props.submitIconHtml,
                onUpdated: $options.onEditSaved
              }, null, 8, ["shell-html", "content-id", "edit-comment-id", "initial-message", "submit-icon-html", "onUpdated"]),
              vue$1.createElementVNode(
                "a",
                {
                  href: "#",
                  class: "comment-cancel-edit-link",
                  onClick: _cache[1] || (_cache[1] = vue$1.withModifiers((...args) => $options.cancelEdit && $options.cancelEdit(...args), ["prevent"]))
                },
                vue$1.toDisplayString($options.cancelEditLabel),
                1
                /* TEXT */
              )
            ],
            64
            /* STABLE_FRAGMENT */
          )) : (vue$1.openBlock(), vue$1.createElementBlock(
            vue$1.Fragment,
            { key: 1 },
            [
              vue$1.createVNode(_component_RichTextOutput, {
                class: "comment-message",
                "data-ui-markdown": "",
                "data-ui-show-more": "",
                "data-read-more-text": $options.readMoreLabel,
                output: $props.comment.messageOutput
              }, null, 8, ["data-read-more-text", "output"]),
              $props.comment.attachmentsHtml ? (vue$1.openBlock(), vue$1.createElementBlock("div", {
                key: 0,
                innerHTML: $props.comment.attachmentsHtml
              }, null, 8, _hoisted_14)) : vue$1.createCommentVNode("v-if", true)
            ],
            64
            /* STABLE_FRAGMENT */
          ))
        ], 8, _hoisted_13),
        vue$1.createElementVNode("div", _hoisted_15, [
          $options.showReplyToggle ? (vue$1.openBlock(), vue$1.createElementBlock("a", {
            key: 0,
            href: "#",
            onClick: _cache[2] || (_cache[2] = vue$1.withModifiers((...args) => $options.toggleReply && $options.toggleReply(...args), ["prevent"]))
          }, [
            vue$1.createTextVNode(
              vue$1.toDisplayString($options.replyLabel),
              1
              /* TEXT */
            ),
            vue$1.createElementVNode("span", {
              class: "comment-count",
              "data-count": $data.childTotal,
              style: vue$1.normalizeStyle($data.childTotal > 0 ? null : "display:none")
            }, " (" + vue$1.toDisplayString($data.childTotal) + ")", 13, _hoisted_16)
          ])) : vue$1.createCommentVNode("v-if", true),
          $options.showReplyToggle && $props.comment.likes ? (vue$1.openBlock(), vue$1.createElementBlock(
            vue$1.Fragment,
            { key: 1 },
            [
              vue$1.createTextVNode(" · ")
            ],
            64
            /* STABLE_FRAGMENT */
          )) : vue$1.createCommentVNode("v-if", true),
          $props.comment.likes ? (vue$1.openBlock(), vue$1.createBlock(_component_LikeButton, {
            key: 2,
            "record-id": $props.comment.recordId,
            "like-count": $props.comment.likes.count,
            "current-user-liked": $props.comment.likes.liked
          }, null, 8, ["record-id", "like-count", "current-user-liked"])) : vue$1.createCommentVNode("v-if", true)
        ]),
        $props.comment.children ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_17, [
          vue$1.createElementVNode("div", _hoisted_18, [
            (vue$1.openBlock(true), vue$1.createElementBlock(
              vue$1.Fragment,
              null,
              vue$1.renderList($data.childItems, (child) => {
                return vue$1.openBlock(), vue$1.createElementBlock(
                  vue$1.Fragment,
                  {
                    key: $options.revisionKey(child)
                  },
                  [
                    _cache[7] || (_cache[7] = vue$1.createElementVNode(
                      "hr",
                      { class: "comment-separator" },
                      null,
                      -1
                      /* CACHED */
                    )),
                    vue$1.createVNode(_component_CommentEntry, {
                      comment: child,
                      "is-nested": true,
                      "can-comment": $props.canComment,
                      "form-shell-html": $props.formShellHtml,
                      "submit-icon-html": $props.submitIconHtml,
                      "page-size": $props.pageSize,
                      onEntryRemoved: $options.onChildRemoved,
                      onEntryUpdated: $options.onChildUpdated
                    }, null, 8, ["comment", "can-comment", "form-shell-html", "submit-icon-html", "page-size", "onEntryRemoved", "onEntryUpdated"])
                  ],
                  64
                  /* STABLE_FRAGMENT */
                );
              }),
              128
              /* KEYED_FRAGMENT */
            )),
            $data.childHasMore ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_19, [
              _cache[8] || (_cache[8] = vue$1.createElementVNode(
                "hr",
                { class: "comment-separator" },
                null,
                -1
                /* CACHED */
              )),
              vue$1.createElementVNode(
                "a",
                {
                  href: "#",
                  class: vue$1.normalizeClass({ disabled: $data.busyReplies }),
                  onClick: _cache[3] || (_cache[3] = vue$1.withModifiers((...args) => $options.loadMoreReplies && $options.loadMoreReplies(...args), ["prevent"]))
                },
                vue$1.toDisplayString($options.moreRepliesLabel),
                3
                /* TEXT, CLASS */
              )
            ])) : vue$1.createCommentVNode("v-if", true)
          ]),
          $data.replyOpen && $props.formShellHtml ? (vue$1.openBlock(), vue$1.createBlock(_component_CommentForm, {
            key: 0,
            ref: "replyForm",
            "shell-html": $props.formShellHtml,
            "content-id": $props.comment.contentId,
            "parent-comment-id": $props.comment.id,
            "submit-icon-html": $props.submitIconHtml,
            onCreated: $options.onReplyCreated
          }, null, 8, ["shell-html", "content-id", "parent-comment-id", "submit-icon-html", "onCreated"])) : vue$1.createCommentVNode("v-if", true)
        ])) : vue$1.createCommentVNode("v-if", true)
      ])
    ], 10, _hoisted_3$1)), [
      [_directive_additions]
    ]);
  }
  const CommentEntry = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const getId = (contentId, parentCommentId) => `C${contentId}P${""}`;
  const _sfc_main$1 = {
    components: { CommentEntry },
    inject: {
      commentRevisions: { default: () => ({}) },
      bumpCommentRevision: { default: () => () => {
      } },
      pruneCommentRevision: { default: () => () => {
      } }
    },
    props: {
      contentId: { type: Number, required: true },
      comments: { type: Array, required: true },
      prevCount: { type: Number, required: true },
      nextCount: { type: Number, required: true },
      pageSize: { type: Number, default: 10 },
      canComment: { type: Boolean, default: false },
      formShellHtml: { type: String, default: null },
      submitIconHtml: { type: String, default: null },
      anchorCommentId: { type: Number, default: null }
    },
    data() {
      return {
        items: [...this.comments],
        remainingPrev: this.prevCount,
        remainingNext: this.nextCount,
        busyPrev: false,
        busyNext: false
      };
    },
    computed: {
      // Legacy markup: comments.php renders `.comment.guest-mode` /
      // `#comments_area_<id>` around this exact list - reproduced so
      // existing theme CSS (`&.guest-mode` in _comment.scss) keeps
      // applying and P2-6's PHP-rendered id contract still resolves.
      guest() {
        return vue.getConfig("user").isGuest === true;
      },
      commentsAreaId() {
        return "comments_area_" + getId(this.contentId);
      },
      prevLabel() {
        return vue.i18n.t("CommentModule.base", "Show previous {count} comments", { count: this.remainingPrev });
      },
      nextLabel() {
        return vue.i18n.t("CommentModule.base", "Show next {count} comments", { count: this.remainingNext });
      }
    },
    watch: {
      // Re-syncs if the parent ever hydrates a genuinely new window (e.g. a
      // future re-fetch) - not exercised by show-more itself, which only
      // ever touches the local copies above.
      comments(value) {
        this.items = [...value];
      },
      prevCount(value) {
        this.remainingPrev = value;
      },
      nextCount(value) {
        this.remainingNext = value;
      }
    },
    methods: {
      revisionKey(comment) {
        return comment.id + ":" + (this.commentRevisions[comment.id] || 0);
      },
      /** Appended at the end, mirroring the legacy Form.prototype.addComment placement. */
      appendRoot(comment) {
        this.items.push(comment);
      },
      removeRoot(id) {
        this.items = this.items.filter((comment) => comment.id !== id);
        this.pruneCommentRevision(id);
      },
      replaceRoot(id, comment) {
        const index = this.items.findIndex((item) => item.id === id);
        if (index !== -1) {
          this.items.splice(index, 1, comment);
        }
      },
      findRoot(id) {
        return this.items.find((comment) => comment.id === id) || null;
      },
      onEntryUpdated({ id, comment }) {
        this.replaceRoot(id, comment);
        this.bumpCommentRevision(id);
      },
      loadPrev() {
        if (this.busyPrev || this.items.length === 0) {
          return;
        }
        this.busyPrev = true;
        const cursor = this.items[0].id;
        vue.client.get(vue.url("/comment/comment/list", {
          contentId: this.contentId,
          commentId: cursor,
          direction: "previous",
          pageSize: this.pageSize
        })).then((response) => {
          this.items = [...response.comments, ...this.items];
          this.remainingPrev = response.prevCount;
        }).catch((e) => {
          vue.log.error(e, true);
        }).finally(() => {
          this.busyPrev = false;
        });
      },
      loadNext() {
        if (this.busyNext || this.items.length === 0) {
          return;
        }
        this.busyNext = true;
        const cursor = this.items[this.items.length - 1].id;
        vue.client.get(vue.url("/comment/comment/list", {
          contentId: this.contentId,
          commentId: cursor,
          direction: "next",
          pageSize: this.pageSize
        })).then((response) => {
          this.items = [...this.items, ...response.comments];
          this.remainingNext = response.nextCount;
        }).catch((e) => {
          vue.log.error(e, true);
        }).finally(() => {
          this.busyNext = false;
        });
      }
    }
  };
  const _hoisted_1 = ["id"];
  const _hoisted_2 = {
    key: 0,
    class: "showMore"
  };
  const _hoisted_3 = {
    key: 1,
    class: "showMore"
  };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_CommentEntry = vue$1.resolveComponent("CommentEntry");
    return vue$1.openBlock(), vue$1.createElementBlock("div", {
      class: vue$1.normalizeClass(["comment", { "guest-mode": $options.guest }]),
      id: $options.commentsAreaId
    }, [
      $data.remainingPrev > 0 && $data.items.length > 0 ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_2, [
        vue$1.createElementVNode(
          "a",
          {
            href: "#",
            class: vue$1.normalizeClass({ disabled: $data.busyPrev }),
            onClick: _cache[0] || (_cache[0] = vue$1.withModifiers((...args) => $options.loadPrev && $options.loadPrev(...args), ["prevent"]))
          },
          vue$1.toDisplayString($options.prevLabel),
          3
          /* TEXT, CLASS */
        )
      ])) : vue$1.createCommentVNode("v-if", true),
      (vue$1.openBlock(true), vue$1.createElementBlock(
        vue$1.Fragment,
        null,
        vue$1.renderList($data.items, (comment) => {
          return vue$1.openBlock(), vue$1.createElementBlock(
            vue$1.Fragment,
            {
              key: $options.revisionKey(comment)
            },
            [
              _cache[2] || (_cache[2] = vue$1.createElementVNode(
                "hr",
                { class: "comment-separator" },
                null,
                -1
                /* CACHED */
              )),
              vue$1.createVNode(_component_CommentEntry, {
                comment,
                "can-comment": $props.canComment,
                "form-shell-html": $props.formShellHtml,
                "submit-icon-html": $props.submitIconHtml,
                "page-size": $props.pageSize,
                highlighted: $props.anchorCommentId !== null && comment.id === $props.anchorCommentId,
                onEntryRemoved: $options.removeRoot,
                onEntryUpdated: $options.onEntryUpdated
              }, null, 8, ["comment", "can-comment", "form-shell-html", "submit-icon-html", "page-size", "highlighted", "onEntryRemoved", "onEntryUpdated"])
            ],
            64
            /* STABLE_FRAGMENT */
          );
        }),
        128
        /* KEYED_FRAGMENT */
      )),
      $data.remainingNext > 0 && $data.items.length > 0 ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_3, [
        _cache[3] || (_cache[3] = vue$1.createElementVNode(
          "hr",
          { class: "comment-separator" },
          null,
          -1
          /* CACHED */
        )),
        vue$1.createElementVNode(
          "a",
          {
            href: "#",
            class: vue$1.normalizeClass({ disabled: $data.busyNext }),
            onClick: _cache[1] || (_cache[1] = vue$1.withModifiers((...args) => $options.loadNext && $options.loadNext(...args), ["prevent"]))
          },
          vue$1.toDisplayString($options.nextLabel),
          3
          /* TEXT, CLASS */
        )
      ])) : vue$1.createCommentVNode("v-if", true)
    ], 10, _hoisted_1);
  }
  const CommentList = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const LIVE_NEW_COMMENT = "humhub:modules:comment:live:NewComment";
  const collectKnownIds = (comments) => {
    const ids = [];
    (comments || []).forEach((comment) => {
      ids.push(comment.id);
      if (comment.children && comment.children.items) {
        comment.children.items.forEach((child) => ids.push(child.id));
      }
    });
    return ids;
  };
  const _sfc_main = {
    // 'ContentModule.base' is preloaded here (rather than declared again on
    // CommentForm, which isn't a directly-mounted island and so has no
    // i18nCategories of its own) for CommentForm's submit button label - see
    // that component's own docblock for why it reuses this category instead
    // of a CommentModule.base key. 'UserModule.base' is preloaded the same
    // way for CommentEntry's online-status overlay label (`onlineLabel`),
    // matching the exact keys `user\widgets\Image::run()` uses.
    i18nCategories: ["CommentModule.base", "ContentModule.base", "UserModule.base"],
    components: { CommentList, CommentForm },
    props: {
      contentId: { type: Number, required: true },
      // serializeWindow() payload: {comments, prevCount, nextCount, total}
      initial: { type: Object, default: null },
      canComment: { type: Boolean, default: false },
      // __VUEFORM__ shell token template, see LegacyFormWrapper.vue
      formShellHtml: { type: String, default: null },
      // Server-rendered submit-button icon HTML - see CommentForm.vue's own docblock.
      submitIconHtml: { type: String, default: null },
      pageSize: { type: Number, default: 10 },
      // permalink highlight target
      anchorCommentId: { type: Number, default: null },
      // stream preview: section hidden until toggled via humhub:comment:toggle
      collapsed: { type: Boolean, default: false }
    },
    data() {
      return {
        comments: this.initial ? this.initial.comments : [],
        prevCount: this.initial ? this.initial.prevCount : 0,
        nextCount: this.initial ? this.initial.nextCount : 0,
        total: this.initial ? this.initial.total : 0,
        loaded: !!this.initial,
        isCollapsed: this.collapsed,
        // id -> revision counter, bumped whenever an entry object is
        // swapped in place under the same id (reveal/edit/live-append) —
        // see the class docblock's "Revision map" section.
        revisions: {},
        // Dedup set for own-create-vs-live races and live-update replay —
        // append-only by design, see the class docblock's "Live updates"
        // section for why entries are never removed on delete.
        knownIds: new Set(this.initial ? collectKnownIds(this.initial.comments) : []),
        // Guards the on-expand fetch in onToggle() against overlapping
        // requests from repeated toggle events (see its own comment).
        expandingBusy: false
      };
    },
    provide() {
      return {
        commentRevisions: this.revisions,
        bumpCommentRevision: this.bumpCommentRevision,
        pruneCommentRevision: this.pruneCommentRevision,
        adjustTotal: this.adjustTotal,
        registerKnownId: this.registerKnownId,
        isKnownId: this.isKnownId
      };
    },
    computed: {
      guest() {
        return vue.getConfig("user").isGuest === true;
      },
      showForm() {
        return this.canComment && !this.guest;
      }
    },
    watch: {
      // Only fires on genuine changes (not the data() initial assignment),
      // so hydrating from `initial` never spuriously notifies the bridge of
      // a count it already rendered itself.
      total(value) {
        this.dispatchCountChanged(value);
      }
    },
    created() {
      if (!this.initial) {
        this.fetchInitial();
      }
    },
    mounted() {
      this.mountEl = this.$el.parentElement;
      if (this.mountEl) {
        this.mountEl.addEventListener("humhub:comment:toggle", this.onToggle);
      }
      vue.events.on(LIVE_NEW_COMMENT, this.onLiveNewComment);
    },
    unmounted() {
      if (this.mountEl) {
        this.mountEl.removeEventListener("humhub:comment:toggle", this.onToggle);
      }
      vue.events.off(LIVE_NEW_COMMENT, this.onLiveNewComment);
    },
    methods: {
      fetchInitial() {
        vue.client.get(vue.url("/comment/comment/list", { contentId: this.contentId, pageSize: this.pageSize })).then((response) => {
          this.comments = response.comments;
          this.prevCount = response.prevCount;
          this.nextCount = response.nextCount;
          this.total = response.total;
          this.knownIds = new Set(collectKnownIds(response.comments));
          this.loaded = true;
        }).catch((e) => {
          vue.log.error(e, true);
          this.loaded = true;
        });
      },
      onToggle() {
        this.isCollapsed = false;
        if (this.comments.length === 0 && this.total > 0 && !this.expandingBusy) {
          this.expandingBusy = true;
          vue.client.get(vue.url("/comment/comment/list", { contentId: this.contentId, pageSize: this.pageSize })).then((response) => {
            this.comments = response.comments;
            this.prevCount = response.prevCount;
            this.nextCount = response.nextCount;
            this.total = response.total;
            collectKnownIds(response.comments).forEach((id) => this.knownIds.add(id));
          }).catch((e) => {
            vue.log.error(e, true);
          }).finally(() => {
            this.expandingBusy = false;
          });
        }
        this.$nextTick(() => {
          if (this.$refs.form) {
            this.$refs.form.focus();
          }
        });
      },
      dispatchCountChanged(total) {
        if (!this.mountEl) {
          return;
        }
        this.mountEl.dispatchEvent(new CustomEvent("humhub:comment:countChanged", {
          bubbles: true,
          detail: { contentId: this.contentId, total }
        }));
      },
      bumpCommentRevision(id) {
        this.revisions[id] = (this.revisions[id] || 0) + 1;
      },
      // Called once an id leaves an owning array for good (delete) — keeps
      // `revisions` from growing forever across a long-lived session. Safe
      // unlike `knownIds`: a pruned id can never legitimately reappear
      // under the same `:key` scheme (the guard on `knownIds` — see
      // `isKnownId()` — stops a stale live/create event from ever
      // resurrecting it into an array in the first place).
      pruneCommentRevision(id) {
        delete this.revisions[id];
      },
      adjustTotal(delta) {
        this.total += delta;
      },
      registerKnownId(id) {
        this.knownIds.add(id);
      },
      isKnownId(id) {
        return this.knownIds.has(id);
      },
      onMainCreated(comment) {
        if (this.isKnownId(comment.id)) {
          return;
        }
        this.registerKnownId(comment.id);
        this.total += 1;
        if (this.$refs.list) {
          this.$refs.list.appendRoot(comment);
        }
      },
      onLiveNewComment(evt, liveEvents) {
        (liveEvents || []).forEach((liveEvent) => this.handleLiveEvent(liveEvent));
      },
      handleLiveEvent(liveEvent) {
        const data = liveEvent && liveEvent.data || {};
        if (Number(data.contentId) !== this.contentId) {
          return;
        }
        const commentId = Number(data.commentId);
        if (this.isKnownId(commentId)) {
          return;
        }
        vue.client.get(vue.url("/comment/comment/info", { id: commentId })).then((comment) => this.appendLiveComment(comment)).catch((e) => {
          vue.log.error(e, true);
        });
      },
      appendLiveComment(comment) {
        if (this.isKnownId(comment.id)) {
          return;
        }
        this.registerKnownId(comment.id);
        this.total += 1;
        if (!this.$refs.list) {
          return;
        }
        if (comment.parentCommentId === null || comment.parentCommentId === void 0) {
          this.$refs.list.appendRoot(comment);
          return;
        }
        const parent = this.$refs.list.findRoot(comment.parentCommentId);
        if (!parent || !parent.children) {
          return;
        }
        const items = [...parent.children.items, comment];
        const total = parent.children.total + 1;
        this.$refs.list.replaceRoot(parent.id, {
          ...parent,
          children: { total, items, hasMore: total > items.length }
        });
        this.bumpCommentRevision(parent.id);
      }
    }
  };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_CommentList = vue$1.resolveComponent("CommentList");
    const _component_CommentForm = vue$1.resolveComponent("CommentForm");
    return vue$1.openBlock(), vue$1.createElementBlock(
      "div",
      {
        class: vue$1.normalizeClass(["bg-light p-2 mt-3 comment-container", { "d-none": $data.isCollapsed }])
      },
      [
        $data.loaded ? (vue$1.openBlock(), vue$1.createBlock(_component_CommentList, {
          key: 0,
          ref: "list",
          "content-id": $props.contentId,
          comments: $data.comments,
          "prev-count": $data.prevCount,
          "next-count": $data.nextCount,
          "page-size": $props.pageSize,
          "can-comment": $options.showForm,
          "form-shell-html": $props.formShellHtml,
          "submit-icon-html": $props.submitIconHtml,
          "anchor-comment-id": $props.anchorCommentId
        }, null, 8, ["content-id", "comments", "prev-count", "next-count", "page-size", "can-comment", "form-shell-html", "submit-icon-html", "anchor-comment-id"])) : vue$1.createCommentVNode("v-if", true),
        $options.showForm && $props.formShellHtml ? (vue$1.openBlock(), vue$1.createBlock(_component_CommentForm, {
          key: 1,
          ref: "form",
          "shell-html": $props.formShellHtml,
          "content-id": $props.contentId,
          "submit-icon-html": $props.submitIconHtml,
          onCreated: $options.onMainCreated
        }, null, 8, ["shell-html", "content-id", "submit-icon-html", "onCreated"])) : vue$1.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue.register("CommentSection", C0);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.comment.vue.js.map
