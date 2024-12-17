<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\components\Widget;
use humhub\modules\marketplace\models\Module;
use humhub\widgets\Button;
use Yii;

/**
 * ModuleInstalledActionButtons shows actions for module
 *
 * @since 1.15
 * @author Luke
 */
class ModuleInstalledActionButtons extends Widget
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

        if ($this->module->getIsEnabled()) {
            if ($this->module->getConfigUrl() != '') {
                $html .= Button::asLink(Yii::t('MarketplaceModule.base', 'Configure'), $this->module->getConfigUrl())
                    ->cssClass('btn btn-sm btn-info');
            }
            $html .= Button::info(Yii::t('MarketplaceModule.base', 'Enabled'))
                ->link(['/admin/module/list'])
                ->icon('check')
                ->cssClass('active')
                ->sm();
        } else {
            $html .= Button::info(Yii::t('MarketplaceModule.base', 'Enable'))
                ->link(['/admin/module/list'])
                ->sm();
        }

        if (trim($html) === '') {
            return '';
        }

        return str_replace('{buttons}', $html, $this->template);
    }

}
