<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\services;

use humhub\modules\comment\models\Comment;
use humhub\modules\comment\Module;
use humhub\modules\comment\serializers\CommentSerializer;
use humhub\modules\content\models\Content;
use Yii;

/**
 * Caches serialized comments and comment windows.
 *
 * This is possible at all because those payloads are **caller-neutral** - every field is
 * identical for everyone who may read the content, so one serialization can be handed to all
 * of them (see `docs/develop/concept-api.md`, "Caller context is not part of a payload").
 * Everything caller-specific - like state, edit/delete permissions - is fetched separately and
 * never cached here.
 *
 * What it saves: serializing a window costs a handful of queries per comment (child counts,
 * attached files, the rich-text extension pipeline). That price is paid on **every page
 * render**, since the comment widget embeds the first window into the page rather than letting
 * the island fetch it, and again for every "show more" and every live update. A stream page
 * with ten posts serializes ten windows.
 *
 * ## Authorization is NOT cached
 *
 * The cache is keyed by content, not by caller: whoever asks still passes the same
 * `Content::canView()` (and `guestHideComments`) checks in the controller or widget before
 * anything is read from here. A cache hit can therefore never widen access.
 *
 * ## Invalidation
 *
 * Entries are not deleted key by key - a content has arbitrarily many windows (cursor,
 * direction, page size). Instead every key carries a per-content **token** that
 * {@see self::invalidateContent()} replaces, which retires all of that content's entries at
 * once. `Comment::afterSave()`/`afterDelete()` call it, so a created, edited or deleted
 * comment (including a reply, which changes its root's preview) takes effect immediately.
 *
 * A random token rather than a counter, because two concurrent invalidations would otherwise
 * be able to settle on the same value and resurrect what they just retired.
 *
 * Deliberately NOT invalidated, and therefore only as fresh as the TTL:
 *
 * - the author's display name and profile image URL, which the payload embeds,
 * - data modules attach through `SerializeEvent`,
 * - a file detached from a comment without touching the comment itself.
 *
 * Set the comment module's `payloadCacheTtl` to `0` to disable caching entirely.
 *
 * @since 1.20
 */
class CommentPayloadCache
{
    /**
     * @var string cache key prefix of the per-content invalidation token
     */
    public const TOKEN_KEY_PREFIX = 'comment.payload.token.';

    /**
     * A comment window, from the cache when possible - see {@see CommentSerializer::window()}
     * for the parameters and the shape.
     */
    public static function window(
        Content $content,
        ?Comment $parentComment = null,
        ?int $commentId = null,
        ?string $direction = null,
        ?int $pageSize = null,
        ?int $limit = null,
    ): array {
        return static::remember(
            $content->id,
            ['window', $parentComment?->id, $commentId, $direction, $pageSize, $limit],
            fn() => CommentSerializer::window($content, $parentComment, $commentId, $direction, $pageSize, $limit),
        );
    }

    /**
     * One serialized comment, from the cache when possible. Worth caching despite being a
     * single record: a live update makes every client that has the thread open fetch the very
     * same new comment.
     */
    public static function comment(Comment $comment): array
    {
        return static::remember(
            $comment->content_id,
            ['comment', $comment->id],
            fn() => CommentSerializer::comment($comment),
        );
    }

    /**
     * Retires every cached payload of one content, by replacing its token.
     *
     * @param Content|int $content the content or its id
     */
    public static function invalidateContent($content): void
    {
        $contentId = $content instanceof Content ? $content->id : (int)$content;

        Yii::$app->cache->set(static::TOKEN_KEY_PREFIX . $contentId, static::newToken());
    }

    /**
     * @param array $keyParts what distinguishes this payload WITHIN its content
     * @param callable():array $build serializes the payload on a miss
     */
    protected static function remember(int $contentId, array $keyParts, callable $build): array
    {
        $ttl = static::getTtl();

        if ($ttl <= 0) {
            return $build();
        }

        $key = array_merge(
            [__CLASS__, $contentId, static::getToken($contentId), Yii::$app->language],
            $keyParts,
        );

        $cached = Yii::$app->cache->get($key);

        if (is_array($cached)) {
            return $cached;
        }

        $payload = $build();
        Yii::$app->cache->set($key, $payload, $ttl);

        return $payload;
    }

    /**
     * The content's current invalidation token, created on first use.
     *
     * Not `getOrSet()`: the token must also survive as the cache's own entry when the payload
     * TTL is short, so it is stored without expiry - it is a single small string per content
     * that carries comments, and a cache flush simply starts a new generation.
     */
    protected static function getToken(int $contentId): string
    {
        $key = static::TOKEN_KEY_PREFIX . $contentId;
        $token = Yii::$app->cache->get($key);

        if (!is_string($token)) {
            $token = static::newToken();
            Yii::$app->cache->set($key, $token);
        }

        return $token;
    }

    protected static function newToken(): string
    {
        return Yii::$app->security->generateRandomString(8);
    }

    protected static function getTtl(): int
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('comment');

        return (int)$module->payloadCacheTtl;
    }
}
