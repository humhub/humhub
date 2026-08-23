/**
 * The comment island's API layer: endpoint calls against the platform's HTTP API (see
 * `docs/develop/concept-api.md`) plus the few fields the island derives itself.
 *
 * The API's conventions mean there is almost nothing to adapt — field names are already
 * camelCase and timestamps are ISO-8601, which `new Date()` parses natively. What this
 * module does add is what a client is *supposed* to derive rather than be told:
 *
 *  - `isEdited`   — `updatedAt !== createdAt`.
 *  - `blocked`    — the author is on the viewer's own block list (CoreJsConfig
 *                   `user.blockedUserIds`). The payload is always unmasked, so revealing a
 *                   masked comment needs no request; masking is a display concern.
 *  - `canAdminDelete` — `canDelete` on someone ELSE's comment, i.e. moderation.
 *
 * Validation failures use the API's `422 {"errors": {attribute: [messages]}}` contract —
 * `extractFieldErrors()` unwraps it for `HumHubForm.setErrors()`.
 */
import { apiUrl, client, getConfig } from '@humhub/vue';

const blockedUserIds = () => getConfig('user').blockedUserIds || [];
const currentUserId = () => {
    const id = getConfig('user').id;
    return typeof id === 'undefined' ? null : id;
};

const toDate = (value) => (value ? new Date(value) : null);

/** Adds the derived fields to a comment from the API (recursively for reply previews). */
export const mapComment = (comment) => {
    if (!comment) {
        return null;
    }

    const createdAt = toDate(comment.createdAt);
    const updatedAt = toDate(comment.updatedAt);
    const author = comment.createdBy || null;

    return {
        ...comment,
        createdAt,
        updatedAt,
        author,
        isEdited: !!(createdAt && updatedAt && updatedAt.getTime() !== createdAt.getTime()),
        blocked: !!(author && blockedUserIds().indexOf(author.id) !== -1),
        files: comment.files || [],
        extensions: comment.extensions || {},
        replies: comment.replies
            ? { ...comment.replies, items: comment.replies.items.map(mapComment) }
            : null,
    };
};

/**
 * Whether deleting this comment would be moderation — `canDelete` on someone ELSE's comment.
 * Derived from the fetched permissions rather than shipped, like `canDelete` itself.
 */
export const isAdminDelete = (comment, canDelete) => !!(
    canDelete
    && comment
    && comment.author
    && currentUserId() !== null
    && comment.author.id !== currentUserId()
);

/** Maps a window response, leaving its counts untouched. */
export const mapWindow = (window) => ({
    ...window,
    results: (window.results || []).map(mapComment),
});

/**
 * Fetches a comment window: the root window of a content, or — with `parentCommentId` — the
 * reply window of one thread. Remaining params (`commentId`, `direction`, `pageSize`,
 * `limit`) pass through to the endpoint.
 */
export const fetchWindow = ({ contentId, parentCommentId, ...params }) => {
    const path = parentCommentId
        ? `comment/parent/${parentCommentId}/window`
        : `comment/content/${contentId}/window`;
    return client.get(apiUrl(path, params)).then(mapWindow);
};

export const fetchComment = (id) => client.get(apiUrl(`comment/${id}`)).then(mapComment);

/**
 * What the caller may do with one comment (`{canEdit, canDelete}`). Not part of the comment
 * payload — it is the only caller-dependent thing the entry needs, and only once its context
 * menu opens, so the payload stays cacheable (see `docs/develop/concept-api.md`).
 */
export const fetchCommentPermissions = (id) => client.get(apiUrl(`comment/${id}/permissions`));

/**
 * Like states of many records in ONE request: `{recordId: {total, liked, canLike}}`. Record
 * ids the caller may no longer see are absent from the map.
 */
export const fetchLikeStates = (recordIds) => (recordIds.length
    ? client.get(apiUrl('like/states', { recordIds: recordIds.join(',') })).then((response) => response.results || {})
    : Promise.resolve({}));

/** The record ids of a mapped window, reply previews included — what fetchLikeStates() wants. */
export const collectRecordIds = (comments) => {
    const ids = [];

    (comments || []).forEach((comment) => {
        if (comment.recordId) {
            ids.push(comment.recordId);
        }
        ((comment.replies && comment.replies.items) || []).forEach((reply) => {
            if (reply.recordId) {
                ids.push(reply.recordId);
            }
        });
    });

    return [...new Set(ids)];
};

export const createComment = ({ contentId, parentCommentId, message, fileList }) => {
    const params = parentCommentId ? { contentId, parentCommentId } : { contentId };
    return client.post(apiUrl('comment', params), { data: { message, fileList } }).then(mapComment);
};

export const updateComment = (id, { message, fileList }) =>
    client.put(apiUrl(`comment/${id}`), { data: { message, fileList } }).then(mapComment);

/**
 * Deletes a comment. `fields` may carry the moderation parameters (`notify`, `message`).
 * The endpoint answers `204 No Content`.
 */
export const deleteComment = (id, fields) =>
    client.del(apiUrl(`comment/${id}`), fields ? { data: fields } : undefined);

/**
 * Field errors of a validation failure, or null. The API answers
 * `422 {"errors": {attribute: [messages]}}`; a rejected `client` call flattens that body
 * onto the response object. `parentCommentId` errors (a reply nested too deep) are
 * re-keyed onto `message` so the single-field comment form can render them.
 */
export const extractFieldErrors = (response) => {
    const errors = (response && response.errors) || null;
    if (!errors) {
        return null;
    }
    if (errors.parentCommentId && !errors.message) {
        return { ...errors, message: errors.parentCommentId };
    }
    return errors;
};
