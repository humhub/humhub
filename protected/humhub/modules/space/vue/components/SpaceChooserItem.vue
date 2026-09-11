<template>
    <a
        :href="space.url"
        class="dropdown-item d-flex"
        :class="{ selected }"
        data-space-chooser-item
        :data-space-guid="space.guid"
        v-bind="relationAttribute"
    >
        <div class="flex-shrink-0 me-2">
            <SpaceImage v-bind="space" :width="24" :link="false" />
        </div>

        <div class="flex-grow-1">
            <strong class="space-name">{{ space.name }}</strong>

            <i
                v-if="badge"
                class="fa badge-space float-end type tt"
                :class="badge.icon"
                :title="badge.title"
                aria-hidden="true"
            ></i>

            <div
                v-if="newItems > 0"
                :data-message-count="newItems"
                class="badge badge-space messageCount float-end tt"
                :title="newItemsTitle"
            >{{ newItems }}</div>

            <br>
            <p class="space-description">{{ shortDescription }}</p>

            <div v-if="space.tags && space.tags.length" class="space-tags d-none">
                <span v-for="tag in space.tags" :key="tag" class="badge badge-light">{{ tag }}</span>
            </div>
        </div>
    </a>
</template>

<script>
/**
 * One space in the chooser, the Vue counterpart of the removed
 * `space\widgets\views\spaceChooserItem.php`.
 *
 * Markup parity with that view, because theme CSS styles the list through it: the
 * `a.dropdown-item.d-flex` carrying `data-space-chooser-item` and `data-space-guid`, one of the
 * four mutually exclusive relation attributes (`data-space-member`, `data-space-following`,
 * `data-space-archived`, `data-space-none` — `_space.scss` and the legacy remote-search
 * selectors address them), the 24px image, the name, the star/history badge, the unread count
 * and the truncated description.
 *
 * The relation is the caller's business, not the payload's: `/api/v2/space` answers the same
 * space to everyone, and the chooser knows which of them are its own from the list it loaded
 * with `scope=mine` (see `SpaceChooser.vue`).
 *
 * @since 1.20
 */
import { i18n } from '@humhub/vue';

const DESCRIPTION_LENGTH = 60;

export default {
    props: {
        // Serialized space (SpaceSerializer::list()).
        space: { type: Object, required: true },
        // 'member', 'following', 'archived' or 'none' - what this space is to the caller.
        relation: { type: String, default: 'none' },
        // Items the caller has not seen since their last visit, 0 for none and for a space
        // they are not a member of.
        newItems: { type: Number, default: 0 },
        // Whether the keyboard selection currently rests on this entry.
        selected: { type: Boolean, default: false },
    },
    computed: {
        relationAttribute() {
            return { ['data-space-' + this.relation]: '' };
        },
        badge() {
            if (this.relation === 'following') {
                return {
                    icon: 'fa-star',
                    title: i18n.t('SpaceModule.chooser', 'You are following this space'),
                };
            }

            if (this.space.archived) {
                return {
                    icon: 'fa-history',
                    title: i18n.t('SpaceModule.chooser', 'This space is archived'),
                };
            }

            return null;
        },
        newItemsTitle() {
            return i18n.t(
                'SpaceModule.chooser',
                '{n,plural,=1{# new entry} other{# new entries}} since your last visit',
                { n: this.newItems },
            );
        },
        shortDescription() {
            const description = this.space.description || '';

            return description.length > DESCRIPTION_LENGTH
                ? description.slice(0, DESCRIPTION_LENGTH) + '...'
                : description;
        },
    },
};
</script>
