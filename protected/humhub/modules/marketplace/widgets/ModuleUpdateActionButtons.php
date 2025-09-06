<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\components\Widget;
use humhub\modules\marketplace\models\Module;
use humhub\widgets\bootstrap\Button;
use Yii;

/**
 * ModuleInstallActionButtons shows actions for module with available update
 *
 * @since 1.11
 * @author Luke
 */
class ModuleUpdateActionButtons extends Widget
{
    /**
     * @var Module
     */
    public $module;

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

        if (!isset($this->module->latestCompatibleVersion) || !$this->module->isInstalled()) {
            return $html;
        }

        $html .= Button::light(Yii::t('MarketplaceModule.base', 'Update'))
            ->link(['/marketplace/update/install', 'moduleId' => $this->module->id])
            ->sm()
            ->action('marketplace.update');

        $html .= Button::light(Yii::t('MarketplaceModule.base', 'Changelog'))
            ->link($this->module->marketplaceUrl . '/changelog')
            ->sm()
            ->outline()
            ->options(['target' => '_blank']);

        return str_replace('{buttons}', $html, $this->template);
    }

}
