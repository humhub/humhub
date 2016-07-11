<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\components;

/**
 * Directory Base Controller
 *
 * @author luke
 */
class Controller extends \humhub\components\Controller
{

    public function init() {
        $this->appendPageTitle(\Yii::t('DirectoryModule.base', 'Directory'));
        return parent::init();
    }
    
    /**
     * @inheritdoc
     */
    public $subLayout = "@humhub/modules/directory/views/directory/_layout";

}
