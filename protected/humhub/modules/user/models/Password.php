<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use Yii;
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
class Password extends \yii\db\ActiveRecord
{

    /**
     * Additional Fields for Scenarios
     */
    public $currentPassword;
    public $newPassword;
    public $newPasswordConfirm;
    public $defaultAlgorithm = "";

    public function init()
    {
        parent::init();

        $this->defaultAlgorithm = "sha1md5";

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
        $this->created_at = new \yii\db\Expression("NOW()");
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
            [['newPassword', 'newPasswordConfirm'], 'trim'],
            [['user_id'], 'integer'],
            [['password', 'salt'], 'string'],
            [['algorithm'], 'string', 'max' => 20],
            [['currentPassword'], CheckPasswordValidator::className(), 'on' => 'changePassword'],
            [['newPassword', 'newPasswordConfirm', 'currentPassword'], 'required', 'on' => 'changePassword'],
            [['newPassword', 'newPasswordConfirm'], 'string', 'min' => 5, 'max' => 255, 'on' => 'changePassword'],
            [['newPassword'], 'unequalsCurrentPassword', 'on' => 'changePassword'],
            [['newPasswordConfirm'], 'compare', 'compareAttribute' => 'newPassword', 'on' => 'changePassword'],
            [['newPasswordConfirm'], 'compare', 'compareAttribute' => 'newPassword', 'on' => 'registration'],
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
        if($this->newPassword === $this->currentPassword) {
            $this->addError($attribute, Yii::t('UserModule.base', 'Your new password must not equal your current password!'));
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
            'currentPassword' => Yii::t('UserModule.base', 'Current password'),
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

        if (Yii::$app->security->compareString($this->password, $this->hashPassword($password)))
            return true;

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
            throw new \yii\base\Exception("Invalid Hashing Algorithm!");
        }
    }

    /**
     * Sets an password and hash it
     *
     * @param string $password
     */
    public function setPassword($newPassword)
    {
        $this->salt = \humhub\libs\UUID::v4();
        $this->algorithm = $this->defaultAlgorithm;
        $this->password = $this->hashPassword($newPassword);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

}
