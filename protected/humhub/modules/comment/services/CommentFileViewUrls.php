<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\services;

use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;

/**
 * Adds the `viewUrl` of the attached files to a serialized comment or comment window, for
 * the current caller.
 *
 * The comment payload is cached for everyone ({@see CommentPayloadCache}), but the file
 * handlers modules contribute may depend on the user (e.g. an editor only for those who may
 * change the file), so `viewUrl` ({@see FileHelper::getViewUrl()}) cannot be part of it. It is
 * added here, after the cache, wherever a payload is handed out. A file offering nothing but
 * the download gets no `viewUrl`.
 *
 * The files are loaded in one query per payload.
 *
 * @since 1.20
 */
class CommentFileViewUrls
{
    /**
     * @param array $comment as returned by {@see \humhub\modules\comment\serializers\CommentSerializer::comment()}
     */
    public static function forComment(array $comment): array
    {
        return static::forComments([$comment])[0];
    }

    /**
     * @param array $window as returned by {@see \humhub\modules\comment\serializers\CommentSerializer::window()}
     */
    public static function forWindow(array $window): array
    {
        $window['results'] = static::forComments($window['results'] ?? []);

        return $window;
    }

    /**
     * @param array[] $comments serialized comments, their reply previews included
     * @return array[]
     */
    private static function forComments(array $comments): array
    {
        $ids = [];
        foreach ($comments as $comment) {
            foreach (static::filesOf($comment) as $file) {
                $ids[] = $file['id'];
            }
        }

        if ($ids === []) {
            return $comments;
        }

        $files = File::find()->where(['id' => array_unique($ids)])->indexBy('id')->all();

        return array_map(static fn(array $comment) => static::withViewUrls($comment, $files), $comments);
    }

    /**
     * @return array[] the serialized files of a comment and of its reply previews
     */
    private static function filesOf(array $comment): array
    {
        $files = $comment['files'] ?? [];
        foreach ($comment['replies']['items'] ?? [] as $reply) {
            $files = array_merge($files, $reply['files'] ?? []);
        }

        return $files;
    }

    /**
     * @param File[] $files by id
     */
    private static function withViewUrls(array $comment, array $files): array
    {
        $comment['files'] = array_map(static function (array $data) use ($files) {
            $viewUrl = isset($files[$data['id']]) ? FileHelper::getViewUrl($files[$data['id']]) : null;

            return $viewUrl === null ? $data : $data + ['viewUrl' => $viewUrl];
        }, $comment['files'] ?? []);

        if (isset($comment['replies']['items'])) {
            $comment['replies']['items'] = array_map(
                static fn(array $reply) => static::withViewUrls($reply, $files),
                $comment['replies']['items'],
            );
        }

        return $comment;
    }
}
