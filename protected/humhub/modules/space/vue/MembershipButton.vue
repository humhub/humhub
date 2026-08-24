<template>
    <a
        v-if="isNone && canJoin"
        href="#"
        :class="[buttonClass, { disabled: busy }]"
        @click.prevent="onJoinClick"
    >{{ joinLabel }}</a>

    <div v-else-if="isInvited" :class="groupClass">
        <a
            href="#"
            :class="[buttonClass, { disabled: busy }]"
            @click.prevent="affirm()"
        >{{ acceptInviteLabel }}</a>
        <button
            type="button"
            class="dropdown-toggle"
            :class="togglerClass"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
        ><span class="sr-only">{{ toggleDropdownLabel }}</span></button>
        <ul class="dropdown-menu">
            <li>
                <a href="#" class="dropdown-item" @click.prevent="remove()">{{ declineInviteLabel }}</a>
            </li>
        </ul>
    </div>

    <a
        v-else-if="isApplicant"
        href="#"
        :class="[pendingClass, { disabled: busy }]"
        @click.prevent="withdraw"
    ><span v-html="clockIconHtml"></span>{{ pendingLabel }}</a>

    <template v-else-if="isMember && showMemberState">
        <a
            v-if="canLeave"
            href="#"
            :class="[memberClass, { disabled: busy }]"
            @click.prevent="leave"
        ><span v-html="checkIconHtml"></span>{{ memberLabel }}</a>
        <a
            v-else
            :href="spaceUrl"
            :class="memberClass"
        ><span v-html="isOwner ? userIconHtml : checkIconHtml"></span>{{ isOwner ? ownerLabel : memberLabel }}</a>
    </template>

    <UiModal v-model:show="showRequest" @opened="focusMessage">
        <template #header="{ titleId }">
            <h5 class="modal-title" :id="titleId" v-html="requestTitle"></h5>
            <button type="button" class="btn-close" :aria-label="closeLabel" @click="showRequest = false"></button>
        </template>

        <div v-if="requestSent" class="text-center">{{ requestSentLabel }}</div>
        <HumHubForm
            v-else
            ref="requestForm"
            model-name="RequestMembershipForm"
            :busy="busy"
            @submit="sendRequest"
        >
            <p>{{ requestIntroLabel }}</p>
            <TextareaField
                ref="messageField"
                attribute="message"
                :label="messageLabel"
                :placeholder="messagePlaceholder"
                required
                v-model="message"
            />
        </HumHubForm>

        <template #footer>
            <button
                v-if="requestSent"
                type="button"
                class="btn btn-light"
                @click="showRequest = false"
            >{{ closeLabel }}</button>
            <template v-else>
                <button type="button" class="btn btn-light" @click="showRequest = false">{{ cancelLabel }}</button>
                <button
                    type="button"
                    class="btn btn-primary"
                    :disabled="busy"
                    @click="sendRequest"
                >{{ sendLabel }}</button>
            </template>
        </template>
    </UiModal>
</template>

<script>
/**
 * The space membership button — join a space, apply for membership, answer an invite, leave
 * again — mounted by `space\widgets\MembershipButton`.
 *
 * ## Why this is an island
 *
 * The server-rendered button used to be re-rendered by the server after every transition, and
 * the presentation of the context it was rendered in (`btn-sm` in the directory, no member
 * state in the space header, ...) therefore had to travel to the client and back to survive
 * that round trip — a request-supplied option array the widget had to sanitize before echoing
 * it into markup (#8381, #8382 in the 1.19 line).
 *
 * Here, presentation is props and state is data: the island renders every state itself from
 * `space\serializers\MembershipSerializer::state()` (inlined by the widget for the first
 * paint, refetched otherwise), and each transition is one API call whose response IS the new
 * state. Nothing about how the button looks ever leaves the server.
 *
 * ## The states, and what each offers
 *
 * | state       | rendered                                    | action                        |
 * |-------------|---------------------------------------------|-------------------------------|
 * | `none`      | "Join" (only when `canJoin`)                | POST, or the request modal    |
 * | `invited`   | "Accept Invite" + dropdown "Decline Invite" | POST / DELETE                 |
 * | `applicant` | "Pending"                                   | DELETE (confirmed)            |
 * | `member`    | "Member"/"Owner", only if `showMemberState` | DELETE (confirmed) or nothing |
 *
 * A space that needs approval opens the request modal instead of posting directly — the same
 * message the legacy `RequestMembershipForm` collected, validated by the same model on the
 * server (a 422 renders on the field, see `HumHubForm`).
 *
 * A state with nothing to offer (a non-member who may not join, a member where the member
 * state is hidden) renders nothing at all, exactly like the widget it replaces.
 *
 * ## Presentation props
 *
 * `buttonClass`/`pendingClass`/`memberClass`/`togglerClass`/`groupClass` carry the classes of
 * each state's button, and `showMemberState` whether the member state is rendered at all (the
 * space header hides it — leaving happens through its controls menu — the space directory shows
 * it). They are properties of the widget, which is where a call site or a theme
 * (`MembershipButton::EVENT_INIT`) sets them.
 *
 * Icons come as rendered markup (`checkIconHtml`, `clockIconHtml`, `userIconHtml`) because the
 * icon provider is pluggable — a client cannot build them.
 *
 * ## `reloadOnJoin`
 *
 * Becoming a member of a space changes the whole page around this button (its menu, what the
 * viewer may see, the dashboard). Where that matters — the space header — the legacy button
 * therefore submitted a plain form and let the server redirect back; `reloadOnJoin` is that
 * behavior, and it is deliberately limited to the transition INTO membership: leaving,
 * withdrawing and declining never reloaded anything.
 *
 * ## The follow button next door
 *
 * Following is offered to non-members only, so a membership change flips the sibling
 * `FollowButton` — which is still a server-rendered widget. The legacy button had the server
 * tell it what to do (`data-show-buttons`/`data-hide-buttons` on the re-rendered markup); the
 * island toggles the buttons of its own space itself, addressed exactly like
 * `humhub.content.container.js` addresses them (by `data-content-container-id` and the
 * `.followButton`/`.unfollowButton` classes).
 *
 * @since 1.20
 */
