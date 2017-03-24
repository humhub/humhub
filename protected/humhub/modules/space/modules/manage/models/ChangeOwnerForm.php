<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
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
        return [
            ['ownerId', 'required'],
            ['ownerId', 'in', 'range' => array_keys($this->getNewOwnerArray())]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ownerId' => Yii::t('SpaceModule.manage', 'Space owner'),
        ];
    }

    /**
     * Returns an array of all possible space owners
     * 
     * @return array containing the user id as key and display name as value
     */
    public function getNewOwnerArray()
    {
        $possibleOwners = [];

        $query = Membership::find()->joinWith(['user', 'user.profile'])->andWhere(['space_membership.group_id' => 'admin', 'space_membership.space_id' => $this->space->id]);
        foreach ($query->all() as $membership) {
            $possibleOwners[$membership->user->id] = $membership->user->displayName;
        }

        return $possibleOwners;
    }

}
