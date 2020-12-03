<?php


namespace humhub\modules\content\widgets\richtext\extensions;


use yii\base\Model;

/**
 * @package humhub\modules\content\widgets\richtext\extensions
 */
abstract class RichTextExtension extends Model
{
    /**
     * @var string defines the extension key for this type link extensions
     */
    public $key;

    /**
     * @param $text
     * @return RichTextExtensionMatch[]
     */
    public static function scan($text)
    {
        return static::instance()->scanExtension($text);
    }

    /**
     * @param $text
     * @return string
     */
    public static function replace($text, callable $callback) : string
    {
        return static::instance()->replaceExtension($text, $callback);
    }

    /**
     * @param $text
     * @return RichTextExtensionMatch[]
     */
    protected function scanExtension($text)
    {
        preg_match_all($this->getRegex(), $text, $matches, PREG_SET_ORDER);

        $result = [];
        foreach ($matches as $match) {
            $result[] = $this->initMatch($match);
        }

        return $result;
    }

    /**
     * @param $text
     * @return string
     */
    protected function replaceExtension($text, callable $callback)
    {
        return preg_replace_callback($this->getRegex(), function($match) use ($callback) {
            return $callback($this->initMatch($match));
        }, $text);
    }

    /**
     * @param RichTextExtensionMatch $match
     * @return string
     */
    public abstract function toPlainText(RichTextExtensionMatch $match) : string;

    /**
     * @param RichTextExtensionMatch $match
     * @return string
     */
    public abstract function initMatch(array $match) : RichTextExtensionMatch;

    /**
     * @param array $match
     * @return string
     */
    public abstract function getRegex() : string;

}
