/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from file/vue/ via `grunt build-vue --module=file`.
 * See docs/develop/ui-js-vuejs.md
 */
(function(vue, vue$1) {
  "use strict";
  function justify(ratios, { width, rowHeight, singleHeight, gap, maxRows }) {
    if (ratios.length === 0 || !(width > 0)) {
      return { rows: [], hidden: 0 };
    }
    const fit = (from, to) => {
      let sum = 0;
      for (let i = from; i <= to; i++) {
        sum += ratios[i];
      }
      return (width - gap * (to - from)) / sum;
    };
    const row = (from, to, height) => ({
      height,
      items: ratios.slice(from, to + 1).map((ratio, offset) => ({ index: from + offset, width: ratio * height }))
    });
    if (ratios.length === 1) {
      return { rows: [row(0, 0, Math.min(singleHeight, fit(0, 0)))], hidden: 0 };
    }
    const rows = [];
    let start = 0;
    let next = 0;
    while (next < ratios.length && rows.length < maxRows) {
      const height = fit(start, next);
      if (height > rowHeight) {
        next++;
        continue;
      }
      const without = next > start ? fit(start, next - 1) : Infinity;
      if (without - rowHeight < rowHeight - height) {
        rows.push(row(start, next - 1, without));
        start = next;
      } else {
        rows.push(row(start, next, height));
        start = ++next;
      }
    }
    if (start < next) {
      rows.push(row(start, next - 1, rowHeight));
    }
    const shown = rows.reduce((count, { items }) => count + items.length, 0);
    return { rows, hidden: ratios.length - shown };
  }
  const _export_sfc = (sfc, props) => {
    const target = sfc.__vccOpts || sfc;
    for (const [key, val] of props) {
      target[key] = val;
    }
    return target;
  };
  const MAX_ROWS = 2;
  const SIZES = { rowHeight: 12, singleHeight: 20, gap: 0.5, width: 36 };
  const PREVIEW_SIZE = 400;
  let observer = null;
  let frame = 0;
  const areas = /* @__PURE__ */ new WeakMap();
  const changed = /* @__PURE__ */ new Set();
  const rootFontSize = () => parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;
  function layoutChanged() {
    frame = 0;
    const rem = rootFontSize();
    changed.forEach((el) => {
      const area = areas.get(el);
      area.resize(area.width, rem);
    });
    changed.clear();
  }
  function resized(entries) {
    entries.forEach(({ target, contentRect }) => {
      const area = areas.get(target);
      const width = Math.round(contentRect.width);
      if (area && width > 0 && width !== area.width) {
        area.width = width;
        changed.add(target);
      }
    });
    if (changed.size > 0 && !frame) {
      frame = requestAnimationFrame(layoutChanged);
    }
  }
  function observe(el, width, resize) {
    if (typeof ResizeObserver === "undefined") {
      return;
    }
    observer ?? (observer = new ResizeObserver(resized));
    areas.set(el, { width, resize });
    observer.observe(el);
  }
  function unobserve(el) {
    observer == null ? void 0 : observer.unobserve(el);
    areas.delete(el);
    changed.delete(el);
  }
  const ratioOf = (file) => file.width > 0 && file.height > 0 ? file.width / file.height : 1;
  const _sfc_main$2 = {
    name: "AttachedImages",
    props: {
      // The files with a preview, see AttachedFiles.vue
      images: { type: Array, required: true },
      // The record's group in the media viewer
      galleryId: { type: String, required: true }
    },
    data() {
      return { box: null };
    },
    computed: {
      layout() {
        const box = this.box || {
          width: SIZES.width * 16,
          rowHeight: SIZES.rowHeight * 16,
          singleHeight: SIZES.singleHeight * 16,
          gap: SIZES.gap * 16
        };
        return justify(this.images.map(ratioOf), { ...box, maxRows: MAX_ROWS });
      },
      shownCount() {
        return this.layout.rows.reduce((count, row) => count + row.items.length, 0);
      },
      hiddenImages() {
        return this.images.slice(this.shownCount);
      }
    },
    created() {
      this.displayed = /* @__PURE__ */ new Set();
    },
    mounted() {
      const width = this.$el.clientWidth;
      if (width > 0) {
        this.measure(width, rootFontSize());
      }
      observe(this.$el, width, this.measure);
    },
    beforeUnmount() {
      unobserve(this.$el);
    },
    methods: {
      // The width and the sizes of _file.scss (CSS variables in rem or px), in px
      measure(width, rem) {
        const style = getComputedStyle(this.$el);
        const size = (name, fallback) => {
          const value = style.getPropertyValue(name);
          const number = parseFloat(value);
          if (Number.isNaN(number)) {
            return fallback * rem;
          }
          return value.trim().endsWith("px") ? number : number * rem;
        };
        this.box = {
          width,
          rowHeight: size("--hh-attached-images-row-height", SIZES.rowHeight),
          singleHeight: size("--hh-attached-images-single-height", SIZES.singleHeight),
          gap: size("--hh-attached-images-gap", SIZES.gap)
        };
      },
      px(value) {
        return `${value}px`;
      },
      isMoreTile(index) {
        return this.layout.hidden > 0 && index === this.shownCount - 1;
      },
      moreLabel(file) {
        return vue.i18n.t("FileModule.base", "{fileName}, {count,plural,=1{one more image} other{# more images}}", {
          fileName: file.fileName,
          count: this.layout.hidden
        });
      },
      // The preview fits into PREVIEW_SIZE², a larger tile (or a sharper screen) gets the display version and keeps
      // it while the area shrinks. An original that fits into the preview is the preview.
      source(file, width) {
        if (this.displayed.has(file.guid)) {
          return file.displayUrl;
        }
        const fits = file.width > 0 && file.height > 0 && file.width <= PREVIEW_SIZE && file.height <= PREVIEW_SIZE;
        if (!file.displayUrl || fits) {
          return file.previewUrl;
        }
        const previewWidth = Math.min(PREVIEW_SIZE, PREVIEW_SIZE * ratioOf(file));
        if (width * (window.devicePixelRatio || 1) <= previewWidth * 1.25) {
          return file.previewUrl;
        }
        if (this.box) {
          this.displayed.add(file.guid);
        }
        return file.displayUrl;
      }
    }
  };
  const _hoisted_1$2 = { class: "post-files-images" };
  const _hoisted_2$2 = ["data-ui-gallery", "href", "data-ui-gallery-src", "title", "aria-label"];
  const _hoisted_3$2 = ["src", "alt", "width", "height"];
  const _hoisted_4$2 = {
    key: 0,
    class: "post-files-image-more",
    "aria-hidden": "true"
  };
  const _hoisted_5$2 = ["data-ui-gallery", "href", "data-ui-gallery-src", "title"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    return vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_1$2, [
      (vue$1.openBlock(true), vue$1.createElementBlock(
        vue$1.Fragment,
        null,
        vue$1.renderList($options.layout.rows, (row, rowIndex) => {
          return vue$1.openBlock(), vue$1.createElementBlock(
            "div",
            {
              key: rowIndex,
              class: "post-files-images-row",
              style: vue$1.normalizeStyle({ height: $options.px(row.height) })
            },
            [
              (vue$1.openBlock(true), vue$1.createElementBlock(
                vue$1.Fragment,
                null,
                vue$1.renderList(row.items, (item) => {
                  return vue$1.openBlock(), vue$1.createElementBlock("a", {
                    key: $props.images[item.index].guid,
                    class: "post-files-image",
                    style: vue$1.normalizeStyle({ width: $options.px(item.width) }),
                    "data-ui-gallery": $props.galleryId,
                    href: $props.images[item.index].url,
                    "data-ui-gallery-src": $props.images[item.index].displayUrl || null,
                    title: $props.images[item.index].fileName,
                    "aria-label": $options.isMoreTile(item.index) ? $options.moreLabel($props.images[item.index]) : null
                  }, [
                    vue$1.createElementVNode("img", {
                      loading: "lazy",
                      decoding: "async",
                      src: $options.source($props.images[item.index], item.width),
                      alt: $props.images[item.index].fileName,
                      width: Math.round(item.width),
                      height: Math.round(row.height)
                    }, null, 8, _hoisted_3$2),
                    $options.isMoreTile(item.index) ? (vue$1.openBlock(), vue$1.createElementBlock(
                      "span",
                      _hoisted_4$2,
                      "+" + vue$1.toDisplayString($options.layout.hidden),
                      1
                      /* TEXT */
                    )) : vue$1.createCommentVNode("v-if", true)
                  ], 12, _hoisted_2$2);
                }),
                128
                /* KEYED_FRAGMENT */
              ))
            ],
            4
            /* STYLE */
          );
        }),
        128
        /* KEYED_FRAGMENT */
      )),
      (vue$1.openBlock(true), vue$1.createElementBlock(
        vue$1.Fragment,
        null,
        vue$1.renderList($options.hiddenImages, (file) => {
          return vue$1.openBlock(), vue$1.createElementBlock("a", {
            key: file.guid,
            hidden: "",
            "data-ui-gallery": $props.galleryId,
            href: file.url,
            "data-ui-gallery-src": file.displayUrl || null,
            title: file.fileName
          }, vue$1.toDisplayString(file.fileName), 9, _hoisted_5$2);
        }),
        128
        /* KEYED_FRAGMENT */
      ))
    ]);
  }
  const AttachedImages = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const VIDEO_TYPES = {
    webm: "video/webm",
    mp4: "video/mp4",
    ogv: "video/ogg",
    mov: "video/quicktime"
  };
  const VIDEO_EXTENSIONS = Object.keys(VIDEO_TYPES);
  const AUDIO_EXTENSIONS = ["mp3", "wav", "m4a", "aac", "flac"];
  const LIST_HEAD = 2;
  const COLLAPSE_FROM = 4;
  let uid = 0;
  const _sfc_main$1 = {
    components: { AttachedImages },
    i18nCategories: ["FileModule.base"],
    props: {
      // File shapes, see the class docblock.
      files: { type: Array, required: true },
      // Scopes the media viewer's gallery to this record's media.
      galleryId: { type: String, required: true },
      // Render the media area at all (`ShowFiles::$preview`).
      preview: { type: Boolean, default: true },
      // Drop media files from the list below - they are already in the media area
      // (the `excludeMediaFilesPreview` setting of the file module).
      excludeMedia: { type: Boolean, default: false }
    },
    data() {
      return { expanded: false };
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
      // Without `excludeMedia` all files, otherwise those the media area doesn't render -
      // a media file it has no preview for (an AVI video, an Ogg audio) stays in the list.
      listed() {
        if (!this.excludeMedia) {
          return this.files;
        }
        const rendered = /* @__PURE__ */ new Set([...this.images, ...this.videos, ...this.audios]);
        return this.files.filter((file) => !rendered.has(file));
      },
      collapsible() {
        return this.listed.length >= COLLAPSE_FROM;
      },
      listedHead() {
        return this.collapsible ? this.listed.slice(0, LIST_HEAD) : this.listed;
      },
      listedTail() {
        return this.collapsible ? this.listed.slice(LIST_HEAD) : [];
      },
      // The list is what "Show all" controls: the wrapper around the hidden rows has no role, a reference to it
      // would be dropped
      listId() {
        return `attached-files-${this.uid}-list`;
      },
      countId() {
        return `attached-files-${this.uid}-count`;
      },
      countLabel() {
        const total = this.listed.length;
        if (this.expanded) {
          return vue.i18n.t("FileModule.base", "{total,plural,one{# file} other{# files}}", { total });
        }
        return vue.i18n.t("FileModule.base", "{shown} of {total,plural,one{# file} other{# files}}", {
          shown: LIST_HEAD,
          total
        });
      },
      showAllLabel() {
        return vue.i18n.t("FileModule.base", "Show all");
      },
      showLessLabel() {
        return vue.i18n.t("FileModule.base", "Show less");
      }
    },
    created() {
      this.uid = ++uid;
      this.expanded = this.listedTail.some((file) => file.highlight);
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
      // image preview - an image preview always wins.
      mediaBucket(extensions) {
        return this.files.filter((file) => !file.previewUrl && extensions.includes(this.extension(file)));
      },
      // "Show all" hands the focus on to the first revealed row (docs/ui/keyboard.md, "Moving the focus"),
      // "Show less" keeps it on the button
      toggle() {
        const focused = document.activeElement === this.$refs.toggle;
        this.expanded = !this.expanded;
        if (this.expanded && focused) {
          this.$nextTick(() => {
            var _a;
            return (_a = this.$refs.more.querySelector(".file-row-name")) == null ? void 0 : _a.focus();
          });
        }
      }
    }
  };
  const _hoisted_1$1 = {
    key: 0,
    class: "post-files"
  };
  const _hoisted_2$1 = {
    key: 0,
    class: "post-files-audio"
  };
  const _hoisted_3$1 = { class: "text-truncate small" };
  const _hoisted_4$1 = ["src", "aria-label"];
  const _hoisted_5$1 = {
    key: 1,
    class: "post-files-videos"
  };
  const _hoisted_6$1 = ["data-ui-gallery", "href", "type", "title"];
  const _hoisted_7$1 = ["src"];
  const _hoisted_8$1 = {
    key: 1,
    class: "list-group file-list"
  };
  const _hoisted_9$1 = ["id"];
  const _hoisted_10$1 = ["inert"];
  const _hoisted_11$1 = {
    class: "file-list-more-inner",
    role: "none"
  };
  const _hoisted_12 = {
    key: 0,
    class: "list-group-item file-row file-list-toggle"
  };
  const _hoisted_13 = ["id"];
  const _hoisted_14 = ["aria-expanded", "aria-controls", "aria-describedby"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_AttachedImages = vue$1.resolveComponent("AttachedImages");
    const _component_FileRow = vue$1.resolveComponent("FileRow");
    const _directive_additions = vue$1.resolveDirective("additions");
    return vue$1.openBlock(), vue$1.createElementBlock(
      vue$1.Fragment,
      null,
      [
        $options.hasMedia ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_1$1, [
          $options.audios.length ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_2$1, [
            (vue$1.openBlock(true), vue$1.createElementBlock(
              vue$1.Fragment,
              null,
              vue$1.renderList($options.audios, (file) => {
                return vue$1.openBlock(), vue$1.createElementBlock("div", {
                  key: file.guid,
                  class: "post-files-audio-item"
                }, [
                  vue$1.createElementVNode(
                    "div",
                    _hoisted_3$1,
                    vue$1.toDisplayString(file.fileName),
                    1
                    /* TEXT */
                  ),
                  vue$1.createElementVNode("audio", {
                    src: file.url,
                    controls: "",
                    preload: "metadata",
                    "aria-label": file.fileName
                  }, null, 8, _hoisted_4$1)
                ]);
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ])) : vue$1.createCommentVNode("v-if", true),
          $options.videos.length ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_5$1, [
            (vue$1.openBlock(true), vue$1.createElementBlock(
              vue$1.Fragment,
              null,
              vue$1.renderList($options.videos, (file) => {
                return vue$1.openBlock(), vue$1.createElementBlock("a", {
                  key: file.guid,
                  class: "post-files-video",
                  "data-ui-gallery": $props.galleryId,
                  href: file.url,
                  type: $options.videoType(file),
                  title: file.fileName
                }, [
                  vue$1.createElementVNode("video", {
                    src: file.url + "#t=0.001",
                    controls: "",
                    preload: "metadata"
                  }, null, 8, _hoisted_7$1)
                ], 8, _hoisted_6$1);
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ])) : vue$1.createCommentVNode("v-if", true),
          $options.images.length ? (vue$1.openBlock(), vue$1.createBlock(_component_AttachedImages, {
            key: 2,
            images: $options.images,
            "gallery-id": $props.galleryId
          }, null, 8, ["images", "gallery-id"])) : vue$1.createCommentVNode("v-if", true)
        ])) : vue$1.createCommentVNode("v-if", true),
        $options.listed.length ? vue$1.withDirectives((vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_8$1, [
          vue$1.createElementVNode("div", {
            id: $options.collapsible ? $options.listId : null,
            class: "file-list-rows",
            role: "list"
          }, [
            (vue$1.openBlock(true), vue$1.createElementBlock(
              vue$1.Fragment,
              null,
              vue$1.renderList($options.listedHead, (file) => {
                return vue$1.openBlock(), vue$1.createBlock(_component_FileRow, {
                  key: file.guid,
                  file
                }, null, 8, ["file"]);
              }),
              128
              /* KEYED_FRAGMENT */
            )),
            $options.collapsible ? (vue$1.openBlock(), vue$1.createElementBlock("div", {
              key: 0,
              ref: "more",
              class: vue$1.normalizeClass(["file-list-more", { show: $data.expanded }]),
              inert: !$data.expanded,
              role: "none"
            }, [
              vue$1.createElementVNode("div", _hoisted_11$1, [
                (vue$1.openBlock(true), vue$1.createElementBlock(
                  vue$1.Fragment,
                  null,
                  vue$1.renderList($options.listedTail, (file) => {
                    return vue$1.openBlock(), vue$1.createBlock(_component_FileRow, {
                      key: file.guid,
                      file
                    }, null, 8, ["file"]);
                  }),
                  128
                  /* KEYED_FRAGMENT */
                ))
              ])
            ], 10, _hoisted_10$1)) : vue$1.createCommentVNode("v-if", true)
          ], 8, _hoisted_9$1),
          $options.collapsible ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_12, [
            _cache[1] || (_cache[1] = vue$1.createElementVNode(
              "i",
              {
                class: "ti ti-files file-row-icon",
                "aria-hidden": "true"
              },
              null,
              -1
              /* CACHED */
            )),
            vue$1.createElementVNode("span", {
              id: $options.countId,
              class: "file-row-text"
            }, vue$1.toDisplayString($options.countLabel), 9, _hoisted_13),
            vue$1.createElementVNode("button", {
              ref: "toggle",
              type: "button",
              class: "btn btn-ghost btn-sm file-row-action",
              "aria-expanded": $data.expanded ? "true" : "false",
              "aria-controls": $options.listId,
              "aria-describedby": $options.countId,
              onClick: _cache[0] || (_cache[0] = (...args) => $options.toggle && $options.toggle(...args))
            }, [
              vue$1.createTextVNode(
                vue$1.toDisplayString($data.expanded ? $options.showLessLabel : $options.showAllLabel) + " ",
                1
                /* TEXT */
              ),
              vue$1.createElementVNode(
                "i",
                {
                  class: vue$1.normalizeClass(["ti", $data.expanded ? "ti-chevron-up" : "ti-chevron-down"]),
                  "aria-hidden": "true"
                },
                null,
                2
                /* CLASS */
              )
            ], 8, _hoisted_14)
          ])) : vue$1.createCommentVNode("v-if", true)
        ])), [
          [_directive_additions]
        ]) : vue$1.createCommentVNode("v-if", true)
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
      currentLabel: () => vue.i18n.t("FileModule.base", "Current"),
      loadingLabel: () => vue.i18n.t("base", "Loading..."),
      errorLabel: () => vue.i18n.t("FileModule.base", "The versions could not be loaded.")
    },
    mounted() {
      vue.client.get(vue.apiUrl("file/" + this.fileId + "/versions")).then((response) => {
        this.versions = Array.isArray(response.results) ? response.results : [];
        this.loading = false;
      }).catch((e) => {
        this.failed = true;
        this.loading = false;
        vue.log.error(e);
      });
    },
    methods: {
      /** "Jane Doe uploaded v3", or "v3" where nobody is known. */
      titleOf(entry) {
        const version = "v" + entry.version;
        return entry.createdBy ? vue.i18n.t("FileModule.base", "{name} uploaded {version}", { name: entry.createdBy.displayName, version }) : version;
      },
      downloadLabelOf(entry) {
        return vue.i18n.t("FileModule.base", "Download version {version}", { version: entry.version });
      },
      formatDate(stamp) {
        if (!stamp) {
          return "";
        }
        const locale = vue.getConfig("i18n").language || void 0;
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
    const _component_UserImage = vue$1.resolveComponent("UserImage");
    return vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_1, [
      $data.loading ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_2, [
        _cache[0] || (_cache[0] = vue$1.createElementVNode(
          "span",
          {
            class: "spinner-border spinner-border-sm",
            "aria-hidden": "true"
          },
          null,
          -1
          /* CACHED */
        )),
        vue$1.createElementVNode(
          "span",
          _hoisted_3,
          vue$1.toDisplayString($options.loadingLabel),
          1
          /* TEXT */
        )
      ])) : $data.failed ? (vue$1.openBlock(), vue$1.createElementBlock(
        "p",
        _hoisted_4,
        vue$1.toDisplayString($options.errorLabel),
        1
        /* TEXT */
      )) : (vue$1.openBlock(), vue$1.createElementBlock("ol", _hoisted_5, [
        (vue$1.openBlock(true), vue$1.createElementBlock(
          vue$1.Fragment,
          null,
          vue$1.renderList($data.versions, (entry) => {
            return vue$1.openBlock(), vue$1.createElementBlock("li", {
              key: entry.version,
              class: "list-group-item file-version"
            }, [
              entry.createdBy ? (vue$1.openBlock(), vue$1.createBlock(
                _component_UserImage,
                vue$1.mergeProps({
                  key: 0,
                  ref_for: true
                }, entry.createdBy, {
                  size: 32,
                  link: false
                }),
                null,
                16
                /* FULL_PROPS */
              )) : vue$1.createCommentVNode("v-if", true),
              vue$1.createElementVNode("div", _hoisted_6, [
                vue$1.createElementVNode("div", _hoisted_7, [
                  vue$1.createTextVNode(
                    vue$1.toDisplayString($options.titleOf(entry)) + " ",
                    1
                    /* TEXT */
                  ),
                  entry.current ? (vue$1.openBlock(), vue$1.createElementBlock(
                    "span",
                    _hoisted_8,
                    vue$1.toDisplayString($options.currentLabel),
                    1
                    /* TEXT */
                  )) : vue$1.createCommentVNode("v-if", true)
                ]),
                vue$1.createElementVNode("div", _hoisted_9, [
                  vue$1.createElementVNode("time", {
                    datetime: entry.createdAt
                  }, vue$1.toDisplayString($options.formatDate(entry.createdAt)), 9, _hoisted_10),
                  vue$1.createTextVNode(
                    " · " + vue$1.toDisplayString($options.shortSize(entry.size)),
                    1
                    /* TEXT */
                  )
                ])
              ]),
              vue$1.createElementVNode("a", {
                class: "btn btn-ghost btn-icon-only",
                href: entry.downloadUrl,
                "aria-label": $options.downloadLabelOf(entry),
                title: $options.downloadLabelOf(entry)
              }, [..._cache[1] || (_cache[1] = [
                vue$1.createElementVNode(
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
  vue.register("AttachedFiles", C0);
  vue.register("FileVersions", C1);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.file.vue.js.map
