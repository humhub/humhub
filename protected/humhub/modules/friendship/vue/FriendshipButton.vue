<template>
    <a
        v-if="isNone"
        href="#"
        :class="[buttonClass, { disabled: busy }]"
        @click.prevent="request"
    ><span v-html="plusIconHtml"></span>{{ friendsLabel }}</a>

    <div v-else-if="isRequestReceived" :class="groupClass">
        <a
            href="#"
            :class="[stateClass, { disabled: busy }]"
            @click.prevent="accept"
        ><span v-html="clockIconHtml"></span>{{ acceptLabel }}</a>
        <button
            type="button"
            class="dropdown-toggle"
            :class="togglerClass"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
        ><span class="visually-hidden">{{ toggleDropdownLabel }}</span></button>
        <ul class="dropdown-menu">
            <li>
                <a
                    href="#"
                    class="dropdown-item"
                    @click.prevent="deny"
                ><span v-html="timesIconHtml"></span>&nbsp;&nbsp;{{ denyLabel }}</a>
            </li>
        </ul>
    </div>

    <a
        v-else-if="isRequestSent"
        href="#"
        :class="[stateClass, { disabled: busy }]"
        @click.prevent="withdraw"
    ><span v-html="clockIconHtml"></span>{{ pendingLabel }}</a>

    <a
        v-else-if="isFriends"
        href="#"
        :class="[stateClass, { disabled: busy }]"
        @click.prevent="end"
    ><span v-html="checkIconHtml"></span>{{ friendsLabel }}</a>
</template>

<script>
/**
 * The friendship button between the viewer and another user — send a request, answer one,
 * end a friendship — mounted by `friendship\widgets\FriendshipButton`.
 *
 * The space membership island's twin (see `space/vue/MembershipButton.vue`), and for the same
 * reason: the server-rendered button was re-rendered after every transition, so the
 * presentation of the context it was rendered in had to travel to the client and back — a
 * request-supplied option array the widget had to sanitize before echoing it into markup
 * (#8381, #8382 in the 1.19 line). Here presentation is props and state is data.
 *
 * ## The states, and what each offers
 *
 * | state             | rendered                                          | action        |
 * |-------------------|---------------------------------------------------|---------------|
 * | `none`            | "＋ Friends"                                       | POST          |
 * | `requestReceived` | "Accept Friend Request" + dropdown "Deny …"       | POST / DELETE |
 * | `requestSent`     | "Pending"                                          | DELETE        |
 * | `friends`         | "✓ Friends"                                        | DELETE        |
 *
 * Every one of them confirms first, exactly like the `data-action-confirm` attributes of the
 * widget it replaces — including the two phrasings that name the other user.
 *
 * ## Presentation props
 *
 * `buttonClass` carries the classes of the "add" button and `stateClass` those of the three
 * states that follow (`togglerClass`/`groupClass` the invite-style dropdown around the
 * received-request state). The widget's own properties feed them. Deliberately fewer knobs
 * than the option array had: it allowed a separate class per button, which no caller ever
 * used — both core call sites give the three state buttons one look.
 *
 * Icons come as rendered markup because the icon provider is pluggable — a client cannot
 * build them.
 *
 * ## The follow button next door
 *
 * Sending or accepting a request makes the viewer follow the other user
 * (`Friendship::add()`), so the sibling `UserFollowButton` — still a server-rendered widget —
 * has to flip. The island toggles it itself, by `data-content-container-id` +
 * `.followButton`/`.unfollowButton`, the same addressing `humhub.content.container.js` uses;
 * the server no longer sends `data-show-buttons`/`data-hide-buttons`. In the profile header
 * the follow control lives inside the controls menu (a link, not the button pair) whenever
 * friendship is enabled, so there is nothing to toggle there.
 *
 * @since 1.20
 */
import { apiUrl, client, i18n, log, modal } from '@humhub/vue';

const STATE_NONE = 'none';
const STATE_REQUEST_SENT = 'requestSent';
const STATE_REQUEST_RECEIVED = 'requestReceived';
const STATE_FRIENDS = 'friends';

