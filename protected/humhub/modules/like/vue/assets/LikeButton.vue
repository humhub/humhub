<script setup>
import {onMounted, ref} from 'vue'
import axios from 'axios'
import {loadTranslations, translate} from 'humhub/translate'

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || ''
const csrfParam = document.querySelector('meta[name="csrf-param"]')?.content || '_csrf'

axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['X-CSRF-Token'] = csrfToken

const props = defineProps({
    isGuest: { type: Boolean, required: true },
    canLike: { type: Boolean, required: true },
    currentUserLiked: { type: Boolean, required: true },
    likeCount: { type: Number, required: true },
    title: { type: String, required: true },
    urls: {
        type: Object,
        required: true,
        validator: (v) => v && 'loginUrl' in v && 'likeUrl' in v && 'unlikeUrl' in v
    }
})

const isLiked = ref(props.currentUserLiked)
const count = ref(props.likeCount)
const isLoading = ref(false)
const isReady = ref(false)

onMounted(async () => {
    await loadTranslations('LikeModule.base')
    isReady.value = true
})

const toggleLike = async (event) => {
    event.preventDefault();

    if (isLoading.value || !props.canLike) return

    if (props.isGuest) {
        window.location.href = props.urls.loginUrl
        return
    }

    isLoading.value = true

    const url = isLiked.value ? props.urls.unlikeUrl : props.urls.likeUrl

    try {
        const response = await axios.post(url, {
            [csrfParam]: csrfToken
        })

        isLiked.value = response.data.currentUserLiked ?? !isLiked.value
        count.value = response.data.likeCounter ?? count.value + (isLiked.value ? 1 : -1)
    } catch (error) {
        console.error('Like/Unike failed:', error)
    } finally {
        isLoading.value = false
    }
}
</script>

<template>
    <template v-if="isReady">
        <a
            href="#"
            @click="toggleLike"
        >
            {{ translate('LikeModule.base', isLiked ? 'Unlike' :'Like') }}
            <span
                v-if="count > 0"
                :title="title"
            >
            ({{ count }})
        </span>
        <div>
            <strong>Yii::t examples</strong>
            <div class="code-example">
                <hr/>
                <code>translate('LikeModule.base', 'and {count} more like this.', {count: 5}) </code>
                <div>{{ translate('LikeModule.base', 'and {count} more like this.', {count: 5}) }}</div>
                <hr/>
            </div>
            <div class="code-example">
                <hr/>
                <code>translate('LikeModule.base', 'There {n,plural,=0{are no cats} =1{is one cat} other{are # cats}}!', {n: 0}) </code>
                <div>{{ translate('LikeModule.base', 'There {n,plural,=0{are no cats} =1{is one cat} other{are # cats\}\}!', {n: 0}) }}</div>
            </div>
            <div class="code-example">
                <hr/>
                <code>translate('LikeModule.base', 'There {n,plural,=0{are no cats} =1{is one cat} other{are # cats}}!', {n: 1}) </code>
                <div>{{ translate('LikeModule.base', 'There {n,plural,=0{are no cats} =1{is one cat} other{are # cats\}\}!', {n: 1}) }}</div>
            </div>
            <div class="code-example">
                <hr/>
                <code>translate('LikeModule.base', 'There {n,plural,=0{are no cats} =1{is one cat} other{are # cats}}!', {n: 42}) </code>
                <div>{{ translate('LikeModule.base', 'There {n,plural,=0{are no cats} =1{is one cat} other{are # cats\}\}!', {n: 42}) }}</div>
            </div>
        </div>
        </a>
    </template>
</template>

<style scoped>
</style>
