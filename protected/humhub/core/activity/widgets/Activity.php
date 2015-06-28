<?php

namespace humhub\core\activity\widgets;

use Yii;

/**
 * ActivityWidget shows an activity.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.modules_core.activity
 * @since 0.5
 */
class Activity extends \yii\base\Widget
{

    protected $themePath = 'modules/activity';

    /**
     * @var Activity is the current activity object.
     */
    public $activity;

    /**
     * @var integer If the Widget is linked to a wall entry id
     */
    public $wallEntryId = 0;

    /**
     * Runs the Widget
     */
    public function run()
    {

        // Possible Security Flaw: Check type!
        $type = $this->activity->type;

        $underlyingObject = $this->activity->getUnderlyingObject();

        // Try to figure out wallEntryId of this activity
        $wallEntryId = 0;
        if ($underlyingObject != null) {
            if ($underlyingObject instanceof \humhub\core\content\components\activerecords\Content || $underlyingObject instanceof \humhub\core\content\components\activerecords\ContentAddon) {
                $wallEntryId = $underlyingObject->content->getFirstWallEntryId();
            }
        }

        // When element is assigned to a workspace, assign variable
        $space = null;
        if ($this->activity->content->space_id != "") {
            $space = $this->activity->content->space;
        }

        // User that fired the activity
        $user = $this->activity->content->user;

        if ($user == null) {
            Yii::warning("Skipping activity without valid user", "warning");
            return;
        }


        // Dertermine View
        $view = "";
        if ($this->activity->module == "") {
            $view = '@humhub/core/activity/views/activities/' . $this->activity->type;
        } else {
            $module = Yii::$app->getModule($this->activity->module, true);

            // Autogenerate Module Path
            $path = str_replace(Yii::getAlias('@app'), '', $module->getBasePath());
            $view = '@app/' . $path . '/views/activities/' . $this->activity->type;
        }

        // Activity Layout can access it
        $this->wallEntryId = $wallEntryId;

        return $this->render($view, array(
                    'activity' => $this->activity,
                    'wallEntryId' => $wallEntryId,
                    'user' => $user,
                    'target' => $underlyingObject,
                    'space' => $space,
                    // Deprecated
                    'workspace' => $space
        ));
    }

}

?>