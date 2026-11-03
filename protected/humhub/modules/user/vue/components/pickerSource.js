import { apiUrl, client } from '@humhub/vue';

/**
 * The `user` picker source (see docs/develop/ui-js-vuejs-picker.md, "Sources"):
 * `GET /api/v2/user/picker`, within `params.spaceId` when given. An empty term needs a space
 * (the endpoint suggests from a search, ids or a space).
 *
 * @since 1.20
 */
export const PAGE_SIZE = 20;

export const userPickerSource = ({ itemKey, params }) => ({
    search(term) {
        if (term === '' && !params.spaceId) {
            return Promise.resolve([]);
        }
        const query = { q: term, pageSize: PAGE_SIZE };
        if (params.spaceId) {
            query.spaceId = params.spaceId;
        }
        return client.get(apiUrl('user/picker', query)).then((response) => (response.results || []).map((user) => ({
            id: String(itemKey === 'guid' ? user.guid : user.id),
            text: user.displayName,
            subText: user.displayNameSub || null,
            image: { type: 'user', guid: user.guid, displayName: user.displayName, imageUrl: user.imageUrl, url: user.url },
        })));
    },
});

/**
 * `UserImage` props of a `user` image description.
 */
export const userPickerImageProps = (image, size) => ({
    guid: image.guid || '',
    displayName: image.displayName || '',
    url: image.url || '',
    imageUrl: image.imageUrl || '',
    size,
    link: false,
});
