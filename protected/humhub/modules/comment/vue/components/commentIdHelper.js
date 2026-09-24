/**
 * Mirrors `humhub\modules\comment\helpers\IdHelper::getId()`
 * (`'C' . $content->id . 'P' . $parentComment?->id`) so client-rendered ids
 * match the legacy server-rendered ones byte-for-byte. PHP's nullsafe
 * concatenation of a null parent comment yields an empty string after "P" -
 * reproduced here via `?? ''`.
 *
 * @param {number} contentId
 * @param {number|null} [parentCommentId]
 * @returns {string}
 */
export const getId = (contentId, parentCommentId) => `C${contentId}P${parentCommentId ?? ''}`;
