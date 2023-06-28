<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\components;

use humhub\components\ActiveRecord;
use humhub\modules\file\models\AttachedImage;
use humhub\modules\file\models\AttachedImageOwnerInterface;
use humhub\modules\file\models\File;
use Imagine\Image\Box;
use Imagine\Image\Point;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\imagine\Image;

/**
 * @property AttachedImage $file
 * @property-read AttachedImageOwnerInterface|null|ActiveRecord $owner
 */
class AttachedImageStorageManager extends StorageManager
{
    public string $originalFileName = '_published';

    public function get($variant = null): string
    {
        if ($variant === '_org') {
            $variant = '_original';
        }

        return parent::get($variant);
    }

    /**
     * @return AttachedImageOwnerInterface|ActiveRecord|null
     * @since 1.15
     */
    public function getOwner(): ?AttachedImageOwnerInterface
    {
        $owner = $this->file->owner;

        return $owner instanceof AttachedImageOwnerInterface
            ? $owner
            : null;
    }

    /**
     * Returns the path of the modified image
     *
     * @return string Path to the image
     * @throws Exception
     */
    public function getPath(): string
    {
        if ($this->storagePath[0] !== "@") {
            $this->storagePath = '@webroot/uploads/' . $this->storagePath;
        }

        return parent::getPath();
    }

    public function setFile(File $file): self
    {
        parent::setFile($file);

//        if ($this->file->file_name) {
//            $this->originalFileName = $this->file->file_name;
//        }

        return $this;
    }


    /**
     * Crops the Original Image
     *
     * @param Int $x
     * @param Int $y
     * @param Int $h
     * @param Int $w
     *
     * @throws Exception
     */
    public function cropOriginal(
        int $x,
        int $y,
        int $h,
        int $w
    ): void {
        $image = Image::getImagine()
            ->open($this->get('_original'));

        $image->crop(new Point($x, $y), new Box($w, $h))
            ->resize(
                $image->getSize()
                    ->heighten($this->file->getHeight())
            )
            ->resize(
                $image->getSize()
                    ->widen($this->file->getWidth())
            )
            ->save($this->get());
    }

    /**
     * Renames the original file name variant to the new name.
     *
     * @throws InvalidConfigException|Exception
     * @see File::afterSave()
     */
    public function rename(
        string $newName,
        bool $throwException = true
    ): bool {
        if ($newName === $this->originalFileName) {
            return true;
        }

        $from = $this->get();
        $to = $this->get($newName);

        if (file_exists($to)) {
            if (!$throwException) {
                return false;
            }

            throw new InvalidConfigException("Filename $newName already exists for File {$this->file->guid}.");
        }

        if (rename($from, $to)) {
            if (!$throwException) {
                return false;
            }

            throw new \RuntimeException(
                "File {$this->file->guid} could not be renamed from $this->originalFileName to $newName."
            );
        }

        $this->originalFileName = $newName;

        return true;
    }
}
