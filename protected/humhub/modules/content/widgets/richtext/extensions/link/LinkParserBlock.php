<?php

namespace humhub\modules\content\widgets\richtext\extensions\link;

use yii\base\Model;
use yii\helpers\Url;

/**
 * Link: <orig> = [<text>](<url> "<title>")
 * Image: <orig> = ![<text><alignment>](<url> "<title>" =<width>x<height>)
 * Video: <orig> = ![<text><alignment>](<url> video controls autoplay muted loop =<width>x<height>)
 * Audio: <orig> = ![<text><alignment>](<url> audio controls autoplay muted loop)
 */
class LinkParserBlock extends Model
{
    public const BLOCK_KEY_URL = 'url';
    public const BLOCK_KEY_TITLE = 'title';
    public const BLOCK_KEY_MD = 'orig';
    public const BLOCK_KEY_TEXT = 'text';
    public const BLOCK_KEY_FILE_ID = 'fileId';
    public const BLOCK_KEY_WIDTH = 'width';
    public const BLOCK_KEY_HEIGHT = 'height';
    public const BLOCK_KEY_CLASS = 'class';
    public const BLOCK_KEY_STYLE = 'style';
    public const BLOCK_KEY_TYPE = 'type';
    public const BLOCK_KEY_CONTROLS = 'controls';
    public const BLOCK_KEY_AUTOPLAY = 'autoplay';
    public const BLOCK_KEY_MUTED = 'muted';
    public const BLOCK_KEY_LOOP = 'loop';

    /**
     * @var array
     */
    public $block;

    /**
     * @var string
     */
    public $parsedText;

    /**
     * @var bool
     */
    public $isValid = false;

    /**
     * @var bool
     */
    public $isImage = false;

