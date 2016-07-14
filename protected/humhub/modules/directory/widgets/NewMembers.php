<?php

namespace humhub\modules\directory\widgets;

use humhub\modules\user\models\User;

/**
 * Shows newly registered users as sidebar widget
 *
 * @package humhub.modules_core.directory.widgets
 * @since 0.11
 * @author Luke
 */
class NewMembers extends \yii\base\Widget
{

    public $showMoreButton = false;

    /**
     * Executes the widgets
     */
    public function run()
    {

        $newUsers = User::find()->orderBy('created_at DESC')->active()->limit(10)->all();

        // Render widgets view
        return $this->render('newMembers', array(
                    'newUsers' => $newUsers, // new users
                    'showMoreButton' => $this->showMoreButton
        ));
    }

}

?>
