<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\widgets;

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
     * Object to show files from
     */
    public $object = null;

    /**
     * Executes the widget.
     */
    public function run()
    {
        if ($this->object instanceof ContentActiveRecord) {
            $widget = $this->object->getWallEntryWidget();

            // File widget disabled in this wall entry
            if ($widget->showFiles === false) {
                return;
            }
        }

        return $this->render('showFiles', [
                    'files' => $this->object->fileManager->find()->andWhere(['show_in_stream' => true])->all(),
                    'previewImage' => new \humhub\modules\file\converter\PreviewImage(),
                    'hideImageFileInfo' => Yii::$app->getModule('file')->settings->get('hideImageFileInfo')
        ]);
    }

}

?>