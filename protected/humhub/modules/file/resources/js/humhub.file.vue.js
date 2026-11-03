/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from file/vue/ via `grunt build-vue --module=file`.
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
  const VIDEO_TYPES = {
    webm: "video/webm",
    mp4: "video/mp4",
    ogv: "video/ogg",
    mov: "video/quicktime"
  };
  const VIDEO_EXTENSIONS = Object.keys(VIDEO_TYPES);
  const AUDIO_EXTENSIONS = ["mp3"];
  const MEDIA_MIME_ICONS = ["mime-image", "mime-video", "mime-audio"];
  const SIZE_UNITS$1 = ["B", "KB", "MB", "GB", "TB"];
  function escapeHtml(value) {
    return String(value ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
  }
  const _sfc_main$1 = {
    props: {
      // File shapes, see the class docblock.
      files: { type: Array, required: true },
      // Scopes the lightbox gallery to this record's media.
      galleryId: { type: String, required: true },
      // Render the media grid at all (`ShowFiles::$preview`).
      preview: { type: Boolean, default: true },
      // Drop media files from the trailing list - they are already in the grid above
      // (the `excludeMediaFilesPreview` setting of the file module).
      excludeMedia: { type: Boolean, default: false },
      // Whether the active theme uses the fluid layout (`ThemeHelper::isFluid()`),
      // which widens the grid's large-breakpoint buckets.
      fluid: { type: Boolean, default: false }
    },
    computed: {
      images() {
        return this.preview ? this.files.filter((file) => !!file.previewUrl) : [];
      },
      videos() {
        return this.preview ? this.mediaBucket(VIDEO_EXTENSIONS) : [];
      },
      audios() {
        return this.preview ? this.mediaBucket(AUDIO_EXTENSIONS) : [];
      },
      hasMedia() {
        return this.images.length > 0 || this.videos.length > 0 || this.audios.length > 0;
      },
      listed() {
        return this.excludeMedia ? this.files.filter((file) => !MEDIA_MIME_ICONS.includes(file.mimeIcon)) : this.files;
      }
    },
    methods: {
      extension(file) {
        const name = String(file.fileName || "");
        const dot = name.lastIndexOf(".");
        return dot === -1 ? "" : name.slice(dot + 1).toLowerCase();
      },
      videoType(file) {
        return VIDEO_TYPES[this.extension(file)];
      },
      // A file belongs to a media bucket by its extension, but only when it has no
      // image preview - an image preview always wins (mirrors showFiles.php, whose
      // if/elseif chain tests the preview converter first).
      mediaBucket(extensions) {
        return this.files.filter((file) => !file.previewUrl && extensions.includes(this.extension(file)));
      },
      // Mirrors showFiles.php's $getColumnClass(). Heights matching these buckets are
      // defined in _file.scss.
      columnClass(count, enlarge = false) {
        let bsColumns = 6;
        let bsColumnsMd = this.fluid ? 4 : 6;
        let bsColumnsLg = this.fluid ? 3 : 4;
        if (count === 1) {
          bsColumns = 12;
          bsColumnsMd = this.fluid ? 6 : 12;
          bsColumnsLg = this.fluid ? 4 : 6;
        }
        if (count === 2) {
          bsColumnsMd = 6;
          bsColumnsLg = this.fluid ? 4 : 6;
        }
        if (enlarge) {
          bsColumnsLg = this.fluid ? 4 : 6;
          if (count === 1) {
            bsColumnsLg = 12;
          }
        }
        return `col-media col-${bsColumns} col-lg-${bsColumnsMd} col-xl-${bsColumnsLg}`;
      },
      // The thumbnail popover the `file.Preview` JsWidget attached by hand, expressed
      // as data attributes instead: the core `popover` UI addition (selector `.po`)
      // initializes them, so markup Vue renders later gets them just the same.
      popoverAttributes(file) {
        if (!file.previewUrl) {
          return {};
        }
        return {
          class: "po",
          "data-bs-toggle": "popover",
          "data-bs-trigger": "hover",
          "data-bs-placement": "right",
          "data-bs-container": "body",
          "data-bs-delay": "100",
          "data-bs-html": "true",
          "data-bs-content": `<img alt="${escapeHtml(file.fileName)}" src="${escapeHtml(file.previewUrl)}" />`
        };
      },
      shortSize(size) {
        let value = Number(size) || 0;
        let unit = 0;
        while (value >= 1024 && unit < SIZE_UNITS$1.length - 1) {
          value /= 1024;
          unit++;
        }
        const formatted = value.toLocaleString(void 0, { maximumFractionDigits: unit === 0 ? 0 : 1 });
        return `${formatted} ${SIZE_UNITS$1[unit]}`;
      }
    }
  };
  const _hoisted_1$1 = {
    key: 0,
    class: "post-files"
  };
  const _hoisted_2$1 = {
    key: 0,
    class: "post-files-audio d-flex flex-wrap justify-content-center"
  };
  const _hoisted_3$1 = { class: "text-truncate small" };
  const _hoisted_4$1 = ["src"];
  const _hoisted_5$1 = {
    key: 1,
    class: "post-files-videos d-flex flex-wrap justify-content-center"
  };
  const _hoisted_6$1 = ["data-ui-gallery", "href", "type", "title"];
  const _hoisted_7$1 = ["src"];
  const _hoisted_8$1 = {
    key: 2,
    class: "post-files-images d-flex flex-wrap justify-content-center"
  };
  const _hoisted_9$1 = ["data-ui-gallery", "href", "title"];
  const _hoisted_10$1 = ["src", "alt"];
  const _hoisted_11$1 = {
    key: 1,
    class: "well post-file-list"
  };
  const _hoisted_12 = { class: "files" };
  const _hoisted_13 = ["data-preview-guid"];
  const _hoisted_14 = ["href"];
  const _hoisted_15 = ["href", "data-file-url", "data-file-name", "data-file-mime"];
  const _hoisted_16 = { class: "time file-fileInfo" };
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return vue.openBlock(), vue.createElementBlock(
      vue.Fragment,
      null,
      [
        $options.hasMedia ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, [
          $options.audios.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2$1, [
            (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              null,
              vue.renderList($options.audios, (file) => {
                return vue.openBlock(), vue.createElementBlock("div", {
                  key: file.guid,
                  class: "col-media col-12"
                }, [
                  vue.createElementVNode(
                    "div",
                    _hoisted_3$1,
                    vue.toDisplayString(file.fileName),
                    1
                    /* TEXT */
                  ),
                  vue.createElementVNode("audio", {
                    src: file.url,
                    controls: "",
                    preload: "metadata",
                    class: "w-100"
                  }, null, 8, _hoisted_4$1)
                ]);
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ])) : vue.createCommentVNode("v-if", true),
          $options.videos.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$1, [
            (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              null,
              vue.renderList($options.videos, (file) => {
                return vue.openBlock(), vue.createElementBlock(
                  "div",
                  {
                    key: file.guid,
                    class: vue.normalizeClass($options.columnClass($options.videos.length, true))
                  },
                  [
                    vue.createElementVNode("a", {
                      "data-ui-gallery": $props.galleryId,
                      href: file.url + "#." + $options.extension(file),
                      type: $options.videoType(file),
                      title: file.fileName,
                      class: "d-flex align-items-center justify-content-center h-100 w-100"
                    }, [
                      vue.createElementVNode("video", {
                        src: file.url + "#t=0.001",
                        controls: "",
                        preload: "metadata",
                        height: "130"
                      }, null, 8, _hoisted_7$1)
                    ], 8, _hoisted_6$1)
                  ],
                  2
                  /* CLASS */
                );
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ])) : vue.createCommentVNode("v-if", true),
          $options.images.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_8$1, [
            (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              null,
              vue.renderList($options.images, (file) => {
                return vue.openBlock(), vue.createElementBlock(
                  "div",
                  {
                    key: file.guid,
                    class: vue.normalizeClass($options.columnClass($options.images.length))
                  },
                  [
                    vue.createElementVNode("a", {
                      "data-ui-gallery": $props.galleryId,
                      href: file.url + "#.jpeg",
                      title: file.fileName
                    }, [
                      vue.createElementVNode("img", {
                        class: "animated fadeIn",
                        src: file.previewUrl,
                        alt: file.fileName
                      }, null, 8, _hoisted_10$1)
                    ], 8, _hoisted_9$1)
                  ],
                  2
                  /* CLASS */
                );
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ])) : vue.createCommentVNode("v-if", true)
        ])), [
          [_directive_additions]
        ]) : vue.createCommentVNode("v-if", true),
        $options.listed.length ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_11$1, [
          vue.createElementVNode("ul", _hoisted_12, [
            (vue.openBlock(true), vue.createElementBlock(
              vue.Fragment,
              null,
              vue.renderList($options.listed, (file) => {
                return vue.openBlock(), vue.createElementBlock("li", {
                  key: file.guid,
                  class: vue.normalizeClass(["file-preview-item mime", file.mimeIcon]),
                  "data-preview-guid": file.guid
                }, [
                  vue.createElementVNode(
                    "span",
                    vue.mergeProps({ class: "file-preview-content" }, { ref_for: true }, $options.popoverAttributes(file)),
                    [
                      vue.createElementVNode(
                        "span",
                        {
                          class: vue.normalizeClass({ highlight: !!file.highlight })
                        },
                        [
                          file.viewUrl ? (vue.openBlock(), vue.createElementBlock("a", {
                            key: 0,
                            href: file.viewUrl,
                            "data-bs-target": "#globalModal"
                          }, vue.toDisplayString(file.fileName), 9, _hoisted_14)) : (vue.openBlock(), vue.createElementBlock("a", {
                            key: 1,
                            href: file.url,
                            target: "_blank",
                            rel: "noopener",
                            "data-pjax-prevent": "",
                            "data-file-download": "",
                            "data-file-url": file.downloadUrl || file.url,
                            "data-file-name": file.fileName,
                            "data-file-mime": file.mimeType
                          }, vue.toDisplayString(file.fileName), 9, _hoisted_15))
                        ],
                        2
                        /* CLASS */
                      ),
                      vue.createElementVNode(
                        "span",
                        _hoisted_16,
                        " - " + vue.toDisplayString($options.shortSize(file.size)),
                        1
                        /* TEXT */
                      )
                    ],
                    16
                    /* FULL_PROPS */
                  )
                ], 10, _hoisted_13);
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ])
        ])), [
          [_directive_additions]
        ]) : vue.createCommentVNode("v-if", true)
      ],
      64
      /* STABLE_FRAGMENT */
    );
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  const SIZE_UNITS = ["B", "KB", "MB", "GB", "TB"];
  const _sfc_main = {
    i18nCategories: ["FileModule.base", "base"],
    props: {
      /** The id of the platform file (not of the record it is attached to). */
      fileId: { type: Number, required: true }
    },
    data() {
      return {
        versions: [],
        loading: true,
        failed: false
      };
    },
    computed: {
      currentLabel: () => vue$1.i18n.t("FileModule.base", "Current"),
      loadingLabel: () => vue$1.i18n.t("base", "Loading..."),
      errorLabel: () => vue$1.i18n.t("FileModule.base", "The versions could not be loaded.")
    },
    mounted() {
      vue$1.client.get(vue$1.apiUrl("file/" + this.fileId + "/versions")).then((response) => {
        this.versions = Array.isArray(response.results) ? response.results : [];
        this.loading = false;
      }).catch((e) => {
        this.failed = true;
        this.loading = false;
        vue$1.log.error(e);
      });
    },
    methods: {
      /** "Jane Doe uploaded v3", or "v3" where nobody is known. */
      titleOf(entry) {
        const version = "v" + entry.version;
        return entry.createdBy ? vue$1.i18n.t("FileModule.base", "{name} uploaded {version}", { name: entry.createdBy.displayName, version }) : version;
      },
      downloadLabelOf(entry) {
        return vue$1.i18n.t("FileModule.base", "Download version {version}", { version: entry.version });
      },
      formatDate(stamp) {
        if (!stamp) {
          return "";
        }
        const locale = vue$1.getConfig("i18n").language || void 0;
        return new Intl.DateTimeFormat(locale, { dateStyle: "medium", timeStyle: "short" }).format(new Date(stamp));
      },
      shortSize(size) {
        let value = Number(size) || 0;
        let unit = 0;
        while (value >= 1024 && unit < SIZE_UNITS.length - 1) {
          value /= 1024;
          unit++;
        }
        const formatted = value.toLocaleString(void 0, { maximumFractionDigits: unit === 0 ? 0 : 1 });
        return `${formatted} ${SIZE_UNITS[unit]}`;
      }
    }
  };
  const _hoisted_1 = { class: "file-versions" };
  const _hoisted_2 = {
    key: 0,
    class: "text-center p-2"
  };
  const _hoisted_3 = {
    class: "visually-hidden",
    role: "status"
  };
  const _hoisted_4 = {
    key: 1,
    class: "text-danger m-0"
  };
  const _hoisted_5 = {
    key: 2,
    class: "list-group list-group-flush file-versions-list"
  };
  const _hoisted_6 = { class: "file-version-body" };
  const _hoisted_7 = { class: "file-version-title" };
  const _hoisted_8 = {
    key: 0,
    class: "badge"
  };
  const _hoisted_9 = { class: "file-version-meta" };
  const _hoisted_10 = ["datetime"];
  const _hoisted_11 = ["href", "aria-label", "title"];
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_UserImage = vue.resolveComponent("UserImage");
    return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
      $data.loading ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2, [
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
          _hoisted_3,
          vue.toDisplayString($options.loadingLabel),
          1
          /* TEXT */
        )
      ])) : $data.failed ? (vue.openBlock(), vue.createElementBlock(
        "p",
        _hoisted_4,
        vue.toDisplayString($options.errorLabel),
        1
        /* TEXT */
      )) : (vue.openBlock(), vue.createElementBlock("ol", _hoisted_5, [
        (vue.openBlock(true), vue.createElementBlock(
          vue.Fragment,
          null,
          vue.renderList($data.versions, (entry) => {
            return vue.openBlock(), vue.createElementBlock("li", {
              key: entry.version,
              class: "list-group-item file-version"
            }, [
              entry.createdBy ? (vue.openBlock(), vue.createBlock(
                _component_UserImage,
                vue.mergeProps({
                  key: 0,
                  ref_for: true
                }, entry.createdBy, {
                  size: 32,
                  link: false
                }),
                null,
                16
                /* FULL_PROPS */
              )) : vue.createCommentVNode("v-if", true),
              vue.createElementVNode("div", _hoisted_6, [
                vue.createElementVNode("div", _hoisted_7, [
                  vue.createTextVNode(
                    vue.toDisplayString($options.titleOf(entry)) + " ",
                    1
                    /* TEXT */
                  ),
                  entry.current ? (vue.openBlock(), vue.createElementBlock(
                    "span",
                    _hoisted_8,
                    vue.toDisplayString($options.currentLabel),
                    1
                    /* TEXT */
                  )) : vue.createCommentVNode("v-if", true)
                ]),
                vue.createElementVNode("div", _hoisted_9, [
                  vue.createElementVNode("time", {
                    datetime: entry.createdAt
                  }, vue.toDisplayString($options.formatDate(entry.createdAt)), 9, _hoisted_10),
                  vue.createTextVNode(
                    " · " + vue.toDisplayString($options.shortSize(entry.size)),
                    1
                    /* TEXT */
                  )
                ])
              ]),
              vue.createElementVNode("a", {
                class: "btn btn-ghost btn-icon-only",
                href: entry.downloadUrl,
                "aria-label": $options.downloadLabelOf(entry),
                title: $options.downloadLabelOf(entry)
              }, [..._cache[1] || (_cache[1] = [
                vue.createElementVNode(
                  "i",
                  {
                    class: "ti ti-download",
                    "aria-hidden": "true"
                  },
                  null,
                  -1
                  /* CACHED */
                )
              ])], 8, _hoisted_11)
            ]);
          }),
          128
          /* KEYED_FRAGMENT */
        ))
      ]))
    ]);
  }
  const C1 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("AttachedFiles", C0);
  vue$1.register("FileVersions", C1);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.file.vue.js.map
