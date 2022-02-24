<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\Module;
use humhub\components\Widget;
use humhub\modules\space\models\Space;
use humhub\widgets\Button;
use Yii;

/**
 * ModuleActionsButton shows actions for module of Space on creating it
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
     * @var Space
     */
    public $space;

    /**
     * @var string Template for buttons
     */
    public $template = '<div class="card-footer">{buttons}</div>';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $html = '';

        $html .= Button::asLink(Yii::t('SpaceModule.manage', 'Activate'), '#')
            ->cssClass('btn btn-sm btn-info enable')
            ->style($this->space->isModuleEnabled($this->module->id) ? 'display:none' : '')
            ->loader()
            ->options([
                'data-action-click' => 'content.container.enableModule',
                'data-action-url' => $this->space->createUrl('/space/manage/module/enable', ['moduleId' => $this->module->id]),
            ]);

        $html .= Button::asLink('<span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;' . Yii::t('ContentModule.modules', 'Activated'), '#')
            ->cssClass('btn btn-sm btn-info active disable')
            ->style(!$this->space->isModuleEnabled($this->module->id) ? 'display:none' : '')
            ->loader()
            ->options([
                'data-action-click' => 'content.container.disableModule',
                'data-action-url' => $this->space->createUrl('/space/manage/module/disable', ['moduleId' => $this->module->id]),
            ]);

        if (trim($html) === '') {
            return '';
        }

        return str_replace('{buttons}', $html, $this->template);
    }

}
