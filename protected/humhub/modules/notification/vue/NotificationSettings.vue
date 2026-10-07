<template>
    <HumHubForm ref="form" class="c-notification-settings" model-name="NotificationSettings" :busy="busy" @submit="save">
        <section
            v-for="channel in channels"
            :key="channel.id"
            class="c-notification-settings__channel mb-4"
            :data-channel="channel.id"
        >
            <h5 class="c-notification-settings__title">{{ channel.title }}</h5>
            <p v-if="channel.fixed" class="c-notification-settings__fixed text-body-secondary">{{ labels.alwaysOn }}</p>
            <template v-else>
                <SelectField
                    v-model="channel.mode"
                    :attribute="`${channel.id}.mode`"
                    :label="labels.mode"
                    :options="channel.modes"
                />
                <CheckboxField
                    v-for="group in channel.groups"
                    :key="group.id"
                    v-model="group.enabled"
                    :attribute="`${channel.id}.group.${group.id}`"
                    :label="group.title"
                    :hint="group.description"
                    :disabled="group.fixed || channel.mode === 'off'"
                />
            </template>
        </section>

        <section v-if="spaces.enabled" class="c-notification-settings__spaces mb-4">
            <label :for="spacesInputId" class="form-label">{{ labels.spaces }}</label>
            <PickerFilterControl
                v-model="spaceIds"
                :filter="spacesFilter"
                :input-id="spacesInputId"
                :multiple="true"
                :search="searchSpaces"
                :resolve="resolveSpaces"
                :item-label="spaceLabel"
                icon="ti-users-group"
                block="c-space-filter"
            >
                <template #option="{ item }">
                    <SpaceImage v-bind="spaceImageProps(item)" :width="24" :link="false" />
                </template>
                <template #chip="{ item }">
                    <SpaceImage v-bind="spaceImageProps(item)" :width="18" :link="false" />
                </template>
            </PickerFilterControl>
        </section>

        <section v-if="summary" class="c-notification-settings__summary mb-4">
            <h5 class="c-notification-settings__title">{{ labels.summary }}</h5>
            <SelectField
                v-model="summary.interval"
                attribute="summary.interval"
                :label="labels.interval"
                :hint="labels.intervalHint"
                :options="summary.intervals"
            />
        </section>

        <div class="c-notification-settings__actions d-flex flex-wrap gap-2">
            <SubmitButton class="btn btn-primary">{{ labels.save }}</SubmitButton>
            <button
                v-if="scope === 'user' && resetUrl"
                type="button"
                class="btn btn-light ms-auto c-notification-settings__reset"
                :disabled="busy"
                @click="reset"
            >{{ labels.reset }}</button>
            <button
                v-if="scope === 'global' && resetAllUrl"
                type="button"
                class="btn btn-danger ms-auto c-notification-settings__reset-all"
                :disabled="busy"
                @click="resetAll"
            >{{ labels.resetAll }}</button>
        </div>
    </HumHubForm>
</template>

<script>
import { apiUrl, client, i18n, log, modal, status } from '@humhub/vue';

const clone = (value) => JSON.parse(JSON.stringify(value));

/**
 * The notification settings page — the account page (`scope = user`, the caller's settings) and
 * the administration page (`scope = global`, the defaults for everyone), mounted by
 * `notification\widgets\SettingsPage`.
 *
 * - Props: `initial` (the payload of `GET /api/v2/notification/settings`: `channels`, `spaces`,
 *   `summary` when the activity summary mails are on), `settingsUrl` (that endpoint, with
 *   `?scope=global` for the defaults), `resetUrl` (the caller's reset, user scope),
 *   `resetAllUrl` (reset every user, global scope, only for `ManageUsers` — else `null`),
 *   `scope` (`user`|`global`).
 * - A section per channel: the web list is always on and has no controls; every other channel a
 *   mode select and a checkbox per group — a `fixed` group (`direct`) checked and disabled, the
 *   groups disabled while the mode is `off`. Then the spaces whose new content notifies (the
 *   core `PickerFilterControl` searching the spaces the caller may see, with the space module's
 *   images, as `SpaceFilterControl` does — but resolving the selection from the payload instead
 *   of a request), the summary mail interval, Save and the reset buttons.
 * - Save sends `PATCH settingsUrl` (JSON: `{channels: [{id, mode, groups: [{id, enabled}]}],
 *   spaces: [ids], summary: {interval}}`); a `422` lands on the fields (unowned errors — the
 *   spaces, say — in the form's error summary), success goes to the status bar. A reset is
 *   confirmed, posts and replaces the state with the answer.
 *
 * @since 1.20
 */
