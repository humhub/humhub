/**
 * The space chooser's endpoint layer — every request the island makes goes through here (the
 * same role `notificationApi.js` has for the notification islands).
 *
 * Endpoints: `humhub\modules\space\controllers\api\SpaceController`, see
 * `docs/develop/concept-api.md`. Both are general: the list is the platform's space list, not a
 * chooser-shaped response, and the counts are asked for by guid rather than "all of mine".
 *
 * @since 1.20
 */
import { apiUrl, client } from '@humhub/vue';

/**
 * One page of spaces.
 *
 * @param {{q?: ?string, scope?: ?string, page?: ?number, pageSize?: ?number}} options
 * @returns {Promise<{results: Array, total: number, page: number, pageSize: number, pages: number}>}
 */
export const fetchSpaces = ({ q = null, scope = null, page = null, pageSize = null } = {}) => {
    const params = {};

    if (q) {
        params.q = q;
    }
    if (scope) {
        params.scope = scope;
    }
    if (page) {
        params.page = page;
    }
    if (pageSize) {
        params.pageSize = pageSize;
    }

    return client.get(apiUrl('space', params)).then(normalizePage);
};

/**
 * What the caller is to the spaces named — membership, following and what is new in them.
 * Asked for the spaces a client displays, never for "every space I am a member of" (see the
 * endpoint's own docblock).
 *
 * @param {string[]} guids
 * @returns {Promise<Object<string, {isMember: boolean, isFollowing: boolean, newItems: number}>>}
 */
export const fetchStates = (guids) => {
    if (!guids.length) {
        return Promise.resolve({});
    }

    return client.get(apiUrl('space/states', { guids }))
        .then((response) => response.results || {});
};

/**
 * The response as the components consume it. The platform client resolves a response onto the
 * response object itself, so the fields are read off it directly (see `notificationApi.js` for
 * the same pattern) — this only fills in what an empty/short response leaves out.
 */
const normalizePage = (response) => ({
    results: Array.isArray(response.results) ? response.results : [],
    total: response.total || 0,
    page: response.page || 1,
    pageSize: response.pageSize || 0,
    pages: response.pages || 0,
});
