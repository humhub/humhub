<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\modules\file\models\File;
use LengthException;
use RuntimeException;
use Yii;

/**
 * Changes the metadata of a file without losing what another process wrote meanwhile
 *
 * The column is rewritten as a whole, so each change re-reads it from the database under a lock per file, applies
 * itself and writes it back. Writers like [[ImageHelper]], [[MediaHelper]] and [[FileVariants]] each own some keys
 * and must not overwrite the ones of the others with the copy their record loaded earlier.
 *
 * @since 1.20
 */
class FileMetadata
{
    /**
     * @var int the length of the column, used when the schema doesn't tell
     */
    public const MAX_LENGTH = 4000;

    private const LOCK_TIMEOUT = 10;

    /**
     * The length of the serialized metadata the column holds
     */
    public static function getMaxLength(): int
    {
        $column = File::getTableSchema()?->getColumn('metadata');

        return $column?->size ?: self::MAX_LENGTH;
    }

    /**
     * Applies a change to the current metadata of a file and stores it; the metadata of the record is updated.
     *
     * @param callable(Metadata): void $mutate changes the metadata it gets, freshly read
     * @param (callable(): bool)|null $commit called under the lock once the new metadata is known to fit, before
     * the write: it may do the work that belongs to the change (a stored file) and returns false to cancel it
     * @return bool whether the metadata is as the change wants it, false when `$commit` cancelled
     * @throws LengthException when the serialized metadata would not fit the column
     * @throws RuntimeException when the lock could not be acquired
     */
    public static function update(File $file, callable $mutate, ?callable $commit = null): bool
    {
        if ($file->isNewRecord) {
            return self::apply($file, false, $mutate, $commit);
        }

        $name = 'file-metadata-' . $file->id;
        $mutex = Yii::$app->mutex;

        if (!$mutex->acquire($name, self::LOCK_TIMEOUT)) {
            throw new RuntimeException('Could not lock the metadata of file ' . $file->id);
        }

        try {
            return self::apply(
                $file,
                File::find()->select('metadata')->where(['id' => $file->id])->scalar(),
                $mutate,
                $commit,
            );
        } finally {
            $mutex->release($name);
        }
    }

    private static function apply(File $file, string|false|null $stored, callable $mutate, ?callable $commit): bool
    {
        $metadata = $stored === false
            ? $file->getMetadata()
            : new Metadata($stored === '' ? null : $stored);

        $before = count($metadata) > 0 ? (string)$metadata : null;
        $mutate($metadata);
        $after = count($metadata) > 0 ? (string)$metadata : null;

        if ($after !== null && strlen($after) > self::getMaxLength()) {
            throw new LengthException('The metadata of file ' . $file->id . ' is too long.');
        }

        if ($commit !== null && $commit() === false) {
            return false;
        }

        if ($after !== $before) {
            $file->updateAttributes(['metadata' => $after]);
        }

        return true;
    }
}
