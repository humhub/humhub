<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets\richtext\converter;

use humhub\modules\content\widgets\richtext\extensions\link\LinkParserBlock;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;

/**
 * This parser can be used to convert HumHub richtext to plain markdown by removing special richtext markdown syntax.
 *
 * Note: The output of this converter will not be encoded, so do not directly embed the output to a HTML view without
 * encoding. When using this as export format, the frontend responsible for rendering the markdown needs to make sure
 * to purify the result and ideally deactivate direct html parsing e.g.: `require('markdown-it')({html:false, ...})`
 *
 * @since 1.8
 */
class RichTextToMarkdownConverter extends BaseRichTextConverter
{
    /**
     * Suffix used for markdown images
     */
    protected const IMAGE_SUFFIX = '!';

    /**
     * Wrapper mark for bold markdown text
     */
    protected const BOLD_WRAPPER = '**';

    /**
     * Wrapper mark for emphasized markdown text
     */
    protected const EMPHASIZE_WRAPPER = '_';

    /**
     * Wrapper mark for strikethrough markdown text
     */
    protected const STRIKE_WRAPPER = '~~';

    /**
     * Wrapper mark for inline code markdown
     */
    protected const INLINE_CODE_WRAPPER = '`';

    /**
     * @var bool
     */
    protected $escapeBackslashBreak = false;

    /**
     * @var bool whether or not tables should be parsed, in case subclasses want to overwrite `renderTable()`, this flag
     * needs to be set to true
     */
    protected $identifyTable = false;

    /**
     * @var bool whether or not quotes should be parsed, in case subclasses want to overwrite `renderQuote()`, this flag
     * needs to be set to true
     */
    protected $identifyQuote = false;

    /**
     * @inheritdoc
     */
    public $format = ProsemirrorRichText::FORMAT_MARKDOWN;

    /**
     * @var array
     */
    public static $cache = [];

    /**
     * @inheritDoc
     */
    protected function onAfterParse($text) : string
    {
        return trim(parent::onAfterParse($text));
    }

    /**
     * html entity mark parser is disabled by removing marker in php doc
     */
    protected function parseEntity($text) { /* Not implemented */}

    /**
     * `<` mark parser is disabled by removing marker in php doc
     */
    protected function parseLt($text) { /* Not implemented */}

    /**
     * `>` mark parser is disabled by removing marker in php doc
     */
    protected function parseGt($text) { /* Not implemented */}


    /**
     * @inheritDoc
     */
    protected function renderPlainLink(LinkParserBlock $linkBlock) : string
    {
        return RichTextLinkExtension::buildLink($linkBlock->getParsedText(),$linkBlock->getUrl(), $linkBlock->getTitle());
    }

    /**
     * @inheritDoc
     */
    protected function renderPlainImage(LinkParserBlock $linkBlock) : string {
        $result = $this->renderPlainLink($linkBlock);
        return $result[0] === '[' ? static::IMAGE_SUFFIX.$result : $result;
    }

    /**
     * Returns a plain markdown representation of an email
     * @param $block
     * @return string
     */
    protected function renderEmail($block)
    {
        return RichTextLinkExtension::buildLink($block[1], 'mailto:'.$block[1]);
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderUrl($block)
    {
        // We currently do not support automatic url to link
        return $block[1];
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderAutoUrl($block)
    {
        // We currently do not support automatic url to link
        return $block[1];
    }

    /*
     * BLOCKS
     */

    /**
     * Returns a plain text representation of a text paragraph
     * @param $block
     * @return string
     */
    protected function renderParagraph($block)
    {
        return $this->renderAbsy($block['content'])."\n\n";
    }

    /**
     * Returns a plain text representation of a list block
     * @param $block
     * @return string
    */
    protected function renderList($block)
    {
        $output = '';
        $count = 0;
        $level = $block['level'] ?? 0;
        foreach ($block['items'] as $item => $itemLines) {
            foreach ($itemLines as &$line) {
                if($line[0] === 'list') {
                    $line['level'] = $level + 1;
                }
            }

            unset( $line );

            $output .= $level !== 0 ? "\n".str_repeat(' ', $level * 3) : '';
            $output .= $block['list'] === 'ol' ? (isset($block['origNums'][$item]) ? $block['origNums'][$item] : ++$count).'. ' : '- ';
            $output .= $this->renderAbsy($itemLines). ($level === 0 ? "\n" : '');
        }

        return $output . ($level === 0 ? "\n" : '');
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderCode($block)
    {
        $lang = $block['language'] ?? '';
        return "```$lang\n".$block['content']."\n```\n\n";
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderQuote($block)
    {
        return '> '.$this->renderAbsy($block['content']) . "\n\n";
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderHeadline($block)
    {
        return str_repeat('#', $block['level']).' '.$this->renderAbsy($block['content'])."\n\n";
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderHtml($block)
    {
        // We do not strip_tags here, since the richtext does not support html and interprets html as normal text
        return $this->br2nl($block['content']). "\n\n";
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderHr($line)
    {
        return "----------------------------------------\n\n";
    }


    /*
     * INLINE MARKS
     */


    /**
     * @param $block
     * @return string
     */
    protected function renderStrike($block)
    {
        return static::STRIKE_WRAPPER.$this->renderAbsy($block[1]).static::STRIKE_WRAPPER;
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderStrong($block)
    {
        return static::BOLD_WRAPPER.$this->renderAbsy($block[1]).static::BOLD_WRAPPER;
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderEmph($block)
    {
        return static::EMPHASIZE_WRAPPER.$this->renderAbsy($block[1]).static::EMPHASIZE_WRAPPER;
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderInlineCode($block)
    {
        return static::INLINE_CODE_WRAPPER.$block[1].static::INLINE_CODE_WRAPPER;
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderInlineHtml($block)
    {
        // We do not strip_tags here, since the richtext does not support html and interprets html as normal text
        return $this->br2nl($block[1]);
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderText($block)
    {
        // We do not strip_tags here, since the richtext does not support html and interprets html as normal text
        // Currently only <br> is supported
        return $this->br2nl($block[1]);
    }


    /**
     * Deactivates table parsing in caste [[identifyTable]] flag is false (default).
     * @param $line
     * @param $lines
     * @param $current
     * @return bool
     */
    protected function identifyTable($line, $lines, $current)
    {
        return $this->identifyTable
            ? parent::identifyTable($line, $lines, $current)
            : false;
    }

    /**
     * Deactivates quote parsing in caste [[identifyQuote]] flag is false (default).
     * @param $line
     * @return false
     */
    protected function identifyQuote($line)
    {
        return $this->identifyQuote
            ? parent::identifyQuote($line)
            : false;
    }


    /**
     * Deactivated by removing marker
     */
    protected function parseTd($markdown) { /* Not implemented */ }
}
