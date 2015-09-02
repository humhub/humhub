<?php

/**
 * This widget is used to show a like link inside the wall entry controls.
 *
 * @package humhub.modules_core.like
 * @since 0.5
 */
class LikeLinkWidget extends HWidget
{

    /**
     * The Object to be liked
     *
     * @var type
     */
    public $object;

    /**
     * Inits the Widget
     *
     */
    public function init()
    {

        // Inject some important Javascript Variables
        Yii::app()->clientScript->setJavascriptVariable(
                "likeUrl", Yii::app()->createUrl('//like/like/like', array('contentModel' => '-className-', 'contentId' => '-id-'))
        );
        Yii::app()->clientScript->setJavascriptVariable(
                "unlikeUrl", Yii::app()->createUrl('//like/like/unlike', array('contentModel' => '-className-', 'contentId' => '-id-'))
        );
        Yii::app()->clientScript->setJavascriptVariable(
                "showLikesUrl", Yii::app()->createUrl('//like/like/showLikes', array('contentModel' => '-className-', 'contentId' => '-id-'))
        );


        // Updates the Like Counter (like.js)
        $likes = Like::GetLikes(get_class($this->object), $this->object->id);
        Yii::app()->clientScript->registerScript(
                "updateLikeCounter" . $this->object->getUniqueId()
                , "updateLikeCounters('" . get_class($this->object) . "', '" . $this->object->id . "', " . count($likes) . ");"
                , CClientScript::POS_READY
        );

        // Ensure Like Javascript is loaded
        Yii::app()->clientScript->registerScriptFile(
                Yii::app()->assetManager->publish(
                        Yii::getPathOfAlias('application.modules_core.like.resources') . '/like.js'
                ), CClientScript::POS_BEGIN
        );


        // Execute Like Javascript Init
        Yii::app()->clientScript->registerScript('initLike', 'initLikeModule();', CClientScript::POS_READY);
    }

    /**
     * Executes the widget.
     */
    public function run()
    {

        $currentUserLiked = false;

        $likes = Like::GetLikes(get_class($this->object), $this->object->id);
        foreach ($likes as $like) {
            if ($like->getUser()->id == Yii::app()->user->id) {
                $currentUserLiked = true;
            }
        }

        $this->render('likeLink', array(
            'likes' => $likes,
            'currentUserLiked' => $currentUserLiked,
            'id' => $this->object->getUniqueId()
        ));
    }

}

?>