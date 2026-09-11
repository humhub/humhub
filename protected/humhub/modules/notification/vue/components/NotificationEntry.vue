<template>
    <a
        class="d-flex"
        :class="{ new: notification.isNew }"
        :href="notification.url"
        :data-notification-id="notification.id"
        :data-notification-group="notification.groupKey || ''"
    >
        <div class="flex-shrink-0 me-3 pt-1 img-profile-space">
            <UserImage
                v-if="notification.originator"
                v-bind="notification.originator"
                :width="32"
                :link="false"
            />
            <SpaceImage
                v-if="notification.space"
                v-bind="notification.space"
                :width="20"
                :link="false"
                class="img-space"
            />
        </div>

        <div class="flex-grow-1">
            <!-- eslint-disable-next-line vue/no-v-html -- server-rendered sentence, see docblock -->
            <span v-html="notification.html"></span>
            <br>
            <time
                class="tt time timeago"
                data-ui-addition="timeago"
                :datetime="notification.createdAt"
                :title="absoluteTime"
            >{{ absoluteTime }}</time>
        </div>

        <div class="flex-shrink-0 ms-2 order-last text-center">
            <span v-if="notification.isNew" class="badge badge-new"></span>
        </div>
    </a>
</template>

<script>
/**
 * One notification, the Vue counterpart of `@notification/views/layouts/web.php`.
 *
 * Markup parity with that layout, because both the dropdown and the overview page style their
 * entries through it: the `d-flex`/`new` anchor with `data-notification-id`/
 * `data-notification-group` (the ids a client dedupes live events against), the
 * originator avatar with the space badge overlaid, the sentence, the relative timestamp and the
 * unread badge.
 *
 * ## `v-html` for the sentence
 *
 * `notification.html` is `BaseNotification::html()` — server-rendered, module-defined,
 * localized markup (`<strong>`-wrapped display names, links into the content). It is the one
 * field of the payload that IS html, and rendering it as text would show markup to the user.
 * Trust boundary: it is composed server-side from `Html::encode()`d values by the notification
 * class, exactly like the server-rendered entry it replaces — no client input passes through
 * here. See `NotificationSerializer` for the shape.
 *
 * The timestamp uses the platform's own `timeago` addition through the same
 * `[data-ui-addition]` mechanism the comment island uses (see
 * `docs/develop/ui-js-vuejs-interop.md`, "`timeago` and `[data-ui-addition]`"), so the initial
 * absolute text is only visible for an instant.
 *
 * @since 1.20
 */
export default {
    props: {
        // Serialized notification (NotificationSerializer::notification()).
        notification: { type: Object, required: true },
    },
    computed: {
        absoluteTime() {
            return this.notification.createdAt ? new Date(this.notification.createdAt).toLocaleString() : '';
        },
    },
};
</script>
