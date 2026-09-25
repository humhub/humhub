<template>
    <div class="c-card-skeleton c-space-card-skeleton" aria-hidden="true">
        <div class="c-space-card-skeleton__cover">
            <span class="c-card-skeleton__block c-space-card-skeleton__avatar"></span>
        </div>
        <div class="c-card-skeleton__header">
            <span class="c-card-skeleton__block c-card-skeleton__title"></span>
        </div>
        <div class="c-card-skeleton__body">
            <span class="c-card-skeleton__block c-card-skeleton__line"></span>
            <span class="c-card-skeleton__block c-card-skeleton__line"></span>
            <span class="c-card-skeleton__block c-card-skeleton__line c-card-skeleton__line--short"></span>
        </div>
        <div class="c-space-card-skeleton__tags">
            <span class="c-card-skeleton__block c-space-card-skeleton__tag"></span>
            <span class="c-card-skeleton__block c-space-card-skeleton__tag"></span>
            <span class="c-card-skeleton__block c-space-card-skeleton__tag"></span>
        </div>
        <div class="c-card-skeleton__footer">
            <span class="c-card-skeleton__block c-card-skeleton__action"></span>
        </div>
    </div>
</template>

<script>
/**
 * The placeholder of a `SpaceCard` while the directory's first page loads (the `skeleton`
 * slot of `CardDirectory`): the kit's `CardSkeleton` blocks in the space card's geometry — the
 * full-width cover with the 80px avatar, the name, three description lines, a row of tags and
 * the full-width button. `space\widgets\SpaceDirectory`'s placeholder renders the same markup.
 *
 * @since 1.20
 */
export default {
    name: 'SpaceCardSkeleton',
};
</script>
