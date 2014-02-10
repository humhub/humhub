<?php

/**
 * This widget is used to show a like link inside the wall entry controls.
 *
 * @package humhub.modules_core.like
 * @since 0.5
 */
class ShowLikesWidget extends HWidget {

    /**
     * The Object to be liked
     *
     * @var type
     */
    public $object;

    /**
     * Executes the widget.
     */
    public function run() {

        $currentUserLiked = false;

        $className = get_class($this->object);
        $id = $this->object->id;

        $likes = Like::GetLikes($className, $id);
        foreach ($likes as $like) {
            if ($like->getUser()->id == Yii::app()->user->id) {
                $currentUserLiked = true;
            }
        }

        // Updates the Like Counter (like.js)
        Yii::app()->clientScript->registerScript(
                "updateLikeCounter" . $this->object->getUniqueId()
                , "updateLikeCounters('" . get_class($this->object) . "', '" . $this->object->id . "', " . count($likes) . ");"
                , CClientScript::POS_READY
        );


        $this->render('showLikes', array(
            'modelName' => $className,
            'modelId' => $id,
            'id' => $this->object->getUniqueId(),
            'currentUserLiked' => $currentUserLiked,
            'likes' => $likes
                )
        );
    }

}

?>