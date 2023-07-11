<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\models\forms;

use humhub\modules\file\models\FileInterface;
use yii\web\UploadedFile;

/**
 * FileUpload model is used for File uploads handled by the UploadAction via ajax.
 *
 * @see    \humhub\modules\file\actions\UploadAction
 * @since  1.15
 */
interface FileUploadInterface extends FileInterface
{
    /**
     * @return UploadedFile|null
     */
    public function getUploadedFile(): ?UploadedFile;

    /**
     * Sets uploaded file to this file model
     *
     * @param UploadedFile $uploadedFile
     */
    public function setUploadedFile(UploadedFile $uploadedFile);
}
