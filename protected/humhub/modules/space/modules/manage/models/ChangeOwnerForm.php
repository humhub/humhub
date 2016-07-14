<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\models;

use Yii;
use yii\base\Model;
use humhub\modules\space\models\Membership;

/**
 * Form Model for space owner change
 *
 * @since 0.5
 */
class ChangeOwnerForm extends Model
{

    /**
     * @var \humhub\modules\space\models\Space
     */
    public $space;

    /**
     * @var string owner id
     */
    public $ownerId;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            array('ownerId', 'required'),
            array('ownerId', 'in', 'range' => array_keys($this->getNewOwnerArray()))
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'ownerId' => Yii::t('SpaceModule.manage', 'Space owner'),
        );
    }

    /**
     * Returns an array of all possible space owners
     */
    public function getNewOwnerArray()
    {
        $possibleOwners = [];

        $query = Membership::find()->joinWith(['user', 'user.profile'])->andWhere(['space_membership.group_id' => 'admin']);
        foreach ($query->all() as $membership) {
            $possibleOwners[$membership->user->id] = $membership->user->displayName;
        }

        return $possibleOwners;
    }

}