export default {
    i18nCategories: ['FriendshipModule.base', 'base'],
    props: {
        userId: { type: Number, required: true },
        // Used in the confirmation dialogs, as trusted markup — see `userNameHtml`.
        userName: { type: String, default: '' },
        // FriendshipSerializer::state(), inlined by the widget. Fetched on mount when absent.
        initial: { type: Object, default: null },
        // Presentation, see the docblock.
        buttonClass: { type: String, default: 'btn btn-accent' },
        stateClass: { type: String, default: 'btn btn-accent active' },
        togglerClass: { type: String, default: 'btn btn-accent active' },
        groupClass: { type: String, default: 'btn-group' },
        // Server-rendered icon markup (the icon provider is pluggable, see `Icon`).
        checkIconHtml: { type: String, default: '' },
        plusIconHtml: { type: String, default: '' },
        clockIconHtml: { type: String, default: '' },
        timesIconHtml: { type: String, default: '' },
    },
    data() {
        return {
            state: this.initial ? this.initial.state : null,
            isFollowing: this.initial ? !!this.initial.isFollowing : false,
            busy: false,
        };
    },
    computed: {
        endpoint() {
            return apiUrl(`user/${this.userId}/friendship`);
        },
        isNone() {
            return this.state === STATE_NONE;
        },
        isRequestSent() {
            return this.state === STATE_REQUEST_SENT;
        },
        isRequestReceived() {
            return this.state === STATE_REQUEST_RECEIVED;
        },
        isFriends() {
            return this.state === STATE_FRIENDS;
        },
        // The display name as the legacy confirmation messages carried it: bold, inside an
        // otherwise translator-authored sentence. `i18n.t()` leaves markup in the message
        // alone (`ignoreTag`), and `modal.confirm()` renders the result as HTML — hence the
        // encoding here, on the one value that is not developer-controlled.
        userNameHtml() {
            return `<strong>${this.escape(this.userName)}</strong>`;
        },
        friendsLabel() {
            return i18n.t('FriendshipModule.base', 'Friends');
        },
        acceptLabel() {
            return i18n.t('FriendshipModule.base', 'Accept Friend Request');
        },
        denyLabel() {
            return i18n.t('FriendshipModule.base', 'Deny friend request');
        },
        pendingLabel() {
            return i18n.t('FriendshipModule.base', 'Pending');
        },
        toggleDropdownLabel() {
            return i18n.t('base', 'Toggle Dropdown');
        },
    },
    created() {
        if (this.state === null) {
            this.load();
        }
    },
    methods: {
        load() {
            client.get(this.endpoint).then((response) => {
                this.apply(response);
            }).catch((response) => {
                log.error(response, true);
                // Without a state the island renders nothing at all - a plain no-relation
                // state at least keeps a button around, and every transition is guarded
                // server side anyway.
                this.apply({ state: STATE_NONE });
            });
        },
        request() {
            return this.confirmThen(
                i18n.t(
                    'FriendshipModule.base',
                    'Would you like to send a friendship request to {userName}?',
                    { userName: this.userNameHtml },
                ),
                () => this.affirm(),
            );
        },
        accept() {
            return this.confirmThen(
                i18n.t('FriendshipModule.base', 'Would you like to accept the friendship request?'),
                () => this.affirm(),
            );
        },
        deny() {
            return this.confirmThen(
                i18n.t('FriendshipModule.base', 'Would you like to withdraw the friendship request?'),
                () => this.remove(),
            );
        },
        withdraw() {
            return this.confirmThen(
                i18n.t('FriendshipModule.base', 'Would you like to withdraw your friendship request?'),
                () => this.remove(),
            );
        },
        end() {
            return this.confirmThen(
                i18n.t(
                    'FriendshipModule.base',
                    'Would you like to end your friendship with {userName}?',
                    { userName: this.userNameHtml },
                ),
                () => this.remove(),
            );
        },
        confirmThen(body, action) {
            if (this.busy) {
                return Promise.resolve();
            }

            return modal.confirm({ body }).then((confirmed) => (confirmed ? action() : null));
        },
        /**
         * POST: sends the request, or accepts the one this user sent — the server decides
         * which from the current state.
         */
        affirm() {
            return this.mutate(() => client.post(this.endpoint));
        },
        /**
         * DELETE: withdraws, denies or ends.
         */
        remove() {
            return this.mutate(() => client.del(this.endpoint));
        },
        /**
         * Every transition answers the new state, so there is nothing to derive here: apply
         * it, then align what depends on it.
         */
        mutate(request) {
            if (this.busy) {
                return Promise.resolve();
            }

            this.busy = true;

            return request().then((response) => {
                this.busy = false;
                this.apply(response);
            }).catch((response) => {
                this.busy = false;
                log.error(response, true);
            });
        },
        apply(state) {
            this.state = state.state;
            this.isFollowing = !!state.isFollowing;

            this.syncFollowButtons();
        },
        /**
         * The server-rendered follow/unfollow pair of this user: exactly one of them is
         * shown. Absent (profile header, guests) means nothing to do.
         */
        syncFollowButtons() {
            const selector = `[data-content-container-id="${this.userId}"]`;

            this.toggle(document.querySelectorAll(`${selector}.followButton`), !this.isFollowing);
            this.toggle(document.querySelectorAll(`${selector}.unfollowButton`), this.isFollowing);
        },
        toggle(elements, visible) {
            elements.forEach((element) => {
                element.classList.toggle('d-none', !visible);
            });
        },
        escape(value) {
            const element = document.createElement('div');
            element.textContent = String(value);

            return element.innerHTML;
        },
    },
};
</script>
