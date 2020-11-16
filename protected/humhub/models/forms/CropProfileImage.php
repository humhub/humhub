<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\models\forms;

use yii\base\Model;
use yii\web\JsExpression;

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
     * @var array
     * @since 1.4
     */
    public $minSize = [50, 50];

    /**
     * @var string
     * @since 1.4
     */
    public $bgColor = 'black';

    /**
     * @var string
     * @since 1.4
     */
    public $bgOpacity = '0.5';

    /**
     * @var string
     * @since 1.4
     */
    public $boxWidth = '440';

    /**
     * @var boolean
     */
    public $keySupport = true;

    /**
     * @var string
     * @since 1.4
     */
    public $onChangeJs = 'function(c){ $("#cropX").val(c.x);$("#cropY").val(c.y);$("#cropW").val(c.w);$("#cropH").val(c.h); }';

    /**
     * Declares the validation rules.
     *
     * @return Array Validation Rules
     */
    public function rules()
    {
        return [
            [['cropX', 'cropY', 'cropW', 'cropH'], 'required'],
            [['cropX', 'cropY', 'cropW', 'cropH'], 'number'],
        ];
    }

    /**
     * @return array
     * @since 1.4
     */
    public function getPluginOptions()
    {
        return  [
            'aspectRatio' => $this->aspectRatio,
            'minSize' => $this->minSize,
            'setSelect' => $this->cropSetSelect,
            'bgColor' =>  $this->bgColor,
            'bgOpacity' => $this->bgOpacity,
            'boxWidth' => $this->boxWidth,
            'onChange' => new JsExpression('function(c){ $("#cropX").val(c.x);$("#cropY").val(c.y);$("#cropW").val(c.w);$("#cropH").val(c.h); }'),
            'keySupport' => $this->keySupport,
        ];
    }

}
