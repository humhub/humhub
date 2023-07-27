<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use yii\base\InvalidCallException;
use yii\web\UploadedFile;

/**
 * Event is the base class for all event classes.
 *
 * @since 1.15
 *
 */
class EventWithUploadedFile extends EventWithTypedResult
{
    // protected properties
    protected ?UploadedFile $uploadedFile;


    /**
     * @return UploadedFile
     */
    public function getUploadedFile(): ?UploadedFile
    {

        return $this->uploadedFile;
    }


    /**
     * @param UploadedFile $uploadedFile
     *
     * @return EventWithUploadedFile
     */
    public function setUploadedFile(?UploadedFile $uploadedFile): EventWithUploadedFile
    {
        try {
            $message = "The Uploaded File property cannot be changed: {$this->uploadedFile->tempName}";
            throw new InvalidCallException($message);
        } catch (InvalidCallException $t) {
            throw $t;
        } catch (\Throwable $t) {
            $this->uploadedFile = $uploadedFile;
        }

        return $this;
    }
}
