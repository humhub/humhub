<template>
    <UiModal
        :show="true"
        dialog-class="c-mp-dialog c-install-dialog"
        :backdrop-close="!locked"
        :keyboard="!locked"
        @update:show="onShow"
    >
        <template #header="{ titleId }">
            <h5 :id="titleId" class="modal-title">{{ title }}</h5>
            <button type="button" class="btn-close" :aria-label="labels.close" :title="labels.close" :disabled="locked" @click="close"></button>
        </template>

        <div ref="root" class="c-install-dialog__content" :data-step="step">
            <section class="c-install-dialog__step">
                <p v-if="step === 'buy'" class="c-mp-dialog__text"><RichText :parts="texts.buy" /></p>

                <template v-else-if="step === 'license'">
                    <p class="c-mp-dialog__text"><RichText :parts="texts.license" /></p>
                    <div class="c-install-dialog__field">
                        <label :for="inputId" class="c-mp-dialog__label">{{ labels.licenceKey }}</label>
                        <div class="c-licence-field" :class="{ 'is-checking': checking }">
                            <input
                                :id="inputId"
                                ref="input"
                                v-model="licenceKey"
                                type="text"
                                class="form-control c-licence-field__input"
                                :class="{ 'is-invalid': keyError }"
                                :placeholder="labels.placeholder"
                                autocomplete="off"
                                spellcheck="false"
                                autocapitalize="characters"
                                :readonly="checking"
                                :aria-invalid="keyError ? 'true' : null"
                                :aria-describedby="keyError ? `${inputId}-error` : null"
                                @input="keyError = null"
                                @keydown.enter.prevent="register"
                            >
                            <i v-if="checking" class="ti ti-loader c-licence-field__icon c-mp-spin" aria-hidden="true"></i>
                        </div>
                        <div v-if="keyError" :id="`${inputId}-error`" class="invalid-feedback d-block" role="alert">{{ keyError }}</div>
                    </div>
                </template>

                <div v-else-if="step === 'confirm'" class="c-install-dialog__module">
                    <img :src="module.imageUrl" class="c-install-dialog__image" width="48" height="48" alt="">
                    <div class="c-install-dialog__module-text">
                        <b class="c-install-dialog__name">{{ module.name }}</b>
                        <span v-if="version" class="c-install-dialog__version">{{ version }}</span>
                    </div>
                </div>

                <template v-else-if="step === 'installing'">
                    <p class="c-mp-dialog__text"><RichText :parts="texts.installing" /></p>
                    <div
                        class="c-install-dialog__progress"
                        role="progressbar"
                        :aria-label="labels.installing"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        :aria-valuenow="Math.round(progress * 100)"
                    ><span class="c-install-dialog__progress-bar" :style="{ '--mp-progress': progress }"></span></div>
                </template>

                <p v-else-if="step === 'activate'" class="c-mp-dialog__text"><RichText :parts="texts.activate" /></p>

                <p v-else-if="step === 'configure'" class="c-mp-dialog__text"><RichText :parts="texts.configure" /></p>

                <p v-else-if="step === 'failed'" class="c-mp-dialog__text">{{ labels.reloadBeforeRetry }}</p>
            </section>

            <div v-if="showNotice" class="c-mp-notice" :class="{ 'c-mp-notice--warning': module.isCommunity }" role="note">
                <i class="ti c-mp-notice__icon" :class="module.isCommunity ? 'ti-alert-triangle' : 'ti-info-circle'" aria-hidden="true"></i>
                <div class="c-mp-notice__text">
                    <p>{{ labels.thirdParty.join(' ') }}</p>
                    <p v-if="module.isCommunity" v-html="labels.community"></p>
                </div>
            </div>

            <p v-if="error" class="c-mp-dialog__error" role="alert">{{ error }}</p>

            <a
                v-if="step === 'buy' && module.checkoutUrl"
                class="btn c-install-dialog__checkout"
                :href="module.checkoutUrl"
                target="_blank"
                rel="noopener noreferrer"
            >{{ labels.buyLicenceKey }}<i class="ti ti-external-link" aria-hidden="true"></i><span class="visually-hidden"> {{ labels.newTab }}</span></a>
        </div>

        <template #footer>
            <template v-if="step === 'buy'">
                <button type="button" class="btn btn-secondary" @click="close">{{ labels.cancel }}</button>
                <button ref="primary" type="button" class="btn btn-primary" @click="go('license')">{{ labels.addLicence }}</button>
            </template>
            <template v-else-if="step === 'license'">
                <button type="button" class="btn btn-secondary" :disabled="checking" @click="go('buy')">{{ labels.back }}</button>
                <button type="button" class="btn btn-primary" :disabled="checking || licenceKey.trim() === ''" @click="register">{{ labels.install }}</button>
            </template>
            <template v-else-if="step === 'confirm'">
                <button type="button" class="btn btn-secondary" @click="close">{{ labels.cancel }}</button>
                <button ref="primary" type="button" class="btn btn-primary" @click="install()">{{ labels.install }}</button>
            </template>
            <button v-else-if="step === 'installing'" type="button" class="btn btn-primary c-mp-dialog__busy" disabled>
                <i class="ti ti-loader c-mp-spin" aria-hidden="true"></i>{{ labels.installingButton }}
            </button>
            <template v-else-if="step === 'activate'">
                <button type="button" class="btn btn-secondary" :disabled="activating" @click="close">{{ labels.close }}</button>
                <button ref="primary" type="button" class="btn btn-primary c-mp-dialog__busy" :disabled="activating" @click="activate">
                    <i v-if="activating" class="ti ti-loader c-mp-spin" aria-hidden="true"></i>{{ labels.activate }}
                </button>
            </template>
            <template v-else-if="step === 'configure'">
                <button type="button" class="btn btn-secondary" @click="close">{{ labels.close }}</button>
                <a ref="primary" class="btn btn-primary" :href="current.configUrl">{{ labels.configure }}</a>
            </template>
            <button v-else-if="step === 'failed'" ref="primary" type="button" class="btn btn-secondary" @click="close">{{ labels.close }}</button>
        </template>
    </UiModal>
