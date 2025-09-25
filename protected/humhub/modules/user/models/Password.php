<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\libs\UUID;
use humhub\modules\user\components\CheckPasswordValidator;
use humhub\modules\user\Module;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "user_password".
 *
 * @property int $id
 * @property int $user_id
 * @property string $algorithm
 * @property string $password
 * @property string $salt
 * @property string $created_at
 *
 * @property-read User $user
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
    public $mustChangePassword;

    public function init()
    {
        parent::init();

        $this->defaultAlgorithm = $this->getDefaultAlgorithm();
    }

    protected function getDefaultAlgorithm(): string
    {
        if (function_exists('password_hash')) {
            return 'bcrypt';
        }

        if (function_exists('hash_algos')) {
            $algos = hash_algos();
            if (in_array('sha512', $algos)) {
                return in_array('whirlpool', $algos) ? 'sha512whirlpool' : 'sha512';
            }
        }

        return 'sha1md5';
    }

    public function beforeSave($insert)
    {
        if (empty($this->password) || empty($this->algorithm)) {
            Yii::error(sprintf('Stop saving of empty password for user #%s', $this->user_id), 'user');
            throw new BadRequestHttpException(Yii::t('UserModule.base', 'Empty password cannot be saved!'));
        }

        $this->created_at = date('Y-m-d H:i:s');

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
            [['mustChangePassword'], 'boolean'],
        ];
    }

    /**
     * The new password has to be unequal to the current password.
     *
     * @param string $attribute
     */
    public function unequalsCurrentPassword($attribute)
    {
        if ($this->newPassword === $this->currentPassword) {
            $this->addError($attribute, Yii::t('UserModule.base', 'Your new password must not be equal your current password!'));
        }
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['changePassword'] = ['newPassword', 'newPasswordConfirm'];
        if (CheckPasswordValidator::hasPassword()) {
            $scenarios['changePassword'][] = 'currentPassword';
        }

        $scenarios['registration'] = ['newPassword', 'newPasswordConfirm', 'mustChangePassword'];

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
            'currentPassword' => Yii::t('UserModule.base', 'Current password'),
            'salt' => 'Salt',
            'created_at' => 'Created At',
            'newPassword' => Yii::t('UserModule.base', 'New password'),
            'newPasswordConfirm' => Yii::t('UserModule.base', 'Confirm new password'),
            'mustChangePassword' => Yii::t('UserModule.base', 'Force password change upon first login'),
        ];
    }

    /**
     * @inerhitdoc
     */
    public function attributeHints()
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('user');
        $passwordHint = $module->getPasswordHint();

        return $passwordHint ? ['newPassword' => $passwordHint] : [];
    }

    /**
     * Validates a given password against database record
     *
     * @param string $password unhashed password
     * @return bool
     */
    public function validatePassword(string $password): bool
    {
        if ($this->algorithm === 'bcrypt') {
            return Yii::$app->security->validatePassword($password . $this->salt, $this->password);
        }

        return Yii::$app->security->compareString($this->password, $this->hashPassword($password));
    }

    /**
     * Hashes a password
     *
     * @param string $password
     * @return string Hashed password
     * @throws Exception
     */
    private function hashPassword(string $password): string
    {
        $password .= $this->salt;

        switch ($this->algorithm) {
            case 'bcrypt':
                return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            case 'sha512whirlpool':
                return hash('sha512', hash('whirlpool', $password));
            case 'sha1md5':
                return sha1(md5($password));
            case 'sha512':
                return hash('sha512', $password);
        }

        throw new Exception('Invalid Hashing Algorithm!');
    }

    /**
     * Sets a password and hash it
     *
     * @param string $newPassword
     * @throws Exception
     */
    public function setPassword(string $newPassword)
    {
        $this->salt = UUID::v4();
        $this->algorithm = $this->defaultAlgorithm;
        $this->password = $this->hashPassword($newPassword);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    private function validateAdvancedPasswordRules(string $attribute)
    {
        $userModule = Yii::$app->getModule('user');
        $additionalRules = $userModule->getPasswordStrength();
        if (is_array($additionalRules) && !empty($additionalRules)) {
            foreach ($additionalRules as $pattern => $message) {
                $errorMessage = $userModule->isCustomPasswordStrength()
                    ? Yii::t('UserModule.custom', $message)
                    : $message;
                try {
                    preg_match($pattern, $this->$attribute, $matches);
                    if (!count($matches)) {
                        $this->addError($attribute, $errorMessage);
                    }
                } catch (\Exception $exception) {
                    throw new ErrorException("Wrong regexp in additional password rules. Target: '{$pattern}'");
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->user->auth_key = Yii::$app->security->generateRandomString(32);
        $this->user->save();
        $this->user->isCurrentUser() && Yii::$app->user->switchIdentity($this->user);
    }

}
