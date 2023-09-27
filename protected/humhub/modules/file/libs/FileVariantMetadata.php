<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\libs\StdClass;
use humhub\modules\file\models\File;

/**
 * WARNING: This class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * ---
 *
 * This class is used to handle the data stored in the `File::$metadata->file` namespace.
 *
 * @since 1.15; this class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * @internal (ToDo)
 *
 * @see File::$metadata
 * @see Metadata
 * @see StdClass
 */
final class FileVariantMetadata extends StdClass
{
    public ?string $hash = null;
    public ?string $mimeType = null;
    public ?int $size = null;

    public function isModified(): bool
    {
        return !empty($this->hash)
            || !empty($this->mimeType)
            || $this->size !== null
            || parent::isModified();
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true): array
    {
        if (empty($fields)) {
            $fields = $this->fieldsModified(true);

            if ($fields === null) {
                return [];
            }
        }

        return parent::toArray($fields, $expand, $recursive);
    }

    public function isFieldModified(string $field): ?bool
    {
        switch ($field) {
            case 'hash':
            case 'mimeType':
                return  !empty($this->$field);

            case 'size':
                return $this->size !== null;
        }

        return parent::isFieldModified($field);
    }
}
