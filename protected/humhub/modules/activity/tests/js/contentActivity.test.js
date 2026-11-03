import { beforeEach, describe, expect, it } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import ContentActivity from '../../vue/ContentActivity.vue';
import UserImage from '../../../../modules/user/vue/UserImage.vue';
import SpaceImage from '../../../../modules/space/vue/SpaceImage.vue';

await import('../../../../resources/js/humhub/humhub.url.js');
await import('../../../../resources/js/humhub/humhub.vue.js');

const mountOptions = (props = {}) => ({
    props: { contentId: 42, ...props },
    global: {
        // See activityBox.test.js: the runtime registers `v-additions` on the real app.
        directives: { additions: { mounted() {}, updated() {} } },
        components: { UserImage, SpaceImage },
    },
});

const activity = (id) => ({
    id,
    key: 'k' + id,
    message: '<strong>Jane</strong> commented on a file',
    url: '/s/x/',
    createdAt: '2026-08-20T10:00:00+00:00',
    groupCount: 1,
    user: { id: 2, guid: 'u2', displayName: 'Jane', url: '/u/jane/', imageUrl: '/u/jane/image.jpg' },
    space: null,
});

let getCalls;
let pages;

beforeEach(() => {
    getCalls = [];
    pages = [];
    globalThis.humhubStubs.client.get = (url) => {
        getCalls.push(url);
        return Promise.resolve(pages.shift() ?? { results: [], nextCursor: null });
    };
});

describe('ContentActivity', () => {
    it('loads the first page of the content\'s history on mount', async () => {
        pages.push({ results: [activity(1), activity(2)], nextCursor: null });
        const wrapper = mount(ContentActivity, mountOptions({ pageSize: 5 }));
        await flushPromises();

        expect(getCalls).toHaveLength(1);
        expect(getCalls[0]).toContain('contentId=42');
        expect(getCalls[0]).toContain('limit=5');
        expect(wrapper.findAll('.activity-entry')).toHaveLength(2);
        expect(wrapper.find('button').exists()).toBe(false);
    });

    it('pages on with the cursor the previous page returned', async () => {
        pages.push({ results: [activity(1)], nextCursor: 'a1:abc' }, { results: [activity(2)], nextCursor: null });
        const wrapper = mount(ContentActivity, mountOptions());
        await flushPromises();

        await wrapper.find('button').trigger('click');
        await flushPromises();

        expect(getCalls[1]).toContain('cursor=a1%3Aabc');
        expect(wrapper.findAll('.activity-entry')).toHaveLength(2);
        expect(wrapper.find('button').exists()).toBe(false);
    });

    it('says when there is nothing yet', async () => {
        const wrapper = mount(ContentActivity, mountOptions());
        await flushPromises();

        expect(wrapper.text()).toContain('There are no activities yet.');
    });
});
