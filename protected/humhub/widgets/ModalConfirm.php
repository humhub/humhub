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
 * ModalConfirmWidget shows a confirm modal before calling an action
 *
 * After successful confirmation this widget returns the response of the called action.
 * So be ensure to write an workflow for that inside your controller action. (for example: close modal, reload page etc.)
 *
 * @package humhub.widgets
 * @since 0.5
 * @author Andreas Strobel
 * @deprecated 1.2 Prefer using js api humhub.ui.modal.confirm.
 */
class ModalConfirm extends \yii\base\Widget
{

    /**
     * @var String Message to show
     */
    public $uniqueID;

    /**
     * @var String define the output element
     */
    public $linkOutput = 'a';

    /**
     * @var String title to show
     */
    public $title;

    /**
     * @var String Message to show
     */
    public $message;

    /**
     * @var String button name for confirming
     */
    public $buttonTrue = "";

    /**
     * @var String button name for canceling
     */
    public $buttonFalse = "";

    /**
     * @var String classes for the displaying link
     */
    public $cssClass;

    /**
     * @var String style for the displaying link
     */
    public $style;

    /**
     * @var String content for the displaying link
     */
    public $linkContent;

    /**
     * @var String original path to view
     */
    public $linkHref;

    /**
     * @var String Tooltip text
     */
    public $linkTooltipText = "";

    /**
     * @var String contains optional JavaScript code to execute, after user clicked the TrueButton
     * By default (when it remains empty), the modal content will be replaced with the content from $linkHref
     */
    public $confirmJS = "";

    /**
     * @var String contains optional JavaScript code to execute after modal has been made visible to the user
     */
    public $modalShownJS = "";
    
    public $ariaLabel = "";

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {
        return $this->render('modalConfirm', array(
                    'uniqueID' => $this->uniqueID,
                    'linkOutput' => $this->linkOutput,
                    'title' => $this->title,
                    'message' => $this->message,
                    'ariaLabel' => $this->ariaLabel,
                    'buttonTrue' => $this->buttonTrue,
                    'buttonFalse' => $this->buttonFalse,
                    'class' => $this->cssClass,
                    'style' => $this->style,
                    'linkContent' => $this->linkContent,
                    'linkHref' => $this->linkHref,
                    'linkTooltipText' => $this->linkTooltipText,
                    'confirmJS' => $this->confirmJS,
                    'modalShownJS' => $this->modalShownJS
        ));
    }

}

?>