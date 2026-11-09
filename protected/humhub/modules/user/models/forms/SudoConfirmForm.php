<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use humhub\modules\user\components\SudoMode;
use Yii;
use yii\base\Model;

/**
 * The password confirmation of the sudo mode (docs/develop/concept-sudo-mode.md)
 *
 * @since 1.20
 */
class SudoConfirmForm extends Model
{
    public ?string $password = null;

    public function __construct(private readonly SudoMode $sudo, $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['password', 'validatePassword', 'skipOnEmpty' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'password' => Yii::t('UserModule.auth', 'Your password'),
        ];
    }

    /**
     * @return bool whether the user confirms with a password; otherwise only a fresh login confirms
     */
    public function hasPasswordFactor(): bool
    {
        return $this->sudo->hasPasswordFactor();
    }

    public function validatePassword(string $attribute): void
    {
        if (!$this->hasPasswordFactor()) {
            $this->addError($attribute, Yii::t('UserModule.auth', 'Please sign in again to continue.'));
            return;
        }

        if ($this->sudo->isThrottled()) {
            $this->addError($attribute, Yii::t('UserModule.auth', 'Too many attempts, try again later.'));
            return;
        }

        if (!$this->sudo->confirm($this->password)) {
            $this->addError($attribute, Yii::t('UserModule.auth', 'Your password is incorrect!'));
        }
    }
}
