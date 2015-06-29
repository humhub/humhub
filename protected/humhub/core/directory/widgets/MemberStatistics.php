<?php

namespace humhub\core\directory\widgets;

use humhub\core\user\models\User;
use humhub\core\user\models\Follow;

/**
 * Shows some membership statistics in the directory - members sidebar.
 *
 * @package humhub.modules_core.directory.views
 * @since 0.5
 * @author Luke
 */
class MemberStatistics extends \yii\base\Widget
{

    /**
     * Executes the widgets
     */
    public function run()
    {

        // Some member stats
        $statsTotalUsers = User::find()->count();

        $statsUserOnline = 99;
        $statsUserFollow = Follow::find()->where(['object_model' => User::className()])->count();

        // Render widgets view
        return $this->render('memberStats', array(
            'statsTotalUsers' => $statsTotalUsers,
            'statsUserOnline' => $statsUserOnline,
            'statsUserFollow' => $statsUserFollow
        ));
    }

}

?>
