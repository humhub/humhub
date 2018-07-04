<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\components;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\admin\permissions\ManageSpaces;
use Yii;
use yii\web\HttpException;

/**
 * Default Space Manage Controller
 *
 * @author luke
 */
class Controller extends ContentContainerController
{

    protected function getAccessRules() {
        return [
            ['login'],
            ['permission' => [ManageSpaces::class]]
        ];
    }
}
