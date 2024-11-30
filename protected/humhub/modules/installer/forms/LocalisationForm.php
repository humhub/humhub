<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\forms;

use humhub\libs\DynamicConfig;
use humhub\libs\TimezoneHelper;
use Yii;
use yii\base\Model;

/**
 * LocalisationForm
 *
 * @since 1.17
 */
class LocalisationForm extends Model
{
    public ?string $language = null;
    public ?string $timeZone = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->language = Yii::$app->settings->get('defaultLanguage');
        $this->timeZone = Yii::$app->settings->get('serverTimeZone');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['language'], $this->hasLanguages() ? 'required' : 'safe'],
            [['timeZone'], 'required'],
            [['language'], 'in', 'range' => array_keys($this->getLanguageOptions())],
            [['timeZone'], 'in', 'range' => array_keys($this->getTimeZoneOptions())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'language' => Yii::t('InstallerModule.base', 'Default Language'),
            'timeZone' => Yii::t('InstallerModule.base', 'Default Timezone'),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        Yii::$app->settings->set('defaultLanguage', $this->language);
        Yii::$app->settings->set('serverTimeZone', $this->timeZone);

        DynamicConfig::rewrite();

        return true;
    }

    public function hasLanguages(): bool
    {
        return count($this->getLanguageOptions()) > 1;
    }

    public function getLanguageOptions(): array
    {
        return Yii::$app->i18n->getAllowedLanguages();
    }

    public function getTimeZoneOptions(): array
    {
        return TimezoneHelper::generateList(true);
    }
}
