<template>
    <a
        ref="toggle"
        href="#"
        id="icon-notifications"
        data-bs-toggle="dropdown"
        :aria-label="openLabel"
        @click.prevent
    ><span :class="{ 'animated swing': animate }" v-html="bellIconHtml"></span></a>

    <span
        v-if="unseenCount > 0"
        id="badge-notifications"
        class="text-bg-danger badge badge-notifications"
    >{{ unseenCount }}</span>

    <ul id="dropdown-notifications" class="dropdown-menu">
        <li>
            <div class="dropdown-header">
                <div class="arrow"></div>
                {{ headerLabel }}
                <div class="dropdown-header-actions">
                    <button
                        v-if="unseenCount > 0"
                        type="button"
                        id="mark-seen-link"
                        class="btn-light btn btn-icon-only btn-sm"
                        :aria-label="markSeenLabel"
                        :title="markSeenLabel"
                        @click="markAsSeen"
                    ><span v-html="checkIconHtml"></span></button>
                    <a
                        class="btn-light btn btn-icon-only btn-sm"
                        :href="settingsUrl"
                        :aria-label="settingsLabel"
                        :title="settingsLabel"
                    ><span v-html="cogIconHtml"></span></a>
                </div>
            </div>
        </li>
        <li>
            <NotificationList
                ref="list"
                class="dropdown-item"
                :initial="initial"
                :page-size="pageSize"
                @loaded="onLoaded"
            />
        </li>
        <li>
            <div class="dropdown-footer">
                <a class="btn btn-light col-lg-12" :href="overviewUrl">{{ showAllLabel }}</a>
            </div>
        </li>
    </ul>
</template>

<script>
/**
 * The notification menu of the top navigation — bell, unread badge and dropdown — mounted by
 * `notification\widgets\Overview` as `<notification-menu id="notification_widget">`.
 *
 * The island covers the WHOLE menu item on purpose: the unread count drives the badge, the
 * bell animation, the visibility of the "mark all as seen" action and the document title, and
 * having one owner for it is what replaces `humhub.notification.js`'s DOM poking across four
 * elements. Ids and classes are the legacy ones (`#icon-notifications`, `#badge-notifications`,
 * `#mark-seen-link`, `#dropdown-notifications`), because theme CSS and the product tour
 * (`tour/config/tour-interface.php`) address them.
 *
 * ## Live updates
 *
 * A notification arriving through the live poll carries only ids
 * (`{notificationId, notificationGroup}`, see `notification\targets\WebTarget`), so the island
 * dedupes against what its list already shows — by id and by group key, the same two keys the
 * legacy dropdown tracked — and then refreshes: the count from the response, the entries only
 * while the dropdown is actually open (a closed dropdown reloads on open anyway).
 *
 * ## Staying current across pjax navigations
 *
 * The island survives a pjax navigation (it lives outside the swapped container), so its count
 * would keep whatever it had when the page was first rendered. `UpdateNotificationCount`, the
 * layout addon that always solved this, now pushes the fresh count as the
 * `humhub:notification:setCount` event instead of poking the legacy JS module - no request per
 * navigation.
 *
 * ## Compatibility with the mail module
 *
 * Two legacy events stay part of the contract, because `humhub/mail` (and its forks) rely on
 * them:
 *
 * - `humhub:notification:updateCount` is triggered on every count change (mail listens).
 * - `humhub:modules:notification:UpdateTitleNotificationCount` is listened to, and mail's own
 *   unread message count is added to the document title through
 *   `humhub.modules.mail.notification.getNewMessageCount()` — the same lookup
 *   `humhub.notification.js` did. The base title comes from the platform's own per-page state
 *   (`pageTitle()`), so it survives pjax navigation.
 *
 * @since 1.20
 */
import { events, i18n, log, pageTitle } from '@humhub/vue';
import NotificationList from './components/NotificationList.vue';
import { markAllAsSeen } from './components/notificationApi.js';

const LIVE_EVENT = 'humhub:modules:notification:live:NewNotification';
const UPDATE_TITLE_EVENT = 'humhub:modules:notification:UpdateTitleNotificationCount';
const UPDATE_COUNT_EVENT = 'humhub:notification:updateCount';
// Server-pushed count, see `notification\widgets\UpdateNotificationCount`: a pjax navigation
// does not re-render the top menu, so the island is told the fresh count instead of refetching
// it on every navigation.
const SET_COUNT_EVENT = 'humhub:notification:setCount';

