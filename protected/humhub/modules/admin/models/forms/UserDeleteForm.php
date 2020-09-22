<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use Yii;
use yii\base\Model;
use humhub\modules\user\models\User;
use humhub\modules\user\jobs\SoftDeleteUser;
use humhub\modules\user\jobs\DeleteUser;
use humhub\modules\space\helpers\MembershipHelper;
use humhub\modules\space\models\Space;

/**
 * UserDeleteForm shows the deletion options for the admin.
 *
 * @since 1.3
 */
class UserDeleteForm extends Model
{

    /**
     * @var User the user record to delete
     */
    public $user;

    /**
     * @var boolean delete also user contributions
     */
    public $deleteContributions = false;

    /**
     * @var boolean delete also user spaces
     */
    public $deleteSpaces = false;

    /**
     * @var Space[]
     */
    protected $_spaces = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->user->status == User::STATUS_SOFT_DELETED) {
            $this->deleteContributions = true;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [];
        $rules[] = [['deleteSpaces'], 'boolean'];

        if ($this->user->status != User::STATUS_SOFT_DELETED) {
            $rules[] = [['deleteContributions'], 'boolean'];
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'deleteContributions' => Yii::t('AdminModule.user', 'Delete all contributions of this user'),
            'deleteSpaces' => Yii::t('AdminModule.user', 'Delete spaces which are owned by this user'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'deleteContributions' => Yii::t('AdminModule.user', 'Using this option any contributions (e.g. contents, comments or likes) of this user will be irrevocably deleted.'),
            'deleteSpaces' => Yii::t('AdminModule.user', 'If this option is not selected, the ownership of the spaces will be transferred to your account.'),
        ];
    }


    /**
     * @inheritDoc
     */
    public function load($data, $formName = null)
    {
        // Handle empty form submit
        if ($this->user->status == User::STATUS_SOFT_DELETED && Yii::$app->request->isPost) {
            return true;
        }

        return parent::load($data, $formName);
    }

    /**
     * Perform user deletion
     * @since 1.3
     */
    public function performDelete()
    {
        if (!$this->validate()) {
            return false;
        }

        // Handle owned spaces by the deleted user
        $ownedSpaces = MembershipHelper::getOwnSpaces($this->user, false);
        if (count($ownedSpaces) !== 0 && empty($this->deleteSpaces)) {
            foreach ($ownedSpaces as $space) {
                $space->addMember(Yii::$app->user->id);
                $space->setSpaceOwner(Yii::$app->user->id);
            }
        }

        if (empty($this->deleteContributions)) {
            Yii::$app->queue->push(new SoftDeleteUser(['user_id' => $this->user->id]));
        } else {
            Yii::$app->queue->push(new DeleteUser(['user_id' => $this->user->id]));
        }

        return true;
    }

    /**
     * Returns all spaces which are owned by the user
     *
     * @return Space[] the spaces
     */
    public function getOwningSpaces()
    {
        if ($this->_spaces !== null) {
            return $this->_spaces;
        }

        $this->_spaces = MembershipHelper::getOwnSpaces($this->user);
        return $this->_spaces;
    }

}
