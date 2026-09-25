/**
 * The space islands' endpoint layer — every request the chooser and the follow button make goes
 * through here (the same role `notificationApi.js` has for the notification islands).
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
 * `purpose` says what the list is for (`directory`, `picker`, `chooser`) — modules may restrict
 * a list in one context and not in another (see `SpaceListQuery::EVENT_INIT`).
 *
 * @param {{q?: ?string, scope?: ?string, purpose?: ?string, page?: ?number, pageSize?: ?number}} options
 * @returns {Promise<{results: Array, total: number, page: number, pageSize: number, pages: number}>}
 */
export const fetchSpaces = ({ q = null, scope = null, purpose = null, page = null, pageSize = null } = {}) => {
    const params = {};

    if (q) {
        params.q = q;
    }
    if (scope) {
        params.scope = scope;
    }
    if (purpose) {
        params.purpose = purpose;
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
 * @param {number[]} ids
 * @returns {Promise<Object<number, {isMember: boolean, isFollowing: boolean, newItems: number,
 *          membership: Object, canViewMembers: boolean, canViewFollowers: boolean,
 *          canFollow: boolean, memberCount: ?number, followerCount: ?number}>>}
 *          keyed by space id; `membership` is the `space/<id>/membership` state, a count is
 *          `null` where the space does not show it
 */
export const fetchStates = (ids) => {
    if (!ids.length) {
        return Promise.resolve({});
    }

    return client.get(apiUrl('space/states', { ids }))
        .then((response) => response.results || {});
};

/**
 * The caller's follow relationship to a space (`space/<id>/follow`, see
 * `humhub\modules\space\controllers\api\FollowController`). Every verb answers the
 * resulting state, so a caller never derives it.
 *
 * `followerCount` is `null` where the space does not show its followers.
 *
 * @typedef {{isFollowing: boolean, followerCount: ?number, canFollow: boolean}} FollowState
 */

/**
 * @param {number} spaceId
 * @returns {Promise<FollowState>}
 */
export const fetchFollow = (spaceId) => client.get(followUrl(spaceId)).then(normalizeFollow);

/**
 * Follows the space (idempotent). Refused (403) for members and where following is disabled.
 *
 * @param {number} spaceId
 * @returns {Promise<FollowState>}
 */
export const follow = (spaceId) => client.put(followUrl(spaceId)).then(normalizeFollow);

/**
 * Stops following the space (idempotent).
 *
 * @param {number} spaceId
 * @returns {Promise<FollowState>}
 */
export const unfollow = (spaceId) => client.del(followUrl(spaceId)).then(normalizeFollow);

const followUrl = (spaceId) => apiUrl(`space/${spaceId}/follow`);

const normalizeFollow = (response) => ({
    isFollowing: !!response.isFollowing,
    followerCount: response.followerCount ?? null,
    canFollow: !!response.canFollow,
});

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
