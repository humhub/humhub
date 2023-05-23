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
    public $template = '{buttons}';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $html = '';

        if ($this->module->isActivated) {
            if ($this->module->getConfigUrl() != '') {
                $html .= Button::asLink(Yii::t('AdminModule.modules', 'Configure'), $this->module->getConfigUrl())
                    ->cssClass('btn btn-sm btn-info active');
            }
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
