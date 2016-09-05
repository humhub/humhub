<?php

namespace humhub\modules\file\widgets;
use humhub\components\ActiveRecord;
use humhub\modules\file\models\File;

/**
 * FileUploadListWidget works in combination of FileUploadButtonWidget and is
 * primary responsible to display some status informations like upload progress
 * or a list of already uploaded files.
 *
 * This widget cannot work standalone! Make sure the attribute "uploaderId" is
 * the same as the corresponding FileUploadListWidget.
 *
 * @package humhub.modules_core.file.widgets
 * @since 0.5
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

        $files = array();
        if ($this->object !== null) {
            if ($this->object->isNewRecord && $this->object->getRelation('content', false) !== null) {
                $files = File::findAll(['guid' => array_map('trim', explode(',', $this->object->content->attachFileGuidsAfterSave))]);
            } else {
                $files = File::getFilesOfObject($this->object);
            }
        }

        return $this->render('fileUploadList', array(
                    'uploaderId' => $this->uploaderId,
                    'files' => $files
        ));
    }

}
