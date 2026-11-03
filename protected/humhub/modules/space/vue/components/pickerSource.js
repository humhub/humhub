import { apiUrl, client } from '@humhub/vue';

/**
 * The `space` picker source (see docs/develop/ui-js-vuejs-picker.md, "Sources"):
 * `GET /api/v2/space?purpose=picker`, `params.scope` (default `member`) — an empty term lists
 * the first spaces of the scope.
 *
 * @since 1.20
 */
export const PAGE_SIZE = 20;

export const spacePickerSource = ({ itemKey, params }) => ({
    search(term) {
        const query = { purpose: 'picker', scope: params.scope || 'member', q: term, pageSize: PAGE_SIZE };
        return client.get(apiUrl('space', query)).then((response) => (response.results || []).map((space) => ({
            id: String(itemKey === 'guid' ? space.guid : space.id),
            text: space.name,
            subText: space.description || null,
            image: { type: 'space', id: space.id, name: space.name, color: space.color, imageUrl: space.imageUrl },
        })));
    },
});

/**
 * `SpaceImage` props of a `space` image description.
 */
export const spacePickerImageProps = (image, size) => ({
    id: image.id ?? null,
    name: image.name || '',
    color: image.color || null,
    imageUrl: image.imageUrl || null,
    width: size,
    link: false,
});
