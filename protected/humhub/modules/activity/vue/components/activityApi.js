/**
 * The activity island's only endpoint layer — every request the box makes goes through here
 * (the same role `notificationApi.js` has for the notification islands).
 *
 * Endpoint: `humhub\modules\activity\controllers\api\ActivityController`, see
 * `docs/develop/concept-api.md`.
 *
 * @since 1.20
 */
import { apiUrl, client } from '@humhub/vue';

/**
 * One page of the activities the caller may see.
 *
 * `cursor` is the previous page's `nextCursor` and is opaque: it is passed back untouched and
 * never built from an entry (see `ActivityWindowService` for why an entry's id would be the
 * wrong cursor).
 *
 * @param {{cursor?: ?string, limit?: ?number, containerGuid?: ?string}} options
 * @returns {Promise<{results: Array, nextCursor: ?string}>}
 */
export const fetchActivities = ({ cursor = null, limit = null, containerGuid = null } = {}) => {
    const params = {};

    if (cursor) {
        params.cursor = cursor;
    }
    if (limit) {
        params.limit = limit;
    }
    if (containerGuid) {
        params.containerGuid = containerGuid;
    }

    return client.get(apiUrl('activity', params)).then(normalizeWindow);
};

/**
 * The response as the components consume it. The platform client resolves a response onto the
 * response object itself, so the fields are read off it directly (see `notificationApi.js` for
 * the same pattern) — this only fills in what an empty/short response leaves out.
 */
const normalizeWindow = (response) => ({
    results: Array.isArray(response.results) ? response.results : [],
    nextCursor: response.nextCursor ?? null,
});
