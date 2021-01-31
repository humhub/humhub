<?php


namespace humhub\modules\content\widgets\richtext\extensions\link;


use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;

/**
 * An richtext link extension match contains the parser result of a richtext extension link of the following format:
 *
 * [<content>](<extension_key>:<extension_id> "<title>" <addition>)
 *
 * @package humhub\modules\content\widgets\richtext\extensions\link
 */
class RichTextLinkExtensionMatch extends RichTextExtensionMatch
{
    private const INDEX_FULL = 0;
    private const INDEX_CONTENT = 1;
    private const INDEX_EXTENSION_KEY = 2;
    private const INDEX_EXTENSION_ID = 3;
    private const INDEX_TITLE = 4;
    private const INDEX_ADDITION = 5;

    /**
     * @var array
     */
    public $match;

    /**
     * @return string
     */
    public function getFull() : string
    {
        return $this->getByIndex(static::INDEX_FULL);
    }

    public function getText() : string
    {
        return $this->getByIndex(static::INDEX_CONTENT);
    }

    public function getExtensionKey() : string
    {
        return $this->getByIndex(static::INDEX_EXTENSION_KEY);
    }

    public function getExtensionId() : string
    {
        return $this->getByIndex(static::INDEX_EXTENSION_ID);
    }

    public function getExtensionUrl() : string
    {
        return $this->getExtensionKey() . ':' . $this->getExtensionId();
    }

    public function getTitle() : string
    {
        return $this->getByIndex(static::INDEX_TITLE);
    }

    public function getAddition() : ?string
    {
        return $this->getByIndex(static::INDEX_ADDITION);
    }

    public function getByIndex(int $index) : string
    {
        return $this->match[$index] ?? '';
    }

    public function isImage() : bool
    {
        return $this->getFull()[0] === '!';
    }
}
