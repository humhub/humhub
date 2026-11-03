import { apiUrl, client } from '@humhub/vue';

/**
 * The `topic` picker source (see docs/develop/ui-js-vuejs-picker.md, "Sources"):
 * `GET /api/v2/topic/picker`, within `params.containerId`; an image is the topic's colour, else
 * the topic module's icon (`params.icon`). An empty term needs a container.
 *
 * @since 1.20
 */
export const PAGE_SIZE = 20;

export const topicPickerSource = ({ params }) => ({
    // A topic added in the picker shows the module icon, as a topic without colour.
    newImage: { type: 'icon', name: params.icon || 'star' },
    search(term) {
        if (term === '' && !params.containerId) {
            return Promise.resolve([]);
        }
        const query = { q: term, pageSize: PAGE_SIZE };
        if (params.containerId) {
            query.containerId = params.containerId;
        }
        return client.get(apiUrl('topic/picker', query)).then((response) => (response.results || []).map((topic) => ({
            id: String(topic.id),
            text: topic.name,
            image: topic.color ? { type: 'color', color: topic.color } : { type: 'icon', name: params.icon || 'star' },
        })));
    },
});
