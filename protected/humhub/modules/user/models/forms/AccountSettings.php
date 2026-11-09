<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;

/**
 * Form model of the "Introduction tour" section of the account settings
 *
 * @since 0.9
 */
class AccountSettings extends Model
{
    public $show_introduction_tour;

    public ?User $user = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->user !== null) {
            $this->show_introduction_tour = $this->getSettings()->get('hideTourPanel');
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['show_introduction_tour'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'show_introduction_tour' => Yii::t('UserModule.account', 'Hide introduction tour panel on dashboard'),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->getSettings()->set('hideTourPanel', $this->show_introduction_tour);

        return true;
    }

    private function getSettings(): ContentContainerSettingsManager
    {
        return Yii::$app->getModule('tour')->settings->contentContainer($this->user);
    }
}
