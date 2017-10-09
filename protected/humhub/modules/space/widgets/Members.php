<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use \yii\base\Widget;
use humhub\modules\space\models\Membership;
use yii\db\Expression;
use humhub\modules\space\models\Space;

/**
 * Space Members Snippet
 *
 * @author Luke
 * @since 0.5
 */
class Members extends Widget
{

    /**
     * @var int maximum members to display
     */
    public $maxMembers = 23;

    /**
     * @var Space the space
     */
    public $space;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $users = $this->getUserQuery()->all();

        return $this->render('members', [
                    'space' => $this->space,
                    'maxMembers' => $this->maxMembers,
                    'users' => $users,
                    'showListButton' => (count($users) == $this->maxMembers),
                    'urlMembersList' => $this->space->createUrl('/space/membership/members-list'),
                    'privilegedUserIds' => $this->getPrivilegedUserIds(),
                    'totalMemberCount' => Membership::getSpaceMembersQuery($this->space)->count()
        ]);
    }

    /**
     * Returns a query for members of this space
     * 
     * @return \yii\db\ActiveQuery the query
     */
    protected function getUserQuery()
    {
        $query = Membership::getSpaceMembersQuery($this->space);
        $query->limit($this->maxMembers);
        $query->orderBy(new Expression('FIELD(space_membership.group_id, "' . Space::USERGROUP_OWNER . '", "' . Space::USERGROUP_MODERATOR . '", "' . Space::USERGROUP_MEMBER . '")'));
        return $query;
    }

    /**
     * Returns an array with a list of privileged user ids.
     * 
     * @return array the user ids separated by priviledged group id. 
     */
    protected function getPrivilegedUserIds()
    {
        $privilegedMembers = [Space::USERGROUP_OWNER => [], Space::USERGROUP_ADMIN => [], Space::USERGROUP_MODERATOR => []];

        $query = Membership::find()->where(['space_id' => $this->space->id]);
        $query->andWhere(['IN', 'group_id', [Space::USERGROUP_OWNER, Space::USERGROUP_ADMIN, Space::USERGROUP_MODERATOR]]);
        foreach ($query->all() as $membership) {
            if (isset($privilegedMembers[$membership->group_id])) {
                $privilegedMembers[$membership->group_id][] = $membership->user_id;
            }
        }

        // Add owner manually, since it's not handled as user group yet
        $privilegedMembers[Space::USERGROUP_OWNER][] = $this->space->created_by;

        return $privilegedMembers;
    }

}

?>