<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\components\Widget;
use humhub\modules\marketplace\models\Licence;
use humhub\modules\marketplace\models\Module;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\bootstrap\Button;
use Yii;

/**
 * ModuleInstallActionButtons shows actions for not installed module
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
    public $template = '<div class="card-footer text-end">{buttons}</div>';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $html = '';

        if (!isset($this->module->latestCompatibleVersion) || $this->module->isInstalled()) {
            return '';
        }

        /** @var \humhub\modules\marketplace\Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        $licence = $marketplaceModule->getLicence();

        if ($this->module->isProFeature() && $licence->type === Licence::LICENCE_TYPE_CE) {
            $html .= Button::primary(Yii::t('MarketplaceModule.base', 'Learn more'))
                ->icon('info-circle')
                ->link('https://www.humhub.com/en/professional-edition')
                ->options(['target' => '_blank'])
                ->sm()
                ->loader(false);
        } elseif ((!empty($this->module->price_request_quote) || !empty($this->module->price_eur)) && !$this->module->purchased) {
            $buyLabel = !empty($this->module->price_request_quote)
                ? Yii::t('MarketplaceModule.base', 'Buy')
                : Yii::t('MarketplaceModule.base', 'Buy (%price%)', ['%price%' => $this->module->price_eur . '€']);

            if ($this->module->isCommunity) {
                $html .= Button::primary($buyLabel)
                    ->action('marketplace.buy', $this->module->checkoutUrl)
                    ->confirm(
                        Yii::t('MarketplaceModule.base', 'Buy unverified community module?'),
                        Yii::t(
                            'MarketplaceModule.base',
                            'You are about to purchase <strong>{moduleName}</strong>, an unverified community module.<br><br>This module is provided by a third party and has not been reviewed or tested by the HumHub team. It may behave unexpectedly, conflict with other modules, or stop working with future HumHub releases.<br><br>Make sure you trust the source before continuing.',
                            ['moduleName' => $this->module->name],
                        ),
                        Yii::t('MarketplaceModule.base', 'Continue to checkout'),
                    )
                    ->sm()
                    ->loader(false);
            } else {
                $html .= Button::primary($buyLabel)
                    ->link($this->module->checkoutUrl)
                    ->sm()
                    ->options(['target' => '_blank'])
                    ->loader(false);
            }
        } else {
            $installButton = Button::primary(Yii::t('MarketplaceModule.base', 'Install'))
                ->action('marketplace.install', ['/marketplace/browse/install'])
                ->options(['data-module-id' => $this->module->id])
                ->sm();

            if ($this->module->isCommunity) {
                $installButton->confirm(
                    Yii::t('MarketplaceModule.base', 'Install unverified community module?'),
                    Yii::t(
                        'MarketplaceModule.base',
                        'You are about to install <strong>{moduleName}</strong>, an unverified community module.<br><br>This module is provided by a third party and has not been reviewed or tested by the HumHub team. It may behave unexpectedly, conflict with other modules, or stop working with future HumHub releases.<br><br>Make sure you trust the source before continuing.',
                        ['moduleName' => $this->module->name],
                    ),
                    Yii::t('MarketplaceModule.base', 'Install anyway'),
                );
            }

            $html .= $installButton;
        }

        if (trim($html) === '') {
            return '';
        }

        return str_replace('{buttons}', $html, $this->template);
    }

}
