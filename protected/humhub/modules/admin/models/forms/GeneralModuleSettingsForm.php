<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\modules\marketplace\Module;
use Yii;
use yii\base\Model;

/**
 * ModuleFilterSettingsForm is used to modify module filter settings
 *
 * @package humhub.modules_core.admin.forms
 * @since 1.11
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

    public function init()
    {
        parent::init();

        $this->marketplaceModule = Yii::$app->getModule('marketplace');
        $this->includeBetaUpdates = (bool)$this->marketplaceModule->settings->get('includeBetaUpdates', false);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['includeBetaUpdates'], 'boolean'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'includeBetaUpdates' => Yii::t('AdminModule.modules', 'Allow module versions in beta status')
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
        }

        return true;
    }
}
