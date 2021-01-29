<?php


namespace humhub\modules\content\widgets\richtext\extensions\emoji;


use humhub\libs\EmojiMap;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;

class RichTextEmojiExtensionMatch extends RichTextExtensionMatch
{

    /**
     * Returns the full match string
     * @return string
     */
    public function getFull(): string
    {
        return $this->getByIndex(0);
    }

    /**
     * Returns the text content of the extension match if supported
     * @return string
     */
    public function getText(): ?string
    {
        return $this->getByIndex(1);
    }

    /**
     * Returns the extension key
     * @return string
     */
    public function getExtensionKey(): string
    {
        return 'emoji';
    }

    /**
     * Returns the id of this extension match, if supported
     * @return string
     */
    public function getExtensionId(): ?string
    {
        return null;
    }

    /**
     * Returns an url of this extension match, if supported
     * @return string
     */
    public function getExtensionUrl(): ?string
    {
        //TODO: Maybe generate URL?
        return null;
    }

    /**
     * Returns a title of this extension match if supported
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getText();
    }

    public function getAsUtf8() : string
    {
        return EmojiMap::MAP[$this->getTitle()] ?? '';
    }
}
