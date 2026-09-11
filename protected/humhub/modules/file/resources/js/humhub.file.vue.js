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
  const VIDEO_EXTENSIONS = ["webm", "mp4", "ogv", "mov"];
  const AUDIO_EXTENSIONS = ["mp3"];
  const MEDIA_MIME_ICONS = ["mime-image", "mime-video", "mime-audio"];
  const SIZE_UNITS = ["B", "KB", "MB", "GB", "TB"];
  function escapeHtml(value) {
    return String(value ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
  }
  const _sfc_main = {
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
        while (value >= 1024 && unit < SIZE_UNITS.length - 1) {
          value /= 1024;
          unit++;
        }
        const formatted = value.toLocaleString(void 0, { maximumFractionDigits: unit === 0 ? 0 : 1 });
        return `${formatted} ${SIZE_UNITS[unit]}`;
      }
    }
  };
  const _hoisted_1 = {
    key: 0,
    class: "post-files"
  };
  const _hoisted_2 = {
    key: 0,
    class: "post-files-audio d-flex flex-wrap justify-content-center"
  };
  const _hoisted_3 = { class: "text-truncate small" };
  const _hoisted_4 = ["src"];
  const _hoisted_5 = {
    key: 1,
    class: "post-files-videos d-flex flex-wrap justify-content-center"
  };
  const _hoisted_6 = ["data-ui-gallery", "href", "title"];
  const _hoisted_7 = ["src"];
  const _hoisted_8 = {
    key: 2,
    class: "post-files-images d-flex flex-wrap justify-content-center"
  };
  const _hoisted_9 = ["data-ui-gallery", "href", "title"];
  const _hoisted_10 = ["src", "alt"];
  const _hoisted_11 = {
    key: 1,
    class: "well post-file-list"
  };
  const _hoisted_12 = { class: "files" };
  const _hoisted_13 = ["data-preview-guid"];
  const _hoisted_14 = ["href"];
  const _hoisted_15 = ["href", "data-file-url", "data-file-name", "data-file-mime"];
  const _hoisted_16 = { class: "time file-fileInfo" };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_additions = vue.resolveDirective("additions");
    return vue.openBlock(), vue.createElementBlock(
      vue.Fragment,
      null,
      [
        $options.hasMedia ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
          $options.audios.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_2, [
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
                    _hoisted_3,
                    vue.toDisplayString(file.fileName),
                    1
                    /* TEXT */
                  ),
                  vue.createElementVNode("audio", {
                    src: file.url,
                    controls: "",
                    preload: "metadata",
                    class: "w-100"
                  }, null, 8, _hoisted_4)
                ]);
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ])) : vue.createCommentVNode("v-if", true),
          $options.videos.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5, [
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
                      title: file.fileName,
                      class: "d-flex align-items-center justify-content-center h-100 w-100"
                    }, [
                      vue.createElementVNode("video", {
                        src: file.url + "#t=0.001",
                        controls: "",
                        preload: "metadata",
                        height: "130"
                      }, null, 8, _hoisted_7)
                    ], 8, _hoisted_6)
                  ],
                  2
                  /* CLASS */
                );
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ])) : vue.createCommentVNode("v-if", true),
          $options.images.length ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_8, [
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
                      }, null, 8, _hoisted_10)
                    ], 8, _hoisted_9)
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
        $options.listed.length ? vue.withDirectives((vue.openBlock(), vue.createElementBlock("div", _hoisted_11, [
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
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue$1.register("AttachedFiles", C0);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.file.vue.js.map
