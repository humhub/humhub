<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Widget;
use humhub\modules\marketplace\Module;
use Yii;

/**
 * Displays info of available updates for modules
 *
 * @since 1.15
 * @author Luke
 */
class ModuleAvailableUpdates extends Widget
{
    private int $count;

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        if (!Module::isEnabled()) {
            return false;
        }

        /* @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');

        $this->count = count($marketplaceModule->onlineModuleManager->getModuleUpdates());

        if ($this->count === 0) {
            return false;
        }

        return parent::beforeRun();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('moduleAvailableUpdates', ['count' => $this->count]);
    }

}
