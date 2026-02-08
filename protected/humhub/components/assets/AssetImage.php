<?php

namespace humhub\components\assets;

use humhub\modules\file\libs\ImageHelper;
use Yii;
use yii\base\Component;
use yii\base\InvalidValueException;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\imagine\Image;
use yii\web\UploadedFile;

class AssetImage extends Component
{
    public string $path;
    public string $file;

    public string $defaultFile;

    public function __construct($config = [])
    {
        parent::__construct($config);

        if (empty($this->file)) {
            throw new InvalidValueException('Static image path cannot be empty.');
        }

        if (empty($this->path)) {
            throw new InvalidValueException('Static image path cannot be empty.');
        }

        $this->path = Yii::getAlias($this->path);
        FileHelper::createDirectory($this->path);
    }

    public function exists(): bool
    {
        return true;
    }

    public function getUrl(array $options = [], $scheme = false): string
    {
        $scaledFileName = $this->path . DIRECTORY_SEPARATOR . $this->getFileNameWithOptions($options);
        if (!file_exists($scaledFileName)) {
            $this->convert($scaledFileName, $options);
        }

        $published = Yii::$app->assetManager->publish($scaledFileName);
        return Url::to($published[1], $scheme);
    }

    public function setNew(?UploadedFile $file = null)
    {
        $this->delete();
        ImageHelper::checkMaxDimensions($file->tempName);
        $image = Image::getImagine()->open($file->tempName);
        ImageHelper::fixJpegOrientation($image, $file->tempName);
        $image->save($this->getFileName());
    }

    private function getFileNameWithOptions(array $options): string
    {
        ksort($options);
        $checksum = hash('xxh32', json_encode($options));

        $info = pathinfo($this->file);
        return $info['filename'] . '_' . $checksum . '.' . $info['extension'];
    }

    private function getFileName(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->file;
    }

    private function convert(string $newFileName, array $options = []): bool
    {
        $fileName = $this->getFileName();
        if (!file_exists($fileName)) {
            if (!empty($this->defaultFile)) {
                $fileName = Yii::getAlias($this->defaultFile);
            }
        }

        $image = Image::getImagine()->open($fileName);

        if (isset($options['square'])) {
            $options['width'] = $options['square'];
            $options['height'] = $options['square'];
        }

        if (isset($options['maxWidth']) && $image->getSize()->getHeight() > $options['maxHeight']) {
            $image->resize($image->getSize()->heighten($options['maxHeight']));
        }
        if (isset($options['maxWidth']) && $image->getSize()->getWidth() > $options['maxWidth']) {
            $image->resize($image->getSize()->widen($options['maxWidth']));
        }

        $image->save($newFileName);

        return true;
    }

    public function delete(): void
    {
        try {
            FileHelper::removeDirectory($this->path);
        } catch (\Exception $e) {
            Yii::error($e, 'admin');
        }

        FileHelper::createDirectory($this->path);
    }

}
