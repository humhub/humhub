<?php

namespace xj\jplayer;

use Yii;
use yii\web\JsExpression;
use yii\helpers\Json;
use xj\jplayer\JplayerWidget;

/**
 * JPlayer Video Widget
 * @author xjflyttp <xjflyttp@gmail.com>
 * @see http://jplayer.org/latest/demo-01-video/
 */
class VideoWidget extends JplayerWidget
{

    /**
     * render view
     * @var string 
     */
    public $tagView = 'video';

    /**
     * @var string
     */
    public $tagClass = 'jp-video';

    /**
     * register Media Options
     */
    protected function setOptions()
    {
        parent::setOptions();
        $this->setMediaOptions();
    }

}
