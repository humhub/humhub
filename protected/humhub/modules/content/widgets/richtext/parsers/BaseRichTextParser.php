<?php


namespace humhub\modules\content\widgets\richtext\parsers;


use cebe\markdown\GithubMarkdown;
use cebe\markdown\inline\LinkTrait;
use humhub\components\Event;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtensionMatch;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtension;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use yii\helpers\Url;

/**
 * This parser can be used to convert a richtext or plain markdown to a plain text format used for example in
 * plain text emails.
 *
 * The [[addExtension()]] function can be used to add additional richtext extensions. By default all extensions registered
 * in [[ProsemirrorRichText::getExtensions()]] are available.
 *
 * > Note: The result of this parser will not be encoded, so do not directly add the result to a HTML view  without
 * encoding it.
 *
 * @since 1.8
 */
abstract class BaseRichTextParser extends GithubMarkdown
{
    /**
     * @event triggered before text is parsed
     */
    const EVENT_BEFORE_PARSE = 'beforeParse';

    /**
     * @event triggered after text is parsed
     */
    const EVENT_AFTER_PARSE = 'afterParse';


    /**
     * @var RichTextLinkExtension[]
     */
    protected $linkExtensions = [];

    /**
     * @var RichTextExtension[]
     */
    protected $extensions = [];

    /**
     * BaseRichTextParser constructor.
     */
    public function __construct()
    {
        $extensions = ProsemirrorRichText::getExtensions();
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * Can be used to add additional richtext extensions
     * @param RichTextExtension $extension
     */
    public function addExtension(RichTextExtension $extension) {
        $this->extensions[] = $extension;
        if($extension instanceof RichTextLinkExtension) {
            $this->linkExtensions[] = $extension;
        }
    }

    /**
     * Converts the given markdown text to plain text.
     *
     * > Note: This parser won't escape or strip tags, so do not use this directly
     * in a HTML view without calling `Html::encode($parser->parse($md))`.
     *
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        $text = $this->onBeforeParse($text);
        $text = parent::parse($text);
        return $this->onAfterParse($text);
    }

    /**
     * Sub classes my use this function to manipulate the richtext prior to parsing it.
     *
     * When overwriting, sub classes need to call:
     *
     * ```php
     * protected function onBeforeParse($text)
     * {
     *    $text = parent::onBeforeParse($text);
     *    // do some modification to text
     *    return $text;
     * }
     * ```
     *
     * @param $text
     * @return mixed|string
     */
    protected function onBeforeParse($text)
    {
        $evt = new Event(['result' => $text]);
        Event::trigger($this, static::EVENT_BEFORE_PARSE, $evt);
        $text = $evt->result;

        foreach ($this->extensions as $extension) {
            $text = $extension->onBeforeParse($text);
        }

        return $text;
    }

    /**
     * Sub classes my use this function to manipulate the parser result.
     *
     * When overwriting, sub classes need to call:
     *
     * ```php
     * protected function onAfterParse($text)
     * {
     *    $text = parent::onAfterParse($text);
     *    // do some modification to text
     *    return $text;
     * }
     * ```
     *
     *
     * @param $text
     * @return mixed|string
     */
    protected function onAfterParse($text)
    {
        $evt = new Event(['result' => $text]);
        Event::trigger($this, static::EVENT_AFTER_PARSE, $evt);
        $text = $evt->result;

        foreach ($this->extensions as $extension) {
            $text = $extension->onAfterParse($text);
        }

        return $text;
    }

    /**
     * Extends parent regex patter in order to support extension metadata as image size ![Scaled Image](http://localhost/static/img/logo.png =150x)
     * @param $markdown
     * @return array|bool
     */
    protected function parseLinkOrImage($markdown)
    {
        if (strpos($markdown, ']') !== false && preg_match('/\[((?>[^\]\[]+|(?R))*)\]/', $markdown, $textMatches)) { // TODO improve bracket regex
            $text = $textMatches[1];
            $offset = strlen($textMatches[0]);
            $markdown = substr($markdown, $offset);

            $pattern = <<<REGEXP
				/(?(R) # in case of recursion match parentheses
					 \(((?>[^\s()]+)|(?R))*\)
				|      # else match a link with title
					^\((((?>[^\s()]+)|(?R))*)(\s+"(.*?)")?(\s+([^)]*))?\)
				)/x
REGEXP;
            if (preg_match($pattern, $markdown, $refMatches)) {
                // inline link
                return [
                    $text,
                    isset($refMatches[2]) ? $refMatches[2] : '', // url
                    empty($refMatches[5]) ? null: $refMatches[5], // title
                    $offset + strlen($refMatches[0]), // offset
                    null, // reference key
                    empty($refMatches[7]) ? null : $refMatches[7] // extension metadata
                ];
            } elseif (preg_match('/^([ \n]?\[(.*?)\])?/s', $markdown, $refMatches)) {
                // reference style link
                if (empty($refMatches[2])) {
                    $key = strtolower($text);
                } else {
                    $key = strtolower($refMatches[2]);
                }
                return [
                    $text,
                    null, // url
                    null, // title
                    $offset + strlen($refMatches[0]), // offset
                    $key,
                ];
            }
        }
        return false;
    }
}
