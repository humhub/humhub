<template>
    <div class="c-marketplace">
        <CardDirectory
            ref="directory"
            :url="listUrl"
            :title="labels.title"
            :filters="filters"
            :page-size="24"
            :meta-keys="['updateCount']"
            id-prefix="marketplace-filter"
            @loaded="onLoaded"
        >
            <template #actions>
                <button
                    type="button"
                    class="btn btn-secondary c-icon-button c-marketplace__settings"
                    :aria-label="labels.settings"
                    :title="labels.settings"
                    @click="openSettings()"
                ><i class="ti ti-settings" aria-hidden="true"></i></button>
                <span class="c-marketplace__update-all-slot" :class="{ 'is-collapsed': !showUpdateAll }" :aria-hidden="showUpdateAll ? null : 'true'">
                    <button
                        type="button"
                        class="btn c-icon-button c-marketplace__update-all"
                        :class="updatingAll ? 'btn-warning is-running' : 'btn-accent'"
                        :aria-label="updatingAll ? labels.cancelAllLabel : labels.updateAllLabel"
                        :title="updatingAll ? labels.cancelAll : labels.updateAll"
                        :tabindex="showUpdateAll ? null : -1"
                        :disabled="updateAllDisabled"
                        @click="updatingAll ? stopUpdateAll() : startUpdateAll()"
                    ><i class="ti ti-refresh c-marketplace__spin" aria-hidden="true"></i></button>
                </span>
            </template>

            <template v-if="coreUpdate" #notice>
                <CoreVersionInfo :latest="coreUpdate.latest" :update-url="coreUpdate.updateUrl" />
            </template>

            <template #card="{ item }">
                <ModuleCard
                    :module="item"
                    :error="stateOf(item.id).error"
                    :update-state="stateOf(item.id).update"
                    :locked="cardsLocked"
                    :query="query"
                    :professional-edition-url="urls.professionalEdition"
                    @install="install(item)"
                    @buy="buy(item)"
                    @update="updateOne(item)"
                    @filter-type="filterByType"
                />
            </template>
        </CardDirectory>

        <div class="visually-hidden" role="status" aria-live="polite">{{ announcement }}</div>

        <InstallDialog
            v-if="dialog && dialog.type === 'install'"
            :key="dialog.key"
            :module="dialog.module"
            :initial-step="dialog.step"
            @registered="dialog.registered = true"
            @installed="onInstalled"
            @activated="replaceModule"
            @close="closeInstall"
        />
        <SettingsDialog
            v-else-if="dialog && dialog.type === 'settings'"
            :key="dialog.key"
            :settings="settingsState"
            :installation-id="installationId"
            :module-administration-url="urls.moduleAdministration"
            :initial-tab="dialog.tab"
            :install-locked="installBlocked()"
            @registered="reload"
            @settings-changed="onSettingsChanged"
            @install="onLicenceInstall"
            @close="closeDialog"
        />
    </div>
</template>

<script>
import { apiUrl, i18n, log, status } from '@humhub/vue';
import CoreVersionInfo from './components/CoreVersionInfo.vue';
import InstallDialog from './components/InstallDialog.vue';
import ModuleCard from './components/ModuleCard.vue';
import SettingsDialog from './components/SettingsDialog.vue';
import { errorMessage, fetchCoreVersion, fetchModules, updateModule } from './components/marketplaceApi.js';
import { createUpdateQueue } from './components/updateQueue.js';

const IDLE = Object.freeze({ error: null, update: null });

// After a successful update the card says "Updated!" for this long, then plays its remove
// animation (`.c-module-card.is-removing`, same duration in `_marketplace.scss`) before it
// shows the updated module.
export const UPDATED_MS = 1200;
export const REMOVE_MS = 360;

// Update states during which an update still occupies its card (see `ModuleCard`).
const UPDATE_BUSY = ['running', 'success', 'removing'];

const reducedMotion = () => typeof window.matchMedia === 'function'
    && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Focus went nowhere: on the body, or on an element that was removed.
const focusLost = () => !document.activeElement || document.activeElement === document.body || !document.activeElement.isConnected;

const wait = (ms) => new Promise((resolve) => {
    setTimeout(resolve, ms);
});

/**
 * The marketplace page (`humhub\modules\marketplace\widgets\MarketplaceBrowser`): the core
 * `CardDirectory` fed by `GET /api/v2/marketplace/module`, with the marketplace's toolbar
 * actions (the settings button opening `SettingsDialog`, and "Update all"), the core version
 * notice, the cards (the search marked in them), and the dialogs. A card's Buy and Install open
 * `InstallDialog` (on its `buy` / `confirm` step), which runs key registration, installation and
 * activation. The browser owns the updates and the per-card state of those, and allows one
 * action at a time: an open install dialog, a running update or "Update all" block every other
 * install and update. One dialog is open at a time.
 *
 * The result of every finished update is announced in one visually hidden live region. A card
 * replaced after its install or update gets the focus back (its primary action, else its title
 * link) when the focus was on it — or was lost with the element it was on.
 *
 * @since 1.20
 */
