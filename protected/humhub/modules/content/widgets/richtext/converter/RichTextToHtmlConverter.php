<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets\richtext\converter;


use cebe\markdown\GithubMarkdown;
use cebe\markdown\inline\LinkTrait;
use humhub\libs\Html;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtensionMatch;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtension;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;

/**
 * This parser can be used to convert HumHub richtext directly to html. Note, this parser will only output html supported
 * by plain markdown without any special richtext features. Some HumHub markdown extensions may be supported in the future.
 *
 * Since the HumHub richtext does not support direct HTML in markdown those HTML elements will be encoded.
 *
 * The output parser output will be purified and can safely be used.
 *
 * Available options:
 *
 *  - `exclude`: Exclude certain blocks or extensions from being rendered
 *  - `linkTarget`: Change link `target` (default `_blank`)
 *  - `prevLinkTarget`: Removes `target` and `rel` attribute from all links
 *  - `linkAsText`: Renders links as plain text
 *
 * @since 1.8
 */
class RichTextToHtmlConverter extends BaseRichTextConverter
{
    /**
     * @var string HtmlPurifier HTML.Doctype configuration
     */
    public $doctype =  'HTML 4.01 Transitional';

    /**
     * @var string HtmlPurifier URI.AllowedSchemes configuration
     */
    public $allowedSchemes = ['http' => true, 'https' => true, 'mailto' => true, 'ftp' => true];

    /**
     * @var string HtmlPurifier HTML.Allowed configuration
     */
    public $htmlAllowed = 'h1,h2,h3,h4,h5,h6,br,b,i,strong,em,a,pre,code,img,tt,div,ins,del,sup,sub,p,ol,ul,table,thead,tbody,tfoot,blockquote,dl,dt,dd,kbd,q,samp,var,hr,li,tr,td,th,s,strike';

    /**
     * @var string HtmlPurifier HTML.AllowedAttributes configuration
     */
    public $htmlAllowedAttributes = 'img.src,img.alt,img.title,code.class,a.rel,a.target,a.href,a.title,th.align,td.align,ol.start';

    /**
     * @var bool whether the output should be purified
     */
    public $purify = true;

    /**
     * @inheritdoc
     */
    public $format = ProsemirrorRichText::FORMAT_HTML;

    /**
     * @var array
     */
    public static $cache = [];

    /**
     * @inheritDoc
     */
    protected function onAfterParse($text) : string
    {
        $text = parent::onAfterParse($text);

        if(!$this->purify) {
            return $text;
        }

        return HtmlPurifier::process($text, function ($config) {
            // Make sure we use non xhtml tags, unfortunately HTML5 is not supported by html purifier
            $config->set('HTML.Doctype', $this->doctype);

            if(!$this->getOption('prevLinkTarget', false)) {
                $config->set('HTML.Nofollow', true);
            }

            $config->set('HTML.Allowed', $this->htmlAllowed);
            $config->set('HTML.AllowedAttributes', $this->htmlAllowedAttributes);
            $config->set('URI.AllowedSchemes',$this->allowedSchemes);

            $htmlDefinition = $config->getHTMLDefinition(true);

            $htmlDefinition->addAttribute('a', 'target', 'Text');
        });
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

    /**
     * Returns a plain text representation of an inline html
     * @param $block
     * @return string
     */
    protected function renderInlineHtml($block)
    {
        // We only support <br> tags
        if($block[1] === '<br>' || $block[1] === '<br />') {
            return '<br>';
        }

        return Html::encode($block[1]);
    }

    /**
     * Returns a plain text representation of a html block
     * @param $block
     * @return string
     */
    protected function renderHtml($block)
    {
        // We do not support direct html in richtext markdown
        return '<p>'.nl2br(Html::encode($this->br2nl($block['content']))).'</p>';
    }
}
