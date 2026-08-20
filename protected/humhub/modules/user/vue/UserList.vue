<template>
    <div class="user-list">
        <p v-if="!busy && !error && users.length === 0">{{ emptyLabel }}</p>

        <div v-if="users.length" class="hh-list">
            <a v-for="user in users" :key="user.guid" :href="user.url" class="d-flex">
                <div class="flex-shrink-0 me-2">
                    <UserImage v-bind="user" :size="50" :link="false" />
                </div>
                <div class="flex-grow-1">
                    <h4 class="mt-0">{{ user.displayName }}</h4>
                </div>
            </a>
        </div>

        <p v-if="error" class="text-danger">{{ errorLabel }}</p>

        <div v-if="hasMore" class="pagination-container text-center">
            <a href="#" :class="{ disabled: busy }" @click.prevent="loadMore">{{ loadMoreLabel }}</a>
        </div>
    </div>
</template>

<script>
import { client, i18n, log } from '@humhub/vue';

/**
 * Generic user-list island (`<user-list>`) - loads and renders a page of users from
 * any endpoint returning the `{ total, users, hasMore, nextPage }` shape (`users`
 * being an array of the serialized author/user shape `UserImage`'s props are modeled
 * on, see `humhub\modules\user\services\UserJsonService`). Not tied to any single
 * caller - the like module's user-list modal (`LikeButton.vue`) is the reference
 * consumer, feeding it `like/like/user-list`, but any module can point this at its
 * own JSON endpoint as long as the response shape matches.
 *
 * The Vue analog of `user\widgets\UserListBox` for this call site: same avatar +
 * linked display-name row layout inside the shared `.hh-list` styling (row classes
 * copied verbatim: `.d-flex` / `.flex-shrink-0.me-2` / `.flex-grow-1` / `h4.mt-0`),
 * same default avatar size (50 - `UserListBox`'s view never overrides
 * `user\widgets\Image`'s own default `$width`). Two deliberate deviations from
 * `UserListBox`'s legacy view:
 *
 * - No `<h5>` subtitle line (`user\models\User::$displayNameSub`) - the shared
 *   author/user JSON shape this component is fed does not carry that field. Adding
 *   it would mean widening the shape every other consumer (`UserImage`,
 *   `CommentJsonService`) also spreads, for a single caller's cosmetic need.
 * - "Load more" appends a next page in place (a real infinite-scroll-style action,
 *   driven by `hasMore`/`nextPage` from the response) instead of `UserListBox`'s
 *   `AjaxLinkPager`, which POSTs a page NUMBER and replaces the entire modal content
 *   (title, list and pager all re-rendered from scratch).
 *
 * @since 1.19
 */
export default {
    name: 'UserList',
    i18nCategories: ['UserModule.base'],
    props: {
        url: { type: String, required: true },
        pageSize: { type: Number, default: null },
    },
    data() {
        return {
            users: [],
            total: 0,
            hasMore: false,
            nextPage: null,
            busy: false,
            error: false,
        };
    },
    computed: {
        emptyLabel() {
            return i18n.t('UserModule.base', 'No users found.');
        },
        errorLabel() {
            return i18n.t('UserModule.base', 'Could not load the user list.');
        },
        loadMoreLabel() {
            return i18n.t('UserModule.base', 'Show more');
        },
    },
    created() {
        this.load(1);
    },
    methods: {
        requestUrl(page) {
            const params = [`page=${page}`];
            if (this.pageSize) {
                params.push(`limit=${this.pageSize}`);
            }
            const separator = this.url.includes('?') ? '&' : '?';
            return `${this.url}${separator}${params.join('&')}`;
        },
        async load(page) {
            this.busy = true;
            this.error = false;

            try {
                const response = await client.get(this.requestUrl(page));
                const users = response.users ?? [];
                this.users = page === 1 ? users : this.users.concat(users);
                this.total = response.total ?? this.users.length;
                this.hasMore = !!response.hasMore;
                this.nextPage = response.nextPage ?? null;
            } catch (e) {
                this.error = true;
                log.error(e);
            } finally {
                this.busy = false;
            }
        },
        loadMore() {
            if (this.busy || !this.hasMore || !this.nextPage) {
                return;
            }
            this.load(this.nextPage);
        },
    },
};
</script>
