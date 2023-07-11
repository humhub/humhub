<?php
/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\components\FileAction;
use humhub\modules\file\models\FileInterface;

/**
 * ImageControllers handles space profile and banner image
 *
 * @author Luke
 */
interface FileControllerInterface
{
    public const ACTION_DELETE = 'delete';
    public const ACTION_DOWNLOAD = 'download';
    public const ACTION_UPLOAD = 'upload';

    public function getActionConfiguration(
        string $actionName,
        bool $throwException = true
    ): ?FileActionConfiguration;

    public function getActionConfigDetection(): bool;

    public function getFile(FileAction $action): ?FileInterface;
}
