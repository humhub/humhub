<?php
/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use humhub\components\ActiveRecord;
use Throwable;
use yii\db\ActiveRecordInterface;

interface AttachedImageOwnerInterface extends ActiveRecordInterface
{
    public function getAttachedImage(array $config): ?AttachedImage;

    /**
     * @return \humhub\modules\file\models\AttachedImage[]
     */
    public function getAttachedImages(): array;

    public function getHeaderImageControllerClass(): string;

    public function createUrlImageView();

    public function createUrlImageCrop(): ?string;

    public function createUrlImageDelete(): ?string;

    public function createUrlImageUpload(): ?string;

    /**
     * @param array         $widgetOptions
     * @param array         $imageOptions
     * @param AttachedImage $image
     *
     * @return string
     * @throws Throwable
     */
    public function renderAttachedImage(
        array $widgetOptions,
        array $imageOptions,
        AttachedImage $image
    ): string;

    /**
     * @param $guid
     *
     * @return ActiveRecord|null
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public static function findByGuid($guid);

}
