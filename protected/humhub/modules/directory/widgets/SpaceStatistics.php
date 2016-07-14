<?php

namespace humhub\modules\directory\widgets;

use humhub\modules\space\models\Space;

/**
 * Shows some space statistics in the directory - spaces sidebar.
 *
 * @package humhub.modules_core.directory.views
 * @since 0.5
 * @author Luke
 */
class SpaceStatistics extends \yii\base\Widget
{

    /**
     * Executes the widgets
     */
    public function run()
    {
        $statsCountSpaces = Space::find()->count();
        $statsCountSpacesHidden = Space::find()->where(['visibility' => Space::VISIBILITY_NONE])->count();
        $statsSpaceMostMembers = Space::find()->where('id = (SELECT space_id  FROM space_membership GROUP BY space_id ORDER BY count(*) DESC LIMIT 1)')->one();

        // Render widgets view
        return $this->render('spaceStats', array(
            'statsSpaceMostMembers' => $statsSpaceMostMembers,
            'statsCountSpaces' => $statsCountSpaces,
            'statsCountSpacesHidden' => $statsCountSpacesHidden,
        ));
    }

}

?>
