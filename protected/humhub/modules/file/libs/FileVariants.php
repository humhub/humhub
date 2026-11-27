<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\modules\file\components\StorageManager;
use humhub\modules\file\models\File;
use Yii;
use yii\base\Exception;

/**
 * The variants of a file: the files stored next to its content, in its storage directory
 *
 * A variant is either *derived* (previews, the display image, conversions: made on demand from the content, dropped
 * with a new content) or *persistent* (versions, subtitles: kept until deleted, see
 * {@see \humhub\modules\file\Module::$persistentVariants}). Variants are told apart by their name only; the storage
 * is the source of truth for what exists, with its size and time.
 *
 * Only names that match a writable type ({@see self::getTypes()}, the subtitles and captions for now) can be written
 * and deleted through this class.
 *
 * ```php
 * $variants = new FileVariants($file);
 * $all = $variants->getAll();
 * $errors = $variants->put('subtitles-de.vtt', $vtt);
 * $variants->delete('subtitles-de.vtt');
 * ```
 *
 * A module adds a writable type with {@see self::registerType()}; it also needs its pattern in
 * `Module::$persistentVariants` to survive a new content.
 *
 * @since 1.20
 */
class FileVariants
{
    /**
     * @var array[]|null the writable types by key (see {@see self::getTypes()})
     */
    private static ?array $types = null;

    public function __construct(private readonly File $file)
    {
    }

    /**
     * The writable types. A type is an array with
     *
     * - `pattern`: the regular expression of the names it takes
     * - `mimeTypes`: the mime type prefixes of the files that may carry it
     * - `maxSize`: the maximum size of the content in bytes
     * - `contentType`: the content type it is served with, inline
     * - `prepare` (optional): `fn(string $content): string`, normalizes the content (e.g. converts a format)
     * - `validate` (optional): `fn(string $content): string[]`, the errors of the prepared content
     *
     * @return array<string, array>
     */
    public static function getTypes(): array
    {
        if (self::$types === null) {
            self::$types = [
                'subtitles' => [
                    'pattern' => '/^(?:subtitles|captions)-[a-z]{2,3}(?:-[A-Za-z0-9]{2,8})*\.vtt$/',
                    'mimeTypes' => ['video/', 'audio/'],
                    'maxSize' => 1048576,
                    'contentType' => 'text/vtt; charset=utf-8',
                    'prepare' => WebVtt::prepare(...),
                    'validate' => WebVtt::validate(...),
                ],
            ];
        }

        return self::$types;
    }

    /**
     * Adds or replaces a writable type, see {@see self::getTypes()} for its definition
     */
    public static function registerType(string $key, array $definition): void
    {
        self::$types = [...self::getTypes(), $key => $definition];
    }

    /**
     * The writable type a variant name belongs to, null when it is none
     */
    private static function findType(string $name): ?array
    {
        foreach (self::getTypes() as $definition) {
            if (preg_match($definition['pattern'], $name)) {
                return $definition;
            }
        }

        return null;
    }

    /**
     * Whether the name belongs to a writable type, which can be written and deleted
     */
    public static function isWritable(string $name): bool
    {
        return self::findType($name) !== null;
    }

    /**
     * Whether a variant is kept with the file ({@see \humhub\modules\file\Module::$persistentVariants})
     */
    public static function isPersistent(string $name): bool
    {
        return StorageManager::matchesAny($name, Yii::$app->getModule('file')->persistentVariants);
    }

    /**
     * The content type a variant is served with, null when it belongs to no writable type
     */
    public static function getContentType(string $name): ?string
    {
        return self::findType($name)['contentType'] ?? null;
    }

    /**
     * The variants of the file found in its storage (all but the content itself), ordered by name
     *
     * @return array<int, array{name: string, persistent: bool, mimeType: string, size: int, updatedAt: int}>
     */
    public function getAll(): array
    {
        $variants = [];

        foreach ($this->file->getStore()->listVariants() as $name => $info) {
            $name = (string)$name;
            $type = self::findType($name);

            $variants[] = [
                'name' => $name,
                'persistent' => self::isPersistent($name),
                'mimeType' => $type !== null
                    ? explode(';', $type['contentType'])[0]
                    : $this->file->getStore()->mimeType($name),
                'size' => $info['size'],
                'updatedAt' => $info['updatedAt'],
            ];
        }

        usort($variants, static fn(array $a, array $b) => strcmp($a['name'], $b['name']));

        return $variants;
    }

    /**
     * One variant of the file, null when it doesn't exist
     *
     * @see self::getAll()
     */
    public function get(string $name): ?array
    {
        foreach ($this->getAll() as $variant) {
            if ($variant['name'] === $name) {
                return $variant;
            }
        }

        return null;
    }

    public function has(string $name): bool
    {
        return $this->file->getStore()->has($name);
    }

    /**
     * Stores a variant of a writable type, replacing an existing one
     *
     * @param string $name the name of the variant
     * @param string $content the content, normalized by the type (subtitles: converted to WebVTT when it is SRT)
     * @return array<string, string[]> the errors by field (`name`, `content`), empty when it was stored
     * @throws Exception when the content could not be written to the storage
     */
    public function put(string $name, string $content): array
    {
        $type = self::findType($name);
        if ($type === null) {
            return ['name' => [Yii::t('FileModule.base', 'A variant of this name can\'t be written.')]];
        }

        if (!$this->isMediaFor($type)) {
            return ['name' => [Yii::t('FileModule.base', 'This file cannot have a variant of this name.')]];
        }

        if (strlen($content) > $type['maxSize']) {
            return ['content' => [Yii::t('FileModule.base', 'The text is too large, the maximum is {size} MB.', ['size' => $type['maxSize'] / 1048576])]];
        }

        if (!mb_check_encoding($content, 'UTF-8')) {
            return ['content' => [Yii::t('FileModule.base', 'The text must be UTF-8 encoded.')]];
        }

        if (isset($type['prepare'])) {
            $content = $type['prepare']($content);
        }

        $errors = isset($type['validate']) ? $type['validate']($content) : [];
        if ($errors !== []) {
            return ['content' => $errors];
        }

        $store = $this->file->getStore();
        $store->setContent($content, $name);

        if (!$store->has($name)) {
            throw new Exception('Could not store the variant ' . $name . ' of file ' . $this->file->id);
        }

        return [];
    }

    /**
     * Deletes a variant of a writable type
     *
     * @return bool false when the name is no writable type's or there is none of that name
     */
    public function delete(string $name): bool
    {
        if (!self::isWritable($name) || !$this->has($name)) {
            return false;
        }

        $this->file->getStore()->delete($name);

        return true;
    }

    private function isMediaFor(array $type): bool
    {
        foreach ($type['mimeTypes'] as $prefix) {
            if (str_starts_with((string)$this->file->mime_type, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
