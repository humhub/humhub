<template>
    <div class="alert alert-warning marketplace-alert" role="status">
        <i class="ti ti-info-circle marketplace-alert-icon" aria-hidden="true"></i>
        <span class="marketplace-alert-text">{{ message }}</span>
        <a v-if="updateUrl" :href="updateUrl" class="btn btn-sm btn-accent marketplace-alert-action">{{ learnMore }}</a>
    </div>
</template>

<script>
import { i18n } from '@humhub/vue';

/**
 * The slim banner between the marketplace page header and the grid saying that a newer HumHub
 * version exists. `MarketplaceBrowser` loads the version info and renders this only when an
 * update is available — nothing when the installation is up to date or humhub.com cannot be
 * reached.
 *
 * @since 1.20
 */
export default {
    name: 'CoreVersionInfo',
    props: {
        latest: { type: String, required: true },
        updateUrl: { type: String, default: null },
    },
    computed: {
        message() {
            return i18n.t('MarketplaceModule.base', 'A new update is available (HumHub %version%)!').replace('%version%', this.latest);
        },
        learnMore() {
            return i18n.t('MarketplaceModule.base', 'Learn more');
        },
    },
};
</script>
