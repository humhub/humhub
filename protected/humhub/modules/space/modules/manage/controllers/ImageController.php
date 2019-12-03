<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\controllers;

use humhub\modules\content\components\ContentContainerControllerAccess;
use humhub\modules\content\controllers\ContainerImageController;
use humhub\modules\space\models\Space;

/**
 * ImageControllers handles space profile and banner image
 *
 * @author Luke
 */
class ImageController extends ContainerImageController
{
    public $validContentContainerClasses = [Space::class];

    public function getAccessRules()
    {
        return [
            [ContentContainerControllerAccess::RULE_USER_GROUP_ONLY => [Space::USERGROUP_ADMIN]],
        ];
    }

    public $imageUploadName = 'spacefiles';
    public $bannerUploadName = 'bannerfiles';

}
