<?php

namespace humhub\modules\content\controllers;

use humhub\modules\content\models\forms\ContentImageUpload;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\HttpException;
use yii\web\Response;

/**
 * Contains profile image and profile banner image upload actions.
 *
 * @package humhub\modules\ui\profile\controllers
 * @since 1.4
 */
abstract class ContainerImageController extends ContainerAttachedImageController
{
    public const TYPE_PROFILE_IMAGE        = 'image';
    public const TYPE_PROFILE_BANNER_IMAGE = 'banner';

    public string $uploadModelClass = ContentImageUpload::class;

    /**
     * @var string file upload name for banner image, this exists due to legacy compatibility for views prio to v1.4
     */
    public string $bannerUploadName = 'images';

    /**
     * Handle the profile image upload
     *
     * @param string $type
     *
     * @return Response
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function actionUpload($type = self::TYPE_PROFILE_IMAGE)
    {
        return $this->handleImageUpload($this->getFileListParameterBase(), $type);
    }

    /**
     * Handle the banner image upload
     * @deprecated since 1.15 (Use actionUpload(static::TYPE_PROFILE_BANNER_IMAGE) instead.)
     * @see self::actionUpload()
     */
    public function actionBannerUpload(): Response
    {
        return $this->actionUpload(static::TYPE_PROFILE_BANNER_IMAGE);
    }

    /**
     * Crops the space image
     */
    public function actionCrop($type = self::TYPE_PROFILE_IMAGE): string
    {
        return $this->handleCrop($type);
    }


    /**
     * Crops the banner image
     * @deprecated since 1.15 (Use actionCrop(static::TYPE_PROFILE_BANNER_IMAGE) instead.)
     * @see self::actionCrop()
     */
    public function actionCropBanner(): string
    {
        return $this->actionCrop(static::TYPE_PROFILE_BANNER_IMAGE);
    }

    /**
     * @param $type
     *
     * @return string
     * @throws Exception
     */
    public function handleCrop($type = self::TYPE_PROFILE_IMAGE): string
    {
        return $this->handleCropInternal(
            $this->contentContainer->getUrl(),
            [
                'container' => $this->contentContainer,
            ],
            $type
        );
    }
    /**
     * Deletes the profile image or profile banner
     *
     * @param $type
     *
     * @return Response
     * @throws Exception
     * @throws InvalidConfigException
     * @throws HttpException
     */
    public function actionDelete($type = self::TYPE_PROFILE_IMAGE): Response
    {
        $image = $this->getImage(null, [$type]);

        $result             = ['type' => $type];
        $result['space_id'] = $this->contentContainer->id; // @deprecated since 1.4
        //                                                    only used in legacy profile image logic

        return $this->actionDeleteInternal($image, $result);
    }
}
