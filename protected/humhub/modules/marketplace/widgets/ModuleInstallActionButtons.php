<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\components\Widget;
use humhub\modules\marketplace\models\Module;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\Button;
use Yii;

/**
 * ModuleInstallActionButtons shows actions for not installed module
 * 
 * @since 1.11
 * @author Luke
 */
class ModuleInstallActionButtons extends Widget
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

        if (!isset($this->module->latestCompatibleVersion) || $this->module->isInstalled()) {
            return '';
        }

        if ($this->module->isProOnly()) {
            $html .= Button::asLink(Icon::get('info-circle') . '&nbsp;&nbsp;' . Yii::t('MarketplaceModule.base', 'Professional Edition'), ['/admin/information'])
                ->cssClass('btn btn-sm btn-default');
        } elseif (!empty($this->module->price_request_quote) && !$this->module->purchased) {
            $html .= Button::asLink(Yii::t('MarketplaceModule.base', 'Buy'), $this->module->checkoutUrl)
                ->cssClass('btn btn-sm btn-primary')
                ->options(['target' => '_blank']);
        } elseif (!empty($this->module->price_eur) && !$this->module->purchased) {
            $html .= Button::asLink(Yii::t('MarketplaceModule.base', 'Buy (%price%)', ['%price%' => $this->module->price_eur . '&euro;']), $this->module->checkoutUrl)
                ->cssClass('btn btn-sm btn-primary')
                ->options(['target' => '_blank']);
        } else {
            $html .= Button::asLink(Yii::t('MarketplaceModule.base', 'Install'), ['/marketplace/browse/install', 'moduleId' => $this->module->id])
                ->cssClass('btn btn-sm btn-primary')
                ->options([
                    'data-method' => 'POST',
                    'data-loader' => 'modal',
                    'data-message' => Yii::t('MarketplaceModule.base', 'Installing module...'),
                ]);
        }

        if (trim($html) === '') {
            return '';
        }

        return str_replace('{buttons}', $html, $this->template);
    }

}
