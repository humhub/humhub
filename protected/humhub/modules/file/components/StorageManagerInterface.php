<?php

namespace humhub\modules\file\components;

use humhub\modules\file\models\File;
use yii\web\UploadedFile;

interface StorageManagerInterface
{
    /**
     * Checks if the requested file or version exists.
     *
     * @param string|null $variant optional the variant string
     * @return bool
     */
    public function has(?string $variant = null): bool;

    /**
     * Returns the complete file path to the stored file (variant).
     *
     * @param string|null $variant optional the variant string
     * @return string the complete file path
     */
    public function get(?string $variant = null): string;

    /**
     * Returns the file content.
     *
     * @since 1.19
     * @param string|null $variant optional the variant string
     * @return string file content
     */
    public function getContent(?string $variant = null): string;

    /**
     * Returns the file content as stream.
     *
     * @since 1.19
     * @param string|null $variant optional the variant string
     * @return resource file stream
     */
    public function getContentStream(?string $variant = null);

    /**
     * Adds or overwrites the file by given UploadedFile in store.
     *
     * @param UploadedFile $file
     * @param string|null $variant the variant identifier
     * @see File::setStoredFile() Use this method to set a new file.
     */
    public function set(UploadedFile $file, ?string $variant = null): void;

    /**
     * Adds or overwrites the file content by given string in store
     *
     * @param string|null $content the new file data
     * @param string|null $variant the variant identifier
     * @see File::setStoredFileContent()  Use this method to set a new file content.
     */
    public function setContent(?string $content, ?string $variant = null): void;

    /**
     * Adds or overwrites the file content by given file path
     *
     * @param string $path the new file path
     * @param string|null $variant the variant identifier
     */
    public function setByPath(string $path, ?string $variant = null): void;

    /**
     * Deletes a stored file (-variant)
     *
     * If not variant is given, also all file variants will be deleted
     * @param string|null $variant the variant identifier
     * @param string[] $except exclude following variants from deletion
     */
    public function delete(?string $variant = null, array $except = []): void;

    /**
     * Get file variants
     * @param string[] $except exclude following variants from deletion
     * @return array Returns the stored variants of the file
     */
    public function getVariants(array $except = []): array;

    /**
     * Sets the file for this storage manager instance
     *
     * @param File $file
     */
    public function setFile(File $file): void;

    /**
     * Returns the checksum of a stored file (-variant)
     *
     * @since 1.19
     * @param string|null $variant the variant identifier
     * @param string $algo the hashing algorithms, defaults to sha1
     */
    public function checksum(?string $variant = null, string $algo = 'sha1'): string;

    /**
     * Returns the mimetype of a stored file (-variant)
     *
     * @since 1.19
     * @param string|null $variant the variant identifier
     * @return string the detected mime type
     */
    public function mimeType(?string $variant = null): string;
}
