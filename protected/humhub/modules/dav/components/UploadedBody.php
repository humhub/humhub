<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\Module as FileModule;
use humhub\modules\file\validators\FileValidator;
use Sabre\DAV\Exception\Forbidden;
use Yii;
use yii\web\UploadedFile;

/**
 * The body of a `PUT`, in a temporary file and checked like an upload of the web interface:
 * the allowed file extensions, the maximum size and the characters of the name
 * (`FileValidator`). Never read into memory; the file is removed with the object.
 *
 * A name the platform would change on upload (`fileNameValidationPattern`) is refused rather
 * than changed: a client finds its file by the name it chose.
 *
 * ```php
 * $body = UploadedBody::fromData($data, $name); // throws Forbidden
 * $file->setStoredFileByPath($body->path);
 * ```
 *
 * @since 1.20
 */
final class UploadedBody
{
    private function __construct(public readonly string $path, public readonly string $name)
    {
    }

    public function __destruct()
    {
        if (is_file($this->path)) {
            @unlink($this->path);
        }
    }

    /**
     * @param resource|string|null $data the body as Sabre hands it over
     * @throws Forbidden when the file may not be stored
     */
    public static function fromData(mixed $data, string $name): self
    {
        self::assertValidName($name);

        $validator = new FileValidator(['skipOnEmpty' => false]);
        $limit = $validator->getSizeLimit();

        $path = tempnam(Yii::getAlias('@runtime'), 'dav-');
        $body = new self($path, $name);

        $target = fopen($path, 'wb');
        try {
            if (is_resource($data)) {
                // One byte more than allowed tells that it is too large, without storing it all.
                stream_copy_to_stream($data, $target, $limit > 0 ? $limit + 1 : -1);
            } elseif ($data !== null) {
                fwrite($target, (string)$data);
            }
        } finally {
            fclose($target);
        }

        $upload = new UploadedFile([
            'name' => $name,
            'tempName' => $path,
            'type' => $body->getMimeType(),
            'size' => $body->getSize(),
            'error' => UPLOAD_ERR_OK,
        ]);

        // Finder and Office create a file empty before they write it; empty content has no
        // type to match the extension against (`application/x-empty`). The extension itself
        // is still checked, and the content once it is written.
        if ($upload->size === 0) {
            $validator->checkExtensionByMimeType = false;
        }

        if (!$validator->validate($upload, $error)) {
            throw new Forbidden((string)$error);
        }

        return $body;
    }

    /**
     * @throws Forbidden for a name the platform would store differently
     */
    public static function assertValidName(string $name): void
    {
        /** @var FileModule $module */
        $module = Yii::$app->getModule('file');
        $pattern = $module->fileNameValidationPattern;

        if ($name === '' || trim($name) !== $name || (!empty($pattern) && preg_match($pattern, $name))) {
            throw new Forbidden(Yii::t('FileModule.base', 'The name "{name}" contains characters that are not allowed.', ['name' => $name]));
        }

        if ($module->denyDoubleFileExtensions && preg_match('/\.\w{2,3}\.\w{2,3}$/', $name)) {
            throw new Forbidden(Yii::t('FileModule.base', 'Double file extensions are not allowed!'));
        }
    }

    public function getSize(): int
    {
        clearstatcache(true, $this->path);

        return (int)filesize($this->path);
    }

    /**
     * By the extension first, as the browser of an upload does (a `.docx` is no `application/zip`).
     */
    public function getMimeType(): string
    {
        return FileHelper::getMimeTypeByExtension($this->name)
            ?? FileHelper::getMimeType($this->path, null, false)
            ?? 'application/octet-stream';
    }
}
