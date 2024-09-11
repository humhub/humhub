<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

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
 * @deprecated since 1.17
 * @since 1.6
 * @package humhub\modules\ui\form\widgets
 */
class ContentVisibilitySelect extends \humhub\widgets\bootstrap\ContentVisibilitySelect
{
}
