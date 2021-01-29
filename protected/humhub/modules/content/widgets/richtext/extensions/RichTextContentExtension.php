<?php


namespace humhub\modules\content\widgets\richtext\extensions;


use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use yii\base\Model;
use humhub\components\ActiveRecord;

abstract class RichTextContentExtension extends Model implements RichTextExtension
{
    /**
     * @var string defines the extension key for this type link extensions
     */
    public $key;

    /**
     * @param string $text
     * @param string $format
     * @param array $options
     * @return string
     */
    public function onAfterConvert(string $text, string $format, array $options = []): string
    {
        return $text;
    }

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

    public function onPostProcess(string $text, ActiveRecord $record, ?string $attribute, array &$result): string
    {
        return $text;
    }

    public function onBeforeOutput(ProsemirrorRichText $richtext, string $output): string {
        return $output;
    }

    public function onAfterOutput(ProsemirrorRichText $richtext, string $output): string {
        return $output;
    }
}
