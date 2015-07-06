<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use Yii;
use humhub\modules\user\components\User;
use humhub\modules\space\models\Space;

/**
 * ActiveQueryContent is an enhanced ActiveQuery with additional selectors for especially content.
 * 
 * @inheritdoc
 *
 * @author luke
 */
class ActiveQueryContent extends \yii\db\ActiveQuery
{

    /**
     * Only returns user readable records
     * 
     * @param \humhub\modules\user\models\User $user
     * @return \humhub\modules\content\components\ActiveQueryContent
     */
    public function readable($user = null)
    {
        if ($user === null) {
            $user = Yii::$app->user->getIdentity();
        }

        $this->joinWith(['content']);
        $this->leftJoin('space_membership', 'content.space_id=space_membership.space_id AND space_membership.user_id=:userId', [':userId' => $user->id]);

        // Build Access Check based on Content Container
        $conditionSpace = 'space.id IS NOT NULL AND (';                                         // space content
        $conditionSpace .= ' (space_membership.status=3)';                                      // user is space member
        $conditionSpace .= ' OR (content.visibility=1 AND space.visibility != 0)';               // visibile space and public content
        $conditionSpace .= ')';
        $conditionUser = 'space.id IS NULL AND (';                                              // No Space Content -> User
        $conditionUser .= '   (content.visibility = 1) OR';                                     // public visible content
        $conditionUser .= '   (content.visibility = 0 AND content.user_id=' . $user->id . ')';  // private content of user
        $conditionUser .= ')';

        $this->andWhere("{$conditionSpace} OR {$conditionUser}");

        return $this;
    }

    /**
     * Limits the returned records to the given ContentContainer.
     * 
     * @param ContentContainerActiveRecord $container
     * @return \humhub\modules\content\components\ActiveQueryContent
     * @throws \yii\base\Exception
     */
    public function contentContainer($container)
    {
        $this->joinWith(['content', 'content.user', 'content.space']);

        if ($container->className() == Space::className()) {
            $this->andWhere(['content.space_id' => $container->id]);
        } elseif ($container->className() == User::className()) {
            $this->andWhere(['content.user_id' => $container->id]);
            $this->andWhere('content.space_id IS NULL OR content.space_id = ""');
        } else {
            throw new \yii\base\Exception("Invalid container given!");
        }

        return $this;
    }

    /**
     * Adds an additional WHERE condition to the existing one.
     * 
     * @inheritdoc
     * 
     * @param type $condition
     * @param type $params
     * @return type
     */
    public function where($condition, $params = array())
    {
        return parent::andWhere($condition, $params);
    }

}
