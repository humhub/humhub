<?php
/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\content\controllers;

use humhub\components\FileAction;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\models\ContentImage;
use humhub\modules\file\libs\AttachedImageControllerTrait;
use humhub\modules\file\libs\FileControllerTrait;
use humhub\modules\file\libs\ImageControllerInterface;
use humhub\modules\file\models\AttachedImage;
use humhub\modules\file\models\forms\FileUpload;
use humhub\modules\file\models\forms\FileUploadInterface;
use yii\web\Response;

/**
 * Contains profile image and profile banner image upload actions.
 *
 * @method FileUpload|FileUploadInterface|null getFile(FileAction $action)
 * @package humhub\modules\ui\profile\controllers
 * @since 1.4
 */
abstract class ContainerAttachedImageController extends ContentContainerController implements ImageControllerInterface
{
    use FileControllerTrait;
    use AttachedImageControllerTrait {
        handleImageUpload as protected _handleImageUpload;
        getImage as protected _getImage;
    }

    protected function handleImageUpload(
        string $uploadName,
        string $type
    ): Response {
        $result = [
            'type' => $type,
            'container_id' => $this->contentContainer->contentcontainer_id,
            // Deprecated, only remained for legacy themes prior to 1.4
            'space_id' => $this->contentContainer->id,
        ];

        return $this->_handleImageUpload($uploadName, $result, $type);
    }


    /**
     * @param FileAction|null $action
     * @param string $type
     *
     * @return ContentImage
     * @noinspection PhpDocSignatureInspection
     */
    public function getImage(?FileAction $action = null, ?array $args = []): ?AttachedImage
    {
        $type = reset($args);

        switch ($type) {
            case 'banner':
                $file = $this->contentContainer->getProfileBannerImage();
                break;

            case 'image':
                $file = $this->contentContainer->getProfileImage();
                break;

            default:
                return null;
        }

        $args['__file'] = $file;

        return $this->_getImage(null, $args);
    }
}
