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
final class FileModuleMetadata extends ModuleMetadata
{
    public const MODULE_NAME = 'file';
}
