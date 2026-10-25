<template>
    <HumHubForm ref="form" class="notification-settings" model-name="NotificationSettings" :class="`notification-settings-${scope}`">
        <section v-if="showProfiles" class="card content-card notification-settings-intro">
            <div class="card-header">
                <h2 class="card-title notification-settings-title">{{ labels.profile }}</h2>
                <span
                    class="notification-settings-status"
                    :class="`is-${saveState}`"
                    role="status"
                    aria-live="polite"
                ><template v-if="statusLabel"><span v-if="saveState === 'saving'" class="spinner-border spinner-border-sm" aria-hidden="true"></span><i v-else class="ti" :class="statusIcon" aria-hidden="true"></i>{{ statusLabel }}</template></span>
            </div>
            <div class="card-body">
                <p class="notification-settings-lead">{{ labels.intro }}</p>
                <div class="form-check-cards" role="radiogroup" :aria-label="labels.profile">
                    <label v-for="option in profiles" :key="option.id" class="form-check-card">
                        <input
                            class="form-check-input"
                            type="radio"
                            :name="`${radioName}-profile`"
                            :value="option.id"
                            :checked="profile === option.id"
                            :data-profile="option.id"
                            @change="onRadioChange($event, () => selectProfile(option.id), () => profile)"
                        >
                        <span class="form-check-card-body">
                            <span class="form-check-card-title">{{ option.title }}</span>
                            <span class="form-check-card-text">{{ option.description }}</span>
                        </span>
                    </label>
                </div>
            </div>
        </section>

        <section class="notification-settings-categories" :class="{ 'card content-card': showProfiles }">
            <div class="notification-settings-head" :class="{ 'card-header': showProfiles }">
                <h3 class="notification-settings-subtitle" :class="{ 'card-title': showProfiles }">{{ labels.categories }}</h3>
                <span
                    v-if="!showProfiles"
                    class="notification-settings-status"
                    :class="`is-${saveState}`"
                    role="status"
                    aria-live="polite"
                ><template v-if="statusLabel"><span v-if="saveState === 'saving'" class="spinner-border spinner-border-sm" aria-hidden="true"></span><i v-else class="ti" :class="statusIcon" aria-hidden="true"></i>{{ statusLabel }}</template></span>
            </div>
            <template v-for="group in categoryGroups" :key="group.module ? 'modules' : 'core'">
                <h4 v-if="group.module" class="notification-settings-divider">{{ labels.fromModules }}</h4>
                <div class="accordion notification-settings-accordion" :class="{ 'accordion-flush': showProfiles }">
                    <div
                        v-for="category in group.items"
                        :key="category.id"
                        class="accordion-item notification-settings-category"
                        :data-category="category.id"
                    >
                        <h5 class="accordion-header">
                            <component
                                :is="category.fixed ? 'div' : 'button'"
                                :type="category.fixed ? null : 'button'"
                                class="accordion-button"
                                :class="{ collapsed: !isExpanded(category), 'accordion-button-static': category.fixed }"
                                :aria-expanded="category.fixed ? null : (isExpanded(category) ? 'true' : 'false')"
                                :aria-controls="category.fixed ? null : panelId(category)"
                                @click="toggle(category)"
                            >
                                <span class="accordion-icon" aria-hidden="true"><i class="ti" :class="category.icon"></i></span>
                                <span class="accordion-title">
                                    {{ category.title }}
                                    <span v-if="category.description" class="accordion-subtitle">{{ category.description }}</span>
                                </span>
                                <span class="notification-settings-badges">
                                    <span
                                        v-for="channel in channelsOf(category)"
                                        :key="channel.id"
                                        class="badge"
                                        :class="{ 'badge-accent': category.channels[channel.id] }"
                                        :data-channel="channel.id"
                                        :data-on="category.channels[channel.id] ? 'true' : 'false'"
                                    >{{ channel.title }}<span class="visually-hidden">: {{ category.channels[channel.id] ? labels.on : labels.off }}</span></span>
                                </span>
                                <span v-if="category.fixed" class="accordion-static-icon" :title="labels.alwaysOn">
                                    <i class="ti ti-lock" aria-hidden="true"></i><span class="visually-hidden">{{ labels.alwaysOn }}</span>
                                </span>
                            </component>
                        </h5>
                        <div v-if="!category.fixed && isExpanded(category)" :id="panelId(category)" class="accordion-collapse collapse show">
                            <div class="accordion-body notification-settings-category-body">
                                <div class="notification-settings-channels" role="group" :aria-label="category.title">
                                    <CheckboxField
                                        v-for="channel in channelsOf(category)"
                                        :key="channel.id"
                                        :model-value="category.channels[channel.id]"
                                        :attribute="`categories.${category.id}.${channel.id}`"
                                        :label="channel.title"
                                        @update:model-value="setSwitch(category, channel.id, $event)"
                                    />
                                </div>
                                <div v-if="category.id === 'content' && spaces.enabled" class="notification-settings-spaces">
                                    <label :for="spacesInputId" class="form-label">{{ labels.spaces }}</label>
                                    <PickerFilterControl
                                        :model-value="spaceIds"
                                        :filter="spacesFilter"
                                        :input-id="spacesInputId"
                                        :multiple="true"
                                        keep-placeholder
                                        :search="searchSpaces"
                                        :resolve="resolveSpaces"
                                        :item-label="spaceLabel"
                                        icon="ti-users-group"
                                        block="space-filter"
                                        @update:model-value="setSpaces"
                                    >
                                        <template #option="{ item }">
                                            <SpaceImage v-bind="spaceImageProps(item)" :width="24" :link="false" />
                                        </template>
                                        <template #chip="{ item }">
                                            <SpaceImage v-bind="spaceImageProps(item)" :width="18" :link="false" />
                                        </template>
                                    </PickerFilterControl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </section>

        <section v-if="privacy" class="notification-settings-privacy">
            <h3 class="notification-settings-subtitle">{{ labels.privacy }}</h3>
            <p class="notification-settings-lead">{{ labels.privacyIntro }}</p>
            <p v-if="privacy.fixed" class="notification-settings-privacy-fixed">
                <i class="ti ti-lock" aria-hidden="true"></i>{{ labels.privacyFixed }}
            </p>
            <div
                class="form-check-cards"
                role="radiogroup"
                :aria-label="labels.privacy"
                :aria-disabled="privacy.fixed ? 'true' : null"
                data-field="privacy"
            >
                <label v-for="option in privacyModes" :key="option.id" class="form-check-card">
                    <input
                        class="form-check-input"
                        type="radio"
                        :name="`${radioName}-privacy`"
                        :value="option.id"
                        :checked="privacy.mode === option.id"
                        :disabled="privacy.fixed"
                        :data-privacy="option.id"
                        @change="onRadioChange($event, () => selectPrivacy(option.id), () => privacy.mode)"
                    >
                    <span class="form-check-card-body">
                        <span class="form-check-card-title">{{ option.title }}</span>
                        <span class="form-check-card-text">{{ option.description }}</span>
                        <span class="notification-settings-privacy-example">{{ option.example }}</span>
                    </span>
                </label>
            </div>
        </section>

        <div class="notification-settings-footer">
            <span class="notification-settings-autosave">{{ labels.autosave }}</span>
            <button
                v-if="scope === 'user' && resetUrl"
                type="button"
                class="btn btn-light notification-settings-reset"
                :disabled="resetting"
                @click="reset"
            >{{ labels.reset }}</button>
            <button
                v-if="scope === 'global' && resetAllUrl"
                type="button"
                class="btn btn-danger notification-settings-reset-all"
                :disabled="resetting"
                @click="resetAll"
            >{{ labels.resetAll }}</button>
        </div>
    </HumHubForm>
