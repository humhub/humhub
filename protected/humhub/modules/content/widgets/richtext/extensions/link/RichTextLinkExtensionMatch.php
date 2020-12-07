<?php


namespace humhub\modules\content\widgets\richtext\extensions\link;


use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;

class RichTextLinkExtensionMatch extends RichTextExtensionMatch
{
    private const LINK_REGEX_GROUP_FULL = 0;
    private const LINK_REGEX_GROUP_CONTENT = 1;
    private const LINK_REGEX_GROUP_EXTENSION_KEY = 2;
    private const LINK_REGEX_GROUP_EXTENSION_ID = 3;
    private const LINK_REGEX_GROUP_TITLE = 4;

    /**
     * @var array
     */
    public $match;

    /**
     * @return string
     */
    public function getFull() : string
    {
        return $this->getByIndex(static::LINK_REGEX_GROUP_FULL);
    }

    public function getText() : string
    {
        return $this->getByIndex(static::LINK_REGEX_GROUP_CONTENT);
    }

    public function getExtensionKey() : string
    {
        return $this->getByIndex(static::LINK_REGEX_GROUP_EXTENSION_KEY);
    }

    public function getExtensionId() : string
    {
        return $this->getByIndex(static::LINK_REGEX_GROUP_EXTENSION_ID);
    }

    public function getUrl() : string
    {
        return $this->getExtensionKey() . ':' . $this->getExtensionId();
    }

    public function getTitle() : string
    {
        return $this->getByIndex(static::LINK_REGEX_GROUP_TITLE);
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
