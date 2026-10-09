/**
 * The notification island's only endpoint layer — every request the islands make goes through
 * here (the same role `commentApi.js` has for the comment island).
 *
 * Endpoints: `humhub\modules\notification\controllers\api\NotificationController`, see
 * `docs/develop/concept-api.md`.
 *
 * @since 1.20
 */
import { apiUrl, client } from '@humhub/vue';

/**
 * One page of the caller's notifications.
 *
 * @param {{cursor?: ?string, limit?: number, categories?: ?string[], seen?: ?string}} options
 *   `cursor` is the previous page's opaque `nextCursor`, `categories` notification category ids
 * @returns {Promise<{results: Array, unseenCount: number, nextCursor: ?string}>}
 */
export const fetchNotifications = ({ cursor = null, limit = null, categories = null, seen = null } = {}) => {
    const params = {};

    if (cursor) {
        params.cursor = cursor;
    }
    if (limit) {
        params.limit = limit;
    }
    if (Array.isArray(categories)) {
        // `categories[]=a&categories[]=b`. An empty selection is a filter of its own - "no
        // category" means an empty list, not "unfiltered" - but an empty array serializes to
        // nothing at all, which the server could not tell apart from an omitted parameter. One
        // empty entry keeps it distinguishable and matches no category.
        params.categories = categories.length ? categories : [''];
    }
    if (seen) {
        params.seen = seen;
    }

    return client.get(apiUrl('notification', params)).then(normalizePage);
};

/**
 * Marks notifications of the caller as seen: the entries of the given notification ids, or
 * every notification without ids.
 *
 * @param {?number[]} ids
 * @returns {Promise<?{unseenCount: number}>} `null` for an empty `ids` list, which sends nothing
 */
export const markAsSeen = (ids = null) => {
    if (Array.isArray(ids) && !ids.length) {
        // Nothing to mark - and an empty list must never reach the server as "everything".
        return Promise.resolve(null);
    }

    const url = apiUrl('notification/mark-as-seen');
    const request = Array.isArray(ids) ? client.post(url, { data: { ids } }) : client.post(url);

    return request.then((response) => ({ unseenCount: Number((response && response.unseenCount) || 0) }));
};

/**
 * The response as the components consume it. The platform client resolves a response onto the
 * response object itself, so the fields are read off it directly (see `commentApi.js` for the
 * same pattern) — this only fills in what an empty/short response leaves out.
 */
const normalizePage = (response) => ({
    results: (response && response.results) || [],
    unseenCount: Number((response && response.unseenCount) || 0),
    nextCursor: (response && response.nextCursor) || null,
});
