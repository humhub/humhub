<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\permissions\CreatePublicContent;
use humhub\modules\space\models\Space;
use humhub\modules\user\helpers\AuthHelper;
use Yii;
use yii\bootstrap\Html;
use yii\bootstrap\InputWidget;

/**
 * ContentVisibilitySelect is a uniform form field for setting the visibility of a content.
 *
 * Features:
 *    - Auto label text and hint text based on the linked ContentContainer
 *    - Hiding if input not needed by the ContentContainer configuration
 *    - Handling of default value
 *
 * Example usage:
 *
 * ```php
 * <?= $form->field($model, $attribute)->widget(ContentVisibilitySelect::class, [
 *     // configure additional widget properties here
 * ]) ?>
 * ```
 *
 * The specified model can either be a ContentActiveRecord or directly a Content record.
 *
 * @since 1.6
 * @package humhub\modules\ui\form\widgets
 */
class ContentVisibilitySelect extends InputWidget
{
    /**
     * @var bool Automatically hide the field when no mulitple visibility modes available
     */
    public $autoHide = true;

    /**
     * @var bool|null Readonly mode (automatically determined if null)
     */
    public $readonly = null;

    /**
     * @var string Property name who is real content owner (For cases when original model is a form model)
     */
    public $contentOwner = 'page';

    /**
     * @var Content
     */
    private $_content;

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->field->label(false);

        if ($this->autoHide && $this->shouldHide()) {
            return '';
        }

        $this->options['label'] = Yii::t('ContentModule.base', 'Public');

        if ($this->getContentContainer() instanceof Space) {
            $this->options['label'] .= ' '
                . Yii::t('ContentModule.base', '(Also visible to non-members of this space)');
        }

        if (
            $this->getContentContainer() === null
            && AuthHelper::isGuestAccessEnabled()
        ) {
            $this->options['label'] .= ' '
                . Yii::t('ContentModule.base', '(Also visible to people who are not logged in)');
        }

        $this->options['title']
            = Yii::t('ContentModule.base', 'Specify who can see this content.');

        if ($this->readonly) {
            $this->options['disabled'] = true;
        }

        $this->setDefaultValue();

        return
            '<div class="checkbox">'
            . Html::activeCheckbox($this->model, $this->attribute, $this->options)
            . '</div>';
    }


    private function setDefaultValue()
    {
        $model = $this->model;
        $attribute = $this->attribute;

        if ($model->$attribute === null) {
            $contentContainer = $this->getContentContainer();
            if ($contentContainer instanceof Space) {
                /** @var Space $contentContainer */
                $model->$attribute = $contentContainer->default_content_visibility;
            }
        }
    }

    /**
     * @return bool
     */
    private function shouldHide()
    {
        $contentContainer = $this->getContentContainer();

        // Should hide on private spaces (Only provide private content visibility option)
        // or if user has no permission to create public content
        if ($contentContainer instanceof Space && $contentContainer->visibility !== Space::VISIBILITY_ALL) {
            /** @var Space $contentContainer */
            if ($contentContainer->visibility === Space::VISIBILITY_NONE
                || !$contentContainer->can(CreatePublicContent::class)) {
                return true;
            }
        }

        // Should hide on global content if Guest access is disabled
        if (
            $contentContainer === null
            && !AuthHelper::isGuestAccessEnabled()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return ContentContainerActiveRecord|null
     */
    private function getContentContainer()
    {
        $content = $this->getContent();
        if ($content !== null) {
            return $content->getContainer();
        }

        return null;
    }

    /**
     * @return Content|null
     */
    private function getContent()
    {
        if ($this->_content !== null) {
            return $this->_content;
        }

        if ($this->model instanceof ContentActiveRecord) {
            $this->_content = $this->model->content;
        } elseif (isset($this->model->{$this->contentOwner}) && $this->model->{$this->contentOwner} instanceof ContentActiveRecord) {
            $this->_content = $this->model->{$this->contentOwner}->content;
        } elseif ($this->model instanceof Content) {
            $this->_content = $this->model;
        }

        return $this->_content;

    }

}
