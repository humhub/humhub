<?php

namespace xj\jplayer;

use Yii;
use xj\jplayer\JplayerWidget;

/**
 * JPlayer Audio Widget
 * @author xjflyttp <xjflyttp@gmail.com>
 * @see http://jplayer.org/latest/demo-01/
 */
class AudioWidget extends JplayerWidget
{

    /**
     * render view
     * @var string 
     */
    public $tagView = 'audio';

    /**
     * @var string
     */
    public $tagClass = 'jp-audio';

    /**
     * register Media Options
     */
    protected function setOptions()
    {
        parent::setOptions();
        $this->setMediaOptions();
    }

}
