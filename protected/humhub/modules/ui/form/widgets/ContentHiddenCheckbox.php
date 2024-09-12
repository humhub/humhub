<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

/**
 * ContentHiddenCheckbox is the form field to set the Content Hidden flag.
 * Both default values and the actual content value are supported by this field.
 *
 * Mainly this field is used to provide a consistent label and hint across modules.
 *
 *  Example usage:
 *  ```
 *  <?= $form->field($model, 'contentHiddenDefault')->widget(ContentHiddenCheckbox::class, [
 *      'type' => ContentHiddenCheckbox::TYPE_CONTENTCONTAINER,
 *  ]); ?>
 *  ```
 *
 * @deprecated since 1.17
 * @since 1.14
 */
class ContentHiddenCheckbox extends \humhub\widgets\bootstrap\ContentHiddenCheckbox
{
}
