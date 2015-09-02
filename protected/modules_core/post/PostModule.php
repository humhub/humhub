<?php

/**
 * @package humhub.modules_core.post
 * @since 0.5
 */
class PostModule extends HWebModule
{

    public $isCoreModule = true;

    public function init()
    {

        $this->setImport(array(
            'post.models.*',
            'post.behaviors.*',
        ));
    }

    public function beforeControllerAction($controller, $action)
    {

        if (parent::beforeControllerAction($controller, $action)) {
            // this method is called before any module controller action is performed
            // you may place customized code here
            return true;
        } else
            return false;
    }

    /**
     * On run of integrity check command, validate all post data
     *
     * @param type $event
     */
    public static function onIntegrityCheck($event)
    {

        $integrityChecker = $event->sender;
        $integrityChecker->showTestHeadline("Validating Post Module (" . Post::model()->count() . " entries)");

        foreach (Post::model()->findAll() as $post) {

            // Check for valid Content Model
            if ($post->content == null) {
                $integrityChecker->showFix("Deleting post with missing content model - post id: " . $post->id);
                if (!$integrityChecker->simulate)
                    $post->delete();
                continue;
            }

            // Check for valid Content Model
            if ($post->content->user === null) {
                $integrityChecker->showFix("Deleting post with missing user - post id: " . $post->id);
                if (!$integrityChecker->simulate)
                    $post->delete();
                continue;
            }
        }
    }

    /**
     * On rebuild of the search index, rebuild all post records
     *
     * @param type $event
     */
    public static function onSearchRebuild($event)
    {
        foreach (Post::model()->findAll() as $obj) {
            #    HSearch::getInstance()->addModel($obj);
            print "p";
        }
    }

}
