<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\modules\file\models\File;
use LengthException;
use Yii;
use yii\base\Exception;

/**
 * The writable persistent variants of a file (see {@see \humhub\modules\file\Module::$persistentVariants}), the
 * subtitles of a video or audio file for now.
 *
 * A variant is stored next to the file's content, like its versions, and goes with the file. What the API lists is kept
 * in the file's metadata (`variants`), so a listing needs no look into the storage. The column is short, so an entry
 * is kept as the list `[kind, generated, size, updatedAt]` and a file takes a limited number of variants.
 *
 * ```php
 * $variants = new FileVariants($file);
 * $errors = $variants->put('subtitles-de.vtt', $vtt, 'captions');
 * $variants->delete('subtitles-de.vtt');
 * ```
 *
 * A further type needs an entry in {@see self::TYPES} and, for the name patterns it uses, one in
 * `Module::$persistentVariants`.
 *
 * @since 1.20
 */
class FileVariants
{
    public const TYPE_SUBTITLES = 'subtitles';

    public const KIND_SUBTITLES = 'subtitles';
    public const KIND_CAPTIONS = 'captions';

    /**
     * @var string the key in the file's metadata
     */
    public const METADATA_KEY = 'variants';

    /**
     * @var array[] the writable types: the pattern of the names (the first group is the language), the mime type
     * prefixes of the files that may carry them, the maximum size of the content in bytes, the content type of the
     * download and the kinds
     */
    private const TYPES = [
        self::TYPE_SUBTITLES => [
            'pattern' => '/^subtitles-([a-z]{2,3}(?:-[A-Za-z0-9]{2,8})*)\.vtt$/',
            'mimeTypes' => ['video/', 'audio/'],
            'maxSize' => 1048576,
            'contentType' => 'text/vtt; charset=utf-8',
            'kinds' => [self::KIND_SUBTITLES, self::KIND_CAPTIONS],
        ],
    ];

    public function __construct(private readonly File $file)
    {
    }

    /**
     * The type and the language of a variant name
     *
     * @return array{type: string, language: string}|null null when the name belongs to no writable type
     */
    public static function parseName(string $name): ?array
    {
        foreach (self::TYPES as $type => $definition) {
            if (preg_match($definition['pattern'], $name, $m)) {
                return ['type' => $type, 'language' => $m[1]];
            }
        }

        return null;
    }

    /**
     * The content type a variant is served with, null when it is none of the writable types
     */
    public static function getContentType(string $name): ?string
    {
        $parsed = self::parseName($name);

        return $parsed === null ? null : self::TYPES[$parsed['type']]['contentType'];
    }

    /**
     * The entries of the file's variants, as kept in the metadata
     *
     * @return array<string, array{kind: string, generated: bool, size: int, updatedAt: int}> by name
     */
    public function getEntries(): array
    {
        $metadata = $this->file->getMetadata();

        return self::readEntries(isset($metadata->{self::METADATA_KEY}) ? $metadata->{self::METADATA_KEY} : null);
    }

    /**
     * @return array<string, array{kind: string, generated: bool, size: int, updatedAt: int}>
     */
    private static function readEntries(mixed $stored): array
    {
        $entries = [];

        foreach (is_array($stored) ? $stored : [] as $name => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            // The list form is the stored one, the keyed one is tolerated
            $entry = isset($entry['kind']) ? $entry : array_combine(
                ['kind', 'generated', 'size', 'updatedAt'],
                array_pad(array_slice(array_values($entry), 0, 4), 4, null),
            );

            $entries[(string)$name] = [
                'kind' => (string)$entry['kind'],
                'generated' => (bool)$entry['generated'],
                'size' => (int)$entry['size'],
                'updatedAt' => (int)$entry['updatedAt'],
            ];
        }

        return $entries;
    }

    public function has(string $name): bool
    {
        return isset($this->getEntries()[$name]);
    }

    /**
     * Stores a variant, a manual save, which replaces an existing one
     *
     * @param string $name the name of the variant
     * @param string $content the content, converted to WebVTT when it is SRT
     * @param string $kind the kind, one of the type's kinds
     * @return array<string, string[]> the errors by field (`name`, `content`, `kind`), empty when it was stored
     * @throws Exception when the content could not be written to the storage
     */
    public function put(string $name, string $content, string $kind): array
    {
        $parsed = self::parseName($name);
        if ($parsed === null) {
            return ['name' => [Yii::t('FileModule.base', 'The name of the variant is invalid.')]];
        }

        $definition = self::TYPES[$parsed['type']];

        if (!$this->isMediaFor($definition)) {
            return ['name' => [Yii::t('FileModule.base', 'This file cannot have subtitles.')]];
        }

        if (!in_array($kind, $definition['kinds'], true)) {
            return ['kind' => [Yii::t('FileModule.base', 'The kind is invalid.')]];
        }

        if (strlen($content) > $definition['maxSize']) {
            return ['content' => [Yii::t('FileModule.base', 'The text is too large, the maximum is {size} MB.', ['size' => $definition['maxSize'] / 1048576])]];
        }

        if (!mb_check_encoding($content, 'UTF-8')) {
            return ['content' => [Yii::t('FileModule.base', 'The text must be UTF-8 encoded.')]];
        }

        $content = WebVtt::prepare($content);
        $errors = WebVtt::validate($content);
        if ($errors !== []) {
            return ['content' => $errors];
        }

        $entry = [
            'kind' => $kind,
            'generated' => false,
            'size' => strlen($content),
            'updatedAt' => time(),
        ];

        try {
            $stored = $this->saveEntry($name, $entry, function () use ($name, $content): bool {
                $store = $this->file->getStore();
                $store->setContent($content, $name);

                return $store->has($name);
            });
        } catch (LengthException) {
            return ['name' => [Yii::t('FileModule.base', 'There are too many variants, delete one first.')]];
        }

        if (!$stored) {
            throw new Exception('Could not store the variant ' . $name . ' of file ' . $this->file->id);
        }

        return [];
    }

    /**
     * Deletes a variant
     *
     * @return bool false when there is none of that name
     */
    public function delete(string $name): bool
    {
        if (!$this->has($name)) {
            return false;
        }

        $this->file->getStore()->delete($name);
        $this->saveEntry($name, null);

        return true;
    }

    private function isMediaFor(array $definition): bool
    {
        foreach ($definition['mimeTypes'] as $prefix) {
            if (str_starts_with((string)$this->file->mime_type, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array|null $entry null removes the entry
     * @param (callable(): bool)|null $commit see [[FileMetadata::update()]]
     * @throws LengthException when the entries don't fit the metadata
     */
    private function saveEntry(string $name, ?array $entry, ?callable $commit = null): bool
    {
        return FileMetadata::update($this->file, function (Metadata $metadata) use ($name, $entry): void {
            // The entries as they are now, not as the record loaded them
            $entries = self::readEntries(isset($metadata->{self::METADATA_KEY}) ? $metadata->{self::METADATA_KEY} : null);

            if ($entry === null) {
                unset($entries[$name]);
            } else {
                $entries[$name] = $entry;
            }

            if ($entries === []) {
                unset($metadata->{self::METADATA_KEY});
            } else {
                $metadata->{self::METADATA_KEY} = array_map(
                    static fn(array $e): array => [$e['kind'], (int)$e['generated'], $e['size'], $e['updatedAt']],
                    $entries,
                );
            }
        }, $commit);
    }
}
