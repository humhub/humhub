<template>
    <HumHubForm ref="form" class="c-notification-settings" model-name="NotificationSettings" :class="`c-notification-settings--${scope}`">
        <section v-if="showProfiles" class="panel panel-default c-notification-settings__card c-notification-settings__intro">
            <div class="c-notification-settings__head">
                <h2 class="c-notification-settings__title">{{ labels.title }}</h2>
                <span
                    class="c-notification-settings__status"
                    :class="`is-${saveState}`"
                    role="status"
                    aria-live="polite"
                ><template v-if="statusLabel"><i class="ti" :class="statusIcon" aria-hidden="true"></i>{{ statusLabel }}</template></span>
            </div>
            <p class="c-notification-settings__lead">{{ labels.intro }}</p>
            <div class="c-notification-settings__profiles" role="radiogroup" :aria-label="labels.profile">
                <button
                    v-for="option in profiles"
                    :key="option.id"
                    type="button"
                    role="radio"
                    class="c-notification-settings__profile"
                    :class="{ 'is-selected': profile === option.id }"
                    :aria-checked="profile === option.id ? 'true' : 'false'"
                    :tabindex="profile === option.id ? 0 : -1"
                    :data-profile="option.id"
                    @click="selectProfile(option.id)"
                    @keydown="onProfileKeydown($event, option.id)"
                >
                    <span class="c-notification-settings__radio" aria-hidden="true"></span>
                    <span class="c-notification-settings__profile-text">
                        <span class="c-notification-settings__profile-title">{{ option.title }}</span>
                        <span class="c-notification-settings__profile-description">{{ option.description }}</span>
                    </span>
                </button>
            </div>
        </section>

        <section class="c-notification-settings__card c-notification-settings__categories" :class="{ 'panel panel-default': showProfiles }">
            <div class="c-notification-settings__head c-notification-settings__categories-head">
                <h3 class="c-notification-settings__subtitle">{{ labels.categories }}</h3>
                <span
                    v-if="!showProfiles"
                    class="c-notification-settings__status"
                    :class="`is-${saveState}`"
                    role="status"
                    aria-live="polite"
                ><template v-if="statusLabel"><i class="ti" :class="statusIcon" aria-hidden="true"></i>{{ statusLabel }}</template></span>
            </div>
            <template v-for="(category, index) in categories" :key="category.id">
                <h4
                    v-if="category.module && (index === 0 || !categories[index - 1].module)"
                    class="c-notification-settings__divider"
                >{{ labels.fromModules }}</h4>
                <div
                    class="c-notification-settings__category"
                    :class="{ 'is-expanded': isExpanded(category), 'is-fixed': category.fixed }"
                    :data-category="category.id"
                >
                    <component
                        :is="category.fixed ? 'div' : 'button'"
                        :type="category.fixed ? null : 'button'"
                        class="c-notification-settings__category-head"
                        :aria-expanded="category.fixed ? null : (isExpanded(category) ? 'true' : 'false')"
                        :aria-controls="category.fixed ? null : panelId(category)"
                        @click="toggle(category)"
                    >
                        <span class="c-notification-settings__category-icon" aria-hidden="true"><i class="ti" :class="category.icon"></i></span>
                        <span class="c-notification-settings__category-text">
                            <span class="c-notification-settings__category-title">{{ category.title }}</span>
                            <span v-if="category.description" class="c-notification-settings__category-description">{{ category.description }}</span>
                        </span>
                        <span class="c-notification-settings__badges">
                            <span
                                v-for="channel in channelsOf(category)"
                                :key="channel.id"
                                class="c-notification-settings__badge"
                                :class="{ 'is-on': category.channels[channel.id] }"
                                :data-channel="channel.id"
                            >{{ channel.title }}<span class="visually-hidden">: {{ category.channels[channel.id] ? labels.on : labels.off }}</span></span>
                        </span>
                        <span v-if="category.fixed" class="c-notification-settings__category-state" :title="labels.alwaysOn">
                            <i class="ti ti-lock" aria-hidden="true"></i><span class="visually-hidden">{{ labels.alwaysOn }}</span>
                        </span>
                        <span v-else class="c-notification-settings__category-state" aria-hidden="true">
                            <i class="ti" :class="isExpanded(category) ? 'ti-chevron-up' : 'ti-chevron-down'"></i>
                        </span>
                    </component>
                    <div v-if="!category.fixed && isExpanded(category)" :id="panelId(category)" class="c-notification-settings__category-body">
                        <div class="c-notification-settings__channels" role="group" :aria-label="category.title">
                            <CheckboxField
                                v-for="channel in channelsOf(category)"
                                :key="channel.id"
                                :model-value="category.channels[channel.id]"
                                :attribute="`categories.${category.id}.${channel.id}`"
                                :label="channel.title"
                                @update:model-value="setSwitch(category, channel.id, $event)"
                            />
                        </div>
                        <div v-if="category.id === 'content' && spaces.enabled" class="c-notification-settings__spaces">
                            <label :for="spacesInputId" class="form-label c-notification-settings__spaces-label">{{ labels.spaces }}</label>
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
                                block="c-space-filter"
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
            </template>
        </section>

        <div class="c-notification-settings__footer">
            <span class="c-notification-settings__autosave">{{ labels.autosave }}</span>
            <button
                v-if="scope === 'user' && resetUrl"
                type="button"
                class="btn btn-light c-notification-settings__reset"
                :disabled="resetting"
                @click="reset"
            >{{ labels.reset }}</button>
            <button
                v-if="scope === 'global' && resetAllUrl"
                type="button"
                class="btn btn-danger c-notification-settings__reset-all"
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

