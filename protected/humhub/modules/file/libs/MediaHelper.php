<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\modules\file\jobs\CollectMediaInfo;
use humhub\modules\file\media\GetId3Reader;
use humhub\modules\file\media\MediaInfo;
use humhub\modules\file\models\File;
use humhub\modules\file\Module;
use Throwable;
use Yii;

/**
 * Collects the size and duration of video and audio files into their metadata, the counterpart of [[ImageHelper]] for
 * images. Each helper owns its keys: this one `duration` (all types), `width` and `height` of videos.
 *
 * @since 1.20
 */
class MediaHelper
{
    /**
     * Stores the metadata of a video (`width`, `height`, `duration`) or audio file (`duration`), as the platform shows
     * it: with the display rotation applied. Values that can't be read are dropped, as are the ones that don't apply to
     * the type (also those of a previous content). Images keep their dimensions, see [[ImageHelper::collectDimensions()]].
     *
     * @param bool $allowCopy Whether a file that is not on a local path may be copied to a temporary one to read it.
     * Otherwise (upload request) a [[CollectMediaInfo]] job does it in the background.
     */
    public static function collectMediaInfo(File $file, bool $allowCopy = false): void
    {
        try {
            $mimeType = (string)$file->mime_type;

            if (in_array($mimeType, ImageHelper::DIMENSION_MIME_TYPES, true)) {
                self::updateMetadata($file, [], ['duration']);
                return;
            }

            $isVideo = str_starts_with($mimeType, 'video/');
            if (!$isVideo && !str_starts_with($mimeType, 'audio/')) {
                self::updateMetadata($file, [], ['width', 'height', 'duration']);
                return;
            }

            $info = self::readFile($file, $isVideo, $allowCopy);
            if ($info === false) {
                // Handed over to the job: the values of the previous content don't apply meanwhile
                self::updateMetadata($file, [], ['width', 'height', 'duration']);
                return;
            }

            $set = array_filter([
                'width' => $isVideo ? $info?->width : null,
                'height' => $isVideo ? $info?->height : null,
                'duration' => $info?->duration !== null ? round($info->duration, 2) : null,
            ], static fn($value) => $value !== null);

            self::updateMetadata($file, $set, array_diff(['width', 'height', 'duration'], array_keys($set)));
        } catch (Throwable $e) {
            Yii::warning('Could not collect the media info of file ' . $file->id . ': ' . $e->getMessage(), 'file');
        }
    }

    /**
     * @return MediaInfo|null The size and duration as stored, `null` when not collected (yet)
     */
    public static function getMediaInfo(File $file): ?MediaInfo
    {
        $metadata = $file->getAttribute('metadata');
        if ($metadata === null || $metadata === '') {
            return null;
        }

        // Parsed into a copy, the record keeps its attribute as loaded. Runs on every render: metadata that can't be
        // parsed has no media info.
        if (!$metadata instanceof Metadata) {
            try {
                $metadata = new Metadata($metadata);
            } catch (Throwable) {
                return null;
            }
        }

        $width = isset($metadata->width, $metadata->height) ? (int)$metadata->width : null;
        $height = $width !== null ? (int)$metadata->height : null;
        $duration = isset($metadata->duration) ? (float)$metadata->duration : null;

        return $width === null && $duration === null ? null : new MediaInfo($width, $height, $duration);
    }

    /**
     * @return float|null Duration in seconds, `null` when not collected (yet)
     */
    public static function getDuration(File $file): ?float
    {
        return self::getMediaInfo($file)?->duration;
    }

    /**
     * The content of the file as stream
     *
     * @return resource
     */
    protected static function openStream(File $file)
    {
        return $file->store->getContentStream();
    }

    /**
     * @return MediaInfo|null|false `false` when the reading was handed over to a job
     */
    private static function readFile(File $file, bool $isVideo, bool $allowCopy): MediaInfo|null|false
    {
        $stream = static::openStream($file);

        try {
            $streamMeta = stream_get_meta_data($stream);
            if (($streamMeta['wrapper_type'] ?? null) === 'plainfile') {
                return self::read($streamMeta['uri'], (string)$file->file_name, $isVideo);
            }

            if (!$allowCopy) {
                Yii::$app->queue->push(new CollectMediaInfo(['fileId' => $file->id]));
                return false;
            }

            // A remote store: the readers need a local path
            $tempFile = FileHelper::getTempFile();
            try {
                $target = fopen($tempFile, 'wb');
                stream_copy_to_stream($stream, $target);
                fclose($target);

                return self::read($tempFile, (string)$file->file_name, $isVideo);
            } finally {
                @unlink($tempFile);
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    private static function read(string $path, string $fileName, bool $isVideo): ?MediaInfo
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');
        $reader = $module->getMediaInfoReader();

        try {
            $info = $reader->read($path, $fileName);
        } catch (Throwable $e) {
            Yii::warning('Could not read the media info with ' . $reader::class . ': ' . $e->getMessage(), 'file');
            $info = null;
        }

        $isIncomplete = static fn(?MediaInfo $info): bool => $info === null
            || $info->duration === null
            || ($isVideo && ($info->width === null || $info->height === null));

        if ($isIncomplete($info) && !$reader instanceof GetId3Reader) {
            $fallback = (new GetId3Reader())->read($path, $fileName);
            if ($fallback !== null) {
                $hasSize = $info?->width !== null && $info?->height !== null;
                $info = new MediaInfo(
                    $hasSize ? $info->width : $fallback->width,
                    $hasSize ? $info->height : $fallback->height,
                    $info?->duration ?? $fallback->duration,
                );
            }
        }

        if ($info === null) {
            Yii::info('Could not read the media info of ' . $path, 'file');
        }

        return $info;
    }

    /**
     * @param array<string, int|float> $set
     * @param string[] $drop
     */
    private static function updateMetadata(File $file, array $set, array $drop): void
    {
        $metadata = $file->getMetadata();
        $changed = false;

        foreach ($set as $key => $value) {
            if (!isset($metadata->$key) || $metadata->$key !== $value) {
                $metadata->$key = $value;
                $changed = true;
            }
        }
        foreach ($drop as $key) {
            if (isset($metadata->$key)) {
                unset($metadata->$key);
                $changed = true;
            }
        }

        if ($changed) {
            $file->updateAttributes(['metadata' => count($metadata) > 0 ? (string)$metadata : null]);
        }
    }
}
