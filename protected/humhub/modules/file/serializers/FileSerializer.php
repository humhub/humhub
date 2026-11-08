<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\serializers;

use humhub\components\ActiveRecord;
use humhub\components\api\Format;
use humhub\libs\MimeHelper;
use humhub\modules\content\models\Content;
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\models\FileHistory;
use humhub\modules\user\models\User;
use humhub\modules\user\serializers\UserSerializer;
use yii\helpers\Url;

/**
 * Serializes attached {@see File} records for the HTTP API (see
 * `docs/develop/concept-api.md`) — camelCase field names, absolute URLs.
 *
 * Clients render attachments themselves from this data; the API never ships rendered HTML
 * for them.
 *
 * @since 1.20
 */
class FileSerializer
{
    /**
     * The files listed as the record's attachments: those shown in the stream, as the legacy
     * `ShowFiles` widget lists them. A file the richtext embeds is not among them (the file
     * extension of the richtext takes it out of the stream when the text is saved).
     *
     * @return array[]
     */
    public static function forRecord(ActiveRecord $record): array
    {
        if ($record instanceof Content) {
            $record = $record->getPolymorphicRelation();
        }

        return array_map(static::file(...), $record->fileManager->findStreamFiles());
    }

    /**
     * @return array{
     *     id: int,
     *     guid: string,
     *     mimeType: string,
     *     size: int,
     *     fileName: string,
     *     mimeIcon: string,
     *     icon: string,
     *     url: string,
     *     downloadUrl: string,
     *     previewUrl: string|null,
     * }
     */
    public static function file(File $file): array
    {
        $previewImage = new PreviewImage();

        return [
            'id' => $file->id,
            'guid' => $file->guid,
            'mimeType' => $file->mime_type,
            'size' => (int)$file->size,
            'fileName' => $file->file_name,
            // The CSS class of the file-type icon (`mime-image`, `mime-pdf`, …) - the same
            // value `FileHelper::getFileInfos()` ships to the legacy file widgets, so a client
            // rendering a file list gets the platform's icon set without mapping mime types
            // itself.
            'mimeIcon' => MimeHelper::getMimeIconClassByExtension(FileHelper::getExtension($file->file_name)),
            // The Tabler name of the file-type icon (`file-type-pdf`, `photo`, …) for a client
            // rendering `<i class="ti ti-<icon>">` - `mimeIcon` stays for the platform's own
            // `mime-*` stylesheet classes.
            'icon' => MimeHelper::getIconNameByExtension(FileHelper::getExtension($file->file_name)),
            'url' => $file->getUrl([], true),
            // Same file, but with the response forcing a download instead of letting the
            // browser display it inline - what a client renders behind a "download" action.
            'downloadUrl' => $file->getUrl(['download' => true], true),
            // Converted preview variant for image files (the same converter the web UI uses
            // for attachment thumbnails); `null` for anything that has no image preview.
            'previewUrl' => $previewImage->applyFile($file) ? Url::to($previewImage->getUrl(), true) : null,
        ];
    }

    /**
     * The versions of a file, newest first: the current content, then each earlier one the file
     * history kept ({@see FileHistory}, only recorded for records that enable it, see
     * `ActiveRecord::$fileManagerEnableHistory`).
     *
     * A version is numbered from the oldest (`1`) up, and names who saved it and when: for the
     * current one the file's last update, for an earlier one the update it was the result of
     * (a history entry is created from the file's state right before it is replaced). An
     * earlier version is downloaded as the file's `old-<id>` variant.
     *
     * @return array{
     *     id: int|null,
     *     version: int,
     *     current: bool,
     *     size: int,
     *     createdAt: string|null,
     *     createdBy: array|null,
     *     downloadUrl: string,
     * }[]
     */
    public static function versions(File $file): array
    {
        $history = $file->historyFiles;
        $number = count($history) + 1;

        $versions = [[
            'id' => null,
            'version' => $number,
            'current' => true,
            'size' => (int)$file->size,
            'createdAt' => Format::dateTime($file->updated_at ?: $file->created_at),
            'createdBy' => UserSerializer::short(User::findOne(['id' => $file->updated_by ?: $file->created_by])),
            'downloadUrl' => $file->getUrl(['download' => true], true),
        ]];

        foreach ($history as $entry) {
            $versions[] = [
                'id' => (int)$entry->id,
                'version' => --$number,
                'current' => false,
                'size' => (int)$entry->size,
                'createdAt' => Format::dateTime($entry->created_at),
                'createdBy' => UserSerializer::short($entry->createdBy),
                'downloadUrl' => $file->getUrl(['variant' => $entry->getFileVariantId(), 'download' => true], true),
            ];
        }

        return $versions;
    }
}