import { apiUrl, client, i18n, log, modal } from '@humhub/vue';

const STATE_NONE = 'none';
const STATE_INVITED = 'invited';
const STATE_APPLICANT = 'applicant';
const STATE_MEMBER = 'member';

export default {
    // `base` covers the modal's own Cancel/Close labels, `SpaceModule.base` everything else.
    i18nCategories: ['SpaceModule.base', 'base'],
    props: {
        spaceId: { type: Number, required: true },
        // Used in the confirmation dialogs ("... membership in Space <name>?"), as trusted
        // markup — see `spaceNameHtml`.
        spaceName: { type: String, default: '' },
        // Where the non-leavable member state links to, i.e. the space itself.
        spaceUrl: { type: String, default: '#' },
        // MembershipSerializer::state(), inlined by the widget. Fetched on mount when absent.
        initial: { type: Object, default: null },
        // Presentation, see the docblock.
        buttonClass: { type: String, default: 'btn btn-accent' },
        pendingClass: { type: String, default: 'btn btn-accent active' },
        memberClass: { type: String, default: 'btn btn-accent active' },
        togglerClass: { type: String, default: 'btn btn-accent' },
        groupClass: { type: String, default: 'btn-group' },
        showMemberState: { type: Boolean, default: false },
        reloadOnJoin: { type: Boolean, default: false },
        // Server-rendered icon markup (the icon provider is pluggable, see `Icon`).
        checkIconHtml: { type: String, default: '' },
        clockIconHtml: { type: String, default: '' },
        userIconHtml: { type: String, default: '' },
    },
    data() {
        return {
            state: this.initial ? this.initial.state : null,
            canJoin: this.initial ? !!this.initial.canJoin : false,
            needsApproval: this.initial ? !!this.initial.needsApproval : false,
            canLeave: this.initial ? !!this.initial.canLeave : false,
            isOwner: this.initial ? !!this.initial.isOwner : false,
            isFollowing: this.initial ? !!this.initial.isFollowing : false,
            busy: false,
            showRequest: false,
            requestSent: false,
            message: '',
        };
    },
    computed: {
        endpoint() {
            return apiUrl(`space/${this.spaceId}/membership`);
        },
        isNone() {
            return this.state === STATE_NONE;
        },
        isInvited() {
            return this.state === STATE_INVITED;
        },
        isApplicant() {
            return this.state === STATE_APPLICANT;
        },
        isMember() {
            return this.state === STATE_MEMBER;
        },
        // The space name as the legacy confirmation messages carried it: bold, inside an
        // otherwise translator-authored sentence. `i18n.t()` leaves markup in the message
        // alone (`ignoreTag`), so both halves are rendered as HTML by `modal.confirm()` -
        // hence the encoding here, on the one value that is not developer-controlled.
        spaceNameHtml() {
            return `<strong>${this.escape(this.spaceName)}</strong>`;
        },
        joinLabel() {
            return i18n.t('SpaceModule.base', 'Join');
        },
        acceptInviteLabel() {
            return i18n.t('SpaceModule.base', 'Accept Invite');
        },
        declineInviteLabel() {
            return i18n.t('SpaceModule.base', 'Decline Invite');
        },
        pendingLabel() {
            return i18n.t('SpaceModule.base', 'Pending');
        },
        memberLabel() {
            return i18n.t('SpaceModule.base', 'Member');
        },
        ownerLabel() {
            return i18n.t('SpaceModule.base', 'Owner');
        },
        toggleDropdownLabel() {
            return i18n.t('base', 'Toggle Dropdown');
        },
        requestTitle() {
            return i18n.t('SpaceModule.base', '<strong>Request</strong> Membership');
        },
        requestIntroLabel() {
            return i18n.t(
                'SpaceModule.base',
                'Access to this Space is restricted. Please introduce yourself to become a member.',
            );
        },
        requestSentLabel() {
            return i18n.t('SpaceModule.base', 'Your request was successfully submitted to the space administrators.');
        },
        messageLabel() {
            return i18n.t('SpaceModule.base', 'Your Message');
        },
        messagePlaceholder() {
            return i18n.t('SpaceModule.base', 'I want to become a member because...');
        },
        sendLabel() {
            return i18n.t('SpaceModule.base', 'Send');
        },
        cancelLabel() {
            return i18n.t('base', 'Cancel');
        },
        closeLabel() {
            return i18n.t('base', 'Close');
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
                // Without a state the island renders nothing at all - a plain non-member
                // state at least keeps a join button around, and joining is guarded server
                // side anyway.
                this.apply({ state: STATE_NONE });
            });
        },
        /**
         * Joining a space that approves memberships means introducing yourself first.
         */
        onJoinClick() {
            if (this.needsApproval) {
                this.openRequest();
                return;
            }

            this.affirm();
        },
        openRequest() {
            this.message = '';
            this.requestSent = false;
            this.showRequest = true;
        },
        focusMessage() {
            if (this.$refs.messageField) {
                this.$refs.messageField.focus();
            }
        },
        /**
         * POST: joins, applies or accepts the invite — which one follows from the state and
         * the space's join policy, and the server decides it (see the API controller).
         */
        affirm(data) {
            return this.mutate(() => client.post(this.endpoint, data ? { data } : undefined));
        },
        /**
         * DELETE: leaves, withdraws the application or declines the invite.
         */
        remove() {
            return this.mutate(() => client.del(this.endpoint));
        },
        withdraw() {
            return this.confirm({
                body: i18n.t(
                    'SpaceModule.base',
                    'Would you like to withdraw your request to join Space {spaceName}?',
                    { spaceName: this.spaceNameHtml },
                ),
            }).then((confirmed) => (confirmed ? this.remove() : null));
        },
        leave() {
            return this.confirm({
                header: i18n.t('SpaceModule.base', '<strong>Leave</strong> Space'),
                body: i18n.t(
                    'SpaceModule.base',
                    'Would you like to end your membership in Space {spaceName}?',
                    { spaceName: this.spaceNameHtml },
                ),
                confirmText: i18n.t('SpaceModule.base', 'Leave'),
            }).then((confirmed) => (confirmed ? this.remove() : null));
        },
        confirm(options) {
            if (this.busy) {
                return Promise.resolve(false);
            }

            return modal.confirm(options);
        },
        sendRequest() {
            if (this.busy) {
                return Promise.resolve();
            }

            this.$refs.requestForm.clearErrors();

            return this.affirm({ message: this.message }).then(() => {
                if (this.isApplicant) {
                    // Same acknowledgement the legacy modal replaced its form with.
                    this.requestSent = true;
                }
            });
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
            const wasMember = this.isMember;

            return request().then((response) => {
                this.busy = false;
                this.apply(response);

                if (!wasMember && this.isMember && this.reloadOnJoin) {
                    // Deliberately no state juggling afterwards - the page is on its way out.
                    this.reloadPage();
                }
            }).catch((response) => {
                this.busy = false;

                const errors = response ? response.errors : null;
                if (response && response.status === 422 && errors && this.$refs.requestForm) {
                    this.$refs.requestForm.setErrors({ errors });
                    return;
                }

                log.error(response, true);
            });
        },
        /** Own method so a test can watch for it instead of navigating. */
        reloadPage() {
            window.location.reload();
        },
        apply(state) {
            this.state = state.state;
            this.canJoin = !!state.canJoin;
            this.needsApproval = !!state.needsApproval;
            this.canLeave = !!state.canLeave;
            this.isOwner = !!state.isOwner;
            this.isFollowing = !!state.isFollowing;

            this.syncFollowButtons();
        },
        /**
         * The server-rendered follow/unfollow pair of this space: hidden for a member,
         * otherwise exactly one of them is shown. Absent (guests, invisible spaces) means
         * nothing to do.
         */
        syncFollowButtons() {
            const selector = `[data-content-container-id="${this.spaceId}"]`;
            const follow = document.querySelectorAll(`${selector}.followButton`);
            const unfollow = document.querySelectorAll(`${selector}.unfollowButton`);

            if (this.state === STATE_MEMBER) {
                this.toggle(follow, false);
                this.toggle(unfollow, false);
                return;
            }

            this.toggle(follow, !this.isFollowing);
            this.toggle(unfollow, this.isFollowing);
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
