<script setup>
import http from 'humhub/http'
import {computed, onBeforeUnmount, ref, watch} from 'vue'

const props = defineProps({
    lazyLoad: {type: Boolean, default: true},
    lazySearchUrl: {type: String, required: true},
    remoteSearchUrl: {type: String, required: true},
    directoryUrl: {type: String, required: true},
    createSpaceUrl: {type: String, required: true},
    canCreateSpace: {type: Boolean, required: true},
    canAccessDirectory: {type: Boolean, required: true},
    currentSpaceImage: {type: String, default: ''},
    directoryIcon: {type: String, default: ''},
    noSpaceHtml: {type: String, required: true},
    spaces: {type: Array, default: () => []},
    text: {type: Object, required: true},
})

const dropdownRef = ref(null)
const dropdownToggleRef = ref(null)
const isOpen = ref(false)
const searchQuery = ref('')
const initialSpaces = ref(props.spaces)
const remoteSpaces = ref([])
const hasLoadedInitialSpaces = ref(!props.lazyLoad || props.spaces.length > 0)
const isLoadingInitialSpaces = ref(false)
const hasLoadedRemoteSpaces = ref(false)
const searchTimer = ref(null)
const requestId = ref(0)

const trimmedSearchQuery = computed(() => searchQuery.value.trim())
const showRemoteSearch = computed(() => trimmedSearchQuery.value.length >= 2)
const renderedSpaces = computed(() => showRemoteSearch.value ? remoteSpaces.value : initialSpaces.value)
const statusMessage = computed(() => {
    if (showRemoteSearch.value) {
        if (hasLoadedRemoteSpaces.value && remoteSpaces.value.length === 0) {
            return props.text.emptyResult
        }

        return ''
    }

    if (trimmedSearchQuery.value.length > 0) {
        return props.text.remoteAtLeastInput
    }

    if (hasLoadedInitialSpaces.value && initialSpaces.value.length === 0) {
        return props.text.emptyOwnResult
    }

    return ''
})

const clearSearchTimer = () => {
    if (searchTimer.value !== null) {
        window.clearTimeout(searchTimer.value)
        searchTimer.value = null
    }
}

const loadInitialSpaces = async () => {
    if (!props.lazyLoad || hasLoadedInitialSpaces.value || isLoadingInitialSpaces.value) {
        return
    }

    isLoadingInitialSpaces.value = true

    try {
        const {data} = await http.get(props.lazySearchUrl)
        initialSpaces.value = Array.isArray(data) ? data : []
        hasLoadedInitialSpaces.value = true
    } catch (error) {
        console.error('Error loading spaces:', error)
    } finally {
        isLoadingInitialSpaces.value = false
    }
}

const fetchRemoteSpaces = async (query) => {
    const currentRequestId = ++requestId.value
    hasLoadedRemoteSpaces.value = false

    try {
        const {data} = await http.get(props.remoteSearchUrl, {
            params: {
                keyword: query,
                target: 'chooser',
            },
        })

        if (currentRequestId !== requestId.value) {
            return
        }

        remoteSpaces.value = Array.isArray(data) ? data : []
        hasLoadedRemoteSpaces.value = true
    } catch (error) {
        if (currentRequestId !== requestId.value) {
            return
        }

        remoteSpaces.value = []
        hasLoadedRemoteSpaces.value = true
        console.error('Error fetching spaces:', error)
    }
}

const closeDropdown = () => {
    isOpen.value = false
    searchQuery.value = ''
    remoteSpaces.value = []
    hasLoadedRemoteSpaces.value = false
    clearSearchTimer()
}

const handleClickOutside = (event) => {
    if (dropdownRef.value?.contains(event.target) || dropdownToggleRef.value?.contains(event.target)) {
        return
    }

    closeDropdown()
}

const toggleDropdown = async (event) => {
    event.preventDefault()
    isOpen.value = !isOpen.value

    if (isOpen.value) {
        await loadInitialSpaces()
    } else {
        closeDropdown()
    }
}

watch(isOpen, (open) => {
    if (open) {
        document.addEventListener('mousedown', handleClickOutside)
    } else {
        document.removeEventListener('mousedown', handleClickOutside)
    }
})

watch(trimmedSearchQuery, (query) => {
    clearSearchTimer()

    if (query.length === 0) {
        remoteSpaces.value = []
        hasLoadedRemoteSpaces.value = false
        if (isOpen.value) {
            loadInitialSpaces()
        }
        return
    }

    if (query.length < 2) {
        remoteSpaces.value = []
        hasLoadedRemoteSpaces.value = false
        return
    }

    searchTimer.value = window.setTimeout(() => {
        fetchRemoteSpaces(query)
    }, 300)
})

onBeforeUnmount(() => {
    clearSearchTimer()
    document.removeEventListener('mousedown', handleClickOutside)
})
</script>

<template>
    <a
        ref="dropdownToggleRef"
        href="#"
        id="space-menu"
        class="nav-link dropdown-toggle"
        @click="toggleDropdown"
        :aria-expanded="isOpen"
    >
        <span v-if="currentSpaceImage" v-html="currentSpaceImage"></span>
        <span v-else v-html="noSpaceHtml"></span>
    </a>

    <ul
        v-if="isOpen"
        ref="dropdownRef"
        class="dropdown-menu show"
        id="space-menu-dropdown"
        style="display: block;"
    >
        <li>
            <form action="" class="dropdown-header dropdown-controls" @submit.prevent>
                <div :class="canAccessDirectory ? 'input-group' : ''">
                    <input
                        v-model="searchQuery"
                        type="text"
                        id="space-menu-search"
                        class="form-control"
                        autocomplete="off"
                        :placeholder="text.search"
                        :title="text.searchForSpaces"
                    >
                    <span v-if="canAccessDirectory" id="space-directory-link" class="input-group-text">
                        <a :href="directoryUrl" v-html="directoryIcon"></a>
                    </span>
                    <div
                        v-if="searchQuery"
                        class="search-reset"
                        id="space-search-reset"
                        @click="searchQuery = ''"
                    >
                        <i class="fa fa-times-circle"></i>
                    </div>
                </div>
            </form>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <div id="space-menu-spaces" class="notLoaded hh-list">
                <div
                    v-for="(space) in renderedSpaces"
                    :key="space.guid"
                    style="display: contents;"
                    v-html="space.output"
                ></div>
                <div v-if="statusMessage" class="text-body-secondary">
                    {{ statusMessage }}
                </div>
            </div>
        </li>

        <li v-if="canCreateSpace">
            <div class="dropdown-footer">
                <a
                    href="#"
                    class="btn btn-accent col-lg-12"
                    data-action-click="ui.modal.load"
                    :data-action-url="createSpaceUrl"
                >
                    {{ text.createSpace }}
                </a>
            </div>
        </li>
    </ul>
</template>
