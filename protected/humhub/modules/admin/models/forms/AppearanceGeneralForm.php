<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use DateTimeZone;
use Yii;
use yii\base\Model;

/**
 * Administration → Appearance → General
 *
 * @since 1.20
 */
class AppearanceGeneralForm extends Model
{
    public $name;
    public $defaultLanguage;
    public $defaultTimeZone;
    public $dateInputDisplayFormat;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->name = Yii::$app->settings->get('name');
        $this->defaultLanguage = Yii::$app->settings->get('defaultLanguage');
        $this->defaultTimeZone = Yii::$app->settings->get('defaultTimeZone');
        $this->dateInputDisplayFormat = Yii::$app->getModule('admin')->settings->get('defaultDateInputFormat');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 150],
            ['defaultLanguage', 'in', 'range' => array_keys(Yii::$app->i18n->getAllowedLanguages())],
            ['defaultTimeZone', 'in', 'range' => DateTimeZone::listIdentifiers()],
            ['dateInputDisplayFormat', 'in', 'range' => array_keys($this->getDateInputDisplayFormats())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('AdminModule.settings', 'Name of the application'),
            'defaultLanguage' => Yii::t('AdminModule.settings', 'Default language'),
            'defaultTimeZone' => Yii::t('AdminModule.settings', 'Default Timezone'),
            'dateInputDisplayFormat' => Yii::t('AdminModule.settings', 'Date input format'),
        ];
    }

    public function getDateInputDisplayFormats(): array
    {
        return [
            '' => Yii::t(
                'AdminModule.settings',
                'Auto format based on user language - Example: {example}',
                ['{example}' => Yii::$app->formatter->asDate(time(), 'short')],
            ),
            'php:d/m/Y' => Yii::t(
                'AdminModule.settings',
                'Fixed format (dd/mm/yyyy) - Example: {example}',
                ['{example}' => Yii::$app->formatter->asDate(time(), 'php:d/m/Y')],
            ),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        Yii::$app->settings->set('name', $this->name);
        Yii::$app->settings->set('defaultLanguage', $this->defaultLanguage);
        Yii::$app->settings->set('defaultTimeZone', $this->defaultTimeZone);
        Yii::$app->getModule('admin')->settings->set('defaultDateInputFormat', $this->dateInputDisplayFormat);

        return true;
    }
}
