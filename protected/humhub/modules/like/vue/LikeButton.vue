<template>
    <span v-if="ready" class="likeLinkContainer">
        <a v-if="!liked" href="#" class="like likeAnchor" @click.prevent="toggle()">{{ likeLabel }}</a>
        <a v-else href="#" class="unlike likeAnchor" @click.prevent="toggle()">{{ unlikeLabel }}</a>
        <a v-if="count > 0" :href="userListUrl" data-bs-target="#globalModal">
            <span class="likeCount">({{ count }})</span>
        </a>
    </span>
</template>

<script>
import { client, i18n, log, url } from '@humhub/vue';

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
        };
    },
    computed: {
        ready() {
            return this.liked !== null && this.count !== null;
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
