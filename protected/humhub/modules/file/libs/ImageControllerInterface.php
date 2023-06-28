<?php
/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\components\FileAction;
use humhub\modules\file\models\AttachedImage;

interface ImageControllerInterface extends FileControllerInterface
{
    public function getImage(?FileAction $action): ?AttachedImage;
}
