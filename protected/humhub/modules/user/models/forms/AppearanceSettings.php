<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use Collator;
use DateTimeZone;
use humhub\components\Appearance;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;

/**
 * Account → Appearance & Accessibility → Appearance
 *
 * @since 1.20
 */
class AppearanceSettings extends Model
{
    public $colorMode;
    public $language;
    public $timeZone;
    public $markdownEditorMode;

    public User $user;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->colorMode = Yii::$app->appearance->get(Appearance::COLOR_MODE, $this->user);
        $this->language = Yii::$app->i18n->getAllowedLanguage($this->user->language);
        $this->timeZone = $this->user->time_zone ?: Yii::$app->settings->get('defaultTimeZone');
        $this->markdownEditorMode = $this->user->settings->get('markdownEditorMode');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['colorMode', 'in', 'range' => array_keys($this->getColorModes())],
            ['language', 'in', 'range' => array_keys(Yii::$app->i18n->getAllowedLanguages())],
            ['timeZone', 'in', 'range' => DateTimeZone::listIdentifiers()],
            ['markdownEditorMode', 'in', 'range' => [User::EDITOR_RICH_TEXT, User::EDITOR_PLAIN]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'colorMode' => Yii::t('UserModule.account', 'Colour mode'),
            'language' => Yii::t('UserModule.account', 'Language'),
            'timeZone' => Yii::t('UserModule.account', 'TimeZone'),
            'markdownEditorMode' => Yii::t('UserModule.account', 'Editor'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'colorMode' => Yii::t('UserModule.account', 'Default: {value}', [
                'value' => $this->getColorModes()[Yii::$app->appearance->getDefault(Appearance::COLOR_MODE)] ?? '',
            ]),
        ];
    }

    public function getColorModes(): array
    {
        return [
            Appearance::SYSTEM => Yii::t('UserModule.account', 'Use system setting'),
            Appearance::LIGHT => Yii::t('UserModule.account', 'Light'),
            Appearance::DARK => Yii::t('UserModule.account', 'Dark'),
        ];
    }

    /**
     * @return array the allowed languages, sorted by their name in the current language
     */
    public function getLanguages(): array
    {
        $languages = Yii::$app->i18n->getAllowedLanguages();
        (new Collator(Yii::$app->language))->asort($languages);

        return $languages;
    }

    public function getEditorModes(): array
    {
        return [
            User::EDITOR_RICH_TEXT => Yii::t('UserModule.account', 'Rich Text'),
            User::EDITOR_PLAIN => Yii::t('UserModule.account', 'Markdown'),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        if (Yii::$app->appearance->isDarkModeAllowed()) {
            Yii::$app->appearance->set(Appearance::COLOR_MODE, $this->colorMode, $this->user);
        }
        $this->user->settings->set('markdownEditorMode', $this->markdownEditorMode);

        $this->user->scenario = User::SCENARIO_EDIT_ACCOUNT_SETTINGS;
        $this->user->language = $this->language;
        $this->user->time_zone = $this->timeZone;

        return $this->user->save();
    }
}
