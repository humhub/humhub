<template>
    <div class="card entity-card entity-card-skeleton people-card-skeleton placeholder-glow" aria-hidden="true">
        <div class="entity-card-cover">
            <span class="placeholder entity-card-skeleton-avatar"></span>
        </div>
        <div class="entity-card-header">
            <span class="placeholder entity-card-skeleton-title"></span>
            <span class="placeholder entity-card-skeleton-subtitle"></span>
        </div>
        <div class="entity-card-body">
            <span class="placeholder entity-card-skeleton-line"></span>
            <span class="placeholder entity-card-skeleton-line entity-card-skeleton-line-short"></span>
        </div>
        <div class="entity-card-tags">
            <span class="placeholder entity-card-skeleton-tag"></span>
            <span class="placeholder entity-card-skeleton-tag"></span>
            <span class="placeholder entity-card-skeleton-tag"></span>
        </div>
        <div class="entity-card-footer">
            <span class="placeholder entity-card-skeleton-action"></span>
        </div>
    </div>
</template>

<script>
/**
 * The placeholder of a `PeopleCard` while the directory's first page loads (the `skeleton`
 * slot of `EntityBrowser`): an entity card skeleton (Bootstrap placeholders) — the
 * full-width cover with the 80px avatar, the name and the subtitle, two detail lines, a row of
 * tags and the full-width button. `user\widgets\PeopleBrowser`'s placeholder renders the same
 * markup.
 *
 * @since 1.20
 */
export default {
    name: 'PeopleCardSkeleton',
};
</script>
