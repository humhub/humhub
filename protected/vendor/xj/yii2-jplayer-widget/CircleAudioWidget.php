<?php

namespace xj\jplayer;

use Yii;
use yii\helpers\Json;
use xj\jplayer\JplayerWidget;

/**
 * JPlayer Audio Widget
 * @author xjflyttp <xjflyttp@gmail.com>
 * @see http://jplayer.org/latest/demo-01/
 */
class CircleAudioWidget extends JplayerWidget
{

    /**
     * Tag Class
     * @var string
     */
    public $tagClass = 'cp-container';

    /**
     * render view
     * @var string 
     */
    public $tagView = 'circle';

    /**
     * Skin Assets
     * @var string
     * @example
     * xj\jplayer\skins\BlueAssets
     * xj\jplayer\skins\PinkAssets
     * xj\jplayer\skins\CircleAssets
     */
    public $skinAsset = 'xj\jplayer\skins\CircleAssets';

    protected function registerAssets()
    {
        parent::registerAssets();
        CircleAssets::register($this->view);
    }

    protected function registerScripts()
    {
        $jplayerSelector = '#' . $this->getSelectorJplayer();
        $jsonOptions = Json::encode($this->jsOptions);
        $jsonMediaOptions = Json::encode($this->mediaOptions);
        $script = <<<EOF
new CirclePlayer("$jplayerSelector",$jsonMediaOptions, $jsonOptions);
EOF;
        $this->view->registerJs($script);
    }

}
