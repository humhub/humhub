<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\components\Module;
use humhub\components\Widget;
use humhub\modules\content\components\behaviors\CompatModuleManager;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\widgets\Button;
use Yii;

/**
 * ModuleActionsButton shows actions for module of Content Container
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
     * @var ContentContainerActiveRecord|CompatModuleManager
     */
    public $contentContainer;

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

        if ($this->module->getContentContainerConfigUrl($this->contentContainer) && $this->contentContainer->isModuleEnabled($this->module->id)) {
            $html .= Button::asLink(Yii::t('ContentModule.modules', 'Configure'), $this->module->getContentContainerConfigUrl($this->contentContainer))
                ->cssClass('btn btn-sm btn-info configure-module-' . $this->module->id);
        }

        if ($this->contentContainer->canDisableModule($this->module->id)) {
            $html .= Button::asLink('<span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;' . Yii::t('ContentModule.modules', 'Activated'), '#')
                ->cssClass('btn btn-sm btn-info active disable disable-module-' . $this->module->id)
                ->style($this->contentContainer->isModuleEnabled($this->module->id) ? '' : 'display:none')
                ->options([
                    'data-action-click' => 'content.container.disableModule',
                    'data-action-url' => $this->getDisableUrl(),
                    'data-reload' => '1',
                    'data-action-confirm' => $this->getDisableConfirmationText(),
                    'data-ui-loader' => 1,
                ]);
        }

        $html .= Button::asLink(Yii::t('ContentModule.modules', 'Enable'), '#')
            ->cssClass('btn btn-sm btn-info enable enable-module-' . $this->module->id)
            ->style($this->contentContainer->isModuleEnabled($this->module->id) ? 'display:none' : '')
            ->options([
                'data-action-click' => 'content.container.enableModule',
                'data-action-url' => $this->getEnableUrl(),
                'data-reload' => '1',
                'data-ui-loader' => 1,
            ]);

        if (trim($html) === '') {
            return '';
        }

        return str_replace('{buttons}', $html, $this->template);
    }

    private function isSpace(): bool
    {
        return $this->contentContainer instanceof Space;
    }

    private function getDisableUrl(): string
    {
        $route = $this->isSpace() ? '/space/manage/module/disable' : '/user/account/disable-module';
        return $this->contentContainer->createUrl($route, ['moduleId' => $this->module->id]);
    }

    private function getDisableConfirmationText(): string
    {
        return $this->isSpace()
            ? Yii::t('ContentModule.manage', 'Are you sure? *ALL* module data for this space will be deleted!')
            : Yii::t('ContentModule.manage', 'Are you really sure? *ALL* module data for your profile will be deleted!');
    }

    private function getEnableUrl(): string
    {
        $route = $this->isSpace() ? '/space/manage/module/enable' : '/user/account/enable-module';
        return $this->contentContainer->createUrl($route, ['moduleId' => $this->module->id]);
    }

}
