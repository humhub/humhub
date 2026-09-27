/**
 * The user islands' endpoint layer — every request the People directory and the follow button
 * make goes through here (the same role `spaceApi.js` has for the space islands).
 *
 * Endpoints: `humhub\modules\user\controllers\api\UserController` and `FollowController`, see
 * `docs/develop/concept-api.md`. The list itself is loaded by the kit's `CardDirectory` from
 * `GET /api/v2/user?purpose=directory`; what the caller is to the listed users comes from here.
 *
 * @since 1.20
 */
import { apiUrl, client } from '@humhub/vue';

/**
 * What the caller is to the users named — asked for the users a client displays (at most 100).
 *
 * @param {number[]} ids
 * @returns {Promise<Object<number, {isSelf: boolean, isFollowing: boolean, canFollow: boolean,
 *          friendship: ?{state: string, isFollowing: boolean}}>>}
 *          keyed by user id; `friendship` is the `user/<id>/friendship` state, `null` while the
 *          friendship system is off and for the caller themselves
 */
export const fetchStates = (ids) => {
    if (!ids.length) {
        return Promise.resolve({});
    }

    return client.get(apiUrl('user/states', { ids }))
        .then((response) => response.results || {});
};

/**
 * The caller's follow relationship to a user (`user/<id>/follow`). Every verb answers the
 * resulting state, so a caller never derives it.
 *
 * `followerCount` is `null` while following is disabled.
 *
 * @typedef {{isFollowing: boolean, followerCount: ?number, canFollow: boolean}} FollowState
 */

/**
 * @param {number} userId
 * @returns {Promise<FollowState>}
 */
export const fetchFollow = (userId) => client.get(followUrl(userId)).then(normalizeFollow);

/**
 * Follows the user (idempotent). Refused (403) for oneself and where following is disabled.
 *
 * @param {number} userId
 * @returns {Promise<FollowState>}
 */
export const follow = (userId) => client.put(followUrl(userId)).then(normalizeFollow);

/**
 * Stops following the user (idempotent).
 *
 * @param {number} userId
 * @returns {Promise<FollowState>}
 */
export const unfollow = (userId) => client.del(followUrl(userId)).then(normalizeFollow);

const followUrl = (userId) => apiUrl(`user/${userId}/follow`);

const normalizeFollow = (response) => ({
    isFollowing: !!response.isFollowing,
    followerCount: response.followerCount ?? null,
    canFollow: !!response.canFollow,
});
