<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\behaviors;

use humhub\components\Module;
use humhub\modules\marketplace\models\Licence;
use humhub\modules\marketplace\Module as MarketplaceModule;
use Yii;
use yii\base\Behavior;

/**
 * Online Module Behavior
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.behaviors
 * @since 1.11
 */
class OnlineModule extends Behavior
{
    /**
     * @inheritdoc
     * @var Module
     */
    public $owner;

    /**
     * @var array the cached info loaded from online
     */
    private $_onlineInfo = null;

    /**
     * @param string|null $field
     * @return array|null|string
     */
    public function getOnlineInfo(?string $field = null)
    {
        if ($this->_onlineInfo !== null) {
            return $this->_onlineInfo;
        }

        /* @var MarketplaceModule $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        if (!($marketplaceModule instanceof MarketplaceModule && $marketplaceModule->enabled)) {
            return null;
        }

        $onlineModules = $marketplaceModule->onlineModuleManager->getModules();

        $this->_onlineInfo = isset($onlineModules[$this->owner->id]) ? $onlineModules[$this->owner->id] : [];

        if ($field === null) {
            return $this->_onlineInfo;
        }

        return $this->_onlineInfo[$field] ?? null;
    }

    public function isProOnly(): bool
    {
        if (empty($this->getOnlineInfo('professional_only'))) {
            return false;
        }

        /* @var MarketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        if (!($marketplaceModule instanceof MarketplaceModule && $marketplaceModule->enabled)) {
            return false;
        }

        return $marketplaceModule->licence->type !== Licence::LICENCE_TYPE_PRO;
    }

    public function getCategories(): array
    {
        $onlineInfo = $this->getOnlineInfo();
        return $onlineInfo['categories'] ?? [];
    }
}
