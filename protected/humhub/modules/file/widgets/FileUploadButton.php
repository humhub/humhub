<?php

namespace humhub\modules\file\widgets;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\PolymorphicRelation;
use yii\base\Widget;

/**
 * FileUploadButtonWidget creates an upload button / system.
 *
 * The button uploads files and stores the uploaded file guids to a given hidden field id.
 * The underlying module can use the guids to adobt these files.
 *
 * The related widget FileUploadListWidget can optionally used to display states
 * of the current upload progress.
 *
 * @package humhub.modules_core.file.widgets
 * @since 0.5
 * @deprecated since version 1.2
 */
class FileUploadButton extends Widget
{
    /**
     * @var String unique id of this uploader
     */
    public $uploaderId = "";

    /**
     * Hidden field which stores uploaded file guids
     *
     * @var string
     */
    public $fileListFieldName = '';

    /**
     * The HActiveRecord which the uploaded files belongs to.
     * Leave empty when object not exists yet.
     *
     * @var ActiveRecord
     */
    public $object = null;

    /**
     * Draws the Upload Button output.
     */
    public function run()
    {
        $objectModel = '';
        $objectId = '';
        if ($this->object !== null) {
            $objectModel = PolymorphicRelation::getObjectModel($this->object);
            $objectId = $this->object->getPrimaryKey();
        }

        return $this->render('fileUploadButton', [
            'fileListFieldName' => $this->fileListFieldName,
            'uploaderId' => $this->uploaderId,
            'objectModel' => $objectModel,
            'objectId' => $objectId,
        ]);
    }

}
