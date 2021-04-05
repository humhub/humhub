<?php


namespace humhub\modules\content\widgets\richtext\extensions\link;


use yii\base\Model;
use yii\helpers\Url;

/**
 * <orig> = [<text>](<url> "<title>")
 */
class LinkParserBlock extends Model
{
    const BLOCK_KEY_URL = 'url';
    const BLOCK_KEY_TITLE = 'title';
    const BLOCK_KEY_MD = 'orig';
    const BLOCK_KEY_TEXT = 'text';
    const BLOCK_KEY_FILE_ID = 'fileId';

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

    public function getMarkdown() : ?string
    {
        return $this->block[static::BLOCK_KEY_MD] ?? null;
    }

    public function getUrl() : ?string
    {
        return $this->block[static::BLOCK_KEY_URL] ?? null;
    }

    public function toAbsoluteUrl() : void
    {
        $url = $this->getUrl();
        if($url && $url[0] === '/') {
            $url = Url::base(true).$url;
        }

        $this->setUrl($url);
    }

    public function setUrl(string $url)
    {
        $this->block[static::BLOCK_KEY_URL] = $url;
    }

    public function getText() : ?array
    {
        return $this->block[static::BLOCK_KEY_TEXT] ?? null;
    }

    public function setText(string $text)
    {
        $this->block[static::BLOCK_KEY_TEXT] = $this->textToBlockFormat($text);
        $this->setParsedText($text);
    }

    public function getTitle() : ?string
    {
        return $this->block[static::BLOCK_KEY_TITLE] ?? null;
    }

    public function setTitle(string $title = null)
    {
        $this->block[static::BLOCK_KEY_TITLE] = $title;
    }

    public function getFileId() : ?string
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
        if($this->isImage()) {
            return $text;
        }

        if(!$text) {
            $text = '';
        }

        return [['text', $text]];
    }

}
