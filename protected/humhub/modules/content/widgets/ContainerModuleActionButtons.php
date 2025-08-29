<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\components\Module;
use humhub\components\Widget;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\widgets\bootstrap\Button;
use Yii;

/**
 * ContainerModuleActionButtons shows actions for module of Content Container
 *
 * @since 1.15
 * @author Luke
 */
class ContainerModuleActionButtons extends Widget
{
    public Module $module;

    public ContentContainerActiveRecord $contentContainer;

    public array $buttons = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $isEnabled = $this->contentContainer->moduleManager->isEnabled($this->module->id);

        if ($this->module->getContentContainerConfigUrl($this->contentContainer) && $isEnabled) {
            $this->buttons[] = Button::asLink(Yii::t('ContentModule.base', 'Configure'), $this->module->getContentContainerConfigUrl($this->contentContainer))
                ->cssClass('btn btn-sm btn-accent configure-module-' . $this->module->id);
        }

        if ($this->contentContainer->moduleManager->canDisable($this->module->id)) {
            $this->buttons[] = Button::accent(Yii::t('ContentModule.base', 'Enabled'))
                ->sm()
                ->icon('check')
                ->cssClass('active disable disable-module-' . $this->module->id . ($isEnabled ? '' : ' d-none'))
                ->action('content.container.disableModule', $this->getDisableUrl())
                ->options(['data-reload' => '1'])
                ->confirm(
                    Yii::t('AdminModule.modules', 'Disable Module'),
                    $this->getDisableConfirmationText(),
                    Yii::t('AdminModule.base', 'Disable'),
                );
        }

        $this->buttons[] = Button::accent(Yii::t('ContentModule.base', 'Enable'))
            ->sm()
            ->cssClass('enable enable-module-' . $this->module->id . ($isEnabled ? ' d-none' : ''))
            ->action('content.container.enableModule', $this->getEnableUrl())
            ->options(['data-reload' => '1']);

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return implode('', $this->buttons);
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
            ? Yii::t('ContentModule.manage', 'Disabling the <strong>{moduleName}</strong> module will permanently delete <strong>all</strong> module-related content from the Space.', ['moduleName' => $this->module->getName()])
            : Yii::t('ContentModule.manage', 'Disabling the <strong>{moduleName}</strong> module will permanently delete <strong>all</strong> module-related content from your Profile.', ['moduleName' => $this->module->getName()]);
    }

    private function getEnableUrl(): string
    {
        $route = $this->isSpace() ? '/space/manage/module/enable' : '/user/account/enable-module';
        return $this->contentContainer->createUrl($route, ['moduleId' => $this->module->id]);
    }

}
