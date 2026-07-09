<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\models\forms;

use humhub\modules\marketplace\Module;
use Yii;
use yii\base\Model;

/**
 * ModuleFilterSettingsForm is used to modify module filter settings
 *
 * @package humhub.modules_core.admin.forms
 * @since 1.15
 */
class GeneralModuleSettingsForm extends Model
{
    /**
     * @var Module
     */
    private $marketplaceModule;

    /**
     * @var bool
     */
    public $includeBetaUpdates;

    /**
     * @var bool
     * @since 1.19
     */
    public $includeCommunityModules;

    public function init()
    {
        parent::init();

        $this->marketplaceModule = Yii::$app->getModule('marketplace');
        $this->includeBetaUpdates = (bool)$this->marketplaceModule->settings->get('includeBetaUpdates', false);
        $this->includeCommunityModules = (bool)$this->marketplaceModule->settings->get('includeCommunityModules', false);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['includeBetaUpdates', 'includeCommunityModules'], 'boolean'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'includeBetaUpdates' => Yii::t('MarketplaceModule.base', 'Allow module versions in beta status'),
            'includeCommunityModules' => Yii::t('MarketplaceModule.base', 'Include community modules'),
        ];
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        if ($this->marketplaceModule) {
            $this->marketplaceModule->settings->set('includeBetaUpdates', $this->includeBetaUpdates);
            $this->marketplaceModule->settings->set('includeCommunityModules', $this->includeCommunityModules);
            Yii::$app->cache->delete('marketplace-categories');
        }

        return true;
    }
}
