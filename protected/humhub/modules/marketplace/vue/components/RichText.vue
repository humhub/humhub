<template>
    <template v-for="(part, index) in parts" :key="index"><component :is="tag" v-if="part[flag]" :class="markClass">{{ part.text }}</component><template v-else>{{ part.text }}</template></template>
</template>

<script>
/**
 * Renders `[{ text, strong|mark }]` parts (from `strongParts()` or `highlightParts()`) as text
 * nodes, the flagged ones inside `tag` (`b` by default, `mark` for search matches). No markup
 * from the parts is ever interpreted.
 *
 * @since 1.20
 */
export default {
    name: 'RichText',
    props: {
        parts: { type: Array, required: true },
        flag: { type: String, default: 'strong' },
        tag: { type: String, default: 'b' },
        markClass: { type: String, default: null },
    },
};
</script>