</template>

<script>
import { i18n, log, status } from '@humhub/vue';
import RichText from './RichText.vue';
import { enableModule, errorMessage, fetchModules, installModule, registerLicenceKey } from './marketplaceApi.js';
import { communityNotice, emphasis, strongParts, thirdPartyNotice } from './messages.js';

// The dialog's height eases between steps for this long; the full progress bar stays this long
// before the next step.
export const STEP_MS = 300;
// The install is one request without progress: the bar eases towards PROGRESS_LIMIT (never
// reaching it) with this time constant and completes when the response arrives.
export const PROGRESS_TICK_MS = 120;
export const PROGRESS_LIMIT = 0.9;
const PROGRESS_TAU_MS = 2500;

const STEPS = ['buy', 'license', 'confirm', 'installing', 'activate', 'configure', 'failed'];

// A 4xx is the server's definite answer (nothing was installed); a 5xx or a network error may
// be a gateway timing out while PHP still installs.
const isDefinite = (response) => Boolean(response) && response.status >= 400 && response.status < 500;

const reducedMotion = () => typeof window.matchMedia === 'function'
    && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

let uid = 0;

/**
 * The install flow of one module, in one dialog whose height eases from step to step:
 *
 * - `buy` (the card's Buy): the two ways to a licence — "Buy License Key" (the checkout page,
 *   new tab) or "Add License" → `license`: the key is registered (`POST marketplace/licence-key`,
 *   a rejected key stays in the field with the API's message), then the module installs.
 * - `confirm` (the card's Install, a licence row's Install): the module; nothing installs
 *   before its Install.
 * - The first step carries the third-party note for a third-party module, with the community
 *   warning for an unverified community module.
 * - `installing`: a progress bar easing towards `PROGRESS_LIMIT` until the single install
 *   request answers. The server cannot abort an install: no Cancel, and the dialog cannot be
 *   closed meanwhile (nor while a key is checked or the module activated). A `4xx` returns to
 *   `confirm` with the error — or, right after a registered key, to `license`: the key did not
 *   license this module. After a `5xx` or a network error the installation may still be running
 *   behind a gateway timeout: the module is re-read, and if it is installed now the flow goes
 *   on; else `failed` shows the error and asks to reload the page before retrying.
 * - `activate` → `POST module/<id>/enable` → `configure` (its `configUrl`), or — without
 *   one — the dialog closes with a success status.
 *
 * Emits `registered` (a key was added), `installed` and `activated` (the changed module record)
 * and `close`. Module names are text nodes, never markup.
 *
 * @since 1.20
 */
