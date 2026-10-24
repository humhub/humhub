<?php

namespace humhub\modules\content\widgets\richtext\extensions\oembed;

use humhub\components\ActiveRecord;
use humhub\helpers\Html;
use humhub\models\UrlOembed;
use humhub\modules\content\widgets\richtext\extensions\link\LinkParserBlock;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;

/**
 * This LinkExtension is used to represent oembed links in the richtext as:
 *
 * [<url>](oembed:<url>)
 *
 * The link stays in the markdown as it is; the preview markup is what the client renders in
 * its place. A server-rendered richtext ({@see ProsemirrorRichText::run()}) ships the previews
 * as a hidden sibling of its envelope ({@see self::onAfterOutput()}), a client that receives the
 * markdown alone ({@see ProsemirrorRichText::getMarkdown()}, e.g. through the API) fetches them
 * itself via `humhub.oembed.js` `load()` - see `docs/develop/ui-js-vuejs-interop.md`,
 * "RichTextOutput". The fetch is per user (the oembed consent, {@see UrlOembed::isAllowedDomain()}),
 * which is why it does not happen in {@see self::onBeforeOutput()}: the processed markdown must
 * stay the same for every reader.
 */
class OembedExtension extends RichTextLinkExtension
{
    /**
     * @inheritdoc
     */
    public $key = 'oembed';

    public static $maxOembed = 10;

    /**
     * @var string the processed markdown of the current render, kept between
     * {@see self::onBeforeOutput()} and {@see self::onAfterOutput()}
     */
    private string $markdown = '';

    public function onBeforeConvertLink(LinkParserBlock $linkBlock): void
    {
        $linkBlock->setUrl($this->cutExtensionKeyFromUrl($linkBlock->getUrl()));
    }

    public function onBeforeOutput(ProsemirrorRichText $richtext, string $output): string
    {
        $this->markdown = $output;
        return $output;
    }

    public function onAfterOutput(ProsemirrorRichText $richtext, string $output): string
    {
        return $output . $this->buildOembedOutput(static::parseOembeds($this->markdown, static::$maxOembed));
    }

    /**
     * @param array $oembeds preview html by url, see {@see self::parseOembeds()}
     * @return string html extension holding the actual oembed dom nodes which will be embedded into the rich text
     */
    private function buildOembedOutput(array $oembeds): string
    {
        $result = '';
        foreach ($oembeds as $url => $oembed) {
            $result .= Html::tag('div', $oembed, ['data-oembed' => Html::encode($url)]);
        }

        return Html::tag('div', $result, ['class' => 'richtext-oembed-container', 'style' => 'display:none']);
    }

    public static function builOembed($url): string
    {
        return static::buildLink($url, 'oembed:' . $url);
    }

    public static function parseOembeds($text, $max = 100)
    {
        $result = [];
        $oembedCount = 0;
        foreach (static::scanLinkExtension($text) as $match) {
            if ($oembedCount === $max) {
                break;
            }

            if (!empty($match->getExtensionId())) {
                $oembedPreview = UrlOembed::getOEmbed($match->getExtensionId());
                if (!empty($oembedPreview)) {
                    $oembedCount++;
                    $result[$match->getExtensionId()] = $oembedPreview;
                }
            }

        }
        return $result;
    }

    public static function buildOembedNotFound($url): string
    {
        return '[' . $url . '](' . $url . ')';
    }

    /**
     * Scans the given text for oembed extension links and preloads the oembed urls. All oembed urls will be added
     * to `$result['oembed']`.
     *
     * @param string $text
     * @param ActiveRecord $record
     * @param string|null $attribute
     * @param array $result
     * @return string
     */
    public function onPostProcess(string $text, ActiveRecord $record, ?string $attribute, array &$result): string
    {
        $result[$this->key] = [];
        foreach ($this->scanExtension($text) as $match) {
            if ($match->getExtensionId() && UrlOembed::hasOEmbedSupport($match->getExtensionId())) {
                UrlOembed::preload($match->getExtensionId());
                $result[$this->key][] = $match->getExtensionId();
            }
        }

        return $text;
    }
}
