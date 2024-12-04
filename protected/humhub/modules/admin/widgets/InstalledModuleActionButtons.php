<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Module;
use humhub\components\Widget;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\widgets\Button;
use Yii;
use yii\helpers\Url;

/**
 * ModuleActionsButton shows actions for module
 *
 * @since 1.15
 * @author Luke
 */
class InstalledModuleActionButtons extends Widget
{
    public Module $module;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!$this->module->getIsEnabled() && Yii::$app->user->can(ManageModules::class)) {
            return Button::asLink(
                Yii::t('AdminModule.base', 'Enable'),
                Url::to(['/admin/module/enable', 'moduleId' => $this->module->id]),
            )
                ->cssClass('btn btn-sm btn-info')
                ->options([
                    'data-method' => 'POST',
                    'data-loader' => 'modal',
                    'data-message' => Yii::t('AdminModule.base', 'Enable module...'),
                ]);
        }

        if ($this->module->getConfigUrl() !== '' && Yii::$app->user->can(ManageSettings::class)) {
            return Button::asLink(Yii::t('AdminModule.base', 'Configure'), $this->module->getConfigUrl())
                ->cssClass('btn btn-sm btn-info active');
        }

        return '';
    }

}
