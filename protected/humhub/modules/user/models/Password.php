<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use Yii;
use yii\base\ErrorException;
use yii\db\ActiveRecord;
use yii\base\Exception;
use yii\db\Expression;
use humhub\libs\UUID;
use humhub\modules\user\components\CheckPasswordValidator;

/**
 * This is the model class for table "user_password".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $algorithm
 * @property string $password
 * @property string $salt
 * @property string $created_at
 */
class Password extends ActiveRecord
{

    /**
     * Additional Fields for Scenarios
     */
    public $currentPassword;
    public $newPassword;
    public $newPasswordConfirm;
    public $defaultAlgorithm = '';

    public function init()
    {
        parent::init();

        $this->defaultAlgorithm = 'sha1md5';

        if (function_exists('hash_algos')) {
            $algos = hash_algos();
            if (in_array('sha512', $algos) && in_array('whirlpool', $algos)) {
                $this->defaultAlgorithm = 'sha512whirlpool';
            } elseif (in_array('sha512', $algos)) {
                $this->defaultAlgorithm = 'sha512';
            }
        }
    }

    public function beforeSave($insert)
    {
        $this->created_at = new Expression('NOW()');

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_password';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['newPassword', 'newPasswordConfirm'], 'required', 'on' => 'registration'],
            [['newPassword', 'newPasswordConfirm'], function ($attribute, $params) {
                $this->validateAdvancedPasswordRules($attribute, $params);
            }],
            [['user_id'], 'integer'],
            [['password', 'salt'], 'string'],
            [['created_at'], 'safe'],
            [['algorithm'], 'string', 'max' => 20],
            [['currentPassword'], CheckPasswordValidator::class, 'on' => 'changePassword'],
            [['newPassword', 'newPasswordConfirm', 'currentPassword'], 'required', 'on' => 'changePassword'],
            [['newPassword'], 'unequalsCurrentPassword', 'on' => 'changePassword'],
            [['newPasswordConfirm'], 'compare', 'compareAttribute' => 'newPassword', 'on' => ['registration', 'changePassword']],
        ];
    }
    
    /**
     * The new password has to be unequal to the current password.
     * 
     * @param type $attribute
     * @param type $params
     */
    public function unequalsCurrentPassword($attribute, $params)
    {
        if ($this->newPassword === $this->currentPassword) {
            $this->addError($attribute, Yii::t('UserModule.base', 'Your new password must not be equal your current password!'));
        }
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['changePassword'] = ['newPassword', 'newPasswordConfirm'];
        if (CheckPasswordValidator::hasPassword()) {
            $scenarios['changePassword'][] = 'currentPassword';
        }

        $scenarios['registration'] = ['newPassword', 'newPasswordConfirm'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'algorithm' => 'Algorithm',
            'password' => Yii::t('UserModule.base', 'Password'),
            'currentPassword' => Yii::t('UserModule.base', 'Current Password'),
            'salt' => 'Salt',
            'created_at' => 'Created At',
            'newPassword' => Yii::t('UserModule.base', 'New password'),
            'newPasswordConfirm' => Yii::t('UserModule.base', 'Confirm new password')
        ];
    }

    /**
     * Validates a given password against database record
     *
     * @param string $password unhashed
     * @return boolean Success
     */
    public function validatePassword($password)
    {

        if (Yii::$app->security->compareString($this->password, $this->hashPassword($password))) {
            return true;
        }

        return false;
    }

    /**
     * Hashes a password
     *
     * @param type $password
     * @param type $algorithm
     * @param type $salt
     * @return Hashed password
     */
    private function hashPassword($password)
    {
        $password .= $this->salt;

        if ($this->algorithm == 'sha1md5') {
            return sha1(md5($password));
        } elseif ($this->algorithm == 'sha512whirlpool') {
            return hash('sha512', hash('whirlpool', $password));
        } elseif ($this->algorithm == 'sha512') {
            return hash('sha512', $password);
        } else {
            throw new Exception('Invalid Hashing Algorithm!');
        }
    }

    /**
     * Sets an password and hash it
     *
     * @param string $password
     */
    public function setPassword($newPassword)
    {
        $this->salt = UUID::v4();
        $this->algorithm = $this->defaultAlgorithm;
        $this->password = $this->hashPassword($newPassword);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    private function validateAdvancedPasswordRules($attribute, $params)
    {
        $userModule = Yii::$app->getModule('user');
        $additionalRules = $userModule->getPasswordStrength();
        if (is_array($additionalRules) && ! empty($additionalRules)) {
            foreach ($additionalRules as $pattern => $message) {
                $errorMessage = $userModule->isCustomPasswordStrength() ?
                    Yii::t('UserModule.custom', $message) :
                    Yii::t('UserModule.base', $message);
                try {
                    preg_match($pattern, $this->$attribute, $matches);
                    if (! count($matches)) {
                        $this->addError($attribute, $errorMessage);
                    }
                } catch (\Exception $exception) {
                    throw new ErrorException("Wrong regexp in additional password rules. Target: '{$pattern}'");
                }
            }
        }
    }

}
