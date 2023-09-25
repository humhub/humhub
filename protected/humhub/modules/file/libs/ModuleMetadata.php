<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\components\Event;
use humhub\libs\StdClass;
use humhub\modules\file\models\File;

/**
 * WARNING: This class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * ---
 *
 * @since 1.15; this class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * @internal (ToDo)
 *
 * @see File::$metadata
 * @see Metadata
 * @see StdClass
 */
abstract class ModuleMetadata extends StdClass
{
    public const MODULE_NAME = '';

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

    public static function onMetadataInit(Event $e)
    {
        if ($e->name !== Metadata::EVENT_INIT) {
            return;
        }

        $metadata = $e->sender;

        if (!$metadata instanceof Metadata) {
            return;
        }

        $metadata->register(static::MODULE_NAME, static::class);
    }
}
