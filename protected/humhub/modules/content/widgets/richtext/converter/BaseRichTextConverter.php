<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets\richtext\converter;


use cebe\markdown\GithubMarkdown;
use humhub\components\ActiveRecord;
use humhub\components\Event;
use humhub\libs\Html;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\content\widgets\richtext\extensions\link\LinkParserBlock;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtension;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use Yii;
use yii\base\InvalidArgumentException;

/**
 * This class serves as base class for richtext converters used to convert HumHub richtext to other formats. The base
 * converter class extends GithubMarkdown markdown parser to support:
 *
 *  - `onBeforeParse` and `onAfterparse` events
 *  - new line by `\`
 *  - registration of richtext extensions
 *  - extended link/image regex e.g. for image size [Scaled Image](http://localhost/static/img/logo.png =150x)
 *
 * The [[addExtension()]] function can be used to add additional richtext extensions. By default all extensions registered
 * in [[ProsemirrorRichText::getExtensions()]] are available.
 *
 * > Note: The result of this parser will not be encoded, so do not directly add the result to a HTML view  without
 * encoding it.
 *
 * @since 1.8
 */
abstract class BaseRichTextConverter extends GithubMarkdown
{
    /**
     * Option key for excluding blocks or extensions.
     * Note, this option affects the cached result.
     */
    const OPTION_EXCLUDE = 'exclude';

    /**
     * Option key for overwriting default link target _blank.
     * Note, this option affects the cached result.
     */
    const OPTION_LINK_TARGET = 'linkTarget';

    /**
     * Option key used for rendering links as plain text.
     * Note, this option affects the cached result.
     */
    const OPTION_LINK_AS_TEXT = 'linkAsText';

    /**
     * Option key used for rendering images as links.
     * Note, this option affects the cached result.
     */
    const OPTION_IMAGE_AS_LINK = 'imageAsLink';

    /**
     * Option key used for rendering images as url links.
     * Note, this option affects the cached result.
     */
    const OPTION_IMAGE_AS_URL = 'imageAsText';

    /**
     * Option key for preventing link target attribute.
     * Note, this option affects the cached result.
     */
    const OPTION_PREV_LINK_TARGET = 'prevLinkTarget';

    /**
     * Cache key can be used to cache parser results
     */
    const OPTION_CACHE_KEY = 'cacheKey';

    /**
     * Maximum entries in the cache
     */
    const MAX_CACHE_ENTRIES = 200;

    /**
     * @inheritdoc
     */
    public $html5 = true;

    /**
     * @var string
     */
    public $format;

    /**
     * @var array
     */
    public $options = [];

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
     * @var array
     */
    public static $cache = [];

    /**
     * @var bool whether or not to support `\\\n` backslash breaks
     * @see https://github.com/cebe/markdown/issues/169
     */
    protected $escapeBackslashBreak = true;


    /**
     * BaseRichTextParser constructor.
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;

        foreach (ProsemirrorRichText::getExtensions() as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * Converts markdown into HTML.
     *
     * @param string $text the richtext to parse
     * @return string the parser result
     */
    public static function process($text, $options = [])
    {
        $parser = new static($options);
        return $parser->parse($text);
    }

    /**
     * Builds a cache key for a given content. When using this function with specific parser options a $prefix should be
     * provided in order to not interfere with other parser results.
     *
     * Options only affecting the result within the `onAfterParse` will not alter the cached result and therefore can
     * be used without prefix.
     *
     * @param ContentOwner $content
     * @param string|null $prefix a prefix used for use-cases with specific parser options to not interfere with other results
     * @return string
     */
    public static function buildCacheKeyForContent(ContentOwner $content, $prefix = null)
    {
        if ($content instanceof ContentAddonActiveRecord) {
            // prevent cache key for comments to use the same cache key of original content
            return static::buildCacheKeyForRecord($content);
        }

        $result = 'content_' . $content->content->id;
        return $prefix ? $prefix.'_'.$result : $result;
    }

