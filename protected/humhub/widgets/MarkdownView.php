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

namespace humhub\widgets;

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
    }

    public function run()
    {
        $this->markdown = \yii\helpers\Html::encode($this->markdown);

        $parserClass = $this->parserClass;

        $parser = new $parserClass;
        $html = $parser->parse($this->markdown);

        if ($this->purifyOutput) {
            $html = \yii\helpers\HtmlPurifier::progress($html);
        }

        return $this->render('markdownView', array('content' => $html));
    }

}
