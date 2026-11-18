<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\helpers\Html;
use humhub\libs\MimeHelper;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\file\handler\DownloadFileHandler;
use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\file\models\File;
use humhub\modules\file\Module;
use humhub\modules\file\widgets\FileDownload;
use Yii;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * FileHelper
 *
 * @since 1.2
 * @author Luke
 */
class FileHelper extends \yii\helpers\FileHelper
{
    /**
     * Checks if given fileName has a extension
     *
     * @param string $fileName the filename
     * @return bool has extension
     */
    public static function hasExtension($fileName)
    {
        /**
         * suggested alternative:
         * `return pathinfo($file, PATHINFO_FILENAME) && pathinfo($file, PATHINFO_EXTENSION);`
         * of
         * `
         * $path_parts = pathinfo($file);
         * return ($path_parts['filename'] ?? false) && ($path_parts['extension'] ?? false);
         * `
         * @see \humhub\tests\codeception\unit\libs\FileHelperTest::testHasExtensionFalsePositives()
         */
        return (str_contains($fileName, '.'));
    }

    /**
     * Returns the extension of a file
     *
     * @param string|File $fileName the filename or File model
     * @return string the extension
     */
    public static function getExtension($fileName)
    {
        if ($fileName instanceof File) {
            $fileName = $fileName->file_name;
        }

        if (!is_string($fileName)) {
            return '';
        }

        $fileParts = pathinfo($fileName);

        return $fileParts['extension'] ?? '';
    }

    /**
     * Returns a `Content-Disposition` header value that is safe to use with both old and new
     * browsers, built the same way [[\yii\web\Response::sendFile()]] builds it.
     *
     * Needed whenever a download is not served by the response itself and the header has to be
     * handed to something else - e.g. [[\humhub\modules\file\actions\DownloadAction]] passing it
     * into a temporary storage URL, where the object key carries no usable file name.
     * [[\yii\web\Response::getDispositionHeaderValue()]] does the same but is protected.
     *
     * @param string $disposition `attachment` or `inline`
     * @param string $fileName the file name presented to the browser
     * @return string the header value
     * @since 1.19
     */
    public static function getContentDispositionHeaderValue(string $disposition, string $fileName): string
    {
        $fallbackName = str_replace(
            ['%', '/', '\\', '"', "\x7F"],
            ['_', '_', '_', '\\"', '_'],
            Inflector::transliterate($fileName, Inflector::TRANSLITERATE_LOOSE),
        );
        $utfName = rawurlencode(str_replace(['%', '/', '\\'], '', $fileName));

        $header = "$disposition; filename=\"$fallbackName\"";
        if ($utfName !== $fallbackName) {
            $header .= "; filename*=utf-8''$utfName";
        }

        return $header;
    }

    /**
     * Creates a file with options
     *
     * @param File $file
     * @return string the rendered HTML link
     * @since 1.2
     */
    public static function createLink(File $file, $options = [], $htmlOptions = [])
    {
        $label = $htmlOptions['label'] ?? Html::encode($file->file_name);

        $fileHandlers = FileHandlerCollection::getByType(
            [
                FileHandlerCollection::TYPE_VIEW,
                FileHandlerCollection::TYPE_EXPORT,
                FileHandlerCollection::TYPE_EDIT,
                FileHandlerCollection::TYPE_IMPORT,
            ],
            $file,
        );
        if (count($fileHandlers) === 1 && $fileHandlers[0] instanceof DownloadFileHandler) {
            $htmlOptions['target'] = '_blank';
            $htmlOptions = array_merge($htmlOptions, FileDownload::getFileDataAttributes($file));
            return Html::a($label, $file->getUrl(), $htmlOptions);
        }

        $htmlOptions = array_merge($htmlOptions, ['data-bs-target' => '#globalModal']);

        $urlOptions = ['/file/view', 'guid' => $file->guid];

        return Html::a($label, Url::to($urlOptions), $htmlOptions);
    }

    /**
     * Determines the content container of a File record
     *
     * @param File $file
     * @return ContentContainerActiveRecord the content container or null
     * @since 1.2
     */
    public static function getContentContainer(File $file)
    {
        $relation = $file->getPolymorphicRelation();

        if ($relation !== null && $relation instanceof ContentActiveRecord) {
            if ($relation->content->container !== null) {
                return $relation->content->container;
            }
        }

        return null;
    }

    /**
     * Returns general file infos as array
     * These information are mainly used by the frontend JavaScript application to handle files.
     *
     * @param File $file the file
     * @return array the file infos
     * @since 1.2
     */
    public static function getFileInfos(File $file)
    {
        $thumbnailUrl = '';
        $previewImage = new PreviewImage();
        if ($previewImage->applyFile($file)) {
            $thumbnailUrl = $previewImage->getUrl();
        }

        return [
            'name' => $file->file_name,
            'guid' => $file->guid,
            'size' => $file->size,
            'mimeType' => $file->mime_type,
            'mimeIcon' => MimeHelper::getMimeIconClassByExtension(self::getExtension($file->file_name)),
            'size_format' => Yii::$app->formatter->asShortSize($file->size, 1),
            'url' => $file->getUrl(),
            'relUrl' => $file->getUrl(null, false),
            'openLink' => FileHelper::createLink($file),
            'thumbnailUrl' => $thumbnailUrl,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getExtensionsByMimeType($mimeType, $magicFile = null)
    {
        $extensionsByMimeType = parent::getExtensionsByMimeType($mimeType, $magicFile);

        /* @var Module $module */
        $module = Yii::$app->getModule('file');
        if (isset($module->additionalMimeTypes) && is_array($module->additionalMimeTypes)) {
            foreach ($module->additionalMimeTypes as $additionalExtension => $additionalMimeType) {
                if ($additionalMimeType === $mimeType) {
                    $extensionsByMimeType[] = $additionalExtension;
                }
            }
        }

        return $extensionsByMimeType;
    }

    public static function getTempFile()
    {
        $tempDir = Yii::getAlias('@runtime/temp');
        FileHelper::createDirectory($tempDir);

        return tempnam($tempDir, 'tmf');
    }

    /**
     * Checks in the database if the given file GUIDs contain at least one file shown in the stream
     * which was created by the given user. This does not check the file system.
     *
     * Files uploaded by the rich text editor are hidden in the stream: they are attached to a record only when
     * they are referenced in the text. So they must not allow to save a record with an empty message.
     *
     * @param array|string|null $fileGuids GUIDs as array or comma separated string, as submitted with `fileList`
     * @param int|null $userId ID of the user who created the files, default is the current user
     * @return bool
     * @since 1.19.5
     */
    public static function existsStreamFile(array|string|null $fileGuids, ?int $userId = null): bool
    {
        if (empty($fileGuids)) {
            return false;
        }

        if (is_string($fileGuids)) {
            $fileGuids = explode(',', $fileGuids);
        }

        $fileGuids = array_filter(array_map('trim', array_filter($fileGuids, 'is_string')));
        $userId ??= Yii::$app->user->id;
        if ($fileGuids === [] || $userId === null) {
            return false;
        }

        return File::find()
            ->where(['guid' => $fileGuids, 'show_in_stream' => 1, 'created_by' => $userId])
            ->exists();
    }

}
