/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from topic/vue/ via `grunt build-vue --module=topic`.
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
  const _sfc_main = {
    name: "TopicFilterControl",
    props: {
      filter: { type: Object, required: true },
      modelValue: { type: [String, Number, Array], default: () => [] },
      inputId: { type: String, default: null }
    },
    emits: ["update:modelValue"],
    computed: {
      containerId() {
        var _a;
        return ((_a = this.filter.props) == null ? void 0 : _a.containerId) ?? null;
      }
    },
    methods: {
      params(params) {
        return this.containerId === null ? params : { ...params, containerId: this.containerId };
      },
      search(q, pageSize) {
        return vue$1.client.get(vue$1.apiUrl("topic/picker", this.params({ q, pageSize }))).then((response) => response.results || []);
      },
      resolve(ids) {
        return vue$1.client.get(vue$1.apiUrl("topic/picker", { ids: ids.join(","), ...this.params({}), pageSize: ids.length })).then((response) => response.results || []);
      },
      itemLabel(topic) {
        return topic.name;
      },
      // A chip names a foreign container only in its tooltip: the chip stays short.
      itemTitle(topic) {
        return this.isForeign(topic) ? `${topic.name} (${topic.container.name})` : null;
      },
      // Without a colour the dot keeps the neutral colour of its class.
      dotStyle(topic) {
        return topic.color ? { backgroundColor: topic.color } : null;
      },
      isForeign(topic) {
        return !!topic.container && String(topic.container.id) !== String(this.containerId);
      }
    }
  };
  const _hoisted_1 = {
    key: 0,
    class: "c-topic-filter__container"
  };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_PickerFilterControl = vue.resolveComponent("PickerFilterControl");
    return vue.openBlock(), vue.createBlock(_component_PickerFilterControl, {
      filter: $props.filter,
      "model-value": $props.modelValue,
      "input-id": $props.inputId,
      multiple: true,
      search: $options.search,
      resolve: $options.resolve,
      "item-label": $options.itemLabel,
      "item-title": $options.itemTitle,
      icon: "ti-star",
      block: "c-topic-filter",
      "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => _ctx.$emit("update:modelValue", $event))
    }, {
      option: vue.withCtx(({ item }) => [
        vue.createElementVNode(
          "span",
          {
            class: "c-topic-filter__dot",
            style: vue.normalizeStyle($options.dotStyle(item))
          },
          null,
          4
          /* STYLE */
        )
      ]),
      "option-detail": vue.withCtx(({ item }) => [
        $options.isForeign(item) ? (vue.openBlock(), vue.createElementBlock(
          "span",
          _hoisted_1,
          vue.toDisplayString(item.container.name),
          1
          /* TEXT */
        )) : vue.createCommentVNode("v-if", true)
      ]),
      chip: vue.withCtx(({ item }) => [
        vue.createElementVNode(
          "span",
          {
            class: "c-topic-filter__dot",
            style: vue.normalizeStyle($options.dotStyle(item))
          },
          null,
          4
          /* STYLE */
        )
      ]),
      _: 1
      /* STABLE */
    }, 8, ["filter", "model-value", "input-id", "search", "resolve", "item-label", "item-title"]);
  }
  const __vite_glob_0_0 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  Object.entries(/* @__PURE__ */ Object.assign({ "./TopicFilterControl.vue": __vite_glob_0_0 })).forEach(([path, component]) => {
    vue$1.register(path.slice("./".length, -".vue".length), component);
  });
  vue$1.registerFilterType("topic", "TopicFilterControl");
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.topic.vue.js.map
