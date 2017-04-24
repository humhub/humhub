<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\Widget;

/**
 * HEditorWidget add users to posts and comments
 *
 * @package humhub.widgets
 * @since 0.5
 * @author Andreas Strobel
 */
class RichTextEditor extends Widget
{

    /**
     * Id of input element which should replaced
     *
     * @var string
     */
    public $id = "";

    /**
     * JSON Search URL
     */
    public $searchUrl = "/search/search/mentioning";

    /**
     * @var string preset content
     */
    public $inputContent = "";

    /**
     * @var \humhub\components\ActiveRecord record record this editor belongs to
     */
    public $record = null;

    /**
     * Inits the widget
     *
     */
    public function init()
    {
        $this->inputContent = nl2br($this->inputContent);
    }

    public function run()
    {
        return $this->render('richTextEditor', ['id' => $this->id, 'userSearchUrl' => $this->searchUrl, 'inputContent' => $this->inputContent]);
    }

}
