<?php


namespace humhub\modules\content\widgets\richtext\converter;


use humhub\libs\Helpers;
use humhub\libs\Html;
use humhub\modules\content\widgets\richtext\extensions\link\LinkParserBlock;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use Yii;

class RichTextToShortTextConverter extends RichTextToPlainTextConverter
{
    /**
     * Option can be used to preserve spaces and new lines in the converter result (default false).
     * Note, this option will not affect cached results and therefore does not require a special cache key.
     */
    public const OPTION_PRESERVE_SPACES = 'preserveNewlines';

    /**
     * Option can be used in combination with OPTIONS_PRESERVE_SPACES in order to allow breaks inside the short text
     */
    public const OPTION_NL2BR = 'nl2br';

    /**
     * @inheritdoc
     */
    public $format = ProsemirrorRichText::FORMAT_SHORTTEXT;

    /**
     * @inheritdoc
     */
    public $identifyTable = true;

    /**
     * @inheritdoc
     */
    public $identifyQuote = true;

    /**
     * @var array
     */
    public static $cache = [];

    /**
     * @inheritDoc
     */
    protected function renderPlainLink(LinkParserBlock $linkBlock) : string
    {
        return $linkBlock->getParsedText();
    }

    /**
     * @inheritDoc
     */
    protected function renderQuote($block)
    {
        return $this->renderAbsy($block['content']);
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderHr($line)
    {
        return "";
    }

    /**
     * @inheritDoc
     */
    protected function renderCode($block)
    {
        return Yii::t('ContentModule.richtexteditor', '[Code Block]')."\n\n";
    }

    /**
     * @inheritDoc
     */
    protected function renderTable($block)
    {
        return Yii::t('ContentModule.richtexteditor', '[Table]')."\n\n";
    }

    /**
     * @inheritDoc
     */
    protected function renderHeadline($block)
    {
        return $this->renderAbsy($block['content'])."\n\n";
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderImage($block)
    {
        return Yii::t('ContentModule.richtexteditor', '[Image]')."\n\n";
    }

    /**
     * @inheritDoc
     */
    protected function renderPlainImage(LinkParserBlock $linkBlock) : string
    {
        $url = $linkBlock->getUrl();
        return RichTextLinkExtension::validateNonExtensionUrl($url) ? $url : '';
    }

    /**
     * @inheritDoc
     */
    protected function onAfterParse($text) : string
    {
        $result = $text;

        if(!$this->getOption(static::OPTION_PRESERVE_SPACES, false)) {
            $result  = trim(preg_replace('/\s+/', ' ', $result));
        }

        $result = parent::onAfterParse($result);
        $result = Html::encode($result);

        if($this->getOption(static::OPTION_NL2BR, false)) {
            $result = nl2br($result, false);
        }

        return $result;
    }
}
