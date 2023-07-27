<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2016-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\models\forms;

use humhub\modules\file\models\File;

/**
 * FileUpload model is used for File uploads handled by the UploadAction via ajax.
 *
 * @see \humhub\modules\file\actions\UploadAction
 * @inheritdoc
 * @since 1.15
 */
class FileUpload extends File implements FileUploadInterface
{
    use FileUploadTrait;
}
