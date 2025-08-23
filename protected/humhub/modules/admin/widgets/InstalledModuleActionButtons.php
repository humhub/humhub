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
use humhub\widgets\bootstrap\Button;
use humhub\widgets\modal\ModalButton;
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
            return ModalButton::accent(Yii::t('AdminModule.base', 'Enable'))
                ->sm()
                ->post(['/admin/module/enable', 'moduleId' => $this->module->id])
                ->options(['data-message' => Yii::t('AdminModule.base', 'Enable module...')]);
        }

        if ($this->module->getConfigUrl() !== '' && Yii::$app->user->can(ManageSettings::class)) {
            return Button::accent(Yii::t('AdminModule.base', 'Configure'))
                ->link($this->module->getConfigUrl())
                ->sm()
                ->outline();
        }

        return '';
    }

}
