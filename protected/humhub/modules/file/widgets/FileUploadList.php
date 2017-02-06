<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\widgets;

use humhub\components\ActiveRecord;

/**
 * FileUploadListWidget works in combination of FileUploadButtonWidget and is
 * primary responsible to display some status informations like upload progress
 * or a list of already uploaded files.
 *
 * This widget cannot work standalone! Make sure the attribute "uploaderId" is
 * the same as the corresponding FileUploadListWidget.
 *
 * @since 0.5
 * @deprecated since version 1.2
 */
class FileUploadList extends \yii\base\Widget
{

    /**
     * @var String unique id of this uploader
     */
    public $uploaderId = "";

    /**
     * If object is set, display also already uploaded files
     *
     * @var ActiveRecord
     */
    public $object = null;

    /**
     * Draw the widget
     */
    public function run()
    {
        $files = [];
        if ($this->object !== null) {
            $files = $this->object->fileManager->find()->all();
        }
        return $this->render('fileUploadList', array(
                    'uploaderId' => $this->uploaderId,
                    'files' => $files
        ));
    }

}
