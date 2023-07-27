<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use humhub\modules\file\models\forms\FileUploadInterface;
use humhub\modules\file\models\forms\FileUploadTrait;

/**
 * FileUpload model is used for File uploads handled by the UploadAction via ajax.
 *
 * @see \humhub\modules\file\actions\UploadAction
 * @author Luke
 * @inheritdoc
 * @since 1.2
 * @deprecated since 1.15. Use \humhub\modules\file\models\forms\FileUpload instead
 * @see \humhub\modules\file\models\forms\FileUpload
 */
class FileUpload extends File implements FileUploadInterface
{
    use FileUploadTrait;
}
