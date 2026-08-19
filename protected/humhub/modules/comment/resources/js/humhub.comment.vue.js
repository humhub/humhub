/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from comment/vue/ via `grunt build-vue --module=comment`.
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
      output: { type: String, default: null }
    }
  };
  const _hoisted_1$4 = ["innerHTML"];
  function _sfc_render$6(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return $props.output ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", {
      key: 0,
      innerHTML: $props.output
    }, null, 8, _hoisted_1$4)), [
      [_directive_additions]
    ]) : vue.createCommentVNode("v-if", true);
  }
  const RichTextOutput = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["render", _sfc_render$6]]);
  const _sfc_main$5 = {
    props: {
      permalink: { type: String, required: true },
      canEdit: { type: Boolean, default: false },
      canDelete: { type: Boolean, default: false },
      canAdminDelete: { type: Boolean, default: false }
    },
    emits: ["edit", "delete", "admin-delete"],
    computed: {
      toggleMenuLabel() {
        return vue$1.i18n.t("base", "Toggle comment menu");
      },
      permalinkLabel() {
        return vue$1.i18n.t("CommentModule.base", "Permalink");
      },
      permalinkTitle() {
        return vue$1.i18n.t("CommentModule.base", "<strong>Permalink</strong> to this comment");
      },
      editLabel() {
        return vue$1.i18n.t("CommentModule.base", "Edit");
      },
      deleteLabel() {
        return vue$1.i18n.t("CommentModule.base", "Delete");
      }
    },
    methods: {
      onEdit() {
        vue$1.log.warn("Comment edit is not implemented yet (P2-5)");
        this.$emit("edit");
      },
      onDelete() {
        vue$1.log.warn("Comment delete is not implemented yet (P2-5)");
        this.$emit(this.canAdminDelete ? "admin-delete" : "delete");
      }
    }
  };
  const _hoisted_1$3 = { class: "nav nav-pills preferences" };
  const _hoisted_2$2 = { class: "nav-item dropdown" };
  const _hoisted_3$2 = ["aria-label"];
  const _hoisted_4$1 = { class: "dropdown-menu dropdown-menu-end" };
  const _hoisted_5$1 = ["data-content-permalink", "data-content-permalink-title"];
  const _hoisted_6$1 = { key: 0 };
  const _hoisted_7$1 = { key: 1 };
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    return vue.openBlock(), vue.createElementBlock(
      vue.Fragment,
      null,
      [
        _cache[2] || (_cache[2] = vue.createElementVNode(
          "div",
          { class: "comment-entry-loader float-end" },
          null,
          -1
          /* CACHED */
        )),
        vue.createElementVNode("ul", _hoisted_1$3, [
          vue.createElementVNode("li", _hoisted_2$2, [
            vue.createElementVNode("a", {
              href: "#",
              class: "nav-link dropdown-toggle",
              "data-bs-toggle": "dropdown",
              "aria-label": $options.toggleMenuLabel,
              "aria-haspopup": "true",
              "aria-expanded": "false",
              role: "button"
            }, null, 8, _hoisted_3$2),
            vue.createElementVNode("ul", _hoisted_4$1, [
              vue.createElementVNode("li", null, [
                vue.createCommentVNode('\n                        Plain anchor reusing the exact legacy attributes\n                        (data-action-click="content.permalink" + the two\n                        data-content-permalink* values) instead of a\n                        Vue-owned click handler: humhub.action.js binds the\n                        [data-action-click] delegate on `document` itself\n                        (see bindAction(document, \'click\', ...) in\n                        humhub.action.js), so it already fires for anchors\n                        injected anywhere in the DOM, Vue-rendered islands\n                        included, with zero extra wiring. The `content`\n                        module (ui.content) is always loaded page-wide\n                        wherever comments can appear, so this "just works".\n                    '),
                vue.createElementVNode("a", {
                  href: "#",
                  class: "dropdown-item",
                  "data-action-click": "content.permalink",
                  "data-content-permalink": $props.permalink,
                  "data-content-permalink-title": $options.permalinkTitle
                }, vue.toDisplayString($options.permalinkLabel), 9, _hoisted_5$1)
              ]),
              $props.canEdit ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_6$1, [
                vue.createElementVNode(
                  "a",
                  {
                    href: "#",
                    class: "dropdown-item",
                    onClick: _cache[0] || (_cache[0] = vue.withModifiers((...args) => $options.onEdit && $options.onEdit(...args), ["prevent"]))
                  },
                  vue.toDisplayString($options.editLabel),
                  1
                  /* TEXT */
                )
              ])) : vue.createCommentVNode("v-if", true),
              $props.canDelete ? (vue.openBlock(), vue.createElementBlock("li", _hoisted_7$1, [
                vue.createElementVNode(
                  "a",
                  {
                    href: "#",
                    class: "dropdown-item",
                    onClick: _cache[1] || (_cache[1] = vue.withModifiers((...args) => $options.onDelete && $options.onDelete(...args), ["prevent"]))
                  },
                  vue.toDisplayString($options.deleteLabel),
                  1
                  /* TEXT */
                )
              ])) : vue.createCommentVNode("v-if", true)
            ])
          ])
        ])
      ],
      64
      /* STABLE_FRAGMENT */
    );
  }
  const CommentControls = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const FORM_TOKEN = "__VUEFORM__";
  const RICHTEXT_SELECTOR = ".humhub-ui-richtext";
  const RICHTEXT_COMPONENT_DATA = "humhub-ui-richtexteditor";
  const UPLOAD_SELECTOR = ".main_comment_upload";
  const UPLOAD_COMPONENT_DATA = "humhub-file-upload";
  let instanceCounter = 0;
  const _sfc_main$4 = {
    props: {
      shellHtml: { type: String, required: true }
    },
    data() {
      return {
        // Module-scope counter (not Math.random()) so builds/output stay
        // deterministic; unique per mounted instance on the page.
        instanceId: "vueform-" + ++instanceCounter
      };
    },
    computed: {
      processedShell() {
        return this.shellHtml.split(FORM_TOKEN).join(this.instanceId);
      }
    },
    methods: {
      getEditorInstance() {
        const node = this.$el.querySelector(RICHTEXT_SELECTOR);
        return node ? jQuery(node).data(RICHTEXT_COMPONENT_DATA) : null;
      },
      getUploadInstance() {
        const node = this.$el.querySelector(UPLOAD_SELECTOR);
        return node ? jQuery(node).data(UPLOAD_COMPONENT_DATA) : null;
      },
      /** @returns {string} the current markdown value of the richtext editor. */
      getValue() {
        const editor = this.getEditorInstance();
        return editor ? editor.editor.serialize() : "";
      },
      /** Prefills the editor with markdown (e.g. for edit mode). */
      setValue(markdown) {
        const editor = this.getEditorInstance();
        if (editor) {
          editor.editor.init(markdown || "");
        }
      },
      /** Empties the editor and resets the upload preview/file inputs. */
      clear() {
        const editor = this.getEditorInstance();
        if (editor) {
          editor.$.trigger("clear");
        }
        const upload = this.getUploadInstance();
        if (upload) {
          upload.reset();
        }
      },
      /** Focuses the richtext editor (e.g. on reply). */
      focus() {
        const editor = this.getEditorInstance();
        if (editor) {
          editor.focus();
        }
      },
      /** @returns {string[]} guids of files currently attached via the upload widget. */
      getFileGuids() {
        const upload = this.getUploadInstance();
        if (!upload) {
          return [];
        }
        const name = upload.options.uploadSubmitName;
        return jQuery(this.$el).find('input[name="' + name + '"]').map(function() {
          return this.value;
        }).get();
      }
    }
  };
  const _hoisted_1$2 = ["innerHTML"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", { innerHTML: $options.processedShell }, null, 8, _hoisted_1$2)), [
      [_directive_additions]
    ]);
  }
  const LegacyFormWrapper = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const _sfc_main$3 = {
    components: { LegacyFormWrapper },
    props: {
      shellHtml: { type: String, required: true },
      contentId: { type: Number, required: true },
      parentCommentId: { type: Number, default: null }
    },
    emits: ["submit"],
    mounted() {
      this.formEl = this.$refs.wrapper.$el.querySelector("form");
      if (this.formEl) {
        this.formEl.addEventListener("submit", this.onSubmit);
      }
    },
    beforeUnmount() {
      if (this.formEl) {
        this.formEl.removeEventListener("submit", this.onSubmit);
      }
    },
    methods: {
      onSubmit(event) {
        event.preventDefault();
        vue$1.log.warn("Comment submit is not implemented yet (P2-5)", {
          contentId: this.contentId,
          parentCommentId: this.parentCommentId
        });
        this.$emit("submit", { contentId: this.contentId, parentCommentId: this.parentCommentId });
      },
      /** Proxies to the wrapper so callers (reply toggle, section toggle) don't touch jQuery/legacy widgets. */
      focus() {
        if (this.$refs.wrapper) {
          this.$refs.wrapper.focus();
        }
      }
    }
  };
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_LegacyFormWrapper = vue.resolveComponent("LegacyFormWrapper");
    return vue.openBlock(), vue.createBlock(_component_LegacyFormWrapper, {
      ref: "wrapper",
      "shell-html": $props.shellHtml
    }, null, 8, ["shell-html"]);
  }
  const CommentForm = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = {
    name: "CommentEntry",
    components: { RichTextOutput, CommentControls, CommentForm },
    props: {
      comment: { type: Object, required: true },
      canComment: { type: Boolean, default: false },
      formShellHtml: { type: String, default: null },
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
    data() {
      return {
        // Reveal-blocked result replaces the masked comment locally; the
        // array item CommentList/CommentEntry-parent holds stays
        // untouched (revealing doesn't change any count).
        revealed: null,
        replyOpen: false,
        childItems: this.comment.children ? [...this.comment.children.items] : [],
        childTotal: this.comment.children ? this.comment.children.total : 0,
        childRemainingNext: this.comment.children ? this.comment.children.total - this.comment.children.items.length : 0,
        // Initial value trusts the server's own preview flag verbatim
        // (CommentJsonService::serializeChildren()'s `total > count($items)`);
        // recomputed with the same formula after each load-more below,
        // rather than solely trusting `nextCount`, so a concurrent
        // create/delete between requests can't leave a stale gate.
        childHasMore: this.comment.children ? this.comment.children.hasMore : false,
        busyReplies: false
      };
    },
    computed: {
      effective() {
        return this.revealed || this.comment;
      },
      showReplyToggle() {
        return !this.isNested && this.canComment;
      },
      blockedLabel() {
        return vue$1.i18n.t("CommentModule.base", "Comment of blocked user.");
      },
      showLabel() {
        return vue$1.i18n.t("CommentModule.base", "Show");
      },
      readMoreLabel() {
        return vue$1.i18n.t("CommentModule.base", "Read full comment...");
      },
      replyLabel() {
        return vue$1.i18n.t("CommentModule.base", "Reply");
      },
      // Same wording/category/placeholder as CommentList's own "show next"
      // link: the legacy nested Comments::widget() reused the exact same
      // ShowMore strings for children, there never was a distinct
      // "replies" message key.
      moreRepliesLabel() {
        return vue$1.i18n.t("CommentModule.base", "Show next {count} comments", { count: this.childRemainingNext });
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
      }
    },
    mounted() {
      if (this.highlighted && typeof this.$el.scrollIntoView === "function") {
        this.$el.scrollIntoView({ block: "center" });
      }
    },
    methods: {
      reveal() {
        vue$1.client.get(vue$1.url("/comment/comment/info", { id: this.comment.id, showBlocked: 1 })).then((response) => {
          this.revealed = response;
        }).catch((e) => {
          vue$1.log.error(e, true);
        });
      },
      loadMoreReplies() {
        if (this.busyReplies || this.childItems.length === 0) {
          return;
        }
        this.busyReplies = true;
        const cursor = this.childItems[this.childItems.length - 1].id;
        vue$1.client.get(vue$1.url("/comment/comment/list", {
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
          vue$1.log.error(e, true);
        }).finally(() => {
          this.busyReplies = false;
        });
      },
      toggleReply() {
        this.replyOpen = !this.replyOpen;
        if (this.replyOpen) {
          this.$nextTick(() => {
            if (this.$refs.replyForm) {
              this.$refs.replyForm.focus();
            }
          });
        }
      },
      onEdit() {
        vue$1.log.warn("TODO(P2-5): edit comment", this.comment.id);
      },
      onDelete() {
        vue$1.log.warn("TODO(P2-5): delete comment", this.comment.id);
      },
      onAdminDelete() {
        vue$1.log.warn("TODO(P2-5): admin-delete comment", this.comment.id);
      }
    }
  };
  const _hoisted_1$1 = ["id"];
  const _hoisted_2$1 = { class: "flex-grow-1 overflow-hidden" };
  const _hoisted_3$1 = ["id"];
  const _hoisted_4 = { class: "flex-shrink-0 comment-header-image" };
  const _hoisted_5 = ["href"];
  const _hoisted_6 = ["src", "alt"];
  const _hoisted_7 = { class: "flex-grow-1" };
  const _hoisted_8 = { class: "comment-heading" };
  const _hoisted_9 = ["href"];
  const _hoisted_10 = ["datetime", "title"];
  const _hoisted_11 = ["id"];
  const _hoisted_12 = ["data-read-more-text"];
  const _hoisted_13 = ["innerHTML"];
  const _hoisted_14 = { class: "wall-entry-controls" };
  const _hoisted_15 = {
    key: 0,
    class: "nested-comments-root"
  };
  const _hoisted_16 = { class: "comment" };
  const _hoisted_17 = {
    key: 0,
    class: "showMore"
  };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_CommentControls = vue.resolveComponent("CommentControls");
    const _component_RichTextOutput = vue.resolveComponent("RichTextOutput");
    const _component_LikeButton = vue.resolveComponent("LikeButton");
    const _component_CommentEntry = vue.resolveComponent("CommentEntry", true);
    const _component_CommentForm = vue.resolveComponent("CommentForm");
    const _directive_additions = vue.resolveDirective("additions");
    return $options.effective.blocked ? (vue.openBlock(), vue.createElementBlock("div", {
      key: 0,
      id: "comment_" + $props.comment.id,
      class: "d-flex comment-blocked-user"
    }, [
      _cache[3] || (_cache[3] = vue.createElementVNode(
        "div",
        { class: "flex-shrink-0 me-2" },
        null,
        -1
        /* CACHED */
      )),
      vue.createElementVNode("div", _hoisted_2$1, [
        vue.createTextVNode(
          vue.toDisplayString($options.blockedLabel) + " ",
          1
          /* TEXT */
        ),
        vue.createElementVNode(
          "a",
          {
            href: "#",
            class: "text-primary",
            onClick: _cache[0] || (_cache[0] = vue.withModifiers((...args) => $options.reveal && $options.reveal(...args), ["prevent"]))
          },
          vue.toDisplayString($options.showLabel),
          1
          /* TEXT */
        )
      ])
    ], 8, _hoisted_1$1)) : vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", {
      key: 1,
      id: "comment_" + $props.comment.id,
      class: vue.normalizeClass(["single-comment d-flex p-2", { "comment-current": $props.highlighted }])
    }, [
      vue.createVNode(_component_CommentControls, {
        permalink: $options.effective.permalink,
        "can-edit": $options.effective.canEdit,
        "can-delete": $options.effective.canDelete,
        "can-admin-delete": $options.effective.canAdminDelete,
        onEdit: $options.onEdit,
        onDelete: $options.onDelete,
        onAdminDelete: $options.onAdminDelete
      }, null, 8, ["permalink", "can-edit", "can-delete", "can-admin-delete", "onEdit", "onDelete", "onAdminDelete"]),
      vue.createElementVNode("div", _hoisted_4, [
        vue.createElementVNode("a", {
          href: $options.effective.author.url
        }, [
          vue.createElementVNode("img", {
            class: "rounded",
            style: { "width": "25px", "height": "25px" },
            src: $options.effective.author.imageUrl,
            alt: $options.effective.author.displayName
          }, null, 8, _hoisted_6)
        ], 8, _hoisted_5)
      ]),
      vue.createElementVNode("div", _hoisted_7, [
        vue.createElementVNode("h4", _hoisted_8, [
          vue.createElementVNode("a", {
            href: $options.effective.author.url
          }, vue.toDisplayString($options.effective.author.displayName), 9, _hoisted_9),
          vue.createElementVNode("small", null, [
            _cache[6] || (_cache[6] = vue.createTextVNode(
              " · ",
              -1
              /* CACHED */
            )),
            vue.createElementVNode("time", {
              class: "tt time timeago",
              "data-ui-addition": "timeago",
              datetime: $props.comment.createdAt,
              title: $options.absoluteTime
            }, vue.toDisplayString($options.absoluteTime), 9, _hoisted_10),
            $props.comment.isEdited ? (vue.openBlock(), vue.createElementBlock(
              vue.Fragment,
              { key: 0 },
              [
                _cache[4] || (_cache[4] = vue.createTextVNode(
                  " · ",
                  -1
                  /* CACHED */
                )),
                _cache[5] || (_cache[5] = vue.createElementVNode(
                  "i",
                  {
                    class: "fa fa-clock-o text-body-secondary",
                    "aria-hidden": "true"
                  },
                  null,
                  -1
                  /* CACHED */
                ))
              ],
              64
              /* STABLE_FRAGMENT */
            )) : vue.createCommentVNode("v-if", true)
          ])
        ]),
        vue.createCommentVNode(" class comment_edit_content required since v1.2 "),
        vue.createElementVNode("div", {
          class: "content comment_edit_content",
          id: "comment_editarea_" + $props.comment.id
        }, [
          vue.createElementVNode("div", {
            class: "comment-message",
            "data-ui-show-more": "",
            "data-read-more-text": $options.readMoreLabel
          }, [
            vue.createVNode(_component_RichTextOutput, {
              output: $options.effective.messageOutput
            }, null, 8, ["output"])
          ], 8, _hoisted_12),
          $options.effective.attachmentsHtml ? (vue.openBlock(), vue.createElementBlock("div", {
            key: 0,
            innerHTML: $options.effective.attachmentsHtml
          }, null, 8, _hoisted_13)) : vue.createCommentVNode("v-if", true)
        ], 8, _hoisted_11),
        vue.createElementVNode("div", _hoisted_14, [
          $options.effective.likes ? (vue.openBlock(), vue.createBlock(_component_LikeButton, {
            key: 0,
            "record-id": $options.effective.recordId,
            "like-count": $options.effective.likes.count,
            "current-user-liked": $options.effective.likes.liked
          }, null, 8, ["record-id", "like-count", "current-user-liked"])) : vue.createCommentVNode("v-if", true),
          $options.showReplyToggle ? (vue.openBlock(), vue.createElementBlock(
            vue.Fragment,
            { key: 1 },
            [
              _cache[7] || (_cache[7] = vue.createTextVNode(
                " · ",
                -1
                /* CACHED */
              )),
              vue.createElementVNode(
                "a",
                {
                  href: "#",
                  onClick: _cache[1] || (_cache[1] = vue.withModifiers((...args) => $options.toggleReply && $options.toggleReply(...args), ["prevent"]))
                },
                vue.toDisplayString($options.replyLabel),
                1
                /* TEXT */
              )
            ],
            64
            /* STABLE_FRAGMENT */
          )) : vue.createCommentVNode("v-if", true)
        ]),
        $props.comment.children ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_15, [
          vue.createElementVNode("div", _hoisted_16, [
            (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              null,
              vue.renderList($data.childItems, (child) => {
                return vue.openBlock(), vue.createElementBlock(
                  vue.Fragment,
                  {
                    key: child.id
                  },
                  [
                    _cache[8] || (_cache[8] = vue.createElementVNode(
                      "hr",
                      { class: "comment-separator" },
                      null,
                      -1
                      /* CACHED */
                    )),
                    vue.createVNode(_component_CommentEntry, {
                      comment: child,
                      "is-nested": true,
                      "can-comment": $props.canComment,
                      "form-shell-html": $props.formShellHtml,
                      "page-size": $props.pageSize
                    }, null, 8, ["comment", "can-comment", "form-shell-html", "page-size"])
                  ],
                  64
                  /* STABLE_FRAGMENT */
                );
              }),
              128
              /* KEYED_FRAGMENT */
            )),
            $data.childHasMore ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_17, [
              _cache[9] || (_cache[9] = vue.createElementVNode(
                "hr",
                { class: "comment-separator" },
                null,
                -1
                /* CACHED */
              )),
              vue.createElementVNode(
                "a",
                {
                  href: "#",
                  class: vue.normalizeClass({ disabled: $data.busyReplies }),
                  onClick: _cache[2] || (_cache[2] = vue.withModifiers((...args) => $options.loadMoreReplies && $options.loadMoreReplies(...args), ["prevent"]))
                },
                vue.toDisplayString($options.moreRepliesLabel),
                3
                /* TEXT, CLASS */
              )
            ])) : vue.createCommentVNode("v-if", true)
          ]),
          $data.replyOpen && $props.formShellHtml ? (vue.openBlock(), vue.createBlock(_component_CommentForm, {
            key: 0,
            ref: "replyForm",
            "shell-html": $props.formShellHtml,
            "content-id": $props.comment.contentId,
            "parent-comment-id": $props.comment.id
          }, null, 8, ["shell-html", "content-id", "parent-comment-id"])) : vue.createCommentVNode("v-if", true)
        ])) : vue.createCommentVNode("v-if", true)
      ])
    ], 10, _hoisted_3$1)), [
      [_directive_additions]
    ]);
  }
  const CommentEntry = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const getId = (contentId, parentCommentId) => `C${contentId}P${""}`;
  const _sfc_main$1 = {
    components: { CommentEntry },
    props: {
      contentId: { type: Number, required: true },
      comments: { type: Array, required: true },
      prevCount: { type: Number, required: true },
      nextCount: { type: Number, required: true },
      pageSize: { type: Number, default: 10 },
      canComment: { type: Boolean, default: false },
      formShellHtml: { type: String, default: null },
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
        return vue$1.getConfig("user").isGuest === true;
      },
      commentsAreaId() {
        return "comments_area_" + getId(this.contentId);
      },
      prevLabel() {
        return vue$1.i18n.t("CommentModule.base", "Show previous {count} comments", { count: this.remainingPrev });
      },
      nextLabel() {
        return vue$1.i18n.t("CommentModule.base", "Show next {count} comments", { count: this.remainingNext });
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
      loadPrev() {
        if (this.busyPrev || this.items.length === 0) {
          return;
        }
        this.busyPrev = true;
        const cursor = this.items[0].id;
        vue$1.client.get(vue$1.url("/comment/comment/list", {
          contentId: this.contentId,
          commentId: cursor,
          direction: "previous",
          pageSize: this.pageSize
        })).then((response) => {
          this.items = [...response.comments, ...this.items];
          this.remainingPrev = response.prevCount;
        }).catch((e) => {
          vue$1.log.error(e, true);
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
        vue$1.client.get(vue$1.url("/comment/comment/list", {
          contentId: this.contentId,
          commentId: cursor,
          direction: "next",
          pageSize: this.pageSize
        })).then((response) => {
          this.items = [...this.items, ...response.comments];
          this.remainingNext = response.nextCount;
        }).catch((e) => {
          vue$1.log.error(e, true);
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
    const _component_CommentEntry = vue.resolveComponent("CommentEntry");
    return vue.openBlock(), vue.createElementBlock("div", {
      class: vue.normalizeClass(["comment", { "guest-mode": $options.guest }]),
      id: $options.commentsAreaId
    }, [
      $data.remainingPrev > 0 && $data.items.length > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2, [
        vue.createElementVNode(
          "a",
          {
            href: "#",
            class: vue.normalizeClass({ disabled: $data.busyPrev }),
            onClick: _cache[0] || (_cache[0] = vue.withModifiers((...args) => $options.loadPrev && $options.loadPrev(...args), ["prevent"]))
          },
          vue.toDisplayString($options.prevLabel),
          3
          /* TEXT, CLASS */
        )
      ])) : vue.createCommentVNode("v-if", true),
      (vue.openBlock(true), vue.createElementBlock(
        vue.Fragment,
        null,
        vue.renderList($data.items, (comment) => {
          return vue.openBlock(), vue.createElementBlock(
            vue.Fragment,
            {
              key: comment.id
            },
            [
              _cache[2] || (_cache[2] = vue.createElementVNode(
                "hr",
                { class: "comment-separator" },
                null,
                -1
                /* CACHED */
              )),
              vue.createVNode(_component_CommentEntry, {
                comment,
                "can-comment": $props.canComment,
                "form-shell-html": $props.formShellHtml,
                "page-size": $props.pageSize,
                highlighted: $props.anchorCommentId !== null && comment.id === $props.anchorCommentId
              }, null, 8, ["comment", "can-comment", "form-shell-html", "page-size", "highlighted"])
            ],
            64
            /* STABLE_FRAGMENT */
          );
        }),
        128
        /* KEYED_FRAGMENT */
      )),
      $data.remainingNext > 0 && $data.items.length > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3, [
        _cache[3] || (_cache[3] = vue.createElementVNode(
          "hr",
          { class: "comment-separator" },
          null,
          -1
          /* CACHED */
        )),
        vue.createElementVNode(
          "a",
          {
            href: "#",
            class: vue.normalizeClass({ disabled: $data.busyNext }),
            onClick: _cache[1] || (_cache[1] = vue.withModifiers((...args) => $options.loadNext && $options.loadNext(...args), ["prevent"]))
          },
          vue.toDisplayString($options.nextLabel),
          3
          /* TEXT, CLASS */
        )
      ])) : vue.createCommentVNode("v-if", true)
    ], 10, _hoisted_1);
  }
  const CommentList = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const _sfc_main = {
    i18nCategories: ["CommentModule.base"],
    components: { CommentList, CommentForm },
    props: {
      contentId: { type: Number, required: true },
      // serializeWindow() payload: {comments, prevCount, nextCount, total}
      initial: { type: Object, default: null },
      canComment: { type: Boolean, default: false },
      // __VUEFORM__ shell token template, see LegacyFormWrapper.vue
      formShellHtml: { type: String, default: null },
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
        isCollapsed: this.collapsed
      };
    },
    computed: {
      guest() {
        return vue$1.getConfig("user").isGuest === true;
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
    },
    unmounted() {
      if (this.mountEl) {
        this.mountEl.removeEventListener("humhub:comment:toggle", this.onToggle);
      }
    },
    methods: {
      fetchInitial() {
        vue$1.client.get(vue$1.url("/comment/comment/list", { contentId: this.contentId, pageSize: this.pageSize })).then((response) => {
          this.comments = response.comments;
          this.prevCount = response.prevCount;
          this.nextCount = response.nextCount;
          this.total = response.total;
          this.loaded = true;
        }).catch((e) => {
          vue$1.log.error(e, true);
          this.loaded = true;
        });
      },
      onToggle() {
        this.isCollapsed = false;
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
      }
    }
  };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_CommentList = vue.resolveComponent("CommentList");
    const _component_CommentForm = vue.resolveComponent("CommentForm");
    return vue.openBlock(), vue.createElementBlock(
      "div",
      {
        class: vue.normalizeClass(["bg-light p-2 mt-3 comment-container", { "d-none": $data.isCollapsed }])
      },
      [
        $data.loaded ? (vue.openBlock(), vue.createBlock(_component_CommentList, {
          key: 0,
          "content-id": $props.contentId,
          comments: $data.comments,
          "prev-count": $data.prevCount,
          "next-count": $data.nextCount,
          "page-size": $props.pageSize,
          "can-comment": $options.showForm,
          "form-shell-html": $props.formShellHtml,
          "anchor-comment-id": $props.anchorCommentId
        }, null, 8, ["content-id", "comments", "prev-count", "next-count", "page-size", "can-comment", "form-shell-html", "anchor-comment-id"])) : vue.createCommentVNode("v-if", true),
        $options.showForm && $props.formShellHtml ? (vue.openBlock(), vue.createBlock(_component_CommentForm, {
          key: 1,
          ref: "form",
          "shell-html": $props.formShellHtml,
          "content-id": $props.contentId
        }, null, 8, ["shell-html", "content-id"])) : vue.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("CommentSection", C0);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.comment.vue.js.map
