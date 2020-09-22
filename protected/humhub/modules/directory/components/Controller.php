<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\components;

use humhub\components\access\StrictAccess;
use humhub\modules\directory\Module;
use Yii;

/**
 * Directory Base Controller
 *
 * @author luke
 */
class Controller extends \humhub\components\Controller
{

    /**
     * @inheritdoc
     */
    public $access = StrictAccess::class;

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['checkModuleActive']
        ];
    }

    /**
     * Global access rule for the current user.
     *
     * @param $rule
     * @param $access
     * @return bool
     */
    public function checkModuleActive($rule, $access)
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('directory');
        if(!$module->canAccess()) {
            $access->code = 403;
            return false;
        }

        return true;
    }



    public function init() {
        $this->appendPageTitle(\Yii::t('DirectoryModule.base', 'Directory'));
        return parent::init();
    }
    
    /**
     * @inheritdoc
     */
    public $subLayout = "@humhub/modules/directory/views/directory/_layout";

}
