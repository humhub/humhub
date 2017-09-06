<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\components;

use humhub\modules\admin\permissions\ManageSpaces;
use Yii;
use yii\web\HttpException;

/**
 * Default Space Manage Controller
 *
 * @author luke
 */
class Controller extends \humhub\modules\content\components\ContentContainerController
{
    /**
     * @inheritdoc
     */
    public $hideSidebar = true;

    
    protected function getAccessRules() {
        return [
            ['login'],
            ['permission' => [
                ManageSpaces::class
            ]]
        ];
    }
}
