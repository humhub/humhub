<?php

namespace humhub\modules\content\widgets\richtext\extensions;

use yii\base\Model;

/**
 * A RichTextExtensionMatch wraps regex results of richtext extensions and provides helper functions to access
 * data of a match.
 */
abstract class RichTextExtensionMatch extends Model
{
    /**
     * @var array
     */
    public $match;

    /**
     * Returns the full match string
     * @return string
     */
    abstract public function getFull(): string;

    /**
     * Returns the extension key
     * @return string
     */
    abstract public function getExtensionKey(): string;

    /**
     * Returns the id of this extension match, the id usually identifies this instance e.g. an url
     * @return string
     */
    abstract public function getExtensionId(): ?string;

    /**
     * Returns the value of a given match index or null
     * @param $index
     * @return string|null
     */
    public function getByIndex(int $index): ?string
    {
        return $this->match[$index] ?? null;
    }
}
