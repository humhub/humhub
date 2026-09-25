<template>
    <UiModal :show="true" dialog-class="c-mp-dialog c-mp-settings" :title="labels.title" @update:show="onShow">
        <div class="c-mp-settings__tabs" role="tablist" :aria-label="labels.title">
            <button
                v-for="item in tabs"
                :id="`${uid}-tab-${item.key}`"
                :key="item.key"
                :ref="`tab-${item.key}`"
                type="button"
                role="tab"
                class="c-mp-settings__tab"
                :class="{ 'is-active': tab === item.key }"
                :aria-selected="tab === item.key ? 'true' : 'false'"
                :aria-controls="`${uid}-panel-${item.key}`"
                :tabindex="tab === item.key ? null : -1"
                @click="tab = item.key"
                @keydown="onTabKeydown"
            ><i class="ti" :class="item.icon" aria-hidden="true"></i>{{ item.label }}</button>
        </div>

        <section
            v-show="tab === 'licenses'"
            :id="`${uid}-panel-licenses`"
            class="c-mp-settings__panel"
            role="tabpanel"
            :aria-labelledby="`${uid}-tab-licenses`"
        >
            <div class="c-mp-settings__key">
                <div class="c-licence-field" :class="{ 'is-valid': added }">
                    <input
                        ref="input"
                        v-model="licenceKey"
                        type="text"
                        class="form-control c-licence-field__input"
                        :class="{ 'is-invalid': keyError, 'is-valid': added }"
                        :placeholder="labels.placeholder"
                        :aria-label="labels.licenceKey"
                        autocomplete="off"
                        spellcheck="false"
                        autocapitalize="characters"
                        :disabled="registering || loading"
                        :aria-invalid="keyError ? 'true' : null"
                        :aria-describedby="keyError ? `${uid}-key-error` : null"
                        @input="onKeyInput"
                        @keydown.enter.prevent="register"
                    >
                </div>
                <button
                    type="button"
                    class="btn btn-primary c-icon-button c-mp-settings__add"
                    :aria-label="labels.addLicenceKey"
                    :title="labels.addLicenceKey"
                    :disabled="registering || loading || licenceKey.trim() === ''"
                    @click="register"
                ><i class="ti" :class="registering ? 'ti-loader c-mp-spin' : (added ? 'ti-check' : 'ti-plus')" aria-hidden="true"></i></button>
            </div>
            <div v-if="keyError" :id="`${uid}-key-error`" class="invalid-feedback d-block" role="alert">{{ keyError }}</div>

            <div v-if="loading" class="c-mp-settings__loading">
                <span class="spinner-border spinner-border-sm" role="status" :aria-label="labels.loading"></span>
            </div>
            <p v-else-if="!licences.length" class="c-mp-settings__empty">{{ labels.none }}</p>
            <ul v-else class="c-licence-list">
                <li v-for="module in licences" :key="module.id" class="c-licence-list__item" :class="{ 'is-entering': module.id === enteringId }">
                    <div class="c-licence-list__clip">
                        <div class="c-licence-list__row">
                            <img :src="module.imageUrl" class="c-licence-list__image" width="40" height="40" alt="">
                            <div class="c-licence-list__text">
                                <p class="c-licence-list__name">{{ module.name }}</p>
                                <p class="c-licence-list__key">{{ licenceText(module) }}</p>
                            </div>
                            <button v-if="isInstalled(module)" type="button" class="btn btn-secondary c-licence-list__action" disabled>{{ labels.installed }}</button>
                            <button
                                v-else
                                type="button"
                                class="btn btn-primary c-licence-list__action"
                                :aria-label="installLabel(module)"
                                :disabled="installLocked"
                                @click="$emit('install', module)"
                            >{{ labels.install }}</button>
                            <a
                                v-if="module.marketplaceUrl"
                                :href="module.marketplaceUrl"
                                class="btn c-icon-button c-icon-button--ghost"
                                target="_blank"
                                rel="noopener"
                                :aria-label="labels.information"
                                :title="labels.information"
                            ><i class="ti ti-info-circle" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </li>
            </ul>
            <p v-if="installationId" class="c-mp-settings__installation">{{ labels.installationId }}</p>
        </section>

        <section
            v-show="tab === 'settings'"
            :id="`${uid}-panel-settings`"
            class="c-mp-settings__panel c-mp-settings__options"
            role="tabpanel"
            :aria-labelledby="`${uid}-tab-settings`"
        >
            <div class="c-setting-row">
                <div class="form-check">
                    <input
                        :id="`${uid}-beta`"
                        class="form-check-input"
                        type="checkbox"
                        data-setting="includeBetaUpdates"
                        :checked="beta"
                        :disabled="saving !== null"
                        :aria-describedby="`${uid}-beta-hint`"
                        @change="toggleBeta($event.target.checked)"
                    >
                    <label class="form-check-label c-setting-row__title" :for="`${uid}-beta`">{{ labels.beta }}</label>
                    <div :id="`${uid}-beta-hint`" class="form-text c-setting-row__hint">{{ labels.betaHint }}</div>
                </div>
            </div>
            <div class="c-setting-row" :class="{ 'is-expanded': acknowledging }">
                <div class="form-check">
                    <input
                        :id="`${uid}-community`"
                        class="form-check-input"
                        type="checkbox"
                        data-setting="includeCommunityModules"
                        :checked="community || acknowledging"
                        :disabled="saving !== null"
                        :aria-describedby="`${uid}-community-hint`"
                        @change="toggleCommunity($event.target.checked)"
                    >
                    <label class="form-check-label c-setting-row__title" :for="`${uid}-community`">{{ labels.community }}</label>
                    <div :id="`${uid}-community-hint`" class="form-text c-setting-row__hint">{{ labels.communityHint }}</div>
                </div>
                <div class="c-setting-row__more">
                    <div class="c-setting-row__more-inner">
                        <div v-if="acknowledging" class="c-mp-notice c-mp-notice--warning">
                            <i class="ti ti-alert-triangle c-mp-notice__icon" aria-hidden="true"></i>
                            <div class="c-mp-notice__text">
                                <div v-html="labels.warning"></div>
                                <div class="form-check">
                                    <input :id="`${uid}-acknowledged`" ref="acknowledged" v-model="acknowledged" class="form-check-input" type="checkbox">
                                    <label class="form-check-label" :for="`${uid}-acknowledged`">{{ labels.acknowledge }}</label>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-primary btn-sm c-mp-dialog__busy" :disabled="!acknowledged || saving !== null" @click="confirmCommunity">
                                        <i v-if="saving === 'includeCommunityModules'" class="ti ti-loader c-mp-spin" aria-hidden="true"></i>{{ labels.confirm }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <p v-if="settingsError" class="c-mp-dialog__error" role="alert">{{ settingsError }}</p>
            <a v-if="moduleAdministrationUrl" :href="moduleAdministrationUrl" class="c-mp-settings__admin">
                <i class="ti ti-apps" aria-hidden="true"></i>{{ labels.moduleAdministration }}
            </a>
        </section>

        <template #footer>
            <button type="button" class="btn btn-secondary" @click="$emit('close')">{{ labels.close }}</button>
        </template>
    </UiModal>
</template>

<script>
import { i18n, log, status } from '@humhub/vue';
import { errorMessage, fetchModules, registerLicenceKey, saveSettings } from './marketplaceApi.js';
import { communityAcknowledge, communityWarning } from './messages.js';

// An added key shows as accepted (check icon, green field) for this long.
export const ADDED_MS = 1600;

const TABS = ['licenses', 'settings'];

let uidSeq = 0;

/**
 * The marketplace settings, in two tabs:
 *
 * - Licenses: registers a licence key (`POST marketplace/licence-key`; a rejected key stays in
 *   the field with the API's message) and lists the purchased modules (`tag=purchased`) with
 *   their key — a newly licensed module enters at the top. A row's Install emits `install` (the
 *   browser closes this dialog and opens the install dialog); the Installation ID below.
 * - Settings: "Beta Modules" and "Unverified Modules" checkboxes, each saved at once
 *   (`PATCH marketplace/settings`) and reverted on an error. Checking unverified modules
 *   ON first asks for the risk acknowledgement inline; only its confirmation saves.
 *
 * Emits `registered` (a key was added), `settings-changed` (the stored settings), `install`
 * (a licensed module) and `close`.
 *
 * @since 1.20
 */
export default {
    name: 'SettingsDialog',
    props: {
        settings: { type: Object, required: true },
        installationId: { type: String, default: '' },
        moduleAdministrationUrl: { type: String, default: null },
        initialTab: {
            type: String,
            default: 'licenses',
            validator: (value) => TABS.includes(value),
        },
        // Another action runs: a licence row cannot start an installation.
        installLocked: { type: Boolean, default: false },
    },
    emits: ['registered', 'settings-changed', 'install', 'close'],
    data() {
        return {
            uid: `marketplace-settings-${++uidSeq}`,
            tab: this.initialTab,
            licenceKey: '',
            keyError: null,
            registering: false,
            added: false,
            loading: true,
            licences: [],
            enteringId: null,
            beta: this.settings.includeBetaUpdates === true,
            community: this.settings.includeCommunityModules === true,
            acknowledging: false,
            acknowledged: false,
            // The setting being saved (`includeBetaUpdates` / `includeCommunityModules`), or null.
            saving: null,
            settingsError: null,
        };
    },
    computed: {
        tabs() {
            return [
                { key: 'licenses', icon: 'ti-key', label: this.labels.licenses },
                { key: 'settings', icon: 'ti-settings', label: this.labels.settings },
            ];
        },
        labels() {
            return {
                title: i18n.t('MarketplaceModule.base', 'Marketplace Settings'),
                licenses: i18n.t('MarketplaceModule.base', 'Licenses'),
                settings: i18n.t('base', 'Settings'),
                licenceKey: i18n.t('MarketplaceModule.base', 'License Key'),
                placeholder: 'XXXX-XXXX-XXXX-XXXX',
                addLicenceKey: i18n.t('MarketplaceModule.base', 'Add License Key'),
                none: i18n.t('MarketplaceModule.base', 'No purchased modules found!'),
                install: i18n.t('MarketplaceModule.base', 'Install'),
                installed: i18n.t('MarketplaceModule.base', 'Installed'),
                information: i18n.t('MarketplaceModule.base', 'Module information'),
                installationId: i18n.t('MarketplaceModule.base', 'Installation ID: {installationId}', { installationId: this.installationId }),
                beta: i18n.t('MarketplaceModule.base', 'Beta Modules'),
                betaHint: i18n.t('MarketplaceModule.base', 'Allow modules in Beta to be installed.'),
                community: i18n.t('MarketplaceModule.base', 'Unverified Modules'),
                communityHint: i18n.t('MarketplaceModule.base', 'Show modules contributed by the community that are not tested or maintained by the HumHub team.'),
                warning: communityWarning(),
                acknowledge: communityAcknowledge(),
                confirm: i18n.t('base', 'Confirm'),
                moduleAdministration: i18n.t('MarketplaceModule.base', 'Module Administration'),
                loading: i18n.t('base', 'Loading...'),
                close: i18n.t('base', 'Close'),
            };
        },
    },
    created() {
        this.loadSeq = 0;
        this.knownIds = null;
        this.load();
    },
    beforeUnmount() {
        clearTimeout(this.addedTimer);
    },
    methods: {
        onShow(show) {
            if (!show) {
                this.$emit('close');
            }
        },
        onTabKeydown(event) {
            const index = TABS.indexOf(this.tab);
            const next = {
                ArrowRight: (index + 1) % TABS.length,
                ArrowLeft: (index - 1 + TABS.length) % TABS.length,
                Home: 0,
                End: TABS.length - 1,
            }[event.key];
            if (next === undefined) {
                return;
            }
            event.preventDefault();
            this.tab = TABS[next];
            this.$nextTick(() => {
                const [button] = [].concat(this.$refs[`tab-${this.tab}`] || []);
                if (button) {
                    button.focus();
                }
            });
        },
        isInstalled(module) {
            return module.installedVersion !== null && module.installedVersion !== undefined;
        },
        licenceText(module) {
            return i18n.t('MarketplaceModule.base', 'License: {licenceKey}', { licenceKey: module.licenceKey || '' });
        },
        installLabel(module) {
            return i18n.t('MarketplaceModule.base', 'Install {moduleName}', { moduleName: module.name });
        },
        // Only the latest load is applied, however the requests answer. The ids of a completed
        // load tell which licence a registered key added; after a failed one nothing is known.
        load() {
            const seq = ++this.loadSeq;
            this.loading = true;
            return fetchModules({ tag: 'purchased' }).then((modules) => {
                if (seq === this.loadSeq) {
                    this.licences = modules;
                    this.knownIds = new Set(modules.map((module) => module.id));
                }
            }, (response) => {
                log.error(response);
                if (seq === this.loadSeq) {
                    this.licences = [];
                    this.knownIds = null;
                }
            }).finally(() => {
                if (seq === this.loadSeq) {
                    this.loading = false;
                }
            });
        },
        onKeyInput() {
            this.keyError = null;
            this.added = false;
        },
        async register() {
            const key = this.licenceKey.trim();
            if (key === '' || this.registering || this.loading) {
                return;
            }
            this.registering = true;
            this.keyError = null;
            const known = this.knownIds;
            try {
                await registerLicenceKey(key);
            } catch (response) {
                this.registering = false;
                this.keyError = errorMessage(response, i18n.t('MarketplaceModule.base', 'Invalid module license key!'));
                log.error(response);
                return;
            }
            this.$emit('registered');
            await this.load();
            this.registering = false;
            this.licenceKey = '';

            // The module this key licensed: the purchased one not listed before. It goes first.
            const added = known ? this.licences.find((module) => !known.has(module.id)) : undefined;
            if (added) {
                this.licences = [added, ...this.licences.filter((module) => module !== added)];
                this.enteringId = added.id;
            }
            status('success', added
                ? i18n.t('MarketplaceModule.base', 'License added for {moduleName}.', { moduleName: added.name })
                : i18n.t('MarketplaceModule.base', 'License added.'));

            this.added = true;
            clearTimeout(this.addedTimer);
            this.addedTimer = setTimeout(() => {
                this.added = false;
            }, ADDED_MS);
            this.$nextTick(() => {
                if (this.$refs.input) {
                    this.$refs.input.focus();
                }
            });
        },
        toggleBeta(checked) {
            this.save('includeBetaUpdates', checked);
        },
        toggleCommunity(checked) {
            this.settingsError = null;
            if (!checked) {
                // Switched off before confirming: nothing was stored, nothing to save.
                if (this.acknowledging) {
                    this.acknowledging = false;
                    this.acknowledged = false;
                    return;
                }
                this.save('includeCommunityModules', false);
                return;
            }
            this.acknowledging = true;
            this.acknowledged = false;
            this.$nextTick(() => {
                if (this.$refs.acknowledged) {
                    this.$refs.acknowledged.focus();
                }
            });
        },
        confirmCommunity() {
            if (this.acknowledged) {
                this.save('includeCommunityModules', true);
            }
        },
        save(key, value) {
            if (this.saving !== null) {
                return;
            }
            const local = key === 'includeBetaUpdates' ? 'beta' : 'community';
            const before = this[local];
            this[local] = value;
            this.saving = key;
            this.settingsError = null;
            saveSettings({ [key]: value ? 1 : 0 }).then((settings) => {
                this.saving = null;
                this.beta = settings.includeBetaUpdates;
                this.community = settings.includeCommunityModules;
                // Another setting saved meanwhile leaves an open acknowledgement as it is.
                if (key === 'includeCommunityModules') {
                    this.acknowledging = false;
                    this.acknowledged = false;
                }
                this.$emit('settings-changed', settings);
            }).catch((response) => {
                this.saving = null;
                this[local] = before;
                this.settingsError = errorMessage(response, i18n.t('MarketplaceModule.base', 'The settings could not be saved.'));
                log.error(response);
            });
        },
    },
};
</script>
