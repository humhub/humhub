<template>
    <article class="c-module-card" :class="cardClasses" :aria-busy="updateState === 'running' ? 'true' : null">
        <div class="c-module-card__cover">
            <a v-if="module.marketplaceUrl" :href="module.marketplaceUrl" target="_blank" rel="noopener" aria-hidden="true" tabindex="-1">
                <img :src="module.imageUrl" class="c-module-card__image" width="80" height="80" alt="">
            </a>
            <img v-else :src="module.imageUrl" class="c-module-card__image" width="80" height="80" alt="">
            <div class="c-module-card__badges">
                <button
                    v-if="module.featured"
                    type="button"
                    class="c-module-card__badge c-module-card__badge--filter c-module-card__badge--featured c-module-card__badge--star"
                    :aria-label="labels.featuredFilter"
                    :title="labels.featured"
                    @click="$emit('filter-type', 'featured')"
                ><i class="ti ti-star-filled" aria-hidden="true"></i></button>
                <span v-if="showUpdatePill" class="c-module-card__badge c-module-card__badge--update" :title="labels.updateTo">
                    <i class="ti ti-arrow-up" aria-hidden="true"></i><span>{{ module.latestCompatibleVersion }}</span>
                </span>
                <button
                    v-else-if="filterTag"
                    type="button"
                    class="c-module-card__badge c-module-card__badge--filter"
                    :class="`c-module-card__badge--${module.badge}`"
                    :title="badgeTitle"
                    :aria-label="badgeFilterLabel"
                    @click="$emit('filter-type', filterTag)"
                >{{ badgeText }}</button>
                <span v-else-if="badgeText" class="c-module-card__badge" :class="`c-module-card__badge--${module.badge}`" :title="badgeTitle">{{ badgeText }}</span>
            </div>
        </div>
        <div class="c-module-card__header">
            <a v-if="module.marketplaceUrl" class="c-module-card__title" :href="module.marketplaceUrl" target="_blank" rel="noopener"><RichText :parts="titleParts" flag="mark" tag="mark" mark-class="c-module-card__mark" /></a>
            <span v-else class="c-module-card__title"><RichText :parts="titleParts" flag="mark" tag="mark" mark-class="c-module-card__mark" /></span>
            <p class="c-module-card__version">{{ versionText }}</p>
        </div>
        <div class="c-module-card__body">
            <p class="c-module-card__text"><RichText :parts="descriptionParts" flag="mark" tag="mark" mark-class="c-module-card__mark" /></p>
            <p v-if="error" class="c-module-card__message text-danger" role="alert">{{ error }}</p>
            <p v-else-if="updateState === 'failed'" class="c-module-card__message text-danger" role="alert">{{ labels.updateFailed }}</p>
        </div>
        <div class="c-module-card__footer">
            <button
                v-if="showUpdateAction"
                type="button"
                class="btn c-module-card__action"
                :class="updateButtonClass"
                :disabled="updateBusy"
                @click="$emit('update')"
            >
                <i v-if="updateState === 'running'" class="ti ti-refresh c-module-card__spin" aria-hidden="true"></i>
                <span>{{ updateLabel }}</span>
            </button>
            <button v-else-if="isInstalled" type="button" class="btn btn-secondary c-module-card__action" disabled>{{ labels.installed }}</button>
            <button v-else-if="module.availability === 'install'" type="button" class="btn btn-primary c-module-card__action" :disabled="locked" @click="$emit('install')">{{ labels.install }}</button>
            <button v-else-if="module.availability === 'buy'" type="button" class="btn btn-primary c-module-card__action" :disabled="locked" @click="$emit('buy')">{{ buyLabel }}</button>
            <a v-else-if="module.availability === 'professionalEdition'" :href="professionalEditionUrl" class="btn btn-primary c-module-card__action" target="_blank" rel="noopener">{{ labels.learnMore }}</a>
            <button v-else type="button" class="btn btn-secondary c-module-card__action" disabled>{{ labels.notCompatible }}</button>

            <a
                v-if="secondaryLink"
                :href="secondaryLink.href"
                class="btn c-icon-button c-icon-button--ghost c-module-card__secondary"
                target="_blank"
                rel="noopener"
                :aria-label="secondaryLink.label"
                :title="secondaryLink.label"
            ><i class="ti" :class="secondaryLink.icon" aria-hidden="true"></i></a>
        </div>
    </article>
</template>

<script>
import { i18n } from '@humhub/vue';
import RichText from './RichText.vue';
import { highlightParts } from './highlight.js';
import { badgeLabel, buyLabel } from './messages.js';

// The badges a type pill can filter by — the values of the `tag` filter.
const FILTER_TAGS = ['professional', 'official', 'community', 'partner'];

