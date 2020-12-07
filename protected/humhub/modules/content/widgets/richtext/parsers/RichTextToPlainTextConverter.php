<?php


namespace humhub\modules\content\widgets\richtext\parsers;


use cebe\markdown\GithubMarkdown;
use cebe\markdown\inline\LinkTrait;
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
class RichTextToPlainTextConverter extends BaseRichTextParser
{
    /**
     * @inheritDoc
     */
    public function onAfterParse($text)
    {
        return trim(parent::onAfterParse($text));
    }

    /**
     * Entity parsing is disabled by removing marker
     */
    protected function parseEntity($text)
    {
        // Not implemented
    }

    /**
     * This parser was disabled by removing marker in php doc since we do not need to encode < for plain text output
     */
    protected function parseLt($text)
    {
        // Not implemented
    }

    /**
     * This parser was disabled by removing marker in php doc since we do not need to encode > for plain text output
     */
    protected function parseGt($text)
    {
        // Not implemented
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
        $url = $block['url'];


        $parsedText = is_string($block['text']) ? $block['text'] : $this->renderAbsy($block['text']);

        if(!$url) {
            return $parsedText;
        }

        foreach ($this->linkExtensions as $linkExtension) {
            if($linkExtension->validateExtensionUrl($url)) {
                $parsedBlock = $block;
                $parsedBlock['text'] = $parsedText;
                return $linkExtension->toPlainText($parsedBlock);
            }
        }

        return RichTextLinkExtension::convertToPlainText($parsedText, $this->getAbsoluteUrl($block['url']));
    }

    private function getAbsoluteUrl($url)
    {
        if($url && $url[0] === '/') {
            return Url::base(true).$url;
        }

        return $url;
    }

    /**
     * Renders the given image block to plain text <text>(<url>).
     *
     * This function respects richtext link extensions and tries to determine a link extension by extension url. A
     * e.g. The plaintext conversion of a file extension `![Some File](file-guid:guid)` will be handled by the
     * `FileExtension` class.
     *
     * @param $block
     * @return string
     */
    protected function renderImage($block)
    {
        $text = $block['text'];

        if(empty($text)) {
            return $this->renderLink($block);
        }

        // Remove image alignment extension from image alt text
        $block['text'] =  preg_replace('/>?<?$/', '', $text);

        return $this->renderLink($block);
    }

    /**
     * Returns a plain text representation of an email
     * @param $block
     * @return string
     */
    protected function renderEmail($block)
    {
        return $block[1];
    }

    /**
     * Returns a plain text representation of an url
     * @param $block
     * @return string
     */
    protected function renderUrl($block)
    {
        return $block[1];
    }

    /**
     * Returns a plain text representation of an auto generated url
     * @param $block
     * @return string
     */
    protected function renderAutoUrl($block)
    {
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
        // TODO: Maybe we could just skip parsing lists at all and just use the markdown syntax
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
            $output .= $block['list'] === 'ol' ? ++$count.'. ' : '- ';
            $output .= $this->renderAbsy($itemLines). ($level === 0 ? "\n" : '');
        }

        return $output . ($level === 0 ? "\n" : '');
    }

    /**
     * Returns a plain text representation of a code block
     * @param $block
     * @return string
     */
    protected function renderCode($block)
    {
        $lang = $block['language'] ?? '';
        return "```$lang\n".$block['content']."\n```\n\n";
    }

    /**
     * Returns a plain text representation of a quote block
     * @param $block
     * @return string
     */
    protected function renderQuote($block)
    {
        return '> '.$this->renderAbsy($block['content']) . "\n\n";
    }

    /**
     * Returns a plain text representation of a headline block
     * @param $block
     * @return string
     */
    protected function renderHeadline($block)
    {
        return str_repeat('#', $block['level']).' '.$this->renderAbsy($block['content'])."\n\n";
    }

    /**
     * Returns a plain text representation of a html block
     * @param $block
     * @return string
     */
    protected function renderHtml($block)
    {
        // We do not strip_tags here, since the richtext does not support html and interprets html as normal text
        return $this->br2nl($block['content']). "\n\n";
    }

    /**
     * Returns a plain text representation of a horizontal rule
     * @param $block
     * @return string
     */
    protected function renderHr($line)
    {
        return '----------------------------------------';
    }


    /*
     * INLINE MARKS
     */


    /**
     * Returns a plain text representation of a strikethrough mark
     * @param $block
     * @return string
     */
    protected function renderStrike($block)
    {
        return $this->renderAbsy($block[1]);
    }

    /**
     * Returns a plain text representation of a strong mark
     * @param $block
     * @return string
     */
    protected function renderStrong($block)
    {
        return $this->renderAbsy($block[1]);
    }

    /**
     * Returns a plain text representation of an emphasis mark
     * @param $block
     * @return string
     */
    protected function renderEmph($block)
    {
        return $this->renderAbsy($block[1]);
    }

    /**
     * Returns a plain text representation of an inline code mark
     * @param $block
     * @return string
     */
    protected function renderInlineCode($block)
    {
        return $block[1];
    }

    /**
     * Returns a plain text representation of an inline html
     * @param $block
     * @return string
     */
    protected function renderInlineHtml($block)
    {
        // We do not strip_tags here, since the richtext does not support html and interprets html as normal text
        return $this->br2nl($block[1]);
    }

    /**
     * Returns a plain text representation of inline text
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
     * Helper function to translate br tags to new lines
     * @param $block
     * @return string
     */
    protected function br2nl($text)
    {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $text);
    }


    /**
     * Deactivates table parsing, just leave the markdown syntax as is for tables.
     * @param $line
     * @param $lines
     * @param $current
     * @return bool
     */
    protected function identifyTable($line, $lines, $current)
    {
        return false;
    }

    /**
     * Deactivated by removing marker
     */
    protected function parseTd($markdown)
    {
        // Not implemented
    }
}
