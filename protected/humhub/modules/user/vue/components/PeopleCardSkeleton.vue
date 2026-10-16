<template>
    <div class="c-card-skeleton c-entity-card-skeleton c-people-card-skeleton" aria-hidden="true">
        <div class="c-entity-card-skeleton__cover">
            <span class="c-card-skeleton__block c-entity-card-skeleton__avatar"></span>
        </div>
        <div class="c-card-skeleton__header">
            <span class="c-card-skeleton__block c-card-skeleton__title"></span>
            <span class="c-card-skeleton__block c-card-skeleton__version"></span>
        </div>
        <div class="c-card-skeleton__body">
            <span class="c-card-skeleton__block c-card-skeleton__line"></span>
            <span class="c-card-skeleton__block c-card-skeleton__line c-card-skeleton__line--short"></span>
        </div>
        <div class="c-entity-card-skeleton__tags">
            <span class="c-card-skeleton__block c-entity-card-skeleton__tag"></span>
            <span class="c-card-skeleton__block c-entity-card-skeleton__tag"></span>
            <span class="c-card-skeleton__block c-entity-card-skeleton__tag"></span>
        </div>
        <div class="c-card-skeleton__footer">
            <span class="c-card-skeleton__block c-card-skeleton__action"></span>
        </div>
    </div>
</template>

<script>
/**
 * The placeholder of a `PeopleCard` while the directory's first page loads (the `skeleton`
 * slot of `CardDirectory`): the kit's `CardSkeleton` blocks in the entity card's geometry — the
 * full-width cover with the 80px avatar, the name and the subtitle, two detail lines, a row of
 * tags and the full-width button. `user\widgets\PeopleDirectory`'s placeholder renders the same
 * markup.
 *
 * @since 1.20
 */
export default {
    name: 'PeopleCardSkeleton',
};
</script>
