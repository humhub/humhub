<template>
    <div class="card entity-card entity-card-skeleton placeholder-glow" aria-hidden="true">
        <div class="entity-card-cover entity-card-cover-plain">
            <span class="placeholder entity-card-skeleton-image"></span>
        </div>
        <div class="entity-card-header">
            <span class="placeholder entity-card-skeleton-title"></span>
            <span class="placeholder entity-card-skeleton-subtitle"></span>
        </div>
        <div class="entity-card-body">
            <span class="placeholder entity-card-skeleton-line"></span>
            <span class="placeholder entity-card-skeleton-line entity-card-skeleton-line-short"></span>
        </div>
        <div class="entity-card-footer">
            <span class="placeholder entity-card-skeleton-action"></span>
            <span class="placeholder entity-card-skeleton-icon"></span>
        </div>
    </div>
</template>

<script>
/**
 * A neutral placeholder card, shown by `EntityGrid` while its first page loads: an entity card
 * (`.entity-card-skeleton`) of Bootstrap placeholders in the module card's geometry — the 80px
 * image, a title and a version bar, two text lines, and the footer's full-width action beside a
 * small square icon button — so the layout does not jump when the results arrive. Its glow stops
 * under `prefers-reduced-motion` (`resources/scss/_entity-card.scss`). A server rendered
 * placeholder of an island may render the same markup.
 *
 * @since 1.20
 */
export default {
    name: 'CardSkeleton',
};
</script>
