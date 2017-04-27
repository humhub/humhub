<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use yii\helpers\Html;
use humhub\libs\Helpers;
use humhub\modules\file\libs\FileHelper;

/**
 * Description of JPlayerWidget
 *
 * @author buddha
 */
class JPlayerPlaylistWidget extends JsWidget
{
    /**
     * Contains the playlist.
     * @var \humhub\modules\file\models\File[]
     */
    public $playlist = [];

    /**
     * @inheritdoc
     */
    public $jsWidget = "media.Jplayer";

    /**
     * @inheritdoc
     */
    public function run()
    {
        if(empty($this->playlist)) {
            return;
        }

        $this->init = $this->getJsonPlaylist();

        $asset = \humhub\assets\JplayerAsset::register($this->getView());

        $options = $this->getOptions();
        $options['data-swf-path'] = $asset->baseUrl.'/jplayer';

        return $this->render('jPlayerAudio', [
            'containerId' => $this->getContainerId(),
            'options' => $options
        ]);
    }

    public function getJsonPlaylist()
    {
        $result = [];
        foreach($this->playlist as $track) {
            $result[] = [
                'title' => Html::encode(Helpers::trimText($track->file_name, 50)),
                FileHelper::getExtension($track->file_name) => $track->getUrl()
            ];
        }
        
        return $result;
    }

    public function getData()
    {
        return [
            'container-id' => '#'.$this->getContainerId()
        ];
    }

    public function getAttributes()
    {
        return [
            'class' => 'jp-jplayer'
        ];
    }

    public function getContainerId()
    {
        return $this->getId(true).'-container';
    }

}
