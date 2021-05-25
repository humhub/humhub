<?php

namespace humhub\modules\content\widgets\richtext\extensions\oembed;

use humhub\models\UrlOembed;
use humhub\modules\content\widgets\richtext\extensions\link\LinkParserBlock;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\components\ActiveRecord;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use yii\helpers\Html;

/**
 * This LinkExtension is used to represent mentionings in the richtext as:
 *
 * [<name>](mention:<guid> "<url>")
 *
 */
class OembedExtension extends RichTextLinkExtension
{
    /**
     * @inheritdoc
     */
    public $key = 'oembed';

    public static $maxOembed = 10;

    /**
     * @var array holds included oembeds used for rendering
     */
    private $oembeds = [];

    public function onBeforeConvertLink(LinkParserBlock $linkBlock) : void
    {
        $linkBlock->setUrl($this->cutExtensionKeyFromUrl($linkBlock->getUrl()));
    }

    public function onBeforeOutput(ProsemirrorRichText $richtext, string $output) : string {
        $this->oembeds = static::parseOembeds($output, static::$maxOembed);
        return $output;
    }

    public function onAfterOutput(ProsemirrorRichText $richtext, string $output) : string {
        return $output . $this->buildOembedOutput();
    }

    /**
     * @return string html extension holding the actual oembed dom nodes which will be embedded into the rich text
     */
    private function buildOembedOutput() : string
    {
        $result = '';
        foreach ($this->oembeds as $url => $oembed) {
            $result .= Html::tag('div', $oembed, ['data-oembed' => Html::encode($url)]);
        }

        return Html::tag('div', $result, ['class' => 'richtext-oembed-container', 'style' => 'display:none']);
    }

    public static function builOembed($url) : string
    {

        return static::buildLink($url, 'oembed:'.$url);
    }

    public static function parseOembeds($text, $max = 100)
    {
        $result = [];
        $oembedCount = 0;
        foreach (static::scanLinkExtension($text) as $match) {
            if($oembedCount === $max) {
                break;
            }

            if(!empty($match->getExtensionId())) {
                $oembedPreview =  UrlOembed::getOEmbed($match->getExtensionId());
                if(!empty($oembedPreview)) {
                    $oembedCount++;
                    $result[$match->getExtensionId()] = $oembedPreview;
                }
            }

        }
        return $result;
    }

    public static function buildOembedNotFound($url) : string
    {
        return '['.$url.']('.$url.')';
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
            if($match->getExtensionId() && UrlOembed::hasOEmbedSupport($match->getExtensionId())) {
                UrlOembed::preload($match->getExtensionId());
                $result[$this->key][] = $match->getExtensionId();
            }
        }

        return $text;
    }
}