/**
 * One card of the marketplace grid (`.c-module-card`, `resources/scss/_marketplace.scss`) —
 * presentational: it shows the module (`MarketplaceModuleSerializer::module()`) and the state
 * of an action running on it, and emits what the user asks for (`install`, `buy`, `update`,
 * `filter-type` with the `tag` filter value of its type pill). `MarketplaceBrowser` performs
 * the actions.
 *
 * - Badges: a round featured star (filters by `tag=featured`) when `module.featured`, next to
 *   either `↑ <latestCompatibleVersion>` while an update is available, or else the type pill of
 *   `badge` (a button filtering by that type; Community/Partner carry the third-party hint as
 *   tooltip).
 * - Primary action: Update (accent), Install / Buy / Learn more (primary), or a disabled
 *   "Installed" / "Not compatible". The ghost icon button links the changelog (update
 *   available) or the marketplace page.
 * - `updateState`: `pending` (queued by "Update all"), `running`, `success` ("Updated!"),
 *   `removing` (the card fades out), `done` (it fades in with the updated module), `failed`.
 *   `locked` disables installing and buying while another action runs.
 * - `query`: the search the list was loaded with; its matches in the name and the description
 *   are marked (`<mark>`, accent- and case-insensitive, text nodes only).
 *
 * @since 1.20
 */
export default {
    name: 'ModuleCard',
    components: { RichText },
    props: {
        module: { type: Object, required: true },
        error: { type: String, default: null },
        updateState: { type: String, default: null },
        locked: { type: Boolean, default: false },
        professionalEditionUrl: { type: String, required: true },
        query: { type: String, default: '' },
    },
    emits: ['install', 'buy', 'update', 'filter-type'],
    computed: {
        titleParts() {
            return highlightParts(this.module.name, this.query);
        },
        descriptionParts() {
            return highlightParts(this.module.description, this.query);
        },
        isInstalled() {
            return this.module.installedVersion !== null && this.module.installedVersion !== undefined;
        },
        // Stays the update button through "Updated!" and the fade-out, until the browser
        // replaced the module with its updated record.
        showUpdateAction() {
            return (this.isInstalled && this.module.updateAvailable) || this.updateState === 'success' || this.updateState === 'removing';
        },
        showUpdatePill() {
            return this.showUpdateAction && Boolean(this.module.latestCompatibleVersion);
        },
        cardClasses() {
            return {
                'is-updating': ['running', 'success', 'removing'].includes(this.updateState),
                'is-removing': this.updateState === 'removing',
                'is-entering': this.updateState === 'done',
            };
        },
        updateBusy() {
            return ['pending', 'running', 'success', 'removing'].includes(this.updateState) || this.locked;
        },
        updateButtonClass() {
            return ['running', 'success', 'removing'].includes(this.updateState) ? 'btn-warning is-updating' : 'btn-accent';
        },
        updateLabel() {
            switch (this.updateState) {
                case 'pending':
                    return this.labels.queued;
                case 'running':
                    return this.labels.updating;
                case 'success':
                case 'removing':
                    return this.labels.updated;
                default:
                    return this.labels.update;
            }
        },
        filterTag() {
            return FILTER_TAGS.includes(this.module.badge) ? this.module.badge : null;
        },
        badgeText() {
            return this.module.badge === 'professional'
                ? i18n.t('MarketplaceModule.base', 'PRO')
                : badgeLabel(this.module.badge);
        },
        badgeTitle() {
            if (this.module.badge === 'community') {
                return `${this.labels.thirdParty} (${i18n.t('MarketplaceModule.base', 'Unverified Community')})`;
            }
            if (this.module.badge === 'partner') {
                return this.labels.thirdParty;
            }
            return this.filterTag ? this.badgeFilterLabel : null;
        },
        badgeFilterLabel() {
            return i18n.t('MarketplaceModule.base', 'Filter by {type}', { type: badgeLabel(this.module.badge) });
        },
        versionText() {
            if (this.isInstalled) {
                return this.module.installedVersion;
            }
            return this.module.latestCompatibleVersion || this.module.latestVersion || '';
        },
        buyLabel() {
            return buyLabel(this.module.price);
        },
        secondaryLink() {
            if (!this.module.marketplaceUrl) {
                return null;
            }
            return this.showUpdateAction
                ? { href: `${this.module.marketplaceUrl}/changelog`, icon: 'ti-file-text', label: this.labels.changelog }
                : { href: this.module.marketplaceUrl, icon: 'ti-info-circle', label: this.labels.information };
        },
        labels() {
            return {
                featured: i18n.t('MarketplaceModule.base', 'Featured'),
                featuredFilter: i18n.t('MarketplaceModule.base', 'Featured: filter by Featured'),
                install: i18n.t('MarketplaceModule.base', 'Install'),
                learnMore: i18n.t('MarketplaceModule.base', 'Learn more'),
                installed: i18n.t('MarketplaceModule.base', 'Installed'),
                notCompatible: i18n.t('MarketplaceModule.base', 'Not compatible'),
                update: i18n.t('MarketplaceModule.base', 'Update'),
                updating: i18n.t('MarketplaceModule.base', 'Updating'),
                updated: i18n.t('MarketplaceModule.base', 'Updated!'),
                queued: i18n.t('MarketplaceModule.base', 'Queued'),
                updateFailed: i18n.t('MarketplaceModule.base', 'Update failed'),
                updateTo: i18n.t('MarketplaceModule.base', 'Update to version {version} available', { version: this.module.latestCompatibleVersion }),
                changelog: i18n.t('MarketplaceModule.base', 'Module changelog'),
                information: i18n.t('MarketplaceModule.base', 'Module information'),
                thirdParty: i18n.t('MarketplaceModule.base', 'This Module was developed by a third-party.'),
            };
        },
    },
};
</script>
