<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\widgets;

use humhub\components\ActiveRecord;
use humhub\modules\file\converter\PreviewImage;
use Yii;
use humhub\modules\content\components\ContentActiveRecord;

/**
 * This widget is used include the files functionality to a wall entry.
 *
 * @since 0.5
 */
class ShowFiles extends \yii\base\Widget
{

    /**
     * @var ActiveRecord Object to show files from
     */
    public $object = null;

    /**
     * @var bool if set to false this widget won't be rendered
     */
    public $active = true;

    /**
     * @var bool if set to false this widget won't render file previews as images/videos/audio
     */
    public $preview = true;

    /**
     * Executes the widget.
     */
    public function run()
    {
        if(!$this->active) {
            return;
        }

        if ($this->object instanceof ContentActiveRecord) {
            $widget = $this->object->getWallEntryWidget();

            // File widget disabled in this wall entry
            if ($widget->showFiles === false) {
                return;
            }
        }

        $hidePreviewFileInfo = ($this->preview) ? Yii::$app->getModule('file')->settings->get('hideImageFileInfo') : false;

        return $this->render('showFiles', [
                    'files' => $this->object->fileManager->findStreamFiles(),
                    'object' => $this->object,
                    'previewImage' => new PreviewImage(),
                    'showPreview' => $this->preview,
                    'hideImageFileInfo' => $hidePreviewFileInfo
        ]);
    }

}

?>