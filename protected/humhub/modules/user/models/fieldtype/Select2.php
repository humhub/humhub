<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

/**
 * ProfileFieldTypeSelect2 handles a searchable list profile fields.
 *
 * @package humhub.modules_core.user.models
 * @since 1.14
 */
class Select2 extends Select
{
    /**
     * @inheritdoc
     */
    public $type = 'select2';
}