// Deep-merges the `categories` and replaces the `spaces` of `patch` into `target` — later wins.
const mergePatch = (target, patch) => {
    Object.entries(patch.categories || {}).forEach(([categoryId, switches]) => {
        target.categories = target.categories || {};
        target.categories[categoryId] = { ...(target.categories[categoryId] || {}), ...switches };
    });
    if (patch.spaces !== undefined) {
        target.spaces = patch.spaces;
    }
    return target;
};

/**
 * The notification settings page — the account page (`scope = user`, the caller's settings) and
 * the administration page (`scope = global`, the defaults for everyone), mounted by
 * `notification\widgets\SettingsPage`.
 *
 * - Props: `initial` (the payload of `GET /api/v2/notification/settings`: `scope`, `channels`,
 *   `categories`, `defaults`, `spaces`), `settingsUrl` (that endpoint, with `?scope=global` for the
 *   defaults), `resetUrl` (the caller's reset, user scope), `resetAllUrl` (reset every user,
 *   global scope, only for `ManageUsers` — else `null`), `scope` (`user`|`global`).
 * - User scope: a profile radio group — *Everything* (every channel of every category on),
 *   *Recommended* (the network's defaults, the payload's `defaults`; choosing it sends `null`
 *   for every switch, so the user follows the defaults again), *Important only* (the web list
 *   on, every other channel only for the categories that cannot be switched off) and *Custom*. The
 *   profile shown is computed from the switches: the first of Recommended, Everything,
 *   Important only they equal, else Custom; choosing Custom changes nothing.
 * - A row per category (core categories, then those of modules under a heading): its icon,
 *   title, description and a badge per channel that applies to it, highlighted when on. A
 *   switchable category's row is a disclosure button revealing a checkbox per channel — and, for
 *   the `content` category, the spaces whose new content notifies (the core `PickerFilterControl`
 *   searching the spaces the caller may see, with the space module's images); a category that
 *   cannot be switched off shows a lock instead.
 * - Autosave: every change sends `PATCH settingsUrl` with only what changed
 *   (`{categories: {<category>: {<channel>: bool|null}}, spaces: [ids]}`), the spaces after
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
                title: i18n.t('NotificationModule.base', 'Notifications'),
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
            };
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
            return { saving: 'ti-loader-2 c-notification-settings__spin', saved: 'ti-check', error: 'ti-alert-circle' }[this.saveState] || '';
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
            return { categories: this.switches(), spaceIds: [...this.spaceIds] };
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
        // Arrow keys move the choice within the radio group, as with native radio buttons.
        onProfileKeydown(event, profileId) {
            const steps = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 };
            if (!steps[event.key]) {
                return;
            }
            event.preventDefault();
            const ids = this.profiles.map((option) => option.id);
            const next = ids[(ids.indexOf(profileId) + steps[event.key] + ids.length) % ids.length];
            this.selectProfile(next);
            this.$nextTick(() => this.$el.querySelector(`[data-profile="${next}"]`)?.focus());
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
