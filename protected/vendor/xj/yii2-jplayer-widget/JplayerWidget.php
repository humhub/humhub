<?php

namespace xj\jplayer;

use Yii;
use yii\base\Widget;
use yii\web\JsExpression;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use xj\jplayer\CommonAssets;

/**
 * JPlayer Widget
 * @author xjflyttp <xjflyttp@gmail.com>
 */
class JplayerWidget extends Widget
{

    /**
     * render to jquery selector
     * @var string 
     * @see http://jplayer.org/latest/developer-guide/#jPlayer-constructor
     * @example
     * #id-name
     * .class-name
     */
    public $selectorJplayer;

    /**
     * cssSelectorAncestor
     * @var string
     * @see http://jplayer.org/latest/developer-guide/#jPlayer-option-cssSelectorAncestor
     */
    public $selectorAncestor;

    /**
     * render Tag Enable
     * @var bool
     */
    public $tagEnable = true;

    /**
     * Tag ID
     * @var string
     */
    public $tagId;

    /**
     * Tag Class
     * @var string
     */
    public $tagClass;

    /**
     * Tag Style
     * @var type 
     */
    public $tagStyle;

    /**
     * render view
     * @var string 
     */
    public $tagView;

    /**
     * jplayer options
     * @var array
     */
    public $jsOptions = [];

    /**
     * Assets Publish Dir
     * @var string 
     */
    private $assetPublishDir;

    /**
     * JPlayer onReady Event
     * @var string 
     * @see http://jplayer.org/latest/developer-guide/#jPlayer-option-ready
     */
    public $onReady;

    /**
     * setMedia Options
     * @var [] 
     * @see http://jplayer.org/latest/developer-guide/#jPlayer-setMedia
     */
    public $mediaOptions = [];

    /**
     * Skin Assets
     * @var string
     * @example
     * xj\jplayer\skins\BlueAssets
     * xj\jplayer\skins\PinkAssets
     * xj\jplayer\skins\CircleAssets
     */
    public $skinAsset = 'xj\jplayer\skins\BlueAssets';

    /**
     * Renders the widget.
     */
    public function run()
    {
        $this->registerAssets();

        $this->setOptions();

        $this->setEvent();

        $this->renderTag();

        $this->registerScripts();

        parent::run();
    }

    /**
     * register Assets/Css
     */
    protected function registerAssets()
    {
        $this->loadSkinAssets();

        $asset = CommonAssets::register($this->view);
        $this->assetPublishDir = $asset->baseUrl;
    }

    protected function loadSkinAssets()
    {
        if ($this->skinAsset !== null) {
            $funcName = $this->skinAsset . '::register';
            call_user_func($funcName, $this->view);
        }
    }

    protected function setOptions()
    {
        $this->jsOptions = ArrayHelper::merge($this->jsOptions, [
                    'swfPath' => $this->assetPublishDir . '/js',
        ]);

        $this->jsOptions['cssSelectorAncestor'] = '#' . $this->getSelectorAncestor();
    }

    protected function setMediaOptions()
    {
        if ($this->mediaOptions !== null) {
            $mediaOptions = Json::encode($this->mediaOptions);
            $readyFunction = "function () {\$(this).jPlayer(\"setMedia\", {$mediaOptions});}";
            $this->jsOptions['ready'] = new JsExpression($readyFunction);
        }
    }

    protected function setEvent()
    {
        if ($this->onReady !== null) {
            $this->jsOptions['ready'] = new JsExpression($this->onReady);
        }
    }

    /**
     * get Default Selector JPlayer
     * @return string
     */
    protected function getDefaultSelectorJplayer()
    {
        return "jquery_jplayer_" . $this->getId();
    }

    /**
     * get Default Selector Ancestor
     * @return string
     */
    protected function getDefaultSelectorAncestor()
    {
        return "jp_container_" . $this->getId();
    }

    /**
     * get Selector JPlayer
     * @return string
     */
    public function getSelectorJplayer()
    {
        return $this->selectorJplayer === null ? $this->getDefaultSelectorJplayer() : $this->selectorJplayer;
    }

    /**
     * get Selector Ancestor
     * @return string
     */
    public function getSelectorAncestor()
    {
        return $this->selectorAncestor === null ? $this->getDefaultSelectorAncestor() : $this->selectorAncestor;
    }

    protected function renderTag()
    {
        if ($this->tagEnable === true) {
            echo $this->getTagHtml();
        }
    }

    protected function getTagHtml()
    {
        $ancestorId = $this->getSelectorAncestor();
        $jplayerId = $this->getSelectorJplayer();
        return $this->render($this->tagView, [
                    'ancestorId' => $ancestorId,
                    'ancestorClass' => $this->tagClass,
                    'ancestorStyle' => $this->tagStyle,
                    'jplayerId' => $jplayerId,
        ]);
    }

    protected function registerScripts()
    {
        $jplayerSelector = '#' . $this->getSelectorJplayer();
        $jsonOptions = Json::encode($this->jsOptions);
        $script = "\$(\"{$jplayerSelector}\").jPlayer({$jsonOptions});";
        $this->view->registerJs($script);
    }

}
