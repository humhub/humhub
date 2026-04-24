import { createApp } from 'vue'
import LikeButton from "./LikeButton.vue";

window.renderLikeButton = (id, props) => {
    createApp(LikeButton, props).mount(`#${id}`)
}
