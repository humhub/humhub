<?php

namespace humhub\components\fs;

use League\Flysystem\Filesystem;

interface MountConfigInterface
{
    /**
     * Config key passed to `Filesystem::temporaryUrl()`: the `Content-Disposition` header value
     * the storage backend should send when the temporary URL is fetched.
     *
     * Object keys carry no usable file name (uploads are stored as `<guid>/file`), so a download
     * served by a redirect to a temporary URL would otherwise be saved under the key's last
     * segment. Mounts backed by a storage that can override response headers on a temporary URL
     * (e.g. S3's `response-content-disposition`) should honor this key; others ignore it.
     *
     * @since 1.19
     */
    public const CONFIG_CONTENT_DISPOSITION = 'content_disposition';

    /**
     * Config key passed to `Filesystem::temporaryUrl()`: the `Content-Type` header value the
     * storage backend should send when the temporary URL is fetched.
     *
     * @see self::CONFIG_CONTENT_DISPOSITION
     * @since 1.19
     */
    public const CONFIG_CONTENT_TYPE = 'content_type';

    public function getBaseUrl(): ?string;
    public function getFileSystem(): FileSystem;

    public function useTemporaryUrls(): bool;
}
