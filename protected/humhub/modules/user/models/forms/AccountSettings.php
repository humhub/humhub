<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

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
    public $show_introduction_tour;

    public ?User $user = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tags'], 'safe'],
            [['show_introduction_tour'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tags' => Yii::t('UserModule.account', 'Profile Tags'),
            'show_introduction_tour' => Yii::t('UserModule.account', 'Hide introduction tour panel on dashboard'),
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
}
