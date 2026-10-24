<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use DateTimeZone;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;

/**
 * Form Model for changing basic account settings
 *
 * @since 0.9
 */
class AccountSettings extends Model
{
    public $tags;
    public $language;
    public $markdownEditorMode;
    public $show_introduction_tour;
    public $timeZone;

    public ?User $user = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tags'], 'safe'],
            [['show_introduction_tour'], 'boolean'],
            [['markdownEditorMode'], 'in', 'range' => [0, 1]],
            [['timeZone'], 'in', 'range' => DateTimeZone::listIdentifiers()],
            ['language', 'in', 'range' => array_keys(Yii::$app->i18n->getAllowedLanguages())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tags' => Yii::t('UserModule.account', 'Profile Tags'),
            'language' => Yii::t('UserModule.account', 'Language'),
            'markdownEditorMode' => Yii::t('UserModule.account', 'Markdown Editor Mode'),
            'show_introduction_tour' => Yii::t('UserModule.account', 'Hide introduction tour panel on dashboard'),
            'timeZone' => Yii::t('UserModule.account', 'TimeZone'),
        ];
    }

    public function attributeHints()
    {
        return [
            'tags' => Yii::t('UserModule.account', 'Add tags to your profile describing you and highlighting your skills and interests. Your tags will be displayed in your profile and in the \'People\' directory.'),
        ];
    }

    public function getTags(): array
    {
        return is_array($this->tags) ? $this->tags : [];
    }

    public function getEditorModeList(): array
    {
        return [
            User::EDITOR_RICH_TEXT => Yii::t('UserModule.account', 'Rich Text'),
            User::EDITOR_PLAIN => Yii::t('UserModule.account', 'Plain'),
        ];
    }
}
