<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use Exception;

/**
 * MarkdownViewWidget shows Markdown flavored content
 *
 * @author luke
 * @since 0.11
 */
class MarkdownView extends \yii\base\Widget
{

    /**
     * Markdown to parse
     *
     * @var string
     */
    public $markdown = "";

    /**
     * Markdown parser class
     *
     * @var string
     */
    public $parserClass = "humhub\libs\Markdown";

    /**
     * Purify output after parsing
     *
     * @var boolean
     */
    public $purifyOutput = true;

    /**
     * Stylesheet for Highlight.js
     */
    public $highlightJsCss = "github";

    /**
     * @var boolean return plain output (do not use widget template)
     */
    public $returnPlain = false;

    public function init()
    {
        if (!\humhub\libs\Helpers::CheckClassType($this->parserClass, "cebe\markdown\Parser")) {
            throw new Exception("Invalid markdown parser class given!");
        }
    }

    public function run()
    {
        $this->markdown = \yii\helpers\Html::encode($this->markdown);

        $parserClass = $this->parserClass;

        $parser = new $parserClass;
        $html = $parser->parse($this->markdown);

        if ($this->purifyOutput) {
            $html = \yii\helpers\HtmlPurifier::process($html, function ($config) {
                        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'ftp' => true, 'file' => true]);
                        $config->getHTMLDefinition(true)
                                ->addAttribute('a', 'target', 'Text');
                    });
        }

        if ($this->returnPlain) {
            return $html;
        }

        return $this->render('markdownView', array('content' => $html, 'highlightJsCss' => $this->highlightJsCss));
    }

}
