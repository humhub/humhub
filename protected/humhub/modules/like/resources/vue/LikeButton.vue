<template>
    <span class="likeLinkContainer">
        <a v-if="!liked" href="#" class="like likeAnchor" @click.prevent="toggle(likeUrl)">{{ likeLabel }}</a>
        <a v-else href="#" class="unlike likeAnchor" @click.prevent="toggle(unlikeUrl)">{{ unlikeLabel }}</a>
        <a v-if="count > 0" :href="userListUrl" data-bs-target="#globalModal">
            <span class="likeCount" :title="title">({{ count }})</span>
        </a>
    </span>
</template>

<script>
import { client, log } from '@humhub/vue';

export default {
    props: {
        likeUrl: { type: String, required: true },
        unlikeUrl: { type: String, required: true },
        userListUrl: { type: String, required: true },
        likeCount: { type: Number, default: 0 },
        currentUserLiked: { type: Boolean, default: false },
        title: { type: String, default: '' },
        // Server-rendered translations: avoids a cold-cache i18n XHR round trip
        // before the island can render its labels (FOUC). Both messages remain
        // PHP-extractable via Yii::t('LikeModule.base', ...) in likeLink.php.
        likeLabel: { type: String, required: true },
        unlikeLabel: { type: String, required: true },
    },
    data() {
        return {
            liked: this.currentUserLiked,
            count: this.likeCount,
            busy: false,
        };
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
