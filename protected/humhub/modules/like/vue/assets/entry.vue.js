import { createApp } from 'vue'
import LikeButton from "./LikeButton.vue";

window.renderLikeButton = (id) => {
    createApp(LikeButton).mount(`#${id}`)
}
