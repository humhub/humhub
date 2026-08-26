<template>
    <div class="activity-entry" :data-activity-id="activity.id">
        <component :is="activity.url ? 'a' : 'div'" :href="activity.url || null">
            <div class="d-flex activity-box-entry">
                <div class="flex-shrink-0 me-3 pt-1 img-profile-space">
                    <UserImage
                        v-if="activity.user"
                        v-bind="activity.user"
                        :width="32"
                        :link="false"
                    />
                    <SpaceImage
                        v-if="showSpace && activity.space"
                        v-bind="activity.space"
                        :width="20"
                        :link="false"
                        class="img-space"
                    />
                </div>

                <div class="flex-grow-1 text-break">
                    <!-- eslint-disable-next-line vue/no-v-html -- server-rendered sentence, see docblock -->
                    <span v-html="activity.message"></span>
                    <br>
                    <time
                        class="tt time timeago"
                        data-ui-addition="timeago"
                        :datetime="activity.createdAt"
                        :title="absoluteTime"
                    >{{ absoluteTime }}</time>
                </div>
            </div>
        </component>
    </div>
</template>

<script>
/**
 * One activity, the Vue counterpart of `@activity/views/layouts/web.php`.
 *
 * Markup parity with that layout, because theme CSS styles entries through it: the
 * `div.activity-entry` carrying `data-activity-id`, the optional link around it, the
 * `d-flex.activity-box-entry` row, the author avatar with the space badge overlaid, the
 * sentence and the relative timestamp (`_activities.scss` pads `.activity-entry > .d-flex`
 * and zeroes the entry itself). The one deviation: an activity without a URL is wrapped in a
 * plain `div` instead of nothing at all, which no rule distinguishes.
 *
 * ## `v-html` for the sentence
 *
 * `activity.message` is `BaseActivity::asWeb()` — server-rendered, module-defined, localized
 * markup (`<strong>`-wrapped display names, links into the content), and for a grouped entry
 * it is the sentence naming the group. It is the one field of the payload that IS html, and
 * rendering it as text would show markup to the user. Trust boundary: it is composed
 * server-side by the activity class from `Html::encode()`d values, exactly like the
 * server-rendered entry it replaces — no client input passes through here. See
 * `ActivitySerializer` for the shape.
 *
 * ## The space badge is the caller's decision, not the payload's
 *
 * `space` travels with every entry; `showSpace` is false inside a container, where naming it on
 * every row would be redundant — the same rule the old layout expressed by checking whether it
 * rendered inside a `ContentContainerController`.
 *
 * The timestamp uses the platform's own `timeago` addition through the `[data-ui-addition]`
 * mechanism (see `docs/develop/ui-js-vuejs-interop.md`), so the initial absolute text is only
 * visible for an instant.
 *
 * @since 1.20
 */
export default {
    props: {
        // Serialized activity (ActivitySerializer::activity()).
        activity: { type: Object, required: true },
        // Whether the space badge is rendered - false inside a container.
        showSpace: { type: Boolean, default: true },
    },
    computed: {
        absoluteTime() {
            return this.activity.createdAt ? new Date(this.activity.createdAt).toLocaleString() : '';
        },
    },
};
</script>
