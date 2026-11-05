<template>
    <li v-for="(handler, index) in handlers" :key="index">
        <a
            class="dropdown-item"
            href="#"
            v-bind="handler.attributes"
            data-action-process="file-handler"
        ><i v-if="handler.icon" :class="'dropdown-item-icon ti ti-' + handler.icon" aria-hidden="true"></i>{{ handler.label }}</a>
    </li>
</template>

<script>
/**
 * The `<li>` rows of file handlers (`{label, icon, attributes}`, see
 * `file\serializers\FileHandlerSerializer` - the core JS config's `file.handlers`), for a
 * `<ul class="dropdown-menu">` of the caller's. Each row is a link carrying the handler's own
 * attributes, so its legacy action (`data-action-click`, delegated by humhub.action.js) runs;
 * `data-action-process="file-handler"` marks it as a handler, as the PHP dropdown does.
 *
 * Internal to `UploadField` and `RichTextField` (not auto-registered, see the module file
 * layout in docs/develop/ui-js-vuejs-components.md).
 *
 * @since 1.20
 */
export default {
    props: {
        handlers: { type: Array, default: () => [] },
    },
};
</script>
