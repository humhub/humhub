<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
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
 * @since 1.11
 * @author Luke
 */
class ModuleActionButtons extends Widget
{

    /**
     * @var Module
     */
    public $module;

    /**
     * @var string Template for buttons
     */
    public $template = '<div class="card-footer text-right">{buttons}</div>';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $html = '';

        if ($this->module->isActivated) {
            if ($this->module->getConfigUrl() != '') {
                $html .= Button::asLink(Yii::t('AdminModule.modules', 'Configure'), $this->module->getConfigUrl())
                    ->cssClass('btn btn-sm btn-info');
            }
            $html .= Button::asLink('<span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;' . Yii::t('AdminModule.modules', 'Activated'), Url::to(['/admin/module/disable', 'moduleId' => $this->module->id]))
                ->cssClass('btn btn-sm btn-info active')
                ->options([
                    'data-method' => 'POST',
                    'data-confirm' => Yii::t('AdminModule.modules', 'Are you sure? *ALL* module data will be lost!')
                ]);
        } else {
            $html .= Button::asLink(Yii::t('AdminModule.modules', 'Activate'), Url::to(['/admin/module/enable', 'moduleId' => $this->module->id]))
                ->cssClass('btn btn-sm btn-info')
                ->options([
                    'data-method' => 'POST',
                    'data-loader' => 'modal',
                    'data-message' => Yii::t('AdminModule.modules', 'Enable module...')
                ]);
        }

        if (trim($html) === '') {
            return '';
        }

        return str_replace('{buttons}', $html, $this->template);
    }

}