export default {
    components: { NotificationList },
    // Preloaded for this island AND for the shared components it nests (UserImage's own alt
    // phrase from `base`, `UserModule.base`), since the mounter only preloads the categories of
    // the top-level island component.
    i18nCategories: ['NotificationModule.base', 'UserModule.base', 'base'],
    props: {
        // First page, inlined by the widget: {results, unseenCount, nextCursor}.
        initial: { type: Object, default: null },
        overviewUrl: { type: String, required: true },
        settingsUrl: { type: String, required: true },
        // Server-rendered icon markup (the icon provider is pluggable, see `Icon`).
        bellIconHtml: { type: String, default: '' },
        checkIconHtml: { type: String, default: '' },
        cogIconHtml: { type: String, default: '' },
        pageSize: { type: Number, default: 6 },
    },
    data() {
        return {
            unseenCount: this.initial ? Number(this.initial.unseenCount || 0) : 0,
            open: false,
            animate: false,
        };
    },
    computed: {
        openLabel() {
            return i18n.t('NotificationModule.base', 'Open the notification dropdown menu');
        },
        headerLabel() {
            return i18n.t('NotificationModule.base', 'Notifications');
        },
        markSeenLabel() {
            return i18n.t('NotificationModule.base', 'Mark all as seen');
        },
        settingsLabel() {
            return i18n.t('NotificationModule.base', 'Notification Settings');
        },
        showAllLabel() {
            return i18n.t('NotificationModule.base', 'Show all notifications');
        },
    },
    mounted() {
        this.$refs.toggle.addEventListener('show.bs.dropdown', this.onShow);
        this.$refs.toggle.addEventListener('hidden.bs.dropdown', this.onHidden);
        events.on(LIVE_EVENT, this.onLiveNotification);
        events.on(UPDATE_TITLE_EVENT, this.updateTitle);
        events.on(SET_COUNT_EVENT, this.onSetCount);
        this.updateTitle();
    },
    beforeUnmount() {
        this.$refs.toggle.removeEventListener('show.bs.dropdown', this.onShow);
        this.$refs.toggle.removeEventListener('hidden.bs.dropdown', this.onHidden);
        events.off(LIVE_EVENT, this.onLiveNotification);
        events.off(UPDATE_TITLE_EVENT, this.updateTitle);
        events.off(SET_COUNT_EVENT, this.onSetCount);
    },
    methods: {
        onShow() {
            this.open = true;
            // Always a fresh page on open, like the legacy dropdown - what is listed may be
            // minutes old, and its unread markers even older.
            this.$refs.list.reload();
        },
        onHidden() {
            this.open = false;
        },
        onLoaded(response) {
            this.setCount(response.unseenCount);
        },
        onSetCount(event, count) {
            this.setCount(count);
        },
        /**
         * Live events carry ids only. Anything already listed is not news; anything else bumps
         * the count, and refreshes the list if the user is looking at it.
         */
        onLiveNotification(event, liveEvents) {
            const fresh = (liveEvents || []).filter((liveEvent) => {
                const data = liveEvent.data || {};
                return !this.$refs.list.has({
                    id: Number(data.notificationId),
                    groupKey: data.notificationGroup || null,
                });
            });

            if (!fresh.length) {
                return;
            }

            if (this.open) {
                this.$refs.list.reload();
                return;
            }

            this.setCount(this.unseenCount + fresh.length);
        },
        markAsSeen() {
            return markAllAsSeen().then(() => {
                this.setCount(0);
                // Reaches a notification overview island on the same page (its list still shows
                // the unread markers) - setCount() itself only fires when the count changed, so
                // this is the explicit signal.
                events.trigger(SET_COUNT_EVENT, [0]);
                if (this.open) {
                    this.$refs.list.reload();
                }
            }).catch((response) => {
                log.error(response, true);
            });
        },
        setCount(count) {
            const next = Number(count) || 0;

            if (next === this.unseenCount) {
                return;
            }

            // Retrigger the bell animation only when something arrived.
            if (next > this.unseenCount) {
                this.animate = false;
                this.$nextTick(() => {
                    this.animate = true;
                });
            }

            this.unseenCount = next;
            events.trigger(UPDATE_COUNT_EVENT, [next]);
            this.updateTitle();
        },
        /**
         * `(3) Dashboard - HumHub` - own unread count plus the mail module's unread messages,
         * the exact arithmetic `humhub.notification.js` did.
         */
        updateTitle() {
            const base = pageTitle() || document.title;
            let count = this.unseenCount;

            const mail = window.humhub && window.humhub.modules && window.humhub.modules.mail;
            if (mail && mail.notification && typeof mail.notification.getNewMessageCount === 'function') {
                count += Number(mail.notification.getNewMessageCount()) || 0;
            }

            document.title = count > 0 ? '(' + count + ') ' + base : base;
        },
    },
};
</script>
