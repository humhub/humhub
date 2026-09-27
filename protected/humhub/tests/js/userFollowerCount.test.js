import { beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import UserFollowButton from '../../modules/user/vue/UserFollowButton.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');
// Its init() subscribes to `user:follow-changed` (the harness calls it at registration).
await import('../../modules/user/resources/js/humhub.user.js');

const FOLLOW_CHANGED = 'user:follow-changed';

// The profile header's counters, as ProfileHeaderCounterSet renders them (counterSetHeader.php).
const renderCounters = () => {
    document.body.replaceChildren();
    globalThis.jQuery(
        '<div class="statistics">'
        + '<a href="#" data-user-follower-count="7"><div class="entry"><span class="count">3</span><br><span class="title">Followers</span></div></a>'
        + '<a href="#"><div class="entry"><span class="count">5</span><br><span class="title">Following</span></div></a>'
        + '</div>',
    ).appendTo(document.body);
};
const counts = () => Array.from(document.querySelectorAll('.count')).map((node) => node.textContent);
const trigger = (payload) => globalThis.humhubStubs.event.trigger(FOLLOW_CHANGED, [payload]);

/**
 * humhub.user.js keeps the profile header's follower counter current when a follow button of
 * the page (the header menu's UserFollowButton island) follows or unfollows the user.
 */
describe('profile header follower count', () => {
    beforeEach(() => {
        renderCounters();
        globalThis.humhub.modules.url.config.template = '/__route__';
    });

    it("takes over the follower count of the user's follow-changed event", () => {
        trigger({ userId: 7, isFollowing: true, followerCount: 4, canFollow: true });

        expect(counts()).toEqual(['4', '5']);
    });

    it('leaves the counters of another user and a count of a disabled follow feature alone', () => {
        trigger({ userId: 8, isFollowing: true, followerCount: 9, canFollow: true });
        trigger({ userId: 7, isFollowing: false, followerCount: null, canFollow: false });

        expect(counts()).toEqual(['3', '5']);
    });

    it('shortens a large count', () => {
        trigger({ userId: 7, isFollowing: true, followerCount: 1234, canFollow: true });

        expect(counts()[0]).toBe('1.2K');
    });

    it('follows the follow button of the page', async () => {
        globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ isFollowing: true, followerCount: 4, canFollow: true }));
        const host = document.createElement('div');
        document.body.appendChild(host);
        const wrapper = mount(UserFollowButton, {
            props: { userId: 7, initial: { isFollowing: false, followerCount: 3, canFollow: true } },
            attachTo: host,
        });

        await wrapper.find('button').trigger('click');
        await flushPromises();

        expect(counts()).toEqual(['4', '5']);
        wrapper.unmount();
    });
});
