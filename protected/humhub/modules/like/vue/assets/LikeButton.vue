<script setup>
import {onBeforeMount, ref} from 'vue'
import axios from 'axios'
// import {translate} from 'humhub/translate'

// const like = await translate('LikeModule.base', 'Like');


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

// const translations = {
//     like: await translate('LikeModule.base', 'Like'),
//     unlike: await translate('LikeModule.base', 'Unlike'),
// }
//
// console.log(translations)

onBeforeMount(async () => {

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
    <a
        href="#"
        @click="toggleLike"
    >
        Like
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
