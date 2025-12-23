<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\form;

use humhub\helpers\Html;
use humhub\modules\ui\icon\widgets\Icon;
use yii\base\Widget;

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
     * @var bool True to render only fields which are active by current scenario and it is not readonly
     * @since 1.18
     */
    public $renderOnlySafeAttributes = false;

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
     * @param string $title the title of the form field group
     * @param bool $isClosed whether the field group is closed by default
     * @return string
     * @since 1.8
     */
    public function beginCollapsibleFields($title, $isClosed = true)
    {
        return
            Html::beginTag('div', [
                'class' => 'form-collapsible-fields',
                'data-ui-widget' => 'ui.form.elements.FormFieldsCollapsible',
                'data-ui-init' => 1,
            ])
            . Html::tag(
                'div',
                Icon::get('plus') . Icon::get('minus') . ' &nbsp;'
                . Html::label($title, null, ['class' => 'control-label']),
                [
                    'class' => 'form-collapsible-fields-label' . ($isClosed ? ' collapsed' : ''),
                    'data-bs-toggle' => 'collapse',
                    'data-bs-target' => '#collapsibleFields-' . ($fieldsetId = (new Widget())->id),
                ],
            )
            . Html::beginTag('fieldset', [
                'id' => 'collapsibleFields-' . $fieldsetId,
                'class' => 'collapse' . ($isClosed ? '' : ' show'),
            ]);
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
