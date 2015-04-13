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
 * MarkdownEditorWidget replaces a textarea element with bootstrap-markdown editor
 * 
 * @todo Allow multiple MarkdownEditorWidget instances on a page
 *
 * @author luke
 * @since 0.11
 */
class MarkdownEditorWidget extends HWidget
{

    /**
     * Html field id of textarea which should be Markdown editor
     * 
     * @var string 
     */
    public $fieldId = "";

    /**
     * HMarkdown parser class used for preview
     * 
     * @var string
     */
    public $parserClass = "HMarkdown";

    /**
     * Stylesheet for Highlight.js for preview
     */
    public $highlightJsCss = "github";

    /**
     * Optional markdown preview url 
     * 
     * @var string
     */
    public $previewUrl = "";

    public function init()
    {
        if ($this->previewUrl == "") {
            $this->previewUrl = $this->createUrl('//markdown/preview', array('parser' => $this->parserClass));
        }

        Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/resources/bootstrap-markdown/css/bootstrap-markdown.min.css');
        Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/bootstrap-markdown-override.css');

        Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/resources/bootstrap-markdown/js/bootstrap-markdown.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/markdownEditor.js');
        Yii::app()->clientScript->setJavascriptVariable('markdownPreviewUrl', $this->previewUrl);

        $translations = array(
            'Bold' => Yii::t('widgets_views_markdownEditor', 'Bold'),
            'Italic' => Yii::t('widgets_views_markdownEditor', 'Italic'),
            'Heading' => Yii::t('widgets_views_markdownEditor', 'Heading'),
            'URL/Link' => Yii::t('widgets_views_markdownEditor', 'URL/Link'),
            'Image/File' => Yii::t('widgets_views_markdownEditor', 'Image/File'),
            'Image' => Yii::t('widgets_views_markdownEditor', 'Image'),
            'List' => Yii::t('widgets_views_markdownEditor', 'List'),
            'Preview' => Yii::t('widgets_views_markdownEditor', 'Preview'),
            'strong text' => Yii::t('widgets_views_markdownEditor', 'strong text'),
            'emphasized text' => Yii::t('widgets_views_markdownEditor', 'emphasized text'),
            'heading text' => Yii::t('widgets_views_markdownEditor', 'heading text'),
            'enter link description here' => Yii::t('widgets_views_markdownEditor', 'enter link description here'),
            'Insert Hyperlink' => Yii::t('widgets_views_markdownEditor', 'Insert Hyperlink'),
            'enter image description here' => Yii::t('widgets_views_markdownEditor', 'enter image description here'),
            'Insert Image Hyperlink' => Yii::t('widgets_views_markdownEditor', 'Insert Image Hyperlink'),
            'enter image title here' => Yii::t('widgets_views_markdownEditor', 'enter image title here'),
            'list text here' => Yii::t('widgets_views_markdownEditor', 'list text here'),
            'Quote' => Yii::t('widgets_views_markdownEditor', 'Quote'),
            'quote here' => Yii::t('widgets_views_markdownEditor', 'quote here'),
            'Code' => Yii::t('widgets_views_markdownEditor', 'Code'),
            'code text here' => Yii::t('widgets_views_markdownEditor', 'code text here')
        );

        $translationsJS = "$.fn.markdown.messages['en'] = {\n";
        foreach ($translations as $key => $value) {
            $translationsJS .= "\t'" . $key . "': '" . CHtml::encode($value) . "',\n";
        }
        $translationsJS .= "};\n";
        Yii::app()->clientScript->registerScript('markdownEditorMessages', $translationsJS, CClientScript::POS_READY);
        Yii::app()->clientScript->registerScript("initMarkDownEditor_" . $this->fieldId, "initMarkdownEditor('" . $this->fieldId . "')", CClientScript::POS_READY);
    }

    public function run()
    {
        $this->render('markdownEditor', array(
            'fieldId' => $this->fieldId
        ));
    }

}
