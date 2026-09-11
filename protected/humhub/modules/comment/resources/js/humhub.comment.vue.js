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
  const _sfc_main$5 = {
    props: {
      // Full adapted comment (see commentApi.js's mapComment()) - added purely so
      // this menu's `context` can expose it; the core entries below keep reading their
      // own discrete props unchanged, to avoid churning them.
      comment: { type: Object, required: true },
      permalink: { type: String, required: true },
      canEdit: { type: Boolean, default: false },
      canDelete: { type: Boolean, default: false },
      canAdminDelete: { type: Boolean, default: false },
      // Edit/delete are not part of the comment payload (which is caller-neutral and
      // therefore cacheable - see docs/develop/concept-api.md); the entry loads them when
      // this menu opens and shows a spinner item until they arrive.
      loadingPermissions: { type: Boolean, default: false }
    },
    // `open`: the menu was opened - the entry uses it to fetch the permissions once.
    emits: ["edit", "delete", "admin-delete", "open"],
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
      // This menu's built-in entries (see the class docblock, "comment.controls menu
      // entries") - `condition`/`onClick` ignore the `context` argument DropdownMenu passes
      // them since this component already has `this.canEdit`/`this.onEdit` etc. directly;
      // only a module's own registered entry needs to read `context.comment`.
      entries() {
        return [
          {
            id: "edit",
            label: vue.i18n.t("CommentModule.base", "Edit"),
            condition: () => this.canEdit,
            onClick: () => this.onEdit()
          },
          {
            id: "delete",
            label: vue.i18n.t("CommentModule.base", "Delete"),
            condition: () => this.canDelete,
            onClick: () => this.onDelete()
          }
        ];
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
  const _hoisted_1$4 = ["data-content-permalink", "data-content-permalink-title"];
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_DropdownMenu = vue$1.resolveComponent("DropdownMenu");
    return vue$1.openBlock(), vue$1.createElementBlock(
      vue$1.Fragment,
      null,
      [
        _cache[1] || (_cache[1] = vue$1.createElementVNode(
          "div",
          { class: "comment-entry-loader float-end" },
          null,
          -1
          /* CACHED */
        )),
        vue$1.createVNode(_component_DropdownMenu, {
          "toggle-aria-label": $options.toggleMenuLabel,
          "menu-id": "comment.controls",
          entries: $options.entries,
          context: { comment: $props.comment },
          loading: $props.loadingPermissions,
          onOpen: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("open"))
        }, {
          default: vue$1.withCtx(() => [
            vue$1.createElementVNode("li", null, [
              vue$1.createCommentVNode('\n                Plain anchor reusing the exact legacy attributes\n                (data-action-click="content.permalink" + the two\n                data-content-permalink* values) instead of a\n                Vue-owned click handler: humhub.action.js binds the\n                [data-action-click] delegate on `document` itself\n                (see bindAction(document, \'click\', ...) in\n                humhub.action.js), so it already fires for anchors\n                injected anywhere in the DOM, Vue-rendered islands\n                included, with zero extra wiring. The `content`\n                module (ui.content) is always loaded page-wide\n                wherever comments can appear, so this "just works".\n                Not a `menu-id`/`entries` entry: that descriptor shape has\n                no room for these legacy data attributes - see\n                DropdownMenu.vue\'s own "Slot contract" docblock note.\n            '),
              vue$1.createElementVNode("a", {
                href: "#",
                class: "dropdown-item",
                "data-action-click": "content.permalink",
                "data-content-permalink": $props.permalink,
                "data-content-permalink-title": $options.permalinkTitle
              }, vue$1.toDisplayString($options.permalinkLabel), 9, _hoisted_1$4)
            ])
          ]),
          _: 1
          /* STABLE */
        }, 8, ["toggle-aria-label", "entries", "context", "loading"])
      ],
      64
      /* STABLE_FRAGMENT */
    );
  }
  const CommentControls = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const _sfc_main$4 = {
    props: {
      show: { type: Boolean, default: false },
      adminMode: { type: Boolean, default: false }
    },
    emits: ["update:show", "confirm"],
    data() {
      return {
        notify: true,
        message: ""
      };
    },
    computed: {
      internalShow: {
        get() {
          return this.show;
        },
        set(value) {
          this.$emit("update:show", value);
        }
      },
      headerHtml() {
        return this.adminMode ? vue.i18n.t("CommentModule.base", "<strong>Delete</strong> comment?") : vue.i18n.t("CommentModule.base", "<strong>Confirm</strong> comment deleting");
      },
      bodyLabel() {
        return vue.i18n.t("CommentModule.base", "Do you really want to delete this comment?");
      },
      reasonLabel() {
        return vue.i18n.t("CommentModule.base", "Reason");
      },
      notifyLabel() {
        return vue.i18n.t("CommentModule.base", "Send a notification to author");
      },
      confirmLabel() {
        return this.adminMode ? vue.i18n.t("CommentModule.base", "Confirm") : vue.i18n.t("CommentModule.base", "Delete");
      },
      cancelLabel() {
        return vue.i18n.t("CommentModule.base", "Cancel");
      },
      confirmDisabled() {
        return this.adminMode && this.notify && this.message.trim() === "";
      }
    },
    watch: {
      show(value) {
        if (value) {
          this.notify = true;
          this.message = "";
        }
      }
    },
    methods: {
      close() {
        this.$emit("update:show", false);
      },
      confirm() {
        const fields = this.adminMode && this.notify ? { notify: 1, message: this.message.trim() } : null;
        this.$emit("confirm", fields);
      }
    }
  };
  const _hoisted_1$3 = ["id", "innerHTML"];
  const _hoisted_2$2 = ["aria-label"];
  const _hoisted_3$2 = { key: 1 };
  const _hoisted_4$1 = ["disabled"];
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_TextareaField = vue$1.resolveComponent("TextareaField");
    const _component_CheckboxField = vue$1.resolveComponent("CheckboxField");
    const _component_HumHubForm = vue$1.resolveComponent("HumHubForm");
    const _component_UiModal = vue$1.resolveComponent("UiModal");
    return vue$1.openBlock(), vue$1.createBlock(_component_UiModal, {
      show: $options.internalShow,
      "onUpdate:show": _cache[5] || (_cache[5] = ($event) => $options.internalShow = $event)
    }, {
      header: vue$1.withCtx(({ titleId }) => [
        vue$1.createElementVNode("h5", {
          class: "modal-title",
          id: titleId,
          innerHTML: $options.headerHtml
        }, null, 8, _hoisted_1$3),
        vue$1.createElementVNode("button", {
          type: "button",
          class: "btn-close",
          "aria-label": $options.cancelLabel,
          onClick: _cache[0] || (_cache[0] = (...args) => $options.close && $options.close(...args))
        }, null, 8, _hoisted_2$2)
      ]),
      footer: vue$1.withCtx(() => [
        vue$1.createElementVNode(
          "button",
          {
            type: "button",
            class: "btn btn-light",
            onClick: _cache[3] || (_cache[3] = (...args) => $options.close && $options.close(...args))
          },
          vue$1.toDisplayString($options.cancelLabel),
          1
          /* TEXT */
        ),
        vue$1.createElementVNode("button", {
          type: "button",
          class: "btn btn-danger",
          disabled: $options.confirmDisabled,
          onClick: _cache[4] || (_cache[4] = (...args) => $options.confirm && $options.confirm(...args))
        }, vue$1.toDisplayString($options.confirmLabel), 9, _hoisted_4$1)
      ]),
      default: vue$1.withCtx(() => [
        $props.adminMode ? (vue$1.openBlock(), vue$1.createBlock(_component_HumHubForm, {
          key: 0,
          "model-name": "AdminDeleteCommentForm"
        }, {
          default: vue$1.withCtx(() => [
            vue$1.createVNode(_component_TextareaField, {
              attribute: "message",
              label: $options.reasonLabel,
              rows: 3,
              disabled: !$data.notify,
              modelValue: $data.message,
              "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $data.message = $event)
            }, null, 8, ["label", "disabled", "modelValue"]),
            vue$1.createVNode(_component_CheckboxField, {
              attribute: "notify",
              label: $options.notifyLabel,
              modelValue: $data.notify,
              "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => $data.notify = $event)
            }, null, 8, ["label", "modelValue"])
          ]),
          _: 1
          /* STABLE */
        })) : (vue$1.openBlock(), vue$1.createElementBlock(
          "p",
          _hoisted_3$2,
          vue$1.toDisplayString($options.bodyLabel),
          1
          /* TEXT */
        ))
      ]),
      _: 1
      /* STABLE */
    }, 8, ["show"]);
  }
  const CommentDeleteModal = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const blockedUserIds = () => vue.getConfig("user").blockedUserIds || [];
  const currentUserId = () => {
    const id = vue.getConfig("user").id;
    return typeof id === "undefined" ? null : id;
  };
  const toDate = (value) => value ? new Date(value) : null;
  const mapComment = (comment) => {
    if (!comment) {
      return null;
    }
    const createdAt = toDate(comment.createdAt);
    const updatedAt = toDate(comment.updatedAt);
    const author = comment.createdBy || null;
    return {
      ...comment,
      createdAt,
      updatedAt,
      author,
      isEdited: !!(createdAt && updatedAt && updatedAt.getTime() !== createdAt.getTime()),
      blocked: !!(author && blockedUserIds().indexOf(author.id) !== -1),
      files: comment.files || [],
      extensions: comment.extensions || {},
      replies: comment.replies ? { ...comment.replies, items: comment.replies.items.map(mapComment) } : null
    };
  };
  const isAdminDelete = (comment, canDelete) => !!(canDelete && comment && comment.author && currentUserId() !== null && comment.author.id !== currentUserId());
  const mapWindow = (window) => ({
    ...window,
    results: (window.results || []).map(mapComment)
  });
  const fetchWindow = ({ contentId, parentCommentId, ...params }) => {
    const path = parentCommentId ? `comment/parent/${parentCommentId}/window` : `comment/content/${contentId}/window`;
    return vue.client.get(vue.apiUrl(path, params)).then(mapWindow);
  };
  const fetchComment = (id) => vue.client.get(vue.apiUrl(`comment/${id}`)).then(mapComment);
  const fetchCommentPermissions = (id) => vue.client.get(vue.apiUrl(`comment/${id}/permissions`));
  const fetchLikeStates = (recordIds) => recordIds.length ? vue.client.get(vue.apiUrl("like/states", { recordIds: recordIds.join(",") })).then((response) => response.results || {}) : Promise.resolve({});
  const collectRecordIds = (comments) => {
    const ids = [];
    (comments || []).forEach((comment) => {
      if (comment.recordId) {
        ids.push(comment.recordId);
      }
      (comment.replies && comment.replies.items || []).forEach((reply) => {
        if (reply.recordId) {
          ids.push(reply.recordId);
        }
      });
    });
    return [...new Set(ids)];
  };
  const createComment = ({ contentId, parentCommentId, message, fileList }) => {
    const params = parentCommentId ? { contentId, parentCommentId } : { contentId };
    return vue.client.post(vue.apiUrl("comment", params), { data: { message, fileList } }).then(mapComment);
  };
  const updateComment = (id, { message, fileList }) => vue.client.put(vue.apiUrl(`comment/${id}`), { data: { message, fileList } }).then(mapComment);
  const deleteComment = (id, fields) => vue.client.del(vue.apiUrl(`comment/${id}`), fields ? { data: fields } : void 0);
  const extractFieldErrors = (response) => {
    const errors = response && response.errors || null;
    if (!errors) {
      return null;
    }
    if (errors.parentCommentId && !errors.message) {
      return { ...errors, message: errors.parentCommentId };
    }
    return errors;
  };
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
      submitIconHtml: { type: String, default: null },
      // Upload field settings: `{max, handlersHtml}` (see the "Attachments" docblock
      // section below), or null when the island rendered no form shell.
      uploadOptions: { type: Object, default: null }
    },
    emits: ["created", "updated"],
    data() {
      return {
        busy: false,
        // Resolved once in mounted() - see the "Submit button placement" docblock
        // section above. `null` until then/if the shell has no button-group
        // container, which Teleport's `disabled` prop treats as "render in place".
        teleportTarget: null,
        // Files attached in THIS editing session (API file shapes, owned by the
        // UploadField below) - see the "Attachments" docblock section.
        files: [],
        // True while the upload field has a request in flight, so the submit button
        // stays disabled until the guids it would submit actually exist.
        uploadBusy: false
      };
    },
    computed: {
      // Same key the legacy submit button's aria-label used (see the
      // "Submit button" docblock section above) - NOT a CommentModule.base
      // key. CommentSection preloads 'ContentModule.base' alongside its
      // own category for exactly this.
      sendLabel() {
        return vue.i18n.t("ContentModule.base", "Submit");
      },
      // Same label the shell's server-rendered upload button carried before the field
      // became Vue-native - again a ContentModule.base key CommentSection preloads.
      attachLabel() {
        return vue.i18n.t("ContentModule.base", "Attach Files");
      },
      uploadMax() {
        return this.uploadOptions && this.uploadOptions.max || 0;
      },
      uploadHandlersHtml() {
        return this.uploadOptions && this.uploadOptions.handlersHtml || "";
      },
      // Deterministic identity for the shell's DOM ids, threaded down to
      // LegacyFormWrapper (see ITS "Unique-id contract" docblock section for
      // the full contract this scheme satisfies): unique among every comment
      // form that can be mounted at once — the main create form (`c<contentId>`),
      // one reply form per commented-on entry (`-r<parentCommentId>`), one edit
      // form per comment (`-e<editCommentId>`) — AND stable across page loads
      // for the same logical form. Stability is what keys the richtext
      // editor's sessionStorage draft backup correctly: the wrapper's own
      // per-page-load counter fallback produced `vueform-1` on EVERY page,
      // merging drafts of unrelated contents across navigations
      // (browser-verified) and arming phantom unsaved-changes confirms.
      formInstanceKey() {
        if (this.editCommentId !== null) {
          return "c" + this.contentId + "-e" + this.editCommentId;
        }
        if (this.parentCommentId !== null) {
          return "c" + this.contentId + "-r" + this.parentCommentId;
        }
        return "c" + this.contentId;
      }
    },
    mounted() {
      const shellEl = this.$refs.richtext.getShellElement();
      this.formEl = shellEl.querySelector("form");
      if (this.formEl) {
        this.formEl.addEventListener("submit", this.onSubmit);
      }
      this.teleportTarget = shellEl.querySelector(".richtext-create-buttons");
      this.zoneEl = shellEl;
      this.zoneEl.addEventListener("drop", this.onZoneDrop);
      this.zoneEl.addEventListener("dragover", this.onZoneDragOver);
      this.zoneEl.addEventListener("paste", this.onZonePaste);
      if (this.initialMessage !== null) {
        this.$nextTick(() => {
          if (this.$refs.richtext) {
            this.$refs.richtext.setValue(this.initialMessage);
          }
        });
      }
    },
    beforeUnmount() {
      if (this.formEl) {
        this.formEl.removeEventListener("submit", this.onSubmit);
      }
      if (this.zoneEl) {
        this.zoneEl.removeEventListener("drop", this.onZoneDrop);
        this.zoneEl.removeEventListener("dragover", this.onZoneDragOver);
        this.zoneEl.removeEventListener("paste", this.onZonePaste);
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
        const payload = {
          message: this.$refs.richtext.getValue(),
          fileList: this.files.map((file) => file.guid)
        };
        this.busy = true;
        this.$refs.form.clearErrors();
        const request = isEdit ? updateComment(this.editCommentId, payload) : createComment({
          contentId: this.contentId,
          parentCommentId: this.parentCommentId,
          ...payload
        });
        request.then((comment) => {
          this.busy = false;
          this.clear();
          this.$emit(isEdit ? "updated" : "created", comment);
        }).catch((response) => {
          this.busy = false;
          const fieldErrors = extractFieldErrors(response);
          if (response && response.status === 422 && fieldErrors) {
            this.$refs.form.setErrors({ errors: fieldErrors });
          } else {
            vue.log.error(response, true);
          }
        });
      },
      /** Proxies to the richtext field so callers (reply toggle, section toggle) don't touch jQuery/legacy widgets. */
      focus() {
        if (this.$refs.richtext) {
          this.$refs.richtext.focus();
        }
      },
      /**
       * Empties the form: the editor (via RichTextField's clear(), which also resets the
       * unsaved-changes guard baseline - see this component's own "Unsaved-changes guard"
       * docblock section) and the attachment list. Called both on a successful create/reply
       * submit (below) and by CommentEntry when a reply/edit form is discarded without
       * submitting.
       */
      clear() {
        if (this.$refs.richtext) {
          this.$refs.richtext.clear();
        }
        if (this.$refs.upload) {
          this.$refs.upload.clear();
        }
      },
      /**
       * Drop/paste anywhere in the comment box attaches files - the area the legacy upload
       * widget pointed its `dropZone`/`pasteZone` options at (the shell's own root element,
       * `#<token>_comment_create_form`). The upload field itself covers its own root; this
       * extends the zone to the whole box, and both funnel into the same `addFiles()`.
       */
      onZoneDrop(event) {
        const files = Array.from(event.dataTransfer && event.dataTransfer.files || []);
        if (files.length && this.$refs.upload) {
          event.preventDefault();
          this.$refs.upload.addFiles(files);
        }
      },
      onZonePaste(event) {
        const files = Array.from(event.clipboardData && event.clipboardData.files || []);
        if (files.length && this.$refs.upload) {
          this.$refs.upload.addFiles(files);
        }
      },
      onZoneDragOver(event) {
        event.preventDefault();
      }
    }
  };
  const _hoisted_1$2 = ["innerHTML"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_RichTextField = vue$1.resolveComponent("RichTextField");
    const _component_UploadField = vue$1.resolveComponent("UploadField");
    const _component_SubmitButton = vue$1.resolveComponent("SubmitButton");
    const _component_HumHubForm = vue$1.resolveComponent("HumHubForm");
    return vue$1.openBlock(), vue$1.createBlock(_component_HumHubForm, {
      ref: "form",
      "model-name": "Comment",
      busy: $data.busy || $data.uploadBusy,
      onSubmit: $options.onSubmit
    }, {
      default: vue$1.withCtx(() => [
        vue$1.createVNode(_component_RichTextField, {
          ref: "richtext",
          attribute: "message",
          "shell-html": $props.shellHtml,
          "instance-key": $options.formInstanceKey
        }, null, 8, ["shell-html", "instance-key"]),
        vue$1.createVNode(_component_UploadField, {
          ref: "upload",
          attribute: "fileList",
          modelValue: $data.files,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => $data.files = $event),
          max: $options.uploadMax,
          "handlers-html": $options.uploadHandlersHtml,
          title: $options.attachLabel,
          "trigger-target": $data.teleportTarget,
          onBusy: _cache[1] || (_cache[1] = ($event) => $data.uploadBusy = $event)
        }, null, 8, ["modelValue", "max", "handlers-html", "title", "trigger-target"]),
        (vue$1.openBlock(), vue$1.createBlock(vue$1.Teleport, {
          to: $data.teleportTarget,
          disabled: !$data.teleportTarget
        }, [
          vue$1.createVNode(_component_SubmitButton, {
            loader: false,
            class: vue$1.normalizeClass(["btn btn-accent btn-comment-submit btn-sm", { "btn-icon-only": $props.submitIconHtml }]),
            "aria-label": $options.sendLabel,
            onClick: $options.onSubmit
          }, {
            default: vue$1.withCtx(() => [
              $props.submitIconHtml ? (vue$1.openBlock(), vue$1.createElementBlock("span", {
                key: 0,
                innerHTML: $props.submitIconHtml
              }, null, 8, _hoisted_1$2)) : (vue$1.openBlock(), vue$1.createElementBlock(
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
            ]),
            _: 1
            /* STABLE */
          }, 8, ["class", "aria-label", "onClick"])
        ], 8, ["to", "disabled"]))
      ]),
      _: 1
      /* STABLE */
    }, 8, ["busy", "onSubmit"]);
  }
  const CommentForm = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const _sfc_main$2 = {
    name: "CommentEntry",
    components: { CommentControls, CommentDeleteModal, CommentForm },
    inject: {
      commentRevisions: { default: () => ({}) },
      bumpCommentRevision: { default: () => () => {
      } },
      pruneCommentRevision: { default: () => () => {
      } },
      adjustTotal: { default: () => () => {
      } },
      adjustRootTotal: { default: () => () => {
      } },
      registerKnownId: { default: () => () => {
      } },
      isKnownId: { default: () => () => false },
      // recordId => {total, liked, canLike} for every comment of this section, owned by
      // CommentSection: the like state is the one per-record value that depends on WHO is
      // asking, so it travels beside the (cacheable) comment payload rather than inside it
      // (see docs/develop/concept-api.md). `ensureLikeStates` loads the states of comments
      // that just entered the tree.
      likeStates: { default: () => ({}) },
      ensureLikeStates: { default: () => () => {
      } },
      // See CommentSection's own prop of that name.
      excludeMediaFiles: { default: false }
    },
    props: {
      comment: { type: Object, required: true },
      canComment: { type: Boolean, default: false },
      formShellHtml: { type: String, default: null },
      submitIconHtml: { type: String, default: null },
      // Upload field settings, handed down to the reply/edit forms this entry can open.
      uploadOptions: { type: Object, default: null },
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
        // Blocked-author masking is purely client-side (see commentApi.js's
        // `blocked` derivation): revealing is a local display toggle, no refetch —
        // the payload always carries the full comment.
        revealed: false,
        // `{canEdit, canDelete}` once the context menu was opened for the first time -
        // fetched then rather than shipped with the comment (see loadPermissions()).
        permissions: null,
        permissionsBusy: false,
        childItems: this.comment.replies ? [...this.comment.replies.items] : [],
        childTotal: this.comment.replies ? this.comment.replies.total : 0,
        // Id of the FIRST (oldest) item of the last SERVER-PAGINATED reply window (initial
        // hydration or a loadMoreReplies() response) - deliberately never touched by
        // onReplyCreated()'s own/live append, see "Previous-direction pagination fix" below.
        childFirstCursorId: this.comment.replies && this.comment.replies.items.length ? this.comment.replies.items[0].id : null,
        busyReplies: false,
        busyEdit: false,
        // Drives the native <CommentDeleteModal> (see its own docblock) — one modal
        // serving both the plain confirm and the admin-delete (notify/reason) mode,
        // selected by the adapted comment's canAdminDelete.
        deleteModalOpen: false
      };
    },
    computed: {
      showReplyToggle() {
        return !this.isNested && this.canComment;
      },
      // The like state of THIS entry, out of the section's map (see the `likeStates` prop).
      // Absent until the section has it, which is why the like link renders conditionally.
      likeState() {
        return this.likeStates[this.comment.recordId] || null;
      },
      // Deleting someone else's comment is moderation — drives the delete modal's
      // reason/notify mode. Derived from the fetched permissions, so `false` until the menu
      // was opened; that is fine, since both delete paths start from that menu.
      canAdminDelete() {
        return isAdminDelete(this.comment, !!(this.permissions && this.permissions.canDelete));
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
      // Single derived count driving BOTH the `v-if="childHasMore"` gate and this label's
      // `{count}` (previously two independently-mutated fields that could desync) -
      // `childTotal` is kept correct by every mutation (onReplyCreated/onChildRemoved),
      // so this can never show a nonzero gate with a stale/zero label or vice versa.
      childRemaining() {
        return Math.max(0, this.childTotal - this.childItems.length);
      },
      childHasMore() {
        return this.childRemaining > 0;
      },
      // Same wording/category/key as CommentList's own "show previous" link
      // (`prevLabel`), not "show next": the hidden replies are always the OLDER ones (the
      // preview shows the NEWEST N, see "Previous-direction pagination fix" above), so this
      // is symmetric with the root list's own loadPrev()/prevLabel, not loadNext()/
      // nextLabel. The legacy nested Comments::widget() reused these exact same ShowMore
      // strings for children too - there never was a distinct "replies" message key.
      moreRepliesLabel() {
        return vue.i18n.t("CommentModule.base", "Show previous {count} comments", { count: this.childRemaining });
      },
      // The adapted comment shape carries real `Date`s (DB-format wire timestamps
      // parsed with the announced server timezone, see commentApi.js/
      // parseServerDateTime). Formatted client-side via the browser locale/timezone
      // (documented parity gap vs. TimeAgo::getFullDateTime(), which uses the HumHub
      // profile timezone). This text is only ever visible for an instant: v-additions
      // runs the real `timeago` addition (registered selector-less in
      // humhub.ui.additions.js, dispatched per-element through the generic
      // `[data-ui-addition]` addition - see TimeAgo::renderTimeAgo()'s own
      // `data-ui-addition="timeago"` markup, reproduced above) on mount,
      // which immediately overwrites it with a live relative time.
      absoluteTime() {
        return this.comment.createdAt ? this.comment.createdAt.toLocaleString() : null;
      },
      // The timeago addition needs a machine-readable instant on the `datetime`
      // attribute — the adapted shape's Date serialized back to ISO.
      createdAtIso() {
        return this.comment.createdAt ? this.comment.createdAt.toISOString() : null;
      },
      // Same client-side-formatting choice as `absoluteTime` above, for the same
      // documented parity gap vs. UpdatedIcon::getByDated()'s server/profile-
      // timezone-formatted tooltip. `null` (no `title` attribute) whenever the
      // comment isn't edited — the marker itself is gated on `isEdited`.
      updatedAtTitle() {
        return this.comment.isEdited && this.comment.updatedAt ? this.comment.updatedAt.toLocaleString() : null;
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
      onEdit() {
        if (this.busyEdit) {
          return;
        }
        this.busyEdit = true;
        fetchComment(this.comment.id).then((comment) => {
          this.editMessage = comment.message;
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
        this.adjustTotal(1);
        this.registerKnownId(comment.id);
        this.ensureLikeStates([comment]);
      },
      onChildRemoved(id) {
        this.childItems = this.childItems.filter((child) => child.id !== id);
        this.childTotal = Math.max(0, this.childTotal - 1);
        this.pruneCommentRevision(id);
      },
      onChildUpdated({ id, comment }) {
        const index = this.childItems.findIndex((child) => child.id === id);
        if (index !== -1) {
          this.childItems.splice(index, 1, comment);
        }
        this.bumpCommentRevision(id);
      },
      // Loads `{canEdit, canDelete}` the first time this entry's context menu opens. They
      // are deliberately not part of the comment payload — see fetchCommentPermissions() and
      // docs/develop/concept-api.md — and they are needed nowhere else, since both the edit
      // and the delete flow start from this menu. Guests never have permissions, so they
      // never trigger a request.
      loadPermissions() {
        if (this.permissions || this.permissionsBusy || vue.getConfig("user").isGuest === true) {
          return;
        }
        this.permissionsBusy = true;
        return fetchCommentPermissions(this.comment.id).then((permissions) => {
          this.permissions = permissions;
        }).catch((e) => {
          vue.log.error(e, true);
        }).then(() => {
          this.permissionsBusy = false;
        });
      },
      // Both CommentControls events land here: the same native <CommentDeleteModal>
      // serves the plain confirm and the admin-delete mode — its `adminMode` prop
      // reads `canAdminDelete` directly, so no per-event branching is needed.
      onDelete() {
        this.deleteModalOpen = true;
      },
      onAdminDelete() {
        this.deleteModalOpen = true;
      },
      performDelete(extraFields) {
        this.deleteModalOpen = false;
        return deleteComment(this.comment.id, extraFields).then(() => {
          this.adjustTotal(-(1 + (this.isNested ? 0 : this.childTotal)));
          if (!this.isNested) {
            this.adjustRootTotal(-1);
          }
          this.$emit("entry-removed", this.comment.id);
        }).catch((e) => {
          vue.log.error(e, true);
        });
      },
      // See this component's own docblock, "Previous-direction pagination fix": cursors
      // from `childFirstCursorId` (the HEAD of the last SERVER-PAGINATED window), mirroring
      // CommentList's own loadPrev() exactly - a straight prepend, no dedup, no total
      // resync (see the docblock for why none of those are needed here).
      loadMoreReplies() {
        if (this.busyReplies || this.childItems.length === 0 || this.childFirstCursorId === null) {
          return;
        }
        this.busyReplies = true;
        const cursor = this.childFirstCursorId;
        fetchWindow({
          contentId: this.comment.contentId,
          parentCommentId: this.comment.id,
          commentId: cursor,
          direction: "previous",
          pageSize: this.pageSize
        }).then((response) => {
          this.childItems = [...response.results, ...this.childItems];
          this.ensureLikeStates(response.results);
          if (response.results.length > 0) {
            this.childFirstCursorId = response.results[0].id;
          }
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
  const _hoisted_5 = { class: "flex-grow-1" };
  const _hoisted_6 = { class: "comment-heading" };
  const _hoisted_7 = ["href", "data-contentcontainer-id", "data-guid"];
  const _hoisted_8 = ["datetime", "title"];
  const _hoisted_9 = ["title"];
  const _hoisted_10 = ["id"];
  const _hoisted_11 = { class: "wall-entry-controls" };
  const _hoisted_12 = ["data-count"];
  const _hoisted_13 = {
    key: 0,
    class: "nested-comments-root"
  };
  const _hoisted_14 = { class: "comment" };
  const _hoisted_15 = {
    key: 0,
    class: "showMore"
  };
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_CommentControls = vue$1.resolveComponent("CommentControls");
    const _component_UserImage = vue$1.resolveComponent("UserImage");
    const _component_CommentForm = vue$1.resolveComponent("CommentForm");
    const _component_RichTextOutput = vue$1.resolveComponent("RichTextOutput");
    const _component_AttachedFiles = vue$1.resolveComponent("AttachedFiles");
    const _component_LikeButton = vue$1.resolveComponent("LikeButton");
    const _component_ExtensionSlot = vue$1.resolveComponent("ExtensionSlot");
    const _component_CommentEntry = vue$1.resolveComponent("CommentEntry", true);
    const _component_CommentDeleteModal = vue$1.resolveComponent("CommentDeleteModal");
    const _directive_additions = vue$1.resolveDirective("additions");
    return $props.comment.blocked && !$data.revealed ? (vue$1.openBlock(), vue$1.createElementBlock("div", {
      key: 0,
      id: "comment_" + $props.comment.id,
      class: "d-flex comment-blocked-user"
    }, [
      _cache[5] || (_cache[5] = vue$1.createElementVNode(
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
            onClick: _cache[0] || (_cache[0] = vue$1.withModifiers(($event) => $data.revealed = true, ["prevent"]))
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
        comment: $props.comment,
        permalink: $props.comment.url,
        "can-edit": $data.permissions ? $data.permissions.canEdit : false,
        "can-delete": $data.permissions ? $data.permissions.canDelete : false,
        "can-admin-delete": $options.canAdminDelete,
        "loading-permissions": $data.permissionsBusy,
        onOpen: $options.loadPermissions,
        onEdit: $options.onEdit,
        onDelete: $options.onDelete,
        onAdminDelete: $options.onAdminDelete
      }, null, 8, ["comment", "permalink", "can-edit", "can-delete", "can-admin-delete", "loading-permissions", "onOpen", "onEdit", "onDelete", "onAdminDelete"]),
      vue$1.createElementVNode("div", _hoisted_4, [
        vue$1.createVNode(
          _component_UserImage,
          vue$1.mergeProps($props.comment.author, { size: 25 }),
          null,
          16
          /* FULL_PROPS */
        )
      ]),
      vue$1.createElementVNode("div", _hoisted_5, [
        vue$1.createElementVNode("h4", _hoisted_6, [
          vue$1.createElementVNode("a", {
            href: $props.comment.author.url,
            "data-contentcontainer-id": $props.comment.author.contentContainerId,
            "data-guid": $props.comment.author.guid
          }, vue$1.toDisplayString($props.comment.author.displayName), 9, _hoisted_7),
          vue$1.createElementVNode("small", null, [
            _cache[7] || (_cache[7] = vue$1.createTextVNode(
              " · ",
              -1
              /* CACHED */
            )),
            vue$1.createElementVNode("time", {
              class: "tt time timeago",
              "data-ui-addition": "timeago",
              datetime: $options.createdAtIso,
              title: $options.absoluteTime
            }, vue$1.toDisplayString($options.absoluteTime), 9, _hoisted_8),
            $props.comment.isEdited ? (vue$1.openBlock(), vue$1.createElementBlock(
              vue$1.Fragment,
              { key: 0 },
              [
                _cache[6] || (_cache[6] = vue$1.createTextVNode(
                  " · ",
                  -1
                  /* CACHED */
                )),
                vue$1.createElementVNode("i", {
                  class: "tt fa fa-clock-o text-body-secondary",
                  title: $options.updatedAtTitle,
                  "aria-hidden": "true"
                }, null, 8, _hoisted_9)
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
                "upload-options": $props.uploadOptions,
                onUpdated: $options.onEditSaved
              }, null, 8, ["shell-html", "content-id", "edit-comment-id", "initial-message", "submit-icon-html", "upload-options", "onUpdated"]),
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
                message: $props.comment.message,
                "render-options": $props.comment.messageRenderOptions
              }, null, 8, ["data-read-more-text", "message", "render-options"]),
              $props.comment.files.length ? (vue$1.openBlock(), vue$1.createBlock(_component_AttachedFiles, {
                key: 0,
                files: $props.comment.files,
                "gallery-id": "gallery-comment-" + $props.comment.id,
                "exclude-media": $options.excludeMediaFiles
              }, null, 8, ["files", "gallery-id", "exclude-media"])) : vue$1.createCommentVNode("v-if", true)
            ],
            64
            /* STABLE_FRAGMENT */
          ))
        ], 8, _hoisted_10),
        vue$1.createElementVNode("div", _hoisted_11, [
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
            }, " (" + vue$1.toDisplayString($data.childTotal) + ")", 13, _hoisted_12)
          ])) : vue$1.createCommentVNode("v-if", true),
          $options.showReplyToggle && $options.likeState && $options.likeState.canLike ? (vue$1.openBlock(), vue$1.createElementBlock(
            vue$1.Fragment,
            { key: 1 },
            [
              vue$1.createTextVNode(" · ")
            ],
            64
            /* STABLE_FRAGMENT */
          )) : vue$1.createCommentVNode("v-if", true),
          $options.likeState && $options.likeState.canLike ? (vue$1.openBlock(), vue$1.createBlock(_component_LikeButton, {
            key: 2,
            "record-id": $props.comment.recordId,
            "like-count": $options.likeState.total,
            "current-user-liked": $options.likeState.liked
          }, null, 8, ["record-id", "like-count", "current-user-liked"])) : vue$1.createCommentVNode("v-if", true),
          vue$1.createVNode(_component_ExtensionSlot, {
            name: "comment.links",
            context: { comment: $props.comment }
          }, null, 8, ["context"])
        ]),
        $props.comment.replies ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_13, [
          vue$1.createElementVNode(
            "div",
            {
              class: vue$1.normalizeClass(["bg-light p-2 mt-3 comment-container", { "d-none": !$data.childItems.length && !$data.replyOpen }])
            },
            [
              vue$1.createElementVNode("div", _hoisted_14, [
                $options.childHasMore ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_15, [
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
                ])) : vue$1.createCommentVNode("v-if", true),
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
                        _cache[8] || (_cache[8] = vue$1.createElementVNode(
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
                          "upload-options": $props.uploadOptions,
                          "page-size": $props.pageSize,
                          onEntryRemoved: $options.onChildRemoved,
                          onEntryUpdated: $options.onChildUpdated
                        }, null, 8, ["comment", "can-comment", "form-shell-html", "submit-icon-html", "upload-options", "page-size", "onEntryRemoved", "onEntryUpdated"])
                      ],
                      64
                      /* STABLE_FRAGMENT */
                    );
                  }),
                  128
                  /* KEYED_FRAGMENT */
                ))
              ]),
              $data.replyOpen && $props.formShellHtml ? (vue$1.openBlock(), vue$1.createBlock(_component_CommentForm, {
                key: 0,
                ref: "replyForm",
                "shell-html": $props.formShellHtml,
                "content-id": $props.comment.contentId,
                "parent-comment-id": $props.comment.id,
                "submit-icon-html": $props.submitIconHtml,
                "upload-options": $props.uploadOptions,
                onCreated: $options.onReplyCreated
              }, null, 8, ["shell-html", "content-id", "parent-comment-id", "submit-icon-html", "upload-options", "onCreated"])) : vue$1.createCommentVNode("v-if", true)
            ],
            2
            /* CLASS */
          )
        ])) : vue$1.createCommentVNode("v-if", true),
        vue$1.createVNode(_component_CommentDeleteModal, {
          show: $data.deleteModalOpen,
          "onUpdate:show": _cache[4] || (_cache[4] = ($event) => $data.deleteModalOpen = $event),
          "admin-mode": $options.canAdminDelete,
          onConfirm: $options.performDelete
        }, null, 8, ["show", "admin-mode", "onConfirm"])
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
      } },
      adjustTotal: { default: () => () => {
      } },
      adjustRootTotal: { default: () => () => {
      } },
      registerKnownId: { default: () => () => {
      } },
      isKnownId: { default: () => () => false },
      // See CommentEntry's own inject block - the like state travels beside the comments,
      // not inside them, and has to be loaded for every window this component pages in.
      ensureLikeStates: { default: () => () => {
      } }
    },
    props: {
      contentId: { type: Number, required: true },
      comments: { type: Array, required: true },
      prevCount: { type: Number, required: true },
      // Badge count (CommentSection's own `total`, ALL comments including replies) - only
      // used here to keep that badge in sync on a real fetch (see loadPrev()/loadNext()),
      // NOT for `remainingNext` (see `rootTotal` below and this component's own docblock,
      // "Root-vs-all total" section).
      total: { type: Number, required: true },
      // Authoritative ROOT-comment count (CommentSection's own `rootTotal`) - drives the
      // `remainingNext` computed below instead of the server's per-request `nextCount` or
      // the all-comments `total`, see this component's own docblock, "Next-pagination gap
      // fix" and "Root-vs-all total".
      rootTotal: { type: Number, required: true },
      pageSize: { type: Number, default: 10 },
      canComment: { type: Boolean, default: false },
      formShellHtml: { type: String, default: null },
      submitIconHtml: { type: String, default: null },
      // Upload field settings, handed down to every form this list can open.
      uploadOptions: { type: Object, default: null },
      anchorCommentId: { type: Number, default: null }
    },
    data() {
      return {
        items: [...this.comments],
        remainingPrev: this.prevCount,
        // Id of the last item of the last SERVER-PAGINATED window (initial hydration or a
        // loadNext() response) - deliberately never touched by appendRoot()/replaceRoot()
        // (own/live creates), see the docblock below.
        lastCursorId: this.comments.length ? this.comments[this.comments.length - 1].id : null,
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
      // Single derived count (see this component's own docblock) instead of a second,
      // independently-mutated `remainingNext` field: `rootTotal` already accounts for
      // every ROOT create/delete/live mutation (CommentSection's `adjustRootTotal()`), so
      // subtracting the hidden-before (`remainingPrev`) and loaded (`items.length`) counts
      // always yields the true hidden-after count, with no separate bookkeeping that can
      // drift out of sync with the "show more" gate. Deliberately `rootTotal`, not `total`
      // (all comments including replies) - see this component's own docblock, "Root-vs-all
      // total".
      remainingNext() {
        return Math.max(0, this.rootTotal - this.items.length - this.remainingPrev);
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
        this.lastCursorId = value.length ? value[value.length - 1].id : null;
      },
      prevCount(value) {
        this.remainingPrev = value;
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
        fetchWindow({
          contentId: this.contentId,
          commentId: cursor,
          direction: "previous",
          pageSize: this.pageSize
        }).then((response) => {
          this.items = [...response.results, ...this.items];
          this.ensureLikeStates(response.results);
          this.remainingPrev = response.prevCount;
          this.adjustTotal(response.total - this.total);
          this.adjustRootTotal((response.rootTotal ?? response.total) - this.rootTotal);
        }).catch((e) => {
          vue.log.error(e, true);
        }).finally(() => {
          this.busyPrev = false;
        });
      },
      // See this component's own docblock, "Next-pagination gap fix": the cursor is the
      // last known-good SERVER cursor (`lastCursorId`), never the tail of `items` (which may
      // be an own/live-appended comment past an unloaded gap). The response can therefore
      // re-return comments already present in `items` (that same appended tail) - deduped via
      // the shared `isKnownId()`/`registerKnownId()` mechanism - and genuinely new ones are
      // spliced in right before the first item newer than the cursor, i.e. before that
      // appended tail, not blindly at the end.
      loadNext() {
        if (this.busyNext || this.items.length === 0 || this.lastCursorId === null) {
          return;
        }
        this.busyNext = true;
        const cursor = this.lastCursorId;
        fetchWindow({
          contentId: this.contentId,
          commentId: cursor,
          direction: "next",
          pageSize: this.pageSize
        }).then((response) => {
          const newComments = response.results.filter((comment) => !this.isKnownId(comment.id));
          newComments.forEach((comment) => this.registerKnownId(comment.id));
          this.ensureLikeStates(newComments);
          let insertIndex = this.items.findIndex((item) => item.id > cursor);
          if (insertIndex === -1) {
            insertIndex = this.items.length;
          }
          this.items.splice(insertIndex, 0, ...newComments);
          if (response.results.length > 0) {
            this.lastCursorId = response.results[response.results.length - 1].id;
          }
          this.adjustTotal(response.total - this.total);
          this.adjustRootTotal((response.rootTotal ?? response.total) - this.rootTotal);
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
                "upload-options": $props.uploadOptions,
                "page-size": $props.pageSize,
                highlighted: $props.anchorCommentId !== null && comment.id === $props.anchorCommentId,
                onEntryRemoved: $options.removeRoot,
                onEntryUpdated: $options.onEntryUpdated
              }, null, 8, ["comment", "can-comment", "form-shell-html", "submit-icon-html", "upload-options", "page-size", "highlighted", "onEntryRemoved", "onEntryUpdated"])
            ],
            64
            /* STABLE_FRAGMENT */
          );
        }),
        128
        /* KEYED_FRAGMENT */
      )),
      $options.remainingNext > 0 && $data.items.length > 0 ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_3, [
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
      if (comment.replies && comment.replies.items) {
        comment.replies.items.forEach((reply) => ids.push(reply.id));
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
    // way for CommentEntry's online-status overlay label (`onlineLabel`), and 'base'
    // for the profile-image alt phrase `UserImage` builds itself (see its docblock),
    // matching the exact keys `user\widgets\Image::run()` uses.
    i18nCategories: ["CommentModule.base", "ContentModule.base", "UserModule.base", "base"],
    components: { CommentList, CommentForm },
    props: {
      contentId: { type: Number, required: true },
      // RAW window payload ({results, prevCount, nextCount, total, rootTotal} — the
      // shape of the comment window endpoint, exactly what this island's own fetches
      // return), mapped once via mapWindow() below.
      initial: { type: Object, default: null },
      // `recordId => {total, liked, canLike}` for the embedded initial window, handed over
      // by the widget: the window payload itself is caller-neutral (and therefore
      // cacheable, see docs/develop/concept-api.md) while THIS page render is per user
      // anyway, so inlining them saves the island its first `like/states` request.
      initialLikeStates: { type: Object, default: () => ({}) },
      canComment: { type: Boolean, default: false },
      // __VUEFORM__ shell token template, see LegacyFormWrapper.vue
      formShellHtml: { type: String, default: null },
      // Server-rendered submit-button icon HTML - see CommentForm.vue's own docblock.
      submitIconHtml: { type: String, default: null },
      // Settings of the form's upload field ({max, handlersHtml}) - see UploadField.vue.
      uploadOptions: { type: Object, default: null },
      pageSize: { type: Number, default: 10 },
      // permalink highlight target
      anchorCommentId: { type: Number, default: null },
      // stream preview: section hidden until toggled via humhub:comment:toggle
      collapsed: { type: Boolean, default: false },
      // The file module's `excludeMediaFilesPreview` setting, provided down to every
      // entry's <AttachedFiles> so a comment's attachment list leaves out what the
      // preview grid above it already shows - the same call `ShowFiles` makes for a
      // wall entry.
      excludeMediaFiles: { type: Boolean, default: false }
    },
    data() {
      const initialWindow = this.initial ? mapWindow(this.initial) : null;
      return {
        comments: initialWindow ? initialWindow.results : [],
        // recordId => like state, for the whole section (see `initialLikeStates`). The
        // only per-record value that depends on who is asking, hence kept beside the
        // comments rather than inside them; `ensureLikeStates()` fills it for comments
        // that enter the tree later (paging, replies, own creates, live updates).
        likeStates: { ...this.initialLikeStates },
        prevCount: initialWindow ? initialWindow.prevCount : 0,
        // Mirrors the raw server payload shape for API completeness, but is no longer
        // read for gating - CommentList derives its own "next" remaining count from
        // `total`/`items.length`/`remainingPrev` instead, see its own docblock ("Next-
        // pagination gap fix") for why the server's per-request `nextCount` alone isn't
        // enough once own/live appends can move the pagination cursor past a real gap.
        nextCount: initialWindow ? initialWindow.nextCount : 0,
        total: initialWindow ? initialWindow.total : 0,
        // Root-only counterpart of `total` (see the class docblock's "Root-only
        // remaining count" section) - falls back to `total` itself when a caller/fixture
        // predates this field, i.e. the OLD (buggy-for-threads-with-replies) formula
        // rather than 0, so an unmigrated payload degrades no worse than before this fix.
        rootTotal: initialWindow ? initialWindow.rootTotal ?? initialWindow.total : 0,
        loaded: !!initialWindow,
        isCollapsed: this.collapsed,
        // id -> revision counter, bumped whenever an entry object is
        // swapped in place under the same id (edit-save/live-append) —
        // see the class docblock's "Revision map" section.
        revisions: {},
        // Dedup set for own-create-vs-live races and live-update replay —
        // append-only by design, see the class docblock's "Live updates"
        // section for why entries are never removed on delete.
        knownIds: new Set(initialWindow ? collectKnownIds(initialWindow.results) : []),
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
        adjustRootTotal: this.adjustRootTotal,
        registerKnownId: this.registerKnownId,
        isKnownId: this.isKnownId,
        likeStates: this.likeStates,
        ensureLikeStates: this.ensureLikeStates,
        excludeMediaFiles: this.excludeMediaFiles
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
        return;
      }
      this.ensureLikeStates(this.comments);
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
        fetchWindow({ contentId: this.contentId }).then((response) => {
          this.comments = response.results;
          this.prevCount = response.prevCount;
          this.nextCount = response.nextCount;
          this.total = response.total;
          this.rootTotal = response.rootTotal ?? response.total;
          this.knownIds = new Set(collectKnownIds(response.results));
          this.ensureLikeStates(response.results);
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
          fetchWindow({ contentId: this.contentId }).then((response) => {
            this.comments = response.results;
            this.prevCount = response.prevCount;
            this.nextCount = response.nextCount;
            this.total = response.total;
            this.rootTotal = response.rootTotal ?? response.total;
            collectKnownIds(response.results).forEach((id) => this.knownIds.add(id));
            this.ensureLikeStates(response.results);
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
      // Root-only counterpart of adjustTotal() - see the class docblock's "Root-only
      // remaining count" section for the full mutation matrix (root create/delete only,
      // never a reply).
      adjustRootTotal(delta) {
        this.rootTotal += delta;
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
        this.ensureLikeStates([comment]);
        this.rootTotal += 1;
        if (this.$refs.list) {
          this.$refs.list.appendRoot(comment);
        }
      },
      /**
       * Loads the like states of comments that just entered the tree, in ONE request for
       * the whole batch, and only for records not already in the map (a re-render, an edit
       * or a reveal never refetches). Failures are logged and leave the affected entries
       * without a like link rather than breaking the list.
       */
      ensureLikeStates(comments) {
        const missing = collectRecordIds(comments).filter((recordId) => !this.likeStates[recordId]);
        if (missing.length === 0) {
          return;
        }
        fetchLikeStates(missing).then((states) => {
          Object.keys(states).forEach((recordId) => {
            this.likeStates[recordId] = states[recordId];
          });
        }).catch((e) => {
          vue.log.error(e, true);
        });
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
        fetchComment(commentId).then((comment) => this.appendLiveComment(comment)).catch((e) => {
          vue.log.error(e, true);
        });
      },
      appendLiveComment(comment) {
        if (this.isKnownId(comment.id)) {
          return;
        }
        this.registerKnownId(comment.id);
        this.total += 1;
        this.ensureLikeStates([comment]);
        const isRoot = comment.parentCommentId === null || comment.parentCommentId === void 0;
        if (isRoot) {
          this.rootTotal += 1;
        }
        if (!this.$refs.list) {
          return;
        }
        if (isRoot) {
          this.$refs.list.appendRoot(comment);
          return;
        }
        const parent = this.$refs.list.findRoot(comment.parentCommentId);
        if (!parent || !parent.replies) {
          return;
        }
        const items = [...parent.replies.items, comment];
        const total = parent.replies.total + 1;
        this.$refs.list.replaceRoot(parent.id, {
          ...parent,
          replies: { total, items, hasMore: total > items.length }
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
          total: $data.total,
          "root-total": $data.rootTotal,
          "page-size": $props.pageSize,
          "can-comment": $options.showForm,
          "form-shell-html": $props.formShellHtml,
          "submit-icon-html": $props.submitIconHtml,
          "upload-options": $props.uploadOptions,
          "anchor-comment-id": $props.anchorCommentId
        }, null, 8, ["content-id", "comments", "prev-count", "total", "root-total", "page-size", "can-comment", "form-shell-html", "submit-icon-html", "upload-options", "anchor-comment-id"])) : vue$1.createCommentVNode("v-if", true),
        $options.showForm && $props.formShellHtml ? (vue$1.openBlock(), vue$1.createBlock(_component_CommentForm, {
          key: 1,
          ref: "form",
          "shell-html": $props.formShellHtml,
          "content-id": $props.contentId,
          "submit-icon-html": $props.submitIconHtml,
          "upload-options": $props.uploadOptions,
          onCreated: $options.onMainCreated
        }, null, 8, ["shell-html", "content-id", "submit-icon-html", "upload-options", "onCreated"])) : vue$1.createCommentVNode("v-if", true)
      ],
      2
      /* CLASS */
    );
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue.register("CommentSection", C0);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.comment.vue.js.map
