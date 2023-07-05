<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Module;
use humhub\components\Widget;
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
        if (!$this->module->isActivated) {
            return Button::asLink(Yii::t('AdminModule.base', 'Activate'),
                    Url::to(['/admin/module/enable', 'moduleId' => $this->module->id]))
                ->cssClass('btn btn-sm btn-info')
                ->options([
                    'data-method' => 'POST',
                    'data-loader' => 'modal',
                    'data-message' => Yii::t('AdminModule.base', 'Enable module...')
                ]);
        }

        if ($this->module->getConfigUrl() !== '') {
            return Button::asLink(Yii::t('AdminModule.base', 'Configure'), $this->module->getConfigUrl())
                ->cssClass('btn btn-sm btn-info active');
        }

        return '';
    }

}
