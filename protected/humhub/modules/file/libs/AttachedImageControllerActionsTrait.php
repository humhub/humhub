<?php
/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\modules\file\models\AttachedImage;
use humhub\modules\file\models\File;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\HttpException;
use yii\web\Response;

trait AttachedImageControllerActionsTrait
{
    /**
     * Crops the space image
     *
     * @throws Exception
     */
    public function actionCrop(...$args): string
    {
        $args = reset($args);

        return $this->handleCrop($args);
    }

    /**
     * Deletes the image
     *
     * @param mixed ...$args
     *
     * @return Response
     * @throws Exception
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function actionDelete(...$args): Response
    {
        $args = end($args);

        $image = File::findOne(['guid' => $args['guid']], File::STATES_AVAILABLE);

        if ($image === null) {
            throw new HttpException(404, "File not found");
        }

        return $this->actionDeleteInternal($image, []);
    }

    abstract protected function actionDeleteInternal(AttachedImage $image, array $result): Response;

    /**
     * Handle the image upload
     *
     * @throws InvalidConfigException|Exception
     */
    public function actionUpload(...$args): Response
    {
        $args = reset($args);

        return $this->handleImageUpload(
            $this->getActionConfiguration(FileControllerInterface::ACTION_UPLOAD)
                ->getFileListParameterBase(),
            $args,
            $args
        );
    }

    abstract protected function handleImageUpload(
        string $uploadName,
        array $params,
        ...$args
    ): Response;
}
