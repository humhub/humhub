<?php

namespace humhub\components\assets;

use humhub\components\fs\AbstractFs;
use humhub\modules\file\libs\ImageHelper;
use Imagine\Image\Box;
use Imagine\Image\Format;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;
use Yii;
use yii\base\Component;
use yii\base\InvalidValueException;
use yii\helpers\Url;
use yii\imagine\Image;
use yii\web\UploadedFile;

/**
 * AssetImage handles the publishing and manipulation of image assets.
 * It facilitates the exposure of protected files (`uploads` dir) via the AssetManager (and when configured a CDN).
 *
 * The class provides built-in image processing capabilities (scaling and cropping) based on a set of transformation options.
 *
 * Available `options` in the configuration array:
 * - `width`/`height`:  Fixed dimensions; the image is scaled and cropped to fit.
 * - `maxWidth`/`maxHeight`: Maximum bounds; the image is downscaled proportionally.
 * - `square`: An integer value to create a fixed-size square crop (e.g., 50 for 50x50).
 *
 * @since v1.19
 */
class AssetImage extends Component
{
    /**
     * @var string File with path to the AssetImage
     */
    public string $file;

    /**
     * @var array Default options, when `getUrl` is called without `options` parameter.
     */
    public array $defaultOptions = [];

    /**
     * @var array Options applied once to the original file upon first save/storage.
     */
    public array $masterOptions = [];
    public ?string $defaultFile = null;

    private string $path;
    private string $fileName;
    private bool $exists;

    private AbstractFs $fs;

    public function __construct($config = [])
    {
        parent::__construct($config);

        if (empty($this->file)) {
            throw new InvalidValueException('Asset image file cannot be empty.');
        }

        $this->fs = Yii::$app->fs->data();

        $this->file = Yii::getAlias($this->file);
        $this->fileName = basename($this->file);
        $this->path = dirname($this->file);
        $this->exists = $this->fs->fileExists($this->file);
        $this->defaultFile = Yii::getAlias($this->defaultFile);

        $this->fs->createDirectory($this->path);
    }

    /**
     * @param array|null $options use `null` use default options, `[]` get unmodified file. All available options in class header
     * @param $scheme
     * @return string the URL
     */
    public function getUrl(?array $options = null, $scheme = false): string
    {
        if (!$this->exists && empty($this->defaultFile)) {
            throw new InvalidValueException('File and DefaultFile cannot be empty.');
        }

        if ($options === null) {
            $options = $this->defaultOptions;
        }

        $scaledFileName = $this->path . DIRECTORY_SEPARATOR . $this->getFileNameWithOptions($options);
        if (!$this->fs->fileExists($scaledFileName)) {
            $this->convert($scaledFileName, $options);
        }

        $published = Yii::$app->assetManager->publish($scaledFileName, ['mount' => $this->fs]);
        return Url::to($published[1], $scheme);
    }

    public function setUploadedFile(?UploadedFile $file = null): void
    {
        $this->setByFile($file->tempName);
    }

    public function setByFile(string $tempFileName): void
    {
        $this->delete();
        ImageHelper::checkMaxDimensions($tempFileName);
        $image = Image::getImagine()->open($tempFileName);
        ImageHelper::fixJpegOrientation($image, $tempFileName);
        $this->fs->write($this->file, $image->get($this->getFileFormat()));

        $this->exists = true;

        if (!empty($this->masterOptions)) {
            $this->convert($this->file, $this->masterOptions);
        }
    }

    private function getFileNameWithOptions(array $options): string
    {
        $fileName = ($this->exists) ? $this->fileName : basename($this->defaultFile);

        ksort($options);
        $checksum = hash('xxh32', json_encode($options));

        $info = pathinfo($fileName);
        return $info['filename'] . '_' . $checksum . '.' . $info['extension'];
    }

    private function convert(string $newFileName, array $options = []): bool
    {
        $image = Image::getImagine();
        if ($this->exists) {
            $image = $image->load($this->fs->read($this->file));
        } else {
            $image = $image->open($this->defaultFile);
        }

        if (isset($options['square'])) {
            $options['width'] = $options['square'];
            $options['height'] = $options['square'];
        }

        if (!empty($options['width']) && !empty($options['height'])) {
            $image = $image->thumbnail(
                new Box($options['width'], $options['height']),
                ManipulatorInterface::THUMBNAIL_OUTBOUND,
            );
        }

        if (isset($options['maxWidth']) && $image->getSize()->getHeight() > $options['maxHeight']) {
            $image = $image->resize($image->getSize()->heighten($options['maxHeight']));
        }

        if (isset($options['maxWidth']) && $image->getSize()->getWidth() > $options['maxWidth']) {
            $image = $image->resize($image->getSize()->widen($options['maxWidth']));
        }

        $this->fs->write($newFileName, $image->get($this->getFileFormat()));

        return true;
    }

    public function delete(): void
    {
        try {
            if ($this->fs->fileExists($this->file)) {
                $this->fs->delete($this->file);
            }
        } catch (\Exception $e) {
            Yii::error($e, 'base');
        }

        $this->exists = false;
        $this->deleteWithOptions();
    }

    public function crop($x, $y, $h, $w)
    {
        $image = Image::getImagine()->load($this->fs->read($this->file))
            ->crop(new Point($x, $y), new Box($w, $h));

        $this->fs->write($this->file, $image->get($this->getFileFormat()));

        $this->deleteWithOptions();
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    private function deleteWithOptions(): void
    {
        $prefix = pathinfo($this->fileName, PATHINFO_FILENAME) . '_';

        foreach ($this->fs->listContents($this->path) as $f) {
            if ($f->isFile() && str_starts_with(basename($f->path()), $prefix)) {
                $this->fs->delete($f->path());
            }
        }
    }

    private function getFileFormat(): string
    {
        $extension = strtolower(pathinfo($this->file, PATHINFO_EXTENSION));

        $formatMap = [
            'jpg' => Format::ID_JPEG,
            'jpeg' => Format::ID_JPEG,
            'png' => Format::ID_PNG,
            'gif' => Format::ID_PNG,
            'webp' => Format::ID_WEBP,
        ];

        return $formatMap[$extension] ?? 'png';
    }

    public function __toString(): string
    {
        return $this->getUrl();
    }
}
