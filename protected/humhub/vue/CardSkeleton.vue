<template>
    <div class="c-card-skeleton" aria-hidden="true">
        <div class="c-card-skeleton__cover">
            <span class="c-card-skeleton__block c-card-skeleton__image"></span>
        </div>
        <div class="c-card-skeleton__header">
            <span class="c-card-skeleton__block c-card-skeleton__title"></span>
            <span class="c-card-skeleton__block c-card-skeleton__version"></span>
        </div>
        <div class="c-card-skeleton__body">
            <span class="c-card-skeleton__block c-card-skeleton__line"></span>
            <span class="c-card-skeleton__block c-card-skeleton__line c-card-skeleton__line--short"></span>
        </div>
        <div class="c-card-skeleton__footer">
            <span class="c-card-skeleton__block c-card-skeleton__action"></span>
            <span class="c-card-skeleton__block c-card-skeleton__icon"></span>
        </div>
    </div>
</template>

<script>
/**
 * A neutral placeholder card, shown by `CardGrid` while its first page loads, with the geometry
 * of a directory card (the design system's module card): the 80px image, a title and a version
 * bar, two text lines, and the footer's full-width action beside a small square icon button —
 * so the layout does not jump when the results arrive. Its shimmer stops under
 * `prefers-reduced-motion` (`.c-card-skeleton` in `resources/scss/_card-directory.scss`). A server
 * rendered placeholder of an island may render the same markup.
 *
 * @since 1.20
 */
export default {
    name: 'CardSkeleton',
};
</script>
