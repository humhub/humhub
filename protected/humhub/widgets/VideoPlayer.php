<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * A HTML5 video player layout.
 *
 * @author BastusIII
 * @since 1.2
 */
class VideoPlayer extends \yii\base\Widget
{

    public $file;
    public $supportedExtensions = ['mp4', 'ogv', 'webm'];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $extension = \humhub\modules\file\libs\FileHelper::getExtension($this->file->file_name);
        if (!in_array($extension, $this->supportedExtensions)) {
            // we do not render anything if the file is not a supported video file
            return;
        }

        $previewImageUrl = $this->file->getPreviewImageUrl();
        $videoId = $this->file->id;
        $videoSrcUrl = $this->file->getUrl();
        $videoType = $this->getMimeTypeByExtension($extension);
        $videoTitle = \yii\helpers\Html::encode($this->file->file_name);
        
        return $this->render('videoPlayer', [
                    "previewImageUrl" => $previewImageUrl,
                    "videoId" => $videoId,
                    "videoSrcUrl" => $videoSrcUrl,
                    "videoType" => $videoType,
                    "videoTitle" => $videoTitle,
                    "captionsLabel" => '', // captions are currently not supported
                    "captionsSrcUrl" => '',
                    "captionsLanguage" => ''
        ]);
    }

    protected function getMimeTypeByExtension($extension) {
        
        $type = \humhub\modules\file\libs\FileHelper::getMimeTypeByExtension($extension);
        if(!$type) {
            switch($extension) {
                case 'mp4':
                    $type = 'video/mp4';
                case 'ogv':
                    $type = 'video/ogg';
                case 'webm':
                    $type = 'video/ogg';
            }
        }
        return $type;
    }
    
}