export default {
    name: 'InstallDialog',
    components: { RichText },
    props: {
        module: { type: Object, required: true },
        initialStep: {
            type: String,
            default: 'confirm',
            validator: (value) => ['buy', 'confirm'].includes(value),
        },
    },
    emits: ['registered', 'installed', 'activated', 'close'],
    data() {
        return {
            step: this.initialStep,
            current: this.module,
            licenceKey: '',
            keyError: null,
            checking: false,
            error: null,
            progress: 0,
            activating: false,
            inputId: `marketplace-install-licence-${++uid}`,
        };
    },
    computed: {
        installing() {
            return this.step === 'installing';
        },
        // A request runs whose outcome the dialog still has to present: it cannot be closed.
        locked() {
            return this.installing || this.checking || this.activating;
        },
        // The note belongs to the step the dialog opened on.
        showNotice() {
            return this.module.isThirdParty === true && this.step === this.initialStep;
        },
        version() {
            return this.module.latestCompatibleVersion || this.module.latestVersion || '';
        },
        title() {
            return {
                buy: i18n.t('MarketplaceModule.base', 'Buy Module'),
                activate: i18n.t('MarketplaceModule.base', 'Activate Module'),
                configure: i18n.t('MarketplaceModule.base', 'Configure Module'),
            }[this.step] || i18n.t('MarketplaceModule.base', 'Install Module');
        },
        texts() {
            const values = {
                moduleName: this.module.name,
                buyLicenseKey: this.labels.buyLicenceKey,
                addLicense: this.labels.addLicence,
            };
            return {
                buy: strongParts(i18n.t('MarketplaceModule.base', 'To install {moduleName}, you need a License Key. Buy one with {buyLicenseKey}, or choose {addLicense} if you already have one.', emphasis('moduleName', 'buyLicenseKey', 'addLicense')), values),
                license: strongParts(i18n.t('MarketplaceModule.base', 'To install {moduleName}, enter the License Key you purchased.', emphasis('moduleName')), values),
                installing: strongParts(i18n.t('MarketplaceModule.base', 'Installing {moduleName}...', emphasis('moduleName')), values),
                activate: strongParts(i18n.t('MarketplaceModule.base', 'You are all done! {moduleName} was installed. To make it available on your network, activate it now or later in Module Administration.', emphasis('moduleName')), values),
                configure: strongParts(i18n.t('MarketplaceModule.base', '{moduleName} was activated! Configure it now or later in Module Administration.', emphasis('moduleName')), values),
            };
        },
        labels() {
            return {
                buyLicenceKey: i18n.t('MarketplaceModule.base', 'Buy License Key'),
                addLicence: i18n.t('MarketplaceModule.base', 'Add License'),
                newTab: i18n.t('MarketplaceModule.base', '(opens in a new tab)'),
                licenceKey: i18n.t('MarketplaceModule.base', 'License Key'),
                placeholder: 'XXXX-XXXX-XXXX-XXXX',
                install: i18n.t('MarketplaceModule.base', 'Install'),
                installing: i18n.t('MarketplaceModule.base', 'Installing {moduleName}...', { moduleName: this.module.name }),
                installingButton: i18n.t('MarketplaceModule.base', 'Installing'),
                activate: i18n.t('MarketplaceModule.base', 'Activate'),
                configure: i18n.t('MarketplaceModule.base', 'Configure'),
                cancel: i18n.t('MarketplaceModule.base', 'Cancel'),
                back: i18n.t('base', 'Back'),
                close: i18n.t('base', 'Close'),
                reloadBeforeRetry: i18n.t('MarketplaceModule.base', 'The installation did not finish. Please reload the page before trying again.'),
                thirdParty: thirdPartyNotice(),
                community: communityNotice(),
            };
        },
    },
    watch: {
        // Runs before the re-render: the old step's height is measured here, the new one's after.
        step() {
            const content = this.content();
            const from = content ? content.offsetHeight : 0;
            this.$nextTick(() => {
                this.easeHeight(content, from);
                this.focusStep();
            });
        },
    },
    beforeUnmount() {
        // Requests still running answer into nothing: no step, no event, no further request.
        this.destroyed = true;
        this.stopProgress();
    },
    methods: {
        content() {
            return this.$refs.root ? this.$refs.root.closest('.modal-content') : null;
        },
        easeHeight(content, from) {
            if (!content || from <= 0 || reducedMotion() || typeof content.animate !== 'function') {
                return;
            }
            const to = content.offsetHeight;
            if (to === from) {
                return;
            }
            content.style.overflow = 'hidden';
            const motion = content.animate([{ height: `${from}px` }, { height: `${to}px` }], { duration: STEP_MS, easing: 'ease' });
            const done = () => {
                content.style.overflow = '';
            };
            motion.onfinish = done;
            motion.oncancel = done;
        },
        // Focus follows the step, so it is never left on a control that just went away.
        focusStep() {
            if (this.step === 'license' && this.$refs.input) {
                this.$refs.input.focus();
            } else if (this.$refs.primary && !this.$refs.primary.disabled) {
                this.$refs.primary.focus();
            } else if (this.$refs.root) {
                const dialog = this.$refs.root.closest('.modal');
                if (dialog) {
                    dialog.focus();
                }
            }
        },
        go(step) {
            if (STEPS.includes(step)) {
                this.error = null;
                this.step = step;
            }
        },
        onShow(show) {
            if (!show) {
                this.close();
            }
        },
        close() {
            if (!this.locked) {
                this.$emit('close');
            }
        },
        register() {
            const key = this.licenceKey.trim();
            if (key === '' || this.checking) {
                return;
            }
            this.checking = true;
            this.keyError = null;
            registerLicenceKey(key).then(() => {
                if (this.destroyed) {
                    return;
                }
                this.checking = false;
                this.$emit('registered');
                this.install(true);
            }, (response) => {
                if (this.destroyed) {
                    return;
                }
                this.checking = false;
                this.keyError = errorMessage(response, i18n.t('MarketplaceModule.base', 'Invalid module license key!'));
                log.error(response);
            });
        },
        startProgress() {
            this.stopProgress();
            this.progress = 0;
            const started = Date.now();
            this.progressTimer = setInterval(() => {
                this.progress = PROGRESS_LIMIT * (1 - Math.exp(-(Date.now() - started) / PROGRESS_TAU_MS));
            }, PROGRESS_TICK_MS);
        },
        stopProgress() {
            clearInterval(this.progressTimer);
            this.progressTimer = null;
        },
        // `registered`: a key was registered just before, for this module.
        async install(registered = false) {
            if (this.installing) {
                return;
            }
            this.error = null;
            this.step = 'installing';
            this.startProgress();
            let installed;
            try {
                installed = await installModule(this.module.id);
            } catch (response) {
                if (this.destroyed) {
                    return;
                }
                log.error(response);
                installed = isDefinite(response) ? null : await this.reread();
                if (this.destroyed) {
                    return;
                }
                if (!installed) {
                    this.installFailed(response, registered);
                    return;
                }
            }
            if (this.destroyed) {
                return;
            }
            this.stopProgress();
            this.progress = 1;
            if (!reducedMotion()) {
                await new Promise((resolve) => {
                    setTimeout(resolve, STEP_MS);
                });
                if (this.destroyed) {
                    return;
                }
            }
            this.current = installed;
            this.$emit('installed', installed);
            status('success', i18n.t('MarketplaceModule.base', '{moduleName} installed successfully.', { moduleName: this.module.name }));
            this.step = 'activate';
        },
        // The module's record after an install without a definite answer, if it is installed
        // now; null otherwise (or when it cannot be read either).
        async reread() {
            try {
                const [found] = (await fetchModules({ id: this.module.id })).filter((module) => module.id === this.module.id);
                return found && found.installedVersion !== null && found.installedVersion !== undefined ? found : null;
            } catch (response) {
                log.error(response);
                return null;
            }
        },
        installFailed(response, registered) {
            this.stopProgress();
            const message = errorMessage(response, i18n.t('MarketplaceModule.base', 'Could not install the module.'));
            if (!isDefinite(response)) {
                // Maybe still installing behind a gateway timeout: no second Install from here.
                this.error = message;
                this.step = 'failed';
            } else if (registered && response.status === 422) {
                // The key was accepted, but the module still cannot be installed: it licenses
                // another module. The browser reloads its list on close (`registered`).
                this.step = 'license';
                this.keyError = i18n.t('MarketplaceModule.base', 'This License Key does not license {moduleName}.', { moduleName: this.module.name });
            } else {
                this.error = message;
                this.step = 'confirm';
            }
        },
        activate() {
            if (this.activating) {
                return;
            }
            this.activating = true;
            this.error = null;
            enableModule(this.module.id).then((result) => {
                if (this.destroyed) {
                    return;
                }
                this.activating = false;
                this.current = { ...this.current, isEnabled: result.isEnabled === true, configUrl: result.configUrl || null };
                this.$emit('activated', this.current);
                if (this.current.configUrl) {
                    this.step = 'configure';
                } else {
                    status('success', i18n.t('MarketplaceModule.base', '{moduleName} was activated.', { moduleName: this.module.name }));
                    this.$emit('close');
                }
            }, (response) => {
                if (this.destroyed) {
                    return;
                }
                this.activating = false;
                this.error = errorMessage(response, i18n.t('MarketplaceModule.base', 'Could not enable the module.'));
                log.error(response);
            });
        },
    },
};
</script>
