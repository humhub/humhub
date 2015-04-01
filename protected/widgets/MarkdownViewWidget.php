<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * MarkdownViewWidget shows Markdown flavored content
 *
 * @author luke
 * @since 0.11
 */
class MarkdownViewWidget extends HWidget
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
    public $parserClass = "HMarkdown";

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

    public function init()
    {
        if (!Helpers::CheckClassType($this->parserClass, "cebe\markdown\Parser")) {
            throw new CException("Invalid markdown parser class given!");
        }

        Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/resources/highlight.js/highlight.pack.js');
        Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/resources/highlight.js/styles/' . $this->highlightJsCss . '.css');
        Yii::app()->clientScript->registerScript("highlightJs", '$("pre code").each(function(i, e) { hljs.highlightBlock(e); });');
    }

    public function run()
    {
        $this->markdown = CHtml::encode($this->markdown);

        $parserClass = $this->parserClass;

        $parser = new $parserClass;
        $html = $parser->parse($this->markdown);

        if ($this->purifyOutput) {
            $purifier = new CHtmlPurifier();
            $html = $purifier->purify($html);
        }

        $this->render('markdownView', array('content' => $html));
    }

}
