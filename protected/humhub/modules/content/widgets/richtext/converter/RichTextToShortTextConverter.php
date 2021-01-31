<?php


namespace humhub\modules\content\widgets\richtext\converter;


use humhub\libs\Html;
use humhub\modules\content\widgets\richtext\extensions\link\LinkParserBlock;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use Yii;

class RichTextToShortTextConverter extends RichTextToPlainTextConverter
{
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
     * @inheritDoc
     */
    protected function renderPlainImage(LinkParserBlock $linkBlock) : string
    {
        return $linkBlock->getUrl();
    }

    /**
     * @inheritDoc
     */
    protected function onAfterParse($text) : string
    {
        // Remove leading slashes
        //$text = preg_replace('/\\\\(\n|\r){1,2}/',  ' ', $text);
        return Html::encode(parent::onAfterParse($text));
    }
}
