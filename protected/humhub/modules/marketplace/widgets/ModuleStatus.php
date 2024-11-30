<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\widgets;

use humhub\components\Widget;
use humhub\modules\marketplace\models\Module;
use Yii;

/**
 * ModuleStatus shows a status of the module
 *
 * @property-read string|null $status
 * @property-read string|null $statusTitle
 * @property-read string $class
 *
 * @since 1.11
 * @author Luke
 */
class ModuleStatus extends Widget
{
    public Module $module;

    /**
     * @var string HTML wrapper around the status
     */
    public $template = '<div class="card-status {class}">{status}</div>';

    /**
     * @var string|null Cached status of the module
     */
    private $_status;

    /**
     * @inheritdoc
     */
    public function run()
    {
        return str_replace(['{status}', '{class}'], [$this->statusTitle, $this->class], $this->template);
    }

    /**
     * @return false|string|null
     */
    public function getStatus()
    {
        if ($this->_status !== null) {
            return $this->_status;
        }

        if ($this->module->isProFeature()) {
            $this->_status = 'professional';
        } elseif ($this->module->featured) {
            $this->_status = 'featured';
        } elseif (!$this->module->isThirdParty) {
            $this->_status = 'official';
        } elseif ($this->module->isPartner) {
            $this->_status = 'partner';
        } elseif ($this->module->isDeprecated) {
            $this->_status = 'deprecated';
        } else {
            $this->_status = 'none';
        }
        // TODO: Implement new status detection

        return $this->_status;
    }

    public function getStatusTitle(): string
    {
        switch ($this->status) {
            case 'professional':
                return Yii::t('MarketplaceModule.base', 'Professional Edition');
            case 'featured':
                return Yii::t('MarketplaceModule.base', 'Featured');
            case 'official':
                return Yii::t('MarketplaceModule.base', 'Official');
            case 'partner':
                return Yii::t('MarketplaceModule.base', 'Partner');
            case 'deprecated':
                return Yii::t('MarketplaceModule.base', 'Deprecated');
            case 'new':
                return Yii::t('MarketplaceModule.base', 'New');
        }

        return '';
    }

    public function getClass(): string
    {
        return 'card-status-' . $this->status;
    }

}
