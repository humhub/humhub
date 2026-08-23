<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\serializers;

use humhub\models\RecordMap;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\like\services\LikeService;

/**
 * Serializes like state for the HTTP API (see `docs/develop/concept-api.md`).
 *
 * @since 1.19
 */
class LikeSerializer
{
    /**
     * The caller-context like state of a record: how many likes it has, whether the caller
     * has liked it, and whether the caller may like it at all (drives a like button's
     * visibility). `liked` and `canLike` are always `false` for guests.
     *
     * Embedded by other shapes that carry likes (e.g. a comment) and returned by the like
     * state/like/unlike endpoints, so a client never has to derive one from the other.
     *
     * @return array{total: int, liked: bool, canLike: bool}
     */
    public static function state(LikeService $likeService): array
    {
        return [
            'total' => $likeService->getCount(),
            'liked' => $likeService->hasLiked(),
            'canLike' => $likeService->canLike(),
        ];
    }

    /**
     * The like states of many already-loaded records, keyed by record id.
     *
     * The like state is the one thing about a record that is both per-record and per-caller,
     * so payloads that want to be caller-neutral (and therefore cacheable) leave it out and
     * a client asks for a whole window's states at once - see
     * `docs/develop/concept-api.md`. Counts and the caller's own likes come from two grouped
     * queries here, not one pair per record.
     *
     * Authorization is the CALLER's business: this assumes every given record may be seen by
     * the current user (which is why the API endpoint filters first, while a widget rendering
     * one content's comments already knows it).
     *
     * @param array<int, ContentProvider> $records record id => record
     * @return array<int, array{total: int, liked: bool, canLike: bool}>
     * @since 1.19
     */
    public static function statesForRecords(array $records): array
    {
        $counts = LikeService::countsForRecords($records);
        $liked = LikeService::likedRecordIds($records);

        $states = [];
        foreach ($records as $recordId => $record) {
            $states[$recordId] = static::state(
                (new LikeService($record))->preloadState(
                    $counts[$recordId] ?? 0,
                    in_array($recordId, $liked, true),
                ),
            );
        }

        return $states;
    }

    /**
     * {@see self::statesForRecords()} for records that still have to be loaded - resolves the
     * ids through {@see RecordMap} (one query for the mapping, one per involved model, with
     * `content` eager-loaded) and skips ids that do not resolve to a likeable record.
     *
     * @param int[] $recordIds
     * @return array<int, array{total: int, liked: bool, canLike: bool}>
     * @since 1.19
     */
    public static function statesByRecordId(array $recordIds): array
    {
        if ($recordIds === []) {
            return [];
        }

        return static::statesForRecords(
            RecordMap::getByIds($recordIds, ContentProvider::class, ['content']),
        );
    }
}
