<template>
    <span v-if="ready" class="likeLinkContainer">
        <template v-if="guest">
            <a :href="loginUrl" data-bs-target="#globalModal">{{ likeLabel }}</a>
            <span v-if="count > 0" class="likeCount">({{ count }})</span>
        </template>
        <template v-else>
            <a v-if="!liked" href="#" class="like likeAnchor" @click.prevent="toggle()">{{ likeLabel }}</a>
            <a v-else href="#" class="unlike likeAnchor" @click.prevent="toggle()">{{ unlikeLabel }}</a>
            <a v-if="count > 0" href="#" @click.prevent="showUserList = true">
                <span class="likeCount">({{ count }})</span>
            </a>
        </template>

        <UiModal v-model:show="showUserList">
            <template #header="{ titleId }">
                <h5 class="modal-title" :id="titleId" v-html="userListTitle"></h5>
                <button type="button" class="btn-close" :aria-label="closeLabel" @click="showUserList = false"></button>
            </template>
            <UserList v-if="showUserList" :url="userListUrl" />
        </UiModal>
    </span>
</template>

<script>
import { client, getConfig, i18n, log, url } from '@humhub/vue';

export default {
    i18nCategories: ['LikeModule.base'],
    props: {
        recordId: { type: Number, required: true },
        likeCount: { type: Number, default: null },
        currentUserLiked: { type: Boolean, default: null },
    },
    data() {
        return {
            liked: this.currentUserLiked,
            count: this.likeCount,
            busy: false,
            showUserList: false,
        };
    },
    computed: {
        // Guests never get a liked state from the server (LikeLink always sends
        // `currentUserLiked: false` for them) — the like/unlike anchors and the
        // user-list link are member-only, so ready() must not wait on `liked`.
        ready() {
            return this.guest ? this.count !== null : (this.liked !== null && this.count !== null);
        },
        guest() {
            return getConfig('user').isGuest === true;
        },
        loginUrl() {
            return getConfig('user').loginUrl;
        },
        likeLabel() {
            // Full i18n.t('Category', 'Message') form — required by message extraction
            return i18n.t('LikeModule.base', 'Like');
        },
        unlikeLabel() {
            return i18n.t('LikeModule.base', 'Unlike');
        },
        userListUrl() {
            return url('/like/like/user-list', { recordId: this.recordId });
        },
        // Same message key the legacy HTML user-list action used for the modal title
        // (`Yii::t('LikeModule.base', "<strong>Users</strong> who like this")`) - kept
        // as trusted, translator-authored markup (rendered via v-html in the template,
        // same trust boundary the legacy `Modal::beginDialog(['title' => $title])` call
        // already had for this exact string - see `humhub\widgets\modal\Modal`'s "not
        // html encoded!" docblock on `$title`), not user input.
        userListTitle() {
            return i18n.t('LikeModule.base', '<strong>Users</strong> who like this');
        },
        // 'base' is not declared in `i18nCategories` above (only `LikeModule.base` is) -
        // an accepted, established gap: UserImage.vue's `onlineLabel` reads
        // `UserModule.base` the same uncovered way, see its own docblock. Falls back to
        // the English source text until some other page load happens to warm the
        // `base` category's translation cache.
        closeLabel() {
            return i18n.t('base', 'Close');
        },
    },
    created() {
        if (!this.ready) {
            this.load();
        }
    },
    methods: {
        load() {
            client.get(url('/like/like/info', { recordId: this.recordId })).then((response) => {
                this.liked = response.currentUserLiked;
                this.count = response.likeCounter;
            }).catch((e) => {
                log.error(e, true);
                // Without this the island stays invisible forever (ready()
                // never turns true) — fall back to a plain, un-liked state so
                // the button still renders; a later toggle() just posts.
                this.liked = false;
                this.count = 0;
            });
        },
        toggle() {
            if (this.busy) {
                return;
            }
            this.busy = true;

            client.post(url(this.liked ? '/like/like/unlike' : '/like/like/like', { recordId: this.recordId })).then((response) => {
                this.liked = response.currentUserLiked;
                this.count = response.likeCounter;
                this.busy = false;
                if (this.liked) {
                    // Legacy bridge: StreamEntry toggles its notification links on this event
                    jQuery(this.$el).trigger('humhub:like:liked');
                }
            }).catch((e) => {
                this.busy = false;
                log.error(e, true);
            });
        },
    },
};
</script>
