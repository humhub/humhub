<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\ui\form\validators;

use humhub\modules\ui\form\widgets\IconPicker;
use Yii;
use yii\validators\Validator;


/**
 * IconValidator validates input from the IconPicker
 *
 * @since 1.3
 * @see IconPicker
 */
class IconValidator extends Validator
{

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $iconPicker = new IconPicker(['model' => $model, 'attribute' => $attribute]);

        if (!in_array($model->$attribute, $iconPicker->getIcons())) {
            $this->addError($model, $attribute, Yii::t('UiModule.form', 'Invalid icon.'));
        }
    }

}
