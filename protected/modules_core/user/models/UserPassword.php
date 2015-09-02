<?php

/**
 * This is the model class for table "user_password".
 *
 * The followings are the available columns in table 'user_password':
 * @property integer $id
 * @property integer $user_id
 * @property string $algorithm
 * @property string $password
 * @property string $salt
 * @property string $created_at
 * 
 * Scenarios:
 *      - newPassword     = On Registration, Additional fields: newPassword, newPasswordConfirm
 *      - changePassword  = Additional Fields: currentPassword, newPassword, newPasswordConfirm
 *                    
 * 
 */
class UserPassword extends HActiveRecord
{

    /**
     * Additional Fields for Scenarios
     */
    public $currentPassword;
    public $newPassword;
    public $newPasswordConfirm;
    public $defaultAlgorithm = "";

    /**
     * Init, detect supported hashing 
     */
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

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserPassword the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'user_password';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {

        $rules = array();

        if ($this->scenario == 'newPassword' || $this->scenario == 'changePassword') {
            $rules[] = array('newPassword', 'length', 'min' => 5, 'max' => 255);
            $rules[] = array('newPassword, newPasswordConfirm', 'required');
            $rules[] = array('newPassword', 'compare', 'compareAttribute' => 'newPasswordConfirm', 'message' => 'Passwords did not match!');
        }

        if ($this->scenario == 'changePassword') {
            $rules[] = array('currentPassword', 'CheckPasswordValidator');
        }

        if ($this->scenario != '')
            return $rules;

        return array(
            array('user_id, algorithm, password, salt, created_at', 'required'),
            array('user_id', 'numerical', 'integerOnly' => true),
            array('algorithm', 'length', 'max' => 20),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, user_id, algorithm, password, created_at', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'currentPassword' => Yii::t('UserModule.forms_AccountLoginForm', 'Current password'),
            'newPassword' => Yii::t('UserModule.forms_AccountLoginForm', 'New password'),
            'newPasswordConfirm' => Yii::t('UserModule.forms_AccountLoginForm', 'New password confirm'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'user', 'user_id'),
        );
    }

    /**
     * Before saving an new record, cleanup all old user passwords
     */
    public function afterSave()
    {
        UserPassword::model()->deleteAllByAttributes(array('user_id' => $this->user_id), 'id != :id ', array(':id' => $this->id));
        return parent::afterSave();
    }

    /**
     * Validates a given password against database record
     * 
     * @param string $password unhashed
     * @return boolean Success
     */
    public function validatePassword($password)
    {

        if (CPasswordHelper::same($this->password, $this->hashPassword($password)))
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
            throw new CException("Invalid Hashing Algorithm!");
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

}
