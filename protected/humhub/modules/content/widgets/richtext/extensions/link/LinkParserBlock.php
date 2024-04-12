<?php

namespace humhub\modules\content\widgets\richtext\extensions\link;

use humhub\libs\Html;
use yii\base\Model;
use yii\helpers\Url;

/**
 * Link: <orig> = [<text>](<url> "<title>")
 * Image: <orig> = ![<text><alignment>](<url> "<title>" =<width>x<height>)
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
            if (substr($text, -2) === '><') {
                $this->setClass('center-block');
                $this->setText(substr($text, 0, -2));
            } elseif (substr($text, -1) === '<') {
                $this->setClass('pull-left');
                $this->setText(substr($text, 0, -1));
            } elseif (substr($text, -1) === '>') {
                $this->setClass('pull-right');
                $this->setText(substr($text, 0, -1));
            }
        }

        if ($this->hasOption(static::BLOCK_KEY_MD) && preg_match('/=(\d+)?x(\d+)?\)$/', $this->block[static::BLOCK_KEY_MD], $size)) {
            $this->setWidth($size[1] ?? null);
            $this->setHeight($size[2] ?? null);
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

    public function setTitle(string $title = null)
    {
        $this->block[static::BLOCK_KEY_TITLE] = $title;
    }

    public function getFileId(): ?string
    {
        return $this->block[static::BLOCK_KEY_FILE_ID] ?? null;
    }

    public function setFileId(string $fileId = null)
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

    public function setBlock(string $text, string $url, string $title = null, $fileId = null)
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

    public function renderImageAttributes(): string
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

        return Html::renderTagAttributes($attrs);
    }

}
