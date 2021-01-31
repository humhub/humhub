<?php


namespace humhub\modules\content\widgets\richtext\extensions\emoji;


use humhub\libs\EmojiMap;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;

/**
 * Richtext emoji extension match contains the result of the following emoji format:
 *
 * :<emojiName>:
 *
 * @package humhub\modules\content\widgets\richtext\extensions\emoji
 */
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
        return $this->getByIndex(1);
    }

    /**
     * Returns the full match string
     * @return string
     */
    public function getEmojiName(): string
    {
        return $this->getExtensionId();
    }
}
