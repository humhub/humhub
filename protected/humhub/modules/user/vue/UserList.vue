<template>
    <div class="user-list">
        <p v-if="!busy && !error && users.length === 0">{{ emptyLabel }}</p>

        <div v-if="users.length" class="hh-list">
            <a v-for="user in users" :key="user.guid" :href="user.url" class="d-flex" @click="$emit('user-click', user)">
                <div class="flex-shrink-0 me-2">
                    <UserImage v-bind="user" :size="50" :link="false" class="m-0" />
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
 * an endpoint returning either of two shapes:
 *
 * - the API's paginated list envelope (`{results, total, page, pageSize, pages}`,
 *   see `docs/develop/concept-api.md`), whose rows are user shapes;
 *   `hasMore`/`nextPage` derive from `page`/`pages`.
 * - the legacy `{ total, users, hasMore, nextPage }` shape, for callers that predate
 *   the API.
 *
 * Not tied to any single caller - the like module's user-list modal
 * (`LikeButton.vue`) is the reference consumer, feeding it the `like/users`
 * endpoint, but any module can point this at its own endpoint as long as the
 * response matches one of the two shapes.
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
 *   it would mean widening the user-short shape every consumer spreads, for a
 *   single caller's cosmetic need.
 * - "Load more" appends a next page in place (a real infinite-scroll-style action,
 *   driven by `hasMore`/`nextPage` from the response) instead of `UserListBox`'s
 *   `AjaxLinkPager`, which POSTs a page NUMBER and replaces the entire modal content
 *   (title, list and pager all re-rendered from scratch).
 *
 * @since 1.19
 */
export default {
    name: 'UserList',
    // INERT for every consumer of this component today: the mounter (humhub.vue.js's
    // mountElement()) only ever reads `i18nCategories` off the TOP-LEVEL component it
    // mounts as an island, and `UserList` is always nested (e.g. inside `LikeButton`'s
    // user-list modal) rather than mounted directly - see
    // docs/develop/ui-js-vuejs-components.md, "Mounting and lifecycle", "i18n
    // preloading". Left declared (rather than removed) because it becomes live and
    // correct the moment some future caller mounts `<user-list>` directly as its own
    // island; until then, every current top-level consumer must declare
    // `UserModule.base` itself (`LikeButton.vue` does, alongside `base` for the same
    // reason - see its own `i18nCategories` comment).
    i18nCategories: ['UserModule.base'],
    props: {
        url: { type: String, required: true },
        pageSize: { type: Number, default: null },
    },
    // `user-click`: fired on every row click (in addition to, not instead of, that row's
    // own native navigation - the `<a href>` is never `preventDefault()`-ed here), with the
    // clicked user as payload. Mirrors legacy `UserListBox`'s `data-modal-close="1"` rows
    // (`userListBox.php`) closing whatever modal hosts the list on click - `LikeButton.vue`
    // listens for this to close its own `UiModal` the same way.
    emits: ['user-click'],
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
            // Own message, not an existing core string - stays in this component's own
            // module category (extractable: MessageController routes .vue files to the
            // JsMessageExtractor via this exact `i18n.t('Category', 'Message')` call form).
            return i18n.t('UserModule.base', 'Could not load the user list.');
        },
        loadMoreLabel() {
            // Reuses the existing core `base` translation (verified against
            // protected/humhub/messages/de/base.php) instead of duplicating it under
            // `UserModule.base` - the same key `CoreJsConfig`'s legacy stream "Show more"
            // button already ships (`Yii::t('base', 'Show more')`).
            return i18n.t('base', 'Show more');
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
                let users;

                if (response.results) {
                    // The API's list envelope — rows are user shapes already
                    // (see the class docblock).
                    users = response.results;
                    this.total = response.total ?? users.length;
                    const currentPage = response.page ?? page;
                    const pages = response.pages ?? currentPage;
                    this.hasMore = currentPage < pages;
                    this.nextPage = this.hasMore ? currentPage + 1 : null;
                } else {
                    users = response.users ?? [];
                    this.total = response.total ?? users.length;
                    this.hasMore = !!response.hasMore;
                    this.nextPage = response.nextPage ?? null;
                }

                this.users = page === 1 ? users : this.users.concat(users);
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
