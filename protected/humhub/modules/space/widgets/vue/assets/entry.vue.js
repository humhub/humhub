import {createApp} from 'vue'
import SpaceChooser from './SpaceChooser.vue'

window.renderSpaceChooser = (id, props) => {
    createApp(SpaceChooser, props).mount(`#${id}`)
}
