<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\form;

use humhub\helpers\Html;
use humhub\modules\ui\icon\widgets\Icon;

/**
 * A HumHub enhanced version of native bootstrap ActiveForm
 *
 * @since 1.2
 * @author Luke
 */
class ActiveForm extends \yii\bootstrap5\ActiveForm
{
    /**
     * @inheritdoc
     */
    public $enableClientValidation = false;

    /**
     * @inheritdoc
     */
    public $fieldClass = ActiveField::class;

    /**
     * @var bool If user trying to leave unsaved data on the page
     * this option implements the message box that asks user to save
     * data of the form before leaving.
     */
    public $acknowledge = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->acknowledge) {
            $this->options['data-ui-addition'] = 'acknowledgeForm';
        }
    }

    /**
     * Starts a section of collapsible form fields.
     * Make sure that the `endCollapsibleFields` method is also called afterwards.
     * It is not possible to nest these sections.
     *
     * @param $title string the title of the form field group
     * @return string
     * @since 1.8
     */
    public function beginCollapsibleFields($title, $isClosed = true)
    {
        $cssClass = ($isClosed) ? 'closed' : 'opened';

        return
            Html::beginTag('div', ['class' => 'form-collapsible-fields ' . $cssClass, 'data-ui-widget' => 'ui.form.elements.FormFieldsCollapsible', 'data-ui-init' => 1])
            . Html::tag(
                'div',
                Html::tag(
                    'div',
                    Icon::get('plus', ['htmlOptions' => ['class' => 'iconOpen']])
                    . Icon::get('minus', ['htmlOptions' => ['class' => 'iconClose']]) . '&nbsp;&nbsp;',
                    ['class' => 'float-start'],
                )
                . Html::label($title, null, ['class' => 'control-label']),
                ['class' => 'form-collapsible-fields-label', 'data-action-click' => 'clickCollab', 'data-bs-toggle' => 'tab'],
            )
            . Html::beginTag('fieldset');
    }

    /**
     * Starts a section of collapsible form fields.
     *
     * @return string
     * @since 1.8
     */
    public function endCollapsibleFields()
    {
        return Html::endTag('fieldset')
            . Html::endTag('div');
    }
}
