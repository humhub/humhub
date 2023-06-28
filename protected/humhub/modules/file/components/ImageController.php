<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\components;

use humhub\modules\file\actions\AttachedImageUploadAction;
use humhub\modules\file\libs\AttachedImageControllerTrait;
use humhub\modules\file\libs\ImageControllerInterface;
use humhub\modules\file\models\forms\FileUploadInterface;
use yii\base\Exception;

/**
 * @property-read FileUploadInterface $newFile
 */
class ImageController extends BaseFileController implements ImageControllerInterface
{
    use AttachedImageControllerTrait;

    // protected properties
    protected static ?string $uploadActionClass = AttachedImageUploadAction::class;

    /**
     * Crops the space image
     *
     * @throws Exception
     * @throws \JsonException
     */
    public function handleCrop(array $args): string
    {
        return $this->handleCropInternal(
            null,
            [
            ],
            $args
        );
    }
}
