<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\serializers;

use humhub\components\ActiveRecord;
use humhub\libs\MimeHelper;
use humhub\modules\content\models\Content;
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
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
     * All files attached to the given record.
     *
     * @return array[]
     */
    public static function forRecord(ActiveRecord $record): array
    {
        if ($record instanceof Content) {
            $record = $record->getPolymorphicRelation();
        }

        return array_map(static::file(...), $record->fileManager->findAll());
    }

    /**
     * @return array{
     *     id: int,
     *     guid: string,
     *     mimeType: string,
     *     size: int,
     *     fileName: string,
     *     mimeIcon: string,
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
            'url' => $file->getUrl([], true),
            // Same file, but with the response forcing a download instead of letting the
            // browser display it inline - what a client renders behind a "download" action.
            'downloadUrl' => $file->getUrl(['download' => true], true),
            // Converted preview variant for image files (the same converter the web UI uses
            // for attachment thumbnails); `null` for anything that has no image preview.
            'previewUrl' => $previewImage->applyFile($file) ? Url::to($previewImage->getUrl(), true) : null,
        ];
    }
}