</template>

<script>
import { apiUrl, client, i18n, log, modal } from '@humhub/vue';

const clone = (value) => JSON.parse(JSON.stringify(value));

// How long the space picker waits for further changes before it saves.
export const SPACES_DEBOUNCE_MS = 600;

// Deep-merges the `categories` and replaces the `spaces` and `privacy` of `patch` into `target` — later wins.
const mergePatch = (target, patch) => {
    Object.entries(patch.categories || {}).forEach(([categoryId, switches]) => {
        target.categories = target.categories || {};
        target.categories[categoryId] = { ...(target.categories[categoryId] || {}), ...switches };
    });
    if (patch.spaces !== undefined) {
        target.spaces = patch.spaces;
    }
    if (patch.privacy !== undefined) {
        target.privacy = patch.privacy;
    }
    return target;
};

// Names the radio groups of an instance apart from those of another
let radioUid = 0;

/**
 * The notification settings page — the account page (`scope = user`, the caller's settings) and
 * the administration page (`scope = global`, the defaults for everyone), mounted by
 * `notification\widgets\SettingsPage`.
 *
 * - Props: `initial` (the payload of `GET /api/v2/notification/settings`: `scope`, `channels`,
 *   `categories`, `defaults`, `spaces`, `privacy`), `settingsUrl` (that endpoint, with `?scope=global` for the
 *   defaults), `resetUrl` (the caller's reset, user scope), `resetAllUrl` (reset every user,
 *   global scope, only for `ManageUsers` — else `null`), `scope` (`user`|`global`).
 * - User scope: a profile radio group — *Everything* (every channel of every category on),
 *   *Recommended* (the network's defaults, the payload's `defaults`; choosing it sends `null`
 *   for every switch, so the user follows the defaults again), *Important only* (the web list
 *   on, every other channel only for the categories that cannot be switched off) and *Custom*. The
 *   profile shown is computed from the switches: the first of Recommended, Everything,
 *   Important only they equal, else Custom; choosing Custom changes nothing.
 * - A Bootstrap accordion item per category (core categories, then those of modules under a
 *   heading, each group an `.accordion`): its icon, title, description and a `.badge` per channel
 *   that applies to it (`badge-accent` when on, a plain, neutral `.badge` when off). A switchable
 *   category's header is the accordion button revealing a checkbox per channel — and, for
 *   the `content` category, the spaces whose new content notifies (the core `PickerFilterControl`
 *   searching the spaces the caller may see, with the space module's images); a category that
 *   cannot be switched off is a static header (`.accordion-button-static`) with a lock.
 * - Global scope: the network's privacy mode of mails and push (`privacy`) — a radio group of
 *   *Full details*, *Hide content* and *Minimal*, each with an example line; disabled with a
 *   note when the configuration file sets it (`privacy.fixed`).
 * - Autosave: every change sends `PATCH settingsUrl` with only what changed
 *   (`{categories: {<category>: {<channel>: bool|null}}, spaces: [ids], privacy: mode}`), the spaces after
 *   `SPACES_DEBOUNCE_MS`, a profile as one request. Requests run one after another; changes
 *   made meanwhile are merged into the next one (the latest value of a switch wins). A failed
 *   request puts the switches it carried back to the last saved state; a `422` lands on the
 *   fields (or the form's error summary), any other failure is logged. The state of the saving
 *   shows next to the title (*Saving…*, *Saved*, *Not saved*), not in the status bar.
 * - A reset is confirmed, posts and replaces the state with the answer.
 *
 * @since 1.20
 */
