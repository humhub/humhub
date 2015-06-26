<?php

namespace humhub\core\activity\widgets;

use yii\web\HttpException;

/**
 * ActivityStreamWidget shows an stream/wall of activities inside a sidebar.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.modules_core.activity
 * @since 0.5
 */
class Stream extends \yii\base\Widget
{

    /**
     * Optional content container if this stream belongs to one
     *
     * @var HActiveRecordContentContainer
     */
    public $contentContainer;

    /**
     * Path to Stream Action to use
     *
     * @var string
     */
    public $streamAction = "";

    /**
     * Inits the activity stream widget
     */
    public function init()
    {
        if ($this->streamAction == "") {
            throw new HttpException(500, 'You need to set the streamAction attribute to use this widget!');
        }
    }

    /**
     * Runs the activity widget
     */
    public function run()
    {
        $streamUrl = $this->getStreamUrl();
        $permaUrl = \yii\helpers\Url::to(['/content/perma/wallEntry']);
        return $this->render('activityStream', array(
                    'streamUrl' => $streamUrl,
                    'permaUrl' => $permaUrl
        ));
    }

    protected function getStreamUrl()
    {
        $params = array(
            'limit' => '10',
            'from' => '-from-',
            'mode' => \humhub\core\content\components\actions\Stream::MODE_ACTIVITY
        );

        if ($this->contentContainer) {
            return $this->contentContainer->createUrl($this->streamAction, $params);
        }

        return \yii\helpers\Url::to(array_merge([$this->streamAction], $params));
    }

}

?>