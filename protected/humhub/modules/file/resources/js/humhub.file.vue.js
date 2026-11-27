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
  const VIDEO_TYPES = {
    webm: "video/webm",
    mp4: "video/mp4",
    ogv: "video/ogg",
    mov: "video/quicktime"
  };
  const VIDEO_EXTENSIONS = Object.keys(VIDEO_TYPES);
  const AUDIO_EXTENSIONS = ["mp3", "wav", "m4a", "aac", "flac"];
  function extensionOf(file) {
    const name = String(file.fileName || "");
    const dot = name.lastIndexOf(".");
    return dot === -1 ? "" : name.slice(dot + 1).toLowerCase();
  }
  function formatDuration(seconds) {
    const total = Number(seconds);
    if (seconds === null || seconds === void 0 || !Number.isFinite(total) || total < 0) {
      return "";
    }
    const whole = Math.floor(total);
    const hours = Math.floor(whole / 3600);
    const minutes = Math.floor(whole % 3600 / 60);
    const secs = String(whole % 60).padStart(2, "0");
    return hours > 0 ? `${hours}:${String(minutes).padStart(2, "0")}:${secs}` : `${minutes}:${secs}`;
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
  const NEAR_VIEWPORT = "200px";
  const VIDEO_FALLBACK_RATIO = 16 / 9;
  const nearCallbacks = /* @__PURE__ */ new Map();
  let nearObserver = null;
  function whenNear(el, callback) {
    if (typeof IntersectionObserver === "undefined") {
      callback();
      return;
    }
    nearObserver ?? (nearObserver = new IntersectionObserver(
      (entries) => entries.forEach(({ target, isIntersecting }) => {
        var _a;
        if (isIntersecting) {
          (_a = nearCallbacks.get(target)) == null ? void 0 : _a();
          unwatch(target);
        }
      }),
      { rootMargin: NEAR_VIEWPORT }
    ));
    nearCallbacks.set(el, callback);
    nearObserver.observe(el);
  }
  function unwatch(el) {
    nearObserver == null ? void 0 : nearObserver.unobserve(el);
    nearCallbacks.delete(el);
  }
  const hasSize = (file) => file.width > 0 && file.height > 0;
  const _sfc_main$2 = {
    name: "AttachedMedia",
    props: {
      // The images and the videos, see AttachedFiles.vue
      media: { type: Array, required: true },
      // The record's group in the media viewer
      galleryId: { type: String, required: true }
    },
    directives: {
      // A video element is watched from its mount to its unmount: a tile that moves to another row gets a new element
      watchVideo: {
        mounted(el, { value, instance }) {
          instance.watchVideo(el, value);
        },
        beforeUnmount(el, { instance }) {
          instance.unwatchVideo(el);
        }
      }
    },
    data() {
      return { box: null, sizes: {}, active: {}, failed: {} };
    },
    computed: {
      layout() {
        const box = this.box || {
          width: SIZES.width * 16,
          rowHeight: SIZES.rowHeight * 16,
          singleHeight: SIZES.singleHeight * 16,
          gap: SIZES.gap * 16
        };
        return justify(this.media.map((file) => this.ratioOf(file)), { ...box, maxRows: MAX_ROWS });
      },
      shownCount() {
        return this.layout.rows.reduce((count, row) => count + row.items.length, 0);
      },
      hiddenMedia() {
        return this.media.slice(this.shownCount);
      }
    },
    created() {
      this.displayed = /* @__PURE__ */ new Set();
      this.videos = /* @__PURE__ */ new Set();
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
      this.videos.forEach((el) => unwatch(el));
      this.videos.clear();
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
      isVideo(file) {
        return !file.previewUrl;
      },
      videoType(file) {
        return VIDEO_TYPES[extensionOf(file)];
      },
      ratioOf(file) {
        var _a;
        if (hasSize(file)) {
          return file.width / file.height;
        }
        return ((_a = this.sizes[file.guid]) == null ? void 0 : _a.ratio) ?? (this.isVideo(file) ? VIDEO_FALLBACK_RATIO : 1);
      },
      // From the file, else what the tile measured
      durationText(file) {
        var _a;
        return formatDuration(file.duration ?? ((_a = this.sizes[file.guid]) == null ? void 0 : _a.duration));
      },
      measured(video, file) {
        const known = this.sizes[file.guid];
        const size = { ...known };
        if (video.videoWidth > 0 && video.videoHeight > 0) {
          size.ratio = video.videoWidth / video.videoHeight;
        }
        if (Number.isFinite(video.duration)) {
          size.duration = video.duration;
        }
        if (size.ratio !== (known == null ? void 0 : known.ratio) || size.duration !== (known == null ? void 0 : known.duration)) {
          this.sizes[file.guid] = size;
        }
      },
      // A video element mounted: loads the frame once the tile is near the viewport
      watchVideo(el, guid) {
        if (this.videos.has(el)) {
          return;
        }
        this.videos.add(el);
        whenNear(el, () => {
          this.active[guid] = true;
        });
      },
      unwatchVideo(el) {
        this.videos.delete(el);
        unwatch(el);
      },
      videoLabel(file) {
        const duration = this.durationText(file);
        const fileName = file.fileName;
        return duration ? vue.i18n.t("FileModule.base", "{fileName}, video, {duration}", { fileName, duration }) : vue.i18n.t("FileModule.base", "{fileName}, video", { fileName });
      },
      isMoreTile(index) {
        return this.layout.hidden > 0 && index === this.shownCount - 1;
      },
      moreLabel() {
        const fileName = this.media[this.shownCount - 1].fileName;
        const count = this.layout.hidden;
        return this.hiddenMedia.every((file) => !this.isVideo(file)) ? vue.i18n.t("FileModule.base", "{fileName}, {count,plural,=1{one more image} other{# more images}}", {
          fileName,
          count
        }) : vue.i18n.t(
          "FileModule.base",
          "{fileName}, {count,plural,=1{one more image or video} other{# more images and videos}}",
          { fileName, count }
        );
      },
      // The preview fits into PREVIEW_SIZE², a larger tile (or a sharper screen) gets the display version and keeps
      // it while the area shrinks. An original that fits into the preview is the preview.
      source(file, width) {
        if (this.displayed.has(file.guid)) {
          return file.displayUrl;
        }
        const fits = hasSize(file) && file.width <= PREVIEW_SIZE && file.height <= PREVIEW_SIZE;
        if (!file.displayUrl || fits) {
          return file.previewUrl;
        }
        const previewWidth = Math.min(PREVIEW_SIZE, PREVIEW_SIZE * this.ratioOf(file));
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
  const _hoisted_2$2 = ["data-ui-gallery", "href", "type", "title", "aria-label"];
  const _hoisted_3$2 = ["src", "preload", "onLoadedmetadata", "onError"];
  const _hoisted_4$2 = {
    key: 1,
    class: "ti ti-movie post-files-video-icon",
    "aria-hidden": "true"
  };
  const _hoisted_5$2 = {
    key: 0,
    class: "post-files-video-duration",
    "aria-hidden": "true"
  };
  const _hoisted_6$2 = {
    key: 3,
    class: "post-files-image-more",
    "aria-hidden": "true"
  };
  const _hoisted_7$2 = ["data-ui-gallery", "href", "data-ui-gallery-src", "title", "aria-label"];
  const _hoisted_8$2 = ["src", "alt", "width", "height"];
  const _hoisted_9$1 = {
    key: 0,
    class: "post-files-image-more",
    "aria-hidden": "true"
  };
  const _hoisted_10$1 = ["data-ui-gallery", "href", "type", "data-ui-gallery-src", "title"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _directive_watch_video = vue$1.resolveDirective("watch-video");
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
                  return vue$1.openBlock(), vue$1.createElementBlock(
                    vue$1.Fragment,
                    {
                      key: $props.media[item.index].guid
                    },
                    [
                      $options.isVideo($props.media[item.index]) ? (vue$1.openBlock(), vue$1.createElementBlock("a", {
                        key: 0,
                        class: vue$1.normalizeClass(["post-files-tile post-files-video", { "is-unplayable": $data.failed[$props.media[item.index].guid] }]),
                        style: vue$1.normalizeStyle({ width: $options.px(item.width) }),
                        "data-ui-gallery": $props.galleryId,
                        href: $props.media[item.index].url,
                        type: $options.videoType($props.media[item.index]),
                        title: $props.media[item.index].fileName,
                        "aria-label": $options.isMoreTile(item.index) ? $options.moreLabel() : $options.videoLabel($props.media[item.index])
                      }, [
                        !$data.failed[$props.media[item.index].guid] ? vue$1.withDirectives((vue$1.openBlock(), vue$1.createElementBlock("video", {
                          key: 0,
                          src: $data.active[$props.media[item.index].guid] ? $props.media[item.index].url + "#t=0.001" : null,
                          preload: $data.active[$props.media[item.index].guid] ? "metadata" : null,
                          muted: "",
                          playsinline: "",
                          "aria-hidden": "true",
                          tabindex: "-1",
                          onLoadedmetadata: ($event) => $options.measured($event.target, $props.media[item.index]),
                          onError: ($event) => $data.failed[$props.media[item.index].guid] = true
                        }, null, 40, _hoisted_3$2)), [
                          [_directive_watch_video, $props.media[item.index].guid]
                        ]) : (vue$1.openBlock(), vue$1.createElementBlock("i", _hoisted_4$2)),
                        !$options.isMoreTile(item.index) && !$data.failed[$props.media[item.index].guid] ? (vue$1.openBlock(), vue$1.createElementBlock(
                          vue$1.Fragment,
                          { key: 2 },
                          [
                            _cache[0] || (_cache[0] = vue$1.createElementVNode(
                              "span",
                              {
                                class: "post-files-video-play",
                                "aria-hidden": "true"
                              },
                              [
                                vue$1.createElementVNode("i", { class: "ti ti-player-play-filled" })
                              ],
                              -1
                              /* CACHED */
                            )),
                            $options.durationText($props.media[item.index]) ? (vue$1.openBlock(), vue$1.createElementBlock(
                              "span",
                              _hoisted_5$2,
                              vue$1.toDisplayString($options.durationText($props.media[item.index])),
                              1
                              /* TEXT */
                            )) : vue$1.createCommentVNode("v-if", true)
                          ],
                          64
                          /* STABLE_FRAGMENT */
                        )) : vue$1.createCommentVNode("v-if", true),
                        $options.isMoreTile(item.index) ? (vue$1.openBlock(), vue$1.createElementBlock(
                          "span",
                          _hoisted_6$2,
                          "+" + vue$1.toDisplayString($options.layout.hidden),
                          1
                          /* TEXT */
                        )) : vue$1.createCommentVNode("v-if", true)
                      ], 14, _hoisted_2$2)) : (vue$1.openBlock(), vue$1.createElementBlock("a", {
                        key: 1,
                        class: "post-files-tile post-files-image",
                        style: vue$1.normalizeStyle({ width: $options.px(item.width) }),
                        "data-ui-gallery": $props.galleryId,
                        href: $props.media[item.index].url,
                        "data-ui-gallery-src": $props.media[item.index].displayUrl || null,
                        title: $props.media[item.index].fileName,
                        "aria-label": $options.isMoreTile(item.index) ? $options.moreLabel() : null
                      }, [
                        vue$1.createElementVNode("img", {
                          loading: "lazy",
                          decoding: "async",
                          src: $options.source($props.media[item.index], item.width),
                          alt: $props.media[item.index].fileName,
                          width: Math.round(item.width),
                          height: Math.round(row.height)
                        }, null, 8, _hoisted_8$2),
                        $options.isMoreTile(item.index) ? (vue$1.openBlock(), vue$1.createElementBlock(
                          "span",
                          _hoisted_9$1,
                          "+" + vue$1.toDisplayString($options.layout.hidden),
                          1
                          /* TEXT */
                        )) : vue$1.createCommentVNode("v-if", true)
                      ], 12, _hoisted_7$2))
                    ],
                    64
                    /* STABLE_FRAGMENT */
                  );
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
        vue$1.renderList($options.hiddenMedia, (file) => {
          return vue$1.openBlock(), vue$1.createElementBlock("a", {
            key: file.guid,
            hidden: "",
            "data-ui-gallery": $props.galleryId,
            href: file.url,
            type: $options.isVideo(file) ? $options.videoType(file) : null,
            "data-ui-gallery-src": file.displayUrl || null,
            title: file.fileName
          }, vue$1.toDisplayString(file.fileName), 9, _hoisted_10$1);
        }),
        128
        /* KEYED_FRAGMENT */
      ))
    ]);
  }
  const AttachedMedia = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const LIST_HEAD = 2;
  const COLLAPSE_FROM = 4;
  let uid = 0;
  const _sfc_main$1 = {
    components: { AttachedMedia },
    i18nCategories: ["FileModule.base"],
    props: {
      // File shapes, see the class docblock.
      files: { type: Array, required: true },
      // Scopes the media viewer's gallery to this record's media.
      galleryId: { type: String, required: true },
      // Render the media grid and the audio players at all (`ShowFiles::$preview`).
      preview: { type: Boolean, default: true },
      // Drop the images and videos from the list below - they are already tiles of the media grid. Audio files stay:
      // the list is where they play
      // (the `excludeMediaFilesPreview` setting of the file module).
      excludeMedia: { type: Boolean, default: false }
    },
    data() {
      return { expanded: false };
    },
    computed: {
      // The tiles of the media grid in the files' order: the images, and the videos the browser can play
      media() {
        return this.preview ? this.files.filter((file) => this.isImage(file) || this.isVideo(file)) : [];
      },
      // Without `excludeMedia` all files, otherwise those the grid doesn't render - a media file it has no tile
      // for (an AVI video) stays in the list, so does every audio file.
      listed() {
        if (!this.excludeMedia) {
          return this.files;
        }
        const rendered = new Set(this.media);
        return this.files.filter((file) => !rendered.has(file));
      },
      // The audio files of the list in its order: the next one plays when one ends
      playlist() {
        return this.preview ? this.listed.filter((file) => this.isAudio(file)) : [];
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
      isImage(file) {
        return !!file.previewUrl;
      },
      // A video by its extension, but only without an image preview - an image preview always wins
      isVideo(file) {
        return !file.previewUrl && VIDEO_EXTENSIONS.includes(extensionOf(file));
      },
      isAudio(file) {
        return !file.previewUrl && AUDIO_EXTENSIONS.includes(extensionOf(file));
      },
      isPlayable(file) {
        return this.preview && this.isAudio(file);
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
    key: 1,
    class: "list-group file-list"
  };
  const _hoisted_3$1 = ["id"];
  const _hoisted_4$1 = ["inert"];
  const _hoisted_5$1 = {
    class: "file-list-more-inner",
    role: "none"
  };
  const _hoisted_6$1 = {
    key: 0,
    class: "list-group-item file-row file-list-toggle"
  };
  const _hoisted_7$1 = ["id"];
  const _hoisted_8$1 = ["aria-expanded", "aria-controls", "aria-describedby"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_AttachedMedia = vue$1.resolveComponent("AttachedMedia");
    const _component_FileRow = vue$1.resolveComponent("FileRow");
    const _directive_additions = vue$1.resolveDirective("additions");
    return vue$1.openBlock(), vue$1.createElementBlock(
      vue$1.Fragment,
      null,
      [
        $options.media.length ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_1$1, [
          vue$1.createVNode(_component_AttachedMedia, {
            media: $options.media,
            "gallery-id": $props.galleryId
          }, null, 8, ["media", "gallery-id"])
        ])) : vue$1.createCommentVNode("v-if", true),
        $options.listed.length ? vue$1.withDirectives((vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_2$1, [
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
                  file,
                  playable: $options.isPlayable(file),
                  playlist: $options.playlist
                }, null, 8, ["file", "playable", "playlist"]);
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
              vue$1.createElementVNode("div", _hoisted_5$1, [
                (vue$1.openBlock(true), vue$1.createElementBlock(
                  vue$1.Fragment,
                  null,
                  vue$1.renderList($options.listedTail, (file) => {
                    return vue$1.openBlock(), vue$1.createBlock(_component_FileRow, {
                      key: file.guid,
                      file,
                      playable: $options.isPlayable(file),
                      playlist: $options.playlist,
                      onCurrent: _cache[0] || (_cache[0] = ($event) => $data.expanded = true)
                    }, null, 8, ["file", "playable", "playlist"]);
                  }),
                  128
                  /* KEYED_FRAGMENT */
                ))
              ])
            ], 10, _hoisted_4$1)) : vue$1.createCommentVNode("v-if", true)
          ], 8, _hoisted_3$1),
          $options.collapsible ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_6$1, [
            _cache[2] || (_cache[2] = vue$1.createElementVNode(
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
            }, vue$1.toDisplayString($options.countLabel), 9, _hoisted_7$1),
            vue$1.createElementVNode("button", {
              ref: "toggle",
              type: "button",
              class: "btn btn-ghost btn-sm file-row-action",
              "aria-expanded": $data.expanded ? "true" : "false",
              "aria-controls": $options.listId,
              "aria-describedby": $options.countId,
              onClick: _cache[1] || (_cache[1] = (...args) => $options.toggle && $options.toggle(...args))
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
            ], 8, _hoisted_8$1)
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
