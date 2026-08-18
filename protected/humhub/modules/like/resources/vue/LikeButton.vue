<template>
    <span class="likeLinkContainer">
        <a v-if="!liked" href="#" class="like likeAnchor" @click.prevent="toggle(likeUrl)">{{ labels.like }}</a>
        <a v-else href="#" class="unlike likeAnchor" @click.prevent="toggle(unlikeUrl)">{{ labels.unlike }}</a>
        <a v-if="count > 0" :href="userListUrl" data-bs-target="#globalModal">
            <span class="likeCount" :title="title">({{ count }})</span>
        </a>
    </span>
</template>

<script>
import { client, i18n, log } from '@humhub/vue';

export default {
    i18nCategories: ['LikeModule.base'],
    props: {
        likeUrl: { type: String, required: true },
        unlikeUrl: { type: String, required: true },
        userListUrl: { type: String, required: true },
        likeCount: { type: Number, default: 0 },
        currentUserLiked: { type: Boolean, default: false },
        title: { type: String, default: '' },
    },
    data() {
        return {
            liked: this.currentUserLiked,
            count: this.likeCount,
            busy: false,
        };
    },
    computed: {
        labels() {
            // Full i18n.t('Category', 'Message') form — required by message extraction
            return {
                like: i18n.t('LikeModule.base', 'Like'),
                unlike: i18n.t('LikeModule.base', 'Unlike'),
            };
        },
    },
    methods: {
        toggle(url) {
            if (this.busy) {
                return;
            }
            this.busy = true;

            client.post(url).then((response) => {
                this.liked = response.currentUserLiked;
                this.count = response.likeCounter;
                if (this.liked) {
                    // Legacy bridge: StreamEntry toggles its notification links on this event
                    jQuery(this.$el).trigger('humhub:like:liked');
                }
                this.busy = false;
            }).catch((e) => {
                log.error(e, true);
                this.busy = false;
            });
        },
    },
};
</script>
