<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Widget;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\marketplace\Module;
use humhub\modules\marketplace\services\MarketplaceService;
use Yii;

/**
 * Displays info of available updates for modules
 *
 * @since 1.15
 * @author Luke
 */
class AvailableModuleUpdatesInfo extends Widget
{
    private int $count;

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        if (!Module::isMarketplaceEnabled() || !Yii::$app->user->can(ManageModules::class)) {
            return false;
        }

        $this->count = (new MarketplaceService())->getPendingModuleUpdateCount();

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
        return $this->render('available-module-updates-info', ['count' => $this->count]);
    }

}