export default {
    name: 'MarketplaceBrowser',
    i18nCategories: ['MarketplaceModule.base', 'base'],
    components: { CoreVersionInfo, InstallDialog, ModuleCard, SettingsDialog },
    props: {
        filters: { type: Array, required: true },
        settings: { type: Object, required: true },
        urls: { type: Object, required: true },
        installationId: { type: String, default: '' },
    },
    data() {
        return {
            settingsState: { ...this.settings },
            updateCount: 0,
            cardStates: {},
            dialog: null,
            updateQueue: null,
            // Set synchronously when "Update all" starts (before its candidates are fetched),
            // so a second click cannot start a second queue.
            updatingAll: false,
            // "Cancel all" was pressed: the running update finishes, nothing further starts.
            stopRequested: false,
            // The search the shown list was loaded with, marked in the cards.
            query: '',
            // Every opened dialog gets a fresh key: two dialogs of the same type in a row are
            // two component instances, never one reusing the other's state.
            dialogSeq: 0,
            // `{ latest, updateUrl }` once humhub.com reported a newer HumHub version.
            coreUpdate: null,
            // The live region's text: the result of the last finished update.
            announcement: '',
        };
    },
    computed: {
        anyUpdateRunning() {
            return Object.values(this.cardStates).some((state) => UPDATE_BUSY.includes(state.update));
        },
        // An install dialog is open: its installation may run any moment (one action at a time).
        installOpen() {
            return this.dialog !== null && this.dialog.type === 'install';
        },
        updateAllBlocked() {
            return this.updatingAll || this.anyUpdateRunning || this.installOpen;
        },
        // While "Update all" runs the button is its "Cancel all" control — usable once the
        // queue exists (its candidates are fetched) and until cancelling was requested.
        updateAllDisabled() {
            return this.updatingAll ? (this.updateQueue === null || this.stopRequested) : this.updateAllBlocked;
        },
        showUpdateAll() {
            return this.updatingAll || this.updateCount > 0;
        },
        cardsLocked() {
            return this.updateQueue !== null || this.updatingAll || this.installOpen || this.anyUpdateRunning;
        },
        listUrl() {
            return apiUrl('marketplace/module');
        },
        labels() {
            return {
                title: i18n.t('MarketplaceModule.base', 'Marketplace'),
                settings: i18n.t('MarketplaceModule.base', 'Marketplace Settings'),
                updateAll: i18n.t('MarketplaceModule.base', 'Update All'),
                updateAllLabel: i18n.t('MarketplaceModule.base', 'Update all modules'),
                cancelAll: i18n.t('MarketplaceModule.base', 'Cancel All'),
                cancelAllLabel: i18n.t('MarketplaceModule.base', 'Cancel all module updates'),
            };
        },
    },
    created() {
        fetchCoreVersion().then((info) => {
            if (info && info.updateAvailable === true && info.latest) {
                this.coreUpdate = { latest: String(info.latest), updateUrl: info.updateUrl || null };
            }
        }).catch((response) => {
            log.error(response);
        });
    },
    methods: {
        stateOf(id) {
            return this.cardStates[id] || IDLE;
        },
        setState(id, patch) {
            this.cardStates[id] = { ...this.stateOf(id), ...patch };
        },
        replaceModule(module, keepFocus = this.hasFocusIn(module.id)) {
            this.$refs.directory?.replaceItem(module.id, module);
            if (keepFocus) {
                this.focusCard(module.id);
            }
        },
        cardCell(id) {
            return this.$el ? [...this.$el.querySelectorAll('[data-id]')].find((cell) => cell.dataset.id === String(id)) || null : null;
        },
        hasFocusIn(id) {
            const cell = this.cardCell(id);
            return Boolean(cell && cell.contains(document.activeElement));
        },
        // Once rendered: the card's primary action, or — disabled ("Installed") — its title link
        // or its secondary link.
        focusCard(id) {
            this.$nextTick(() => {
                const cell = this.cardCell(id);
                if (!cell) {
                    return;
                }
                const target = ['.c-module-card__action', 'a.c-module-card__title', '.c-module-card__secondary']
                    .map((selector) => cell.querySelector(selector))
                    .find((element) => element && !element.disabled);
                if (target) {
                    target.focus();
                }
            });
        },
        announce(message) {
            this.announcement = message;
        },
        reload() {
            return this.$refs.directory?.reload();
        },
        onLoaded({ meta, values }) {
            this.updateCount = meta.updateCount || 0;
            this.query = values && typeof values.q === 'string' ? values.q.trim() : '';
        },
        filterByType(tag) {
            this.$refs.directory.setFilter('tag', tag);
        },
        openDialog(type, payload = {}) {
            this.dialog = { type, ...payload, key: ++this.dialogSeq };
        },
        closeDialog() {
            this.dialog = null;
        },
        openSettings(tab = 'licenses') {
            if (!this.installOpen) {
                this.openDialog('settings', { tab });
            }
        },
        installBlocked() {
            return this.updatingAll || this.updateQueue !== null || this.installOpen || this.anyUpdateRunning;
        },
        openInstall(module, step) {
            if (this.installBlocked()) {
                return false;
            }
            this.openDialog('install', { module, step, registered: false, installed: false });
            return true;
        },
        install(module) {
            return this.openInstall(module, 'confirm');
        },
        buy(module) {
            return this.openInstall(module, 'buy');
        },
        onInstalled(module) {
            this.dialog.installed = true;
            this.replaceModule(module);
        },
        // A key registered in the dialog without the installation succeeding: the list behind
        // it (Buy → Install) is out of date.
        // An installed card's Install button went away with the installation: the focus the
        // dialog hands back goes to the new card instead.
        closeInstall() {
            const { module, registered, installed } = this.dialog || {};
            const stale = registered && !installed;
            this.closeDialog();
            if (stale) {
                this.reload();
            }
            if (installed) {
                this.$nextTick(() => {
                    if (focusLost()) {
                        this.focusCard(module.id);
                    }
                });
            }
        },
        async update(module) {
            const hadFocus = this.hasFocusIn(module.id);
            this.setState(module.id, { update: 'running', error: null });
            let updated;
            try {
                updated = await updateModule(module.id);
            } catch (response) {
                const error = errorMessage(response, i18n.t('MarketplaceModule.base', 'Update failed'));
                this.setState(module.id, { update: 'failed', error });
                this.announce(`${module.name}: ${error}`);
                log.error(response);
                throw response;
            }
            await this.presentUpdated(module.id, updated, hadFocus);
            this.announce(this.updatedMessage(updated));
            return updated;
        },
        updatedMessage(updated) {
            return i18n.t('MarketplaceModule.base', 'Module "{moduleName}" has been updated to version {newVersion} successfully.', {
                moduleName: updated.name,
                newVersion: updated.installedVersion,
            });
        },
        // "Updated!" on the card, its remove animation, then the updated module fading in —
        // at once under `prefers-reduced-motion`. `hadFocus`: the card had the focus when the
        // update started (the disabled Update button may have lost it since).
        async presentUpdated(id, updated, hadFocus = false) {
            if (!reducedMotion()) {
                this.setState(id, { update: 'success' });
                await wait(UPDATED_MS);
                this.setState(id, { update: 'removing' });
                await wait(REMOVE_MS);
            }
            this.replaceModule(updated, this.hasFocusIn(id) || (hadFocus && focusLost()));
            this.setState(id, { update: 'done' });
        },
        async updateOne(module) {
            if (this.installBlocked()) {
                return;
            }
            try {
                const updated = await this.update(module);
                this.updateCount = Math.max(0, this.updateCount - 1);
                status('success', this.updatedMessage(updated));
            } catch (response) {
                status('error', this.stateOf(module.id).error);
            }
        },
        async startUpdateAll() {
            if (this.updateAllBlocked) {
                return;
            }
            this.updatingAll = true;
            this.stopRequested = false;
            try {
                let candidates;
                try {
                    candidates = await fetchModules({ status: 'update' });
                } catch (response) {
                    status('error', errorMessage(response, i18n.t('MarketplaceModule.base', 'Update failed')));
                    return;
                }

                const byId = Object.fromEntries(candidates.map((module) => [module.id, module]));
                candidates.forEach((module) => this.setState(module.id, { update: 'pending', error: null }));

                this.updateQueue = createUpdateQueue(candidates.map((module) => module.id), (id) => this.update(byId[id]));
                const { stopped, failed } = await this.updateQueue.run();
                this.updateQueue = null;

                candidates.forEach((module) => {
                    if (this.stateOf(module.id).update === 'pending') {
                        this.setState(module.id, { update: null });
                    }
                });

                // Stopped on request: the cards show what was done, no summary.
                if (!stopped) {
                    if (failed.length) {
                        status('warning', i18n.t('MarketplaceModule.base', 'Some modules could not be updated.'));
                    } else {
                        status('success', i18n.t('MarketplaceModule.base', 'Update successful'));
                    }
                }
                await this.reload();
            } finally {
                this.updateQueue = null;
                this.updatingAll = false;
                this.stopRequested = false;
            }
        },
        stopUpdateAll() {
            if (this.updateQueue) {
                this.stopRequested = true;
                this.updateQueue.stop();
            }
        },
        onSettingsChanged(settings) {
            this.settingsState = settings;
            this.reload();
            this.$refs.directory.reloadFilterOptions();
        },
        onLicenceInstall(module) {
            if (this.installBlocked()) {
                return;
            }
            this.closeDialog();
            this.install(module);
        },
    },
};
</script>
