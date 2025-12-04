<template>
    <div class="like-button-wrapper">
        <button
            @click="toggleLike"
            :class="['like-btn', { liked: isLiked }]"
            :title="likeTitle"
            :disabled="!canLike || isLoading"
            :aria-pressed="isLiked"
            aria-label="Like button"
        >
            Like
            <span v-if="showCount" class="like-count">{{ likesCount }}</span>
        </button>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps({
    objectId: { type: String, required: true },
    contentModel: { type: String, required: true },
    canLike: { type: Boolean, default: true },
    initialLiked: { type: Boolean, default: false },
    initialLikes: { type: Array, default: () => [] },
})

const emit = defineEmits(['like-toggled'])

const isLiked = ref(props.initialLiked)
const likesCount = ref(props.initialLikes.length)
const isLoading = ref(false)

const showCount = computed(() => likesCount.value > 0)

const likeTitle = computed(() => {
    if (likesCount.value === 0) return 'No likes yet'
    if (likesCount.value === 1 && isLiked.value) return 'You like this'
    if (likesCount.value === 1) return '1 person likes this'
    return `${likesCount.value} people like this`
})

const toggleLike = async () => {
    if (!props.canLike || isLoading.value) return

    isLoading.value = true

    const action = isLiked.value ? 'unlike' : 'like'
    const url = `/like/like/${action}?contentModel=${props.contentModel}&contentId=${props.objectId}`

    try {
        const res = await fetch(url, { method: 'POST' })
        const data = await res.json()

        // Only update if backend confirms
        isLiked.value = !isLiked.value
        likesCount.value = data.likesCount ?? likesCount.value

        emit('like-toggled', {
            isLiked: isLiked.value,
            count: likesCount.value,
        })
    } catch (err) {
        console.error('Like action failed:', err)
    } finally {
        isLoading.value = false
    }
}

watch(() => [props.initialLiked, props.initialLikes], ([liked, likes]) => {
    isLiked.value = liked
    likesCount.value = likes?.length ?? 0
})

onMounted(() => {
})
</script>
