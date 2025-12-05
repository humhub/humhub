<script setup>
import {onBeforeMount, ref} from 'vue'
import axios from 'axios'
import i18next from 'i18next'


const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || ''
const csrfParam = document.querySelector('meta[name="csrf-param"]')?.content || '_csrf'

axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['X-CSRF-Token'] = csrfToken

const props = defineProps({
    language: { type: String, required: true },
    translations: { type: Array, required: true },
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

onBeforeMount(async () => {
    await i18next.init({
        lng: props.language,
        debug: true,
        resources: props.translations
    });
})

const toggleLike = async () => {
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
    <a
        href="#"
        @click="toggleLike"
    >
        {{ i18next.t(isLiked ? 'unlike' : 'like') }}
        <span
            v-if="count > 0"
            :title="title"
        >
            ({{ count }})
        </span>
    </a>
</template>

<style scoped>
</style>
