<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\controllers;

use humhub\components\access\ControllerAccess;
use humhub\modules\file\components\BaseFileController;

/**
 * UploadController provides uploading functions for files
 *
 * @since 0.5
 *
 * @property-read array[][] $accessRules
 */
class FileController extends BaseFileController
{
    // protected properties
    /**
     * @inheritdoc
     */
    protected $access = ControllerAccess::class;

    /**
     * @inheritdoc
     */
    public function getAccessRules(): array
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY => ['upload', 'delete']],
        ];
    }
}