    /**
     * @var string
     */
    public $result;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initImageOptions();
    }

    protected function initImageOptions()
    {
        if (!$this->isImage()) {
            return;
        }

        if ($this->hasOption(static::BLOCK_KEY_TEXT)) {
            // Extract image alignment from image alt text
            $text = trim((string)$this->block[static::BLOCK_KEY_TEXT]);
            if (str_ends_with($text, '><')) {
                $this->setClass('d-block mx-auto');
                $this->setText(substr($text, 0, -2));
            } elseif (str_ends_with($text, '<')) {
                $this->setClass('float-start');
                $this->setText(substr($text, 0, -1));
            } elseif (str_ends_with($text, '>')) {
                $this->setClass('float-end');
                $this->setText(substr($text, 0, -1));
            }
        }

        if ($this->hasOption(static::BLOCK_KEY_MD)) {
            $origTag = (string)$this->block[static::BLOCK_KEY_MD];

            if (preg_match('/\((.*?)\)/', $origTag, $options)) {
                $options = preg_split('/\s+/', $options[1], -1, PREG_SPLIT_NO_EMPTY);
                foreach ($options as $option) {
                    switch ($option) {
                        case 'video':
                        case 'audio':
                            $this->setType($option);
                            break;
                        case 'controls':
                            $this->setControls(true);
                            break;
                        case 'autoplay':
                            $this->setAutoplay(true);
                            break;
                        case 'muted':
                            $this->setMuted(true);
                            break;
                        case 'loop':
                            $this->setLoop(true);
                            break;
                    }
                }
            }

            if (preg_match('/\([^)]+?\b(video|audio)\b[^)]*?\)/i', $origTag, $type)) {
                $this->setType($type[1]);
            }

            if (preg_match('/=(\d+)?x(\d+)?\)$/', $origTag, $size)) {
                $this->setWidth($size[1] ?? null);
                $this->setHeight($size[2] ?? null);
            }
        }
    }

    public function getMarkdown(): ?string
    {
        return $this->block[static::BLOCK_KEY_MD] ?? null;
    }

    public function getUrl(): ?string
    {
        return $this->block[static::BLOCK_KEY_URL] ?? null;
    }

    public function toAbsoluteUrl(): void
    {
        $url = $this->getUrl();
        if ($url && $url[0] === '/') {
            $url = Url::base(true) . $url;
        }

        $this->setUrl($url);
    }

    public function setUrl(string $url)
    {
        $this->block[static::BLOCK_KEY_URL] = $url;
    }

    public function getText()
    {
        return $this->block[static::BLOCK_KEY_TEXT] ?? null;
    }

    public function setText(string $text)
    {
        $this->block[static::BLOCK_KEY_TEXT] = $this->textToBlockFormat($text);
        $this->setParsedText($text);
    }

    public function getTitle(): ?string
    {
        return $this->block[static::BLOCK_KEY_TITLE] ?? null;
    }

    public function setTitle(?string $title = null)
    {
        $this->block[static::BLOCK_KEY_TITLE] = $title;
    }

    public function getFileId(): ?string
    {
        return $this->block[static::BLOCK_KEY_FILE_ID] ?? null;
    }

    public function setFileId(?string $fileId = null)
    {
        $this->block[static::BLOCK_KEY_FILE_ID] = $fileId;
    }

    public function getParsedText()
    {
        return $this->parsedText;
    }

    public function setParsedText(string $text)
    {
        $this->parsedText = $text;
    }

    public function getWidth()
    {
        return $this->block[static::BLOCK_KEY_WIDTH] ?? null;
    }

    public function setWidth($width)
    {
        $this->block[static::BLOCK_KEY_WIDTH] = $width;
    }

    public function getHeight()
    {
        return $this->block[static::BLOCK_KEY_HEIGHT] ?? null;
    }

    public function setHeight($height)
    {
        $this->block[static::BLOCK_KEY_HEIGHT] = $height;
    }

    public function getClass()
    {
        return $this->block[static::BLOCK_KEY_CLASS] ?? null;
    }

    public function setClass($class)
    {
        $this->block[static::BLOCK_KEY_CLASS] = $class;
    }

    public function getStyle(): ?array
    {
        return $this->block[static::BLOCK_KEY_STYLE] ?? null;
    }

    public function setStyle(array $style)
    {
        if (!isset($this->block[static::BLOCK_KEY_STYLE])) {
            $this->block[static::BLOCK_KEY_STYLE] = [];
        }

        $this->block[static::BLOCK_KEY_STYLE] = array_merge($this->block[static::BLOCK_KEY_STYLE], $style);
    }

    public function setType(string $type): void
    {
        $this->block[static::BLOCK_KEY_TYPE] = $type;
    }

    public function getType(): string
    {
        return $this->block[static::BLOCK_KEY_TYPE] ?? 'image';
    }

    public function setControls(bool $enabled): void
    {
        $this->block[static::BLOCK_KEY_CONTROLS] = $enabled;
    }

    public function getControls(): bool
    {
        return $this->block[static::BLOCK_KEY_CONTROLS] ?? false;
    }

    public function setAutoplay(bool $enabled): void
    {
        $this->block[static::BLOCK_KEY_AUTOPLAY] = $enabled;
    }

    public function getAutoplay(): bool
    {
        return $this->block[static::BLOCK_KEY_AUTOPLAY] ?? false;
    }

    public function setMuted(bool $enabled): void
    {
        $this->block[static::BLOCK_KEY_MUTED] = $enabled;
    }

    public function getMuted(): bool
    {
        return $this->block[static::BLOCK_KEY_MUTED] ?? false;
    }

    public function setLoop(bool $enabled): void
    {
        $this->block[static::BLOCK_KEY_LOOP] = $enabled;
    }

    public function getLoop(): bool
    {
        return $this->block[static::BLOCK_KEY_LOOP] ?? false;
    }

    public function setBlock(string $text, string $url, ?string $title = null, $fileId = null)
    {
        $this->setUrl($url);
        $this->setText($text);
        $this->setTitle($title);
        $this->setFileId($fileId);
    }

    public function invalidate()
    {
        $this->isValid = false;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult(string $result)
    {
        $this->result = $result;
    }

    public function isValid()
    {
        return $this->isValid;
    }

    public function isImage()
    {
        return $this->isImage;
    }

    private function textToBlockFormat(string $text)
    {
        if ($this->isImage()) {
            return $text;
        }

        if (!$text) {
            $text = '';
        }

        return [['text', $text]];
    }

    public function hasOption(string $key): bool
    {
        return isset($this->block[$key]) && $this->block[$key] !== '' && $this->block[$key] !== [];
    }

    public function getImageAttributes(): array
    {
        $attrs = [
            'src' => $this->getUrl(),
            'alt' => $this->getText(),
        ];
        if ($this->hasOption(static::BLOCK_KEY_TITLE)) {
            $attrs['title'] = $this->getTitle();
        }
        if ($this->hasOption(static::BLOCK_KEY_WIDTH)) {
            $attrs['width'] = $this->getWidth();
        }
        if ($this->hasOption(static::BLOCK_KEY_HEIGHT)) {
            $attrs['height'] = $this->getHeight();
        }
        if ($this->hasOption(static::BLOCK_KEY_CLASS)) {
            $attrs['class'] = $this->getClass();
        }
        if ($this->hasOption(static::BLOCK_KEY_STYLE)) {
            $attrs['style'] = $this->getStyle();
        }

        return $attrs;
    }

    public function getVideoAttributes(): array
    {
        $attrs = $this->getImageAttributes();
        $attrs['title'] = $this->getText();
        unset($attrs['alt']);

        if ($this->hasOption(static::BLOCK_KEY_CONTROLS)) {
            $attrs['controls'] = 'controls';
        }
        if ($this->hasOption(static::BLOCK_KEY_AUTOPLAY)) {
            $attrs['autoplay'] = 'autoplay';
        }
        if ($this->hasOption(static::BLOCK_KEY_MUTED)) {
            $attrs['muted'] = 'muted';
        }
        if ($this->hasOption(static::BLOCK_KEY_LOOP)) {
            $attrs['loop'] = 'loop';
        }

        return $attrs;
    }

    public function getAudioAttributes(): array
    {
        $attrs = $this->getVideoAttributes();

        if (isset($attrs['width'])) {
            unset($attrs['width']);
        }
        if (isset($attrs['height'])) {
            unset($attrs['height']);
        }

        return $attrs;
    }

}
