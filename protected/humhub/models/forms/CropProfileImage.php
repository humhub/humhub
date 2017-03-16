<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\models\forms;

use yii\base\Model;

/**
 * CropProfileImageForm is a form for image cropping.
 *
 * Will used by user or space profile image cropping.
 *
 * @package humhub.forms
 * @since 0.5
 */
class CropProfileImage extends Model
{

    /**
     * @var Int X Coordinates of the area
     */
    public $cropX;

    /**
     * @var Int Y Coordinates of the area
     */
    public $cropY;

    /**
     * @var Int is the width of the area
     */
    public $cropW;

    /**
     * @var Int is the height of the area
     */
    public $cropH;

    /**
     * @var int image ratio
     */
    public $aspectRatio = 1;

    /**
     * @var array crop default position
     */
    public $cropSetSelect = [0, 0, 100, 100];

    /**
     * Declares the validation rules.
     *
     * @return Array Validation Rules
     */
    public function rules()
    {
        return array(
            array(['cropX', 'cropY', 'cropW', 'cropH'], 'required'),
            array(['cropX', 'cropY', 'cropW', 'cropH'], 'number'),
        );
    }

}
