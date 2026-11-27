<template>
    <FileRow
        v-additions="item.file"
        :file="item.file"
        :action="action"
        :progress="item.progress ?? null"
        :error="item.error || null"
        :busy="Boolean(item.busy)"
        :popover-placement="popoverPlacement"
        @remove="$emit('remove')"
    />
</template>

<script>
/**
 * A row of `UploadList`: the `FileRow` of one item. Internal building block of the list (a subdirectory of `vue/`,
 * so it is not registered).
 *
 * The row reads the state of its item itself: a progress tick renders this row only, not the list, whose transition
 * group would update and measure every row. The additions (the image preview popover) run again only when the row
 * shows another file.
 *
 * @since 1.20
 */
export default {
    name: 'UploadRow',
    props: {
        item: { type: Object, required: true },
        action: { type: String, required: true },
        popoverPlacement: { type: String, default: 'right' },
    },
    emits: ['remove'],
};
</script>
