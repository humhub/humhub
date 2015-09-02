<?php

/**
 * ActivityWidget shows an activity.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.modules_core.activity
 * @since 0.5
 */
class ActivityWidget extends HWidget {

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
    public function run() {

        // Possible Security Flaw: Check type!
        $type = $this->activity->type;

        $underlyingObject = $this->activity->getUnderlyingObject();

        // Try to figure out wallEntryId of this activity
        $wallEntryId = 0;
        if ($underlyingObject != null) {
            if ($underlyingObject instanceof HActiveRecordContent || $underlyingObject instanceof HActiveRecordContentAddon) {
                $wallEntryId = $underlyingObject->content->getFirstWallEntryId();
            }
        }

        // When element is assigned to a workspace, assign variable
        $workspace = null;
        if ($this->activity->content->space_id != "") {
            $workspace = Space::model()->findByPk($this->activity->content->space_id);
        }

        // User that fired the activity
        $user = $this->activity->content->user;

        if ($user == null) {
            Yii::log("Skipping activity without valid user", "warning");
            return;
        }


        // Dertermine View
        $view = "";
        if ($this->activity->module == "") {
            $view = 'application.modules_core.activity.views.activities.' . $this->activity->type;
        } else {
            $view = $this->activity->module . '.views.activities.' . $this->activity->type;
        }

        // Activity Layout can access it
        $this->wallEntryId = $wallEntryId;

        $this->render($view, array(
            'activity' => $this->activity,
            'wallEntryId' => $wallEntryId,
            'user' => $user,
            'target' => $underlyingObject,
            //'contentTarget' => $contentTarget,
            'workspace' => $workspace
        ));
    }

}

?>