    /**
     * Builds a cache key for a given record. When using this function with specific parser options a $prefix should be
     * provided in order to not interfere with other parser results.
     *
     * Options only affecting the result within the `onAfterParse` will not alter the cached result and therefore can
     * be used without prefix.
     *
     * @param ContentOwner $content
     * @param string|null $prefix a prefix used for use-cases with specific parser options to not interfere with other results
     * @return string
     */
    public static function buildCacheKeyForRecord(ActiveRecord $record, $prefix = null)
    {
        $result = get_class($record).'_'.$record->getUniqueId();
        return $prefix ? $prefix.'_'.$result : $result;
    }

    /**
     * This function can be used to flush existing parser result caches of this converter class
     */
    public static function flushCache()
    {
        static::$cache = [];
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
     * @inheritDoc
     */
    public function parse($text): string
    {
        try {
            $result = null;
            $cacheKey = $this->getOption(static::OPTION_CACHE_KEY, null);

            if($cacheKey && isset(static::$cache[$cacheKey])) {
                $result = static::$cache[$cacheKey];
            }

            if($result === null) {
                $result = $this->onBeforeParse($text);
                $result = parent::parse($result);

                // We cache the whole parser result, this way we can reuse the same result e.g. for different maxLength
                // or other post processes
                if($cacheKey && count(static::$cache) < static::MAX_CACHE_ENTRIES) {
                    static::$cache[$cacheKey] = $result;
                }
            }

            $result = $this->onAfterParse($result);

            return $result;
        } catch (\Throwable $t) {
            Yii::error($t);
            return '[ParserError]';
        }
    }

    /**
     * This function is called right before the parser starts parsing the given `$text`.
     * Sub classes may use this function to prepare the richtext prior to parsing.
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

        // Remove leading new backslash new lines e.g. "Test\\\n" -> "Test"
        $text = preg_replace('/\\\\(\n|\r){1,2}$/',  '', $text);

        foreach ($this->extensions as $extension) {
            $text = $extension->onBeforeConvert($text, $this->format, $this->options);
        }

        return $text;
    }

    protected function renderAbsy($blocks)
    {
        if(!empty($this->getExcludes())) {
            $blocks = array_filter($blocks, function($block) {
                return !in_array($block[0], $this->getExcludes(), true);
            });
        }

        return parent::renderAbsy($blocks);
    }


    /**
     * This function is called after the parser starts parsing the given `$text`.
     * Sub classes may use this function to manipulate the parser result.
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
    protected function onAfterParse($text) : string
    {
        $evt = new Event(['result' => $text]);
        Event::trigger($this, static::EVENT_AFTER_PARSE, $evt);
        $text = $evt->result;

        foreach ($this->extensions as $extension) {
            $text = $extension->onAfterConvert($text, $this->format, $this->options);
        }

        return $text;
    }

    /**
     * @inheritDoc
     *
     * Parses for backslash hard breaks as `FirstLine\\\nSecondLine` if [[escapeBackslashBreak]] is active (default)
     *
     * @marker \
     * @see https://github.com/cebe/markdown/issues/169
     */
    protected function parseEscape($text)
    {
        # If the backslash is followed by a newline.
        # Note: GFM doesn't allow spaces after the backslash.
        if ($this->escapeBackslashBreak && isset($text[1]) && $text[1] === "\n") {
            $br = $this->html5 ? "<br>\n" : "<br />\n";
            # Return the line break
            return [["text", $br], 2];
        }

        # Otherwise parse the sequence normally
        return parent::parseEscape($text);

    }

    /**
     * Extends parent regex patter in order to support extension metadata as image size ![Scaled Image](http://localhost/static/img/logo.png =150x)
     * @param $markdown
     * @return array|bool
     */
    protected function parseLinkOrImage($markdown)
    {
        if (strpos($markdown, ']') !== false && preg_match('/\[((?>[^\]\[]+|(?R))*)\]/', $markdown, $textMatches)) {
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

    /**
     * Renders the given link block to plain text <text>(<url>).
     *
     * This function respects richtext link extensions and tries to determine a link extension by extension url. A
     * e.g. The plaintext conversion of a mention extension `[Some Name](metion:guid)` will be handled by the
     * `MentioningExtension` class.
     *
     * @param $block
     * @return string
     */
    protected function renderLink($block)
    {
       return $this->renderLinkOrImage(new LinkParserBlock([
           'block' => $block,
           'parsedText' => is_string($block['text']) ? $block['text'] : $this->renderAbsy($block['text'])
       ]));
    }

    protected function renderLinkOrImage(LinkParserBlock $linkBlock)
    {
        if(!$linkBlock->getUrl()) {
            return $linkBlock->getParsedText();
        }

        foreach ($this->linkExtensions as $linkExtension) {
            if(in_array($linkExtension->key, $this->getExcludes())) {
                return '';
            }

            if($linkExtension->validateExtensionUrl($linkBlock->getUrl())) {
                $linkExtension->onBeforeConvertLink($linkBlock);
                $linkBlock->toAbsoluteUrl();
                return $this->renderLinkExtension($linkExtension, $linkBlock);
            }
        }

        $linkBlock->toAbsoluteUrl();

        if($linkBlock->isImage()) {
            return $this->renderPlainImage($linkBlock);
        }

        return $this->renderPlainLink($linkBlock);
    }

    private function getExcludes()
    {
        return $this->getOption(static::OPTION_EXCLUDE, []);
    }

    public function getOption(string $key, $default = null)
    {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    protected function renderImage($block)
    {
        $text = $block['text'];

        // Remove image alignment extension from image alt text
        $block['text'] = preg_replace('/>?<?$/', '', $text);

        if($this->getOption(static::OPTION_IMAGE_AS_URL, false)) {
            return Html::encode($block['url']);
        }

        if($this->getOption(static::OPTION_IMAGE_AS_LINK, false)) {
            $text = empty($block['text']) ? $block['url'] : $block['text'];
            $linkBlock = $block;
            $linkBlock[0] = 'link';
            $linkBlock['text'] = [['text', $text]];
            return $this->renderPlainLink(new LinkParserBlock(['block' => $linkBlock]));
        }

        return $this->renderLinkOrImage(new LinkParserBlock([
            'block' => $block,
            'isImage' => true,
            'parsedText' => is_string($block['text']) ? $block['text'] : $this->renderAbsy($block['text'])
        ]));
    }

    /**
     * @param RichTextLinkExtension $ext
     * @param LinkParserBlock $linkBlock
     * @return string
     */
    protected function renderLinkExtension(RichTextLinkExtension $ext, LinkParserBlock $linkBlock) : string {
        if($linkBlock->getResult()) {
            return $linkBlock->getResult();
        }

        if($linkBlock->isImage()) {
            return $this->renderPlainImage($linkBlock);
        }

        return $this->renderPlainLink($linkBlock);
    }

    /**
     * @param LinkParserBlock $linkBlock
     * @return string
     */
    protected function renderPlainLink(LinkParserBlock $linkBlock) : string {
        $block = $linkBlock->block;

        if (isset($block['refkey'])) {
            if (($ref = $this->lookupReference($block['refkey'])) !== false) {
                $block = array_merge($block, $ref);
            } else {
                return $block['orig'];
            }
        }

        if($this->getOption(static::OPTION_LINK_AS_TEXT, false)) {
            return $this->renderAbsy($block['text']);
        }

        $target = Html::encode($this->getOption(static::OPTION_LINK_TARGET, '_blank'));
        $targetAttr = !$this->getOption(static::OPTION_PREV_LINK_TARGET, false) ? " target=\"$target\"" : '';

        return '<a href="' . htmlspecialchars($block['url'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"'. $targetAttr
            . (empty($block['title']) ? '' : ' title="' . htmlspecialchars($block['title'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"')
            . '>' . $this->renderAbsy($block['text']) . '</a>';
    }

    /**
     * @param LinkParserBlock $linkBlock
     * @return string
     */
    protected function renderPlainImage(LinkParserBlock $linkBlock) : string {
        return parent::renderImage($linkBlock->block);
    }

    /**
     * Helper function to translate br tags to new lines
     * @param $block
     * @return string
     */
    protected function br2nl($text)
    {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $text);
    }

    /**
     * @inheritdoc
     */
    protected function consumeOl($lines, $current)
    {
        $result = parent::consumeOl($lines, $current);

        if (is_array($lines) && count($lines) > 0 && !empty($result[0]['items'])) {
            $result[0]['origNums'] = [];
            $i = array_keys($result[0]['items'])[0];
            foreach ($lines as $line) {
                if (preg_match('/^(\d+)\./', $line, $num)) {
                    $result[0]['origNums'][$i++] = $num[1];
                }
            }
        }

        return $result;
    }
}