export default {
    name: 'NotificationSettings',
    i18nCategories: ['NotificationModule.base', 'base'],
    props: {
        initial: { type: Object, required: true },
        settingsUrl: { type: String, required: true },
        resetUrl: { type: String, default: null },
        resetAllUrl: { type: String, default: null },
        scope: { type: String, default: 'user' },
    },
    data() {
        return {
            ...this.stateOf(this.initial),
            expanded: {},
            saveState: 'idle',
            resetting: false,
            spacesInputId: 'notificationsettings-spaces',
            radioName: `notificationsettings-${++radioUid}`,
        };
    },
    created() {
        // Non-reactive: the short shapes of the spaces the payload names, which answer the
        // picker's resolve() without a request; the saved state failed requests return to;
        // the request in flight and the changes waiting for it.
        this.knownSpaces = this.spacesOf(this.initial);
        this.saved = this.snapshot();
        this.inFlight = null;
        this.pending = null;
        this.spacesTimer = null;
    },
    beforeUnmount() {
        clearTimeout(this.spacesTimer);
    },
    computed: {
        labels() {
            return {
                intro: i18n.t('NotificationModule.base', 'Pick a profile for where your notifications reach you. Any change below turns it into “Custom”.'),
                profile: i18n.t('NotificationModule.base', 'Profile'),
                categories: i18n.t('NotificationModule.base', 'Categories'),
                fromModules: i18n.t('NotificationModule.base', 'From modules'),
                alwaysOn: i18n.t('NotificationModule.base', 'Always on'),
                on: i18n.t('NotificationModule.base', 'on'),
                off: i18n.t('NotificationModule.base', 'off'),
                spaces: i18n.t('NotificationModule.base', 'From these Spaces'),
                spacesPlaceholder: i18n.t('NotificationModule.base', 'Add a Space…'),
                saving: i18n.t('NotificationModule.base', 'Saving…'),
                saved: i18n.t('base', 'Saved'),
                notSaved: i18n.t('NotificationModule.base', 'Not saved'),
                autosave: i18n.t('NotificationModule.base', 'Changes are saved right away.'),
                reset: i18n.t('NotificationModule.base', 'Reset to defaults'),
                resetConfirm: i18n.t('NotificationModule.base', 'Do you want to reset your notification settings to the defaults?'),
                resetAll: i18n.t('NotificationModule.base', 'Reset all users'),
                resetAllConfirm: i18n.t('NotificationModule.base', 'Do you want to reset the settings concerning notifications for all users?'),
                privacy: i18n.t('NotificationModule.base', 'Privacy of e-mails and push messages'),
                privacyIntro: i18n.t('NotificationModule.base', 'How much notification e-mails, push messages and the activity summary e-mail (E-Mail Summaries) show. They leave the network and may be read on a lock screen or in a shared mailbox. The notifications in the network always show everything.'),
                privacyFixed: i18n.t('NotificationModule.base', 'This setting is set in the configuration file and cannot be changed here.'),
            };
        },
        privacyModes() {
            return [
                {
                    id: 'full',
                    title: i18n.t('NotificationModule.base', 'Full details'),
                    description: i18n.t('NotificationModule.base', 'Names, content previews and the content itself.'),
                    example: i18n.t('NotificationModule.base', 'Jane Doe commented on your post “Budget 2027”.'),
                },
                {
                    id: 'redacted',
                    title: i18n.t('NotificationModule.base', 'Hide content'),
                    description: i18n.t('NotificationModule.base', 'Who did what, without the content, titles or Space names.'),
                    example: i18n.t('NotificationModule.base', 'Jane Doe commented on your post.'),
                },
                {
                    id: 'minimal',
                    title: i18n.t('NotificationModule.base', 'Minimal'),
                    description: i18n.t('NotificationModule.base', 'Only the kind of notification and how many, without names.'),
                    example: i18n.t('NotificationModule.base', 'New notification: Reactions on my content'),
                },
            ];
        },
        profiles() {
            return [
                { id: 'everything', title: i18n.t('NotificationModule.base', 'Everything'), description: i18n.t('NotificationModule.base', 'Every category on all channels.') },
                { id: 'recommended', title: i18n.t('NotificationModule.base', 'Recommended'), description: i18n.t('NotificationModule.base', 'The defaults of this network.') },
                { id: 'important', title: i18n.t('NotificationModule.base', 'Important only'), description: i18n.t('NotificationModule.base', 'Only what is addressed to you by e-mail and push.') },
                { id: 'custom', title: i18n.t('NotificationModule.base', 'Custom'), description: i18n.t('NotificationModule.base', 'Your own choice below.') },
            ];
        },
        showProfiles() {
            return this.scope === 'user' && this.defaults !== null;
        },
        // The profile the current switches equal, else `custom`.
        profile() {
            const current = this.switches();
            const match = ['recommended', 'everything', 'important']
                .find((id) => this.categories.every((category) => Object.keys(category.channels)
                    .every((channelId) => current[category.id][channelId] === this.profileValue(id, category, channelId))));
            return match || 'custom';
        },
        statusLabel() {
            return { saving: this.labels.saving, saved: this.labels.saved, error: this.labels.notSaved }[this.saveState] || '';
        },
        statusIcon() {
            return { saved: 'ti-check', error: 'ti-alert-circle' }[this.saveState] || '';
        },
        // The core's categories, then those of modules under their own heading; each an accordion.
        categoryGroups() {
            return [false, true]
                .map((module) => ({ module, items: this.categories.filter((category) => !!category.module === module) }))
                .filter((group) => group.items.length);
        },
        spacesFilter() {
            return { key: 'spaces', label: this.labels.spaces, placeholder: this.labels.spacesPlaceholder, multiple: true };
        },
    },
    methods: {
        // The editable state of a payload: a copy, so a reset can replace it wholesale.
        stateOf(payload) {
            const selected = payload.spaces?.selected || [];
            return {
                channels: clone(payload.channels || []),
                categories: clone(payload.categories || []).map((category) => ({ ...category, channels: category.channels || {} })),
                defaults: payload.defaults ? clone(payload.defaults) : null,
                spaces: { enabled: payload.spaces?.enabled !== false },
                spaceIds: selected.map((space) => String(space.id)),
                privacy: payload.privacy ? clone(payload.privacy) : null,
            };
        },
        applyState(payload) {
            this.knownSpaces = this.spacesOf(payload);
            Object.assign(this, this.stateOf(payload));
            this.saved = this.snapshot();
        },
        spacesOf(payload) {
            return Object.fromEntries((payload.spaces?.selected || []).map((space) => [String(space.id), space]));
        },
        // `{<category>: {<channel>: bool}}` of the current switches.
        switches() {
            return Object.fromEntries(this.categories.map((category) => [category.id, { ...category.channels }]));
        },
        snapshot() {
            return { categories: this.switches(), spaceIds: [...this.spaceIds], privacy: this.privacy?.mode ?? null };
        },
        channelsOf(category) {
            return this.channels.filter((channel) => Object.prototype.hasOwnProperty.call(category.channels, channel.id));
        },
        // What the profile sets the channel of the category to.
        profileValue(profileId, category, channelId) {
            if (category.fixed) {
                return true;
            }
            switch (profileId) {
                case 'everything':
                    return true;
                case 'recommended':
                    return this.defaults?.[category.id]?.[channelId] !== false;
                case 'important':
                    return channelId === 'web';
                default:
                    return category.channels[channelId];
            }
        },
        isExpanded(category) {
            return !category.fixed && !!this.expanded[category.id];
        },
        toggle(category) {
            if (!category.fixed) {
                this.expanded[category.id] = !this.expanded[category.id];
            }
        },
        panelId(category) {
            return `notification-settings-category-${category.id}`;
        },
        category(id) {
            return this.categories.find((category) => category.id === id);
        },
        setSwitch(category, channelId, value) {
            category.channels[channelId] = !!value;
            this.enqueue({ categories: { [category.id]: { [channelId]: !!value } } });
        },
        setSpaces(ids) {
            this.spaceIds = ids.map(String);
            clearTimeout(this.spacesTimer);
            this.spacesTimer = setTimeout(() => {
                this.spacesTimer = null;
                this.enqueue({ spaces: this.spaceIds.map(Number) });
            }, SPACES_DEBOUNCE_MS);
        },
        selectProfile(profileId) {
            if (profileId === 'custom' || profileId === this.profile) {
                return;
            }
            const patch = { categories: {} };
            this.categories.filter((category) => !category.fixed).forEach((category) => {
                Object.keys(category.channels).forEach((channelId) => {
                    const value = this.profileValue(profileId, category, channelId);
                    category.channels[channelId] = value;
                    patch.categories[category.id] = patch.categories[category.id] || {};
                    // Recommended follows the network's defaults: the user's own switches are removed
                    patch.categories[category.id][channelId] = profileId === 'recommended' ? null : value;
                });
            });
            if (Object.keys(patch.categories).length) {
                this.enqueue(patch);
            }
        },
        selectPrivacy(mode) {
            if (!this.privacy || this.privacy.fixed || this.privacy.mode === mode) {
                return;
            }
            this.privacy.mode = mode;
            this.enqueue({ privacy: mode });
        },
        // A radio of a group (arrow keys move the choice natively). A choice the state does not
        // take (Custom) is put back: the group is checked by the state, not by the click.
        onRadioChange(event, select, current) {
            select();
            const group = this.$el.querySelectorAll(`input[name="${event.target.name}"]`);
            this.$nextTick(() => group.forEach((input) => { input.checked = input.value === current(); }));
        },
        enqueue(patch) {
            this.pending = mergePatch(this.pending || {}, patch);
            this.saveState = 'saving';
            this.flush();
        },
        flush() {
            if (this.inFlight || !this.pending) {
                return Promise.resolve();
            }
            const patch = this.pending;
            this.pending = null;
            this.$refs.form?.clearErrors();
            this.inFlight = this.request('patch', this.settingsUrl, patch).then((response) => {
                this.inFlight = null;
                this.remember(patch, response);
                if (this.pending) {
                    return this.flush();
                }
                this.saveState = 'saved';
                return null;
            }).catch((response) => {
                this.inFlight = null;
                this.revert(patch);
                this.saveState = 'error';
                if (response && response.status === 422) {
                    this.$refs.form?.setErrors(response);
                } else {
                    log.error(response);
                }
                return this.pending ? this.flush() : null;
            });
            return this.inFlight;
        },
        // A saved patch becomes the state failed requests return to; without further changes
        // waiting, the answer is the state.
        remember(patch, response) {
            Object.entries(patch.categories || {}).forEach(([categoryId, switches]) => {
                Object.keys(switches).forEach((channelId) => {
                    const answered = (response?.categories || []).find((category) => category.id === categoryId)?.channels?.[channelId];
                    const value = answered !== undefined ? answered : this.category(categoryId)?.channels[channelId];
                    this.saved.categories[categoryId] = { ...(this.saved.categories[categoryId] || {}), [channelId]: value };
                });
            });
            if (patch.spaces !== undefined) {
                this.saved.spaceIds = patch.spaces.map(String);
            }
            if (patch.privacy !== undefined) {
                this.saved.privacy = response?.privacy?.mode ?? patch.privacy;
            }
            if (!this.pending && !this.spacesTimer && response && Array.isArray(response.categories)) {
                const expanded = this.expanded;
                this.applyState(response);
                this.expanded = expanded;
            }
        },
        // Puts what a failed patch carried back to the last saved state — unless a later change
        // of the same switch waits to be sent.
        revert(patch) {
            Object.entries(patch.categories || {}).forEach(([categoryId, switches]) => {
                const category = this.category(categoryId);
                Object.keys(switches).forEach((channelId) => {
                    if (category && this.pending?.categories?.[categoryId]?.[channelId] === undefined) {
                        category.channels[channelId] = this.saved.categories[categoryId]?.[channelId] ?? category.channels[channelId];
                    }
                });
            });
            if (patch.spaces !== undefined && this.pending?.spaces === undefined && !this.spacesTimer) {
                this.spaceIds = [...this.saved.spaceIds];
            }
            if (patch.privacy !== undefined && this.pending?.privacy === undefined && this.privacy) {
                this.privacy.mode = this.saved.privacy;
            }
        },
        request(method, url, data) {
            const cfg = data === undefined ? {} : { data: JSON.stringify(data), contentType: 'application/json' };
            return client[method](url, cfg);
        },
        reset() {
            return this.confirmAndPost(this.resetUrl, { body: this.labels.resetConfirm }, true);
        },
        resetAll() {
            return this.confirmAndPost(this.resetAllUrl, { body: this.labels.resetAllConfirm }, false);
        },
        confirmAndPost(url, options, replace) {
            if (this.resetting || !url) {
                return Promise.resolve();
            }
            return modal.confirm(options).then((confirmed) => {
                if (!confirmed) {
                    return null;
                }
                this.resetting = true;
                this.saveState = 'saving';
                this.$refs.form.clearErrors();
                return client.post(url).then((response) => {
                    this.resetting = false;
                    if (replace) {
                        this.applyState(response);
                    }
                    this.saveState = 'saved';
                }).catch((response) => {
                    this.resetting = false;
                    this.saveState = 'error';
                    log.error(response);
                });
            });
        },
        searchSpaces(q, pageSize) {
            return client.get(apiUrl('space', { purpose: 'picker', scope: 'all', q, pageSize }))
                .then((response) => response.results || []);
        },
        // The selection comes with the payload; an id it does not name (none, normally) is asked
        // for like `SpaceFilterControl` does.
        resolveSpaces(ids) {
            const known = ids.map((id) => this.knownSpaces[id]).filter(Boolean);
            const unknown = ids.filter((id) => !this.knownSpaces[id]);
            if (!unknown.length) {
                return Promise.resolve(known);
            }
            return client.get(apiUrl('space', { purpose: 'picker', scope: 'all', ids: unknown.join(','), pageSize: unknown.length }))
                .then((response) => known.concat(response.results || []));
        },
        spaceLabel(space) {
            return space.name;
        },
        spaceImageProps(space) {
            return {
                id: space.id,
                name: space.name,
                url: space.url,
                color: space.color,
                imageUrl: space.imageUrl,
                contentContainerId: space.contentContainerId ?? null,
            };
        },
    },
};
</script>
