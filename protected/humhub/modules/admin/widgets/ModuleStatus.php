<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Module;
use humhub\components\OnlineModule;
use humhub\components\Widget;
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

    /**
     * @var Module
     */
    public $module;

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

        $onlineModule = new OnlineModule(['module' => $this->module]);

        if ($onlineModule->isProOnly) {
            $this->_status = 'professional';
        } else if ($onlineModule->isFeatured) {
            $this->_status = 'featured';
        } else if (!$onlineModule->isThirdParty) {
            $this->_status = 'official';
        } else if ($onlineModule->isPartner) {
            $this->_status = 'partner';
        } else if ($onlineModule->isDeprecated) {
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
                return Yii::t('AdminModule.modules', 'Professional Edition');
            case 'featured':
                return Yii::t('AdminModule.modules', 'Featured');
            case 'official':
                return Yii::t('AdminModule.modules', 'Official');
            case 'partner':
                return Yii::t('AdminModule.modules', 'Partner');
            case 'deprecated':
                return Yii::t('AdminModule.modules', 'Deprecated');
            case 'new':
                return Yii::t('AdminModule.modules', 'New');
        }

        return '';
    }

    public function getClass(): string
    {
        return 'card-status-' . $this->status;
    }

}
