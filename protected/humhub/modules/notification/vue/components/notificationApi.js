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
 * @param {{cursor?: ?number, limit?: number, categories?: ?string[], seen?: ?string}} options
 * @returns {Promise<{results: Array, unseenCount: number, nextCursor: ?number}>}
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
        // empty entry keeps it distinguishable and matches no category, exactly like the
        // server-rendered filter with every checkbox cleared.
        params.categories = categories.length ? categories : [''];
    }
    if (seen) {
        params.seen = seen;
    }

    return client.get(apiUrl('notification', params)).then(normalizeWindow);
};

/** Marks every notification of the caller as seen. */
export const markAllAsSeen = () => client.post(apiUrl('notification/mark-as-seen'));

/**
 * The response as the components consume it. The platform client resolves a response onto the
 * response object itself, so the fields are read off it directly (see `commentApi.js` for the
 * same pattern) — this only fills in what an empty/short response leaves out.
 */
const normalizeWindow = (response) => ({
    results: (response && response.results) || [],
    unseenCount: Number((response && response.unseenCount) || 0),
    nextCursor: (response && response.nextCursor) || null,
});
