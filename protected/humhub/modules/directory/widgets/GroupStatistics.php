<?php

namespace humhub\modules\directory\widgets;

use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;

/**
 * Shows some group statistics in the directory - groups sidebar.
 *
 * @package humhub.modules_core.directory.views
 * @since 0.5
 * @author Luke
 */
class GroupStatistics extends \yii\base\Widget
{

    /**
     * Executes the widgets
     */
    public function run()
    {

        $groups = Group::find()->count();
        $users = User::find()->count();

        $statsAvgMembers = $users / $groups;
        $statsTopGroup = Group::find()->where('id = (SELECT group_id  FROM group_user GROUP BY group_id ORDER BY count(*) DESC LIMIT 1)')->one();

        // Render widgets view
        return $this->render('groupStats', array(
                    'statsTotalGroups' => $groups,
                    'statsAvgMembers' => round($statsAvgMembers, 1),
                    'statsTopGroup' => $statsTopGroup,
                    'statsTotalUsers' => $users,
        ));
    }

}

?>