export default {
    name: 'NotificationSettings',
    i18nCategories: ['NotificationModule.base', 'ActivityModule.base', 'base'],
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
            busy: false,
            spacesInputId: 'notificationsettings-spaces',
        };
    },
    created() {
        // Non-reactive: the short shapes of the spaces the payload names, which answer the
        // picker's resolve() without a request.
        this.knownSpaces = this.spacesOf(this.initial);
    },
    computed: {
        labels() {
            return {
                alwaysOn: i18n.t('NotificationModule.base', 'Always on'),
                mode: i18n.t('NotificationModule.base', 'Mode'),
                spaces: i18n.t('NotificationModule.base', 'Receive \'New Content\' Notifications for the following spaces'),
                summary: i18n.t('ActivityModule.base', 'E-Mail Summaries'),
                interval: i18n.t('ActivityModule.base', 'Interval'),
                intervalHint: i18n.t('ActivityModule.base', 'You will only receive an e-mail if there is something new.'),
                save: i18n.t('base', 'Save'),
                saved: i18n.t('base', 'Saved'),
                reset: i18n.t('NotificationModule.base', 'Reset to defaults'),
                resetConfirm: i18n.t('NotificationModule.base', 'Do you want to reset your notification settings to the defaults?'),
                resetAll: i18n.t('NotificationModule.base', 'Reset for all users'),
                resetAllConfirm: i18n.t('NotificationModule.base', 'Do you want to reset the settings concerning notifications for all users?'),
            };
        },
        spacesFilter() {
            return { key: 'spaces', label: this.labels.spaces, multiple: true };
        },
    },
    methods: {
        // The editable state of a payload: a copy, so a reset can replace it wholesale.
        stateOf(payload) {
            const selected = payload.spaces?.selected || [];
            return {
                channels: clone(payload.channels || []),
                spaces: { enabled: payload.spaces?.enabled !== false },
                spaceIds: selected.map((space) => String(space.id)),
                summary: payload.summary ? clone(payload.summary) : null,
            };
        },
        applyState(payload) {
            this.knownSpaces = this.spacesOf(payload);
            Object.assign(this, this.stateOf(payload));
        },
        spacesOf(payload) {
            return Object.fromEntries((payload.spaces?.selected || []).map((space) => [String(space.id), space]));
        },
        payload() {
            const data = {
                channels: this.channels.filter((channel) => !channel.fixed).map((channel) => ({
                    id: channel.id,
                    mode: channel.mode,
                    groups: channel.groups.filter((group) => !group.fixed).map((group) => ({ id: group.id, enabled: !!group.enabled })),
                })),
            };
            if (this.spaces.enabled) {
                data.spaces = this.spaceIds.map(Number);
            }
            if (this.summary) {
                data.summary = { interval: Number(this.summary.interval) };
            }
            return data;
        },
        request(method, url, data) {
            const cfg = data === undefined ? {} : { data: JSON.stringify(data), contentType: 'application/json' };
            return client[method](url, cfg);
        },
        save() {
            if (this.busy) {
                return Promise.resolve();
            }
            this.busy = true;
            this.$refs.form.clearErrors();

            return this.request('patch', this.settingsUrl, this.payload()).then((response) => {
                this.busy = false;
                this.applyState(response);
                status('success', this.labels.saved);
            }).catch((response) => {
                this.busy = false;
                if (response && response.status === 422) {
                    this.$refs.form.setErrors(response);
                    this.$refs.form.focusFirstError();
                } else {
                    log.error(response);
                }
            });
        },
        reset() {
            return this.confirmAndPost(this.resetUrl, { body: this.labels.resetConfirm }, true);
        },
        resetAll() {
            return this.confirmAndPost(this.resetAllUrl, { body: this.labels.resetAllConfirm }, false);
        },
        confirmAndPost(url, options, replace) {
            if (this.busy || !url) {
                return Promise.resolve();
            }
            return modal.confirm(options).then((confirmed) => {
                if (!confirmed) {
                    return null;
                }
                this.busy = true;
                this.$refs.form.clearErrors();
                return client.post(url).then((response) => {
                    this.busy = false;
                    if (replace) {
                        this.applyState(response);
                    }
                    status('success', this.labels.saved);
                }).catch((response) => {
                    this.busy = false;
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
