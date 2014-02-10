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

/**
 * CropProfileImageForm is a form for image cropping.
 *
 * Will used by user or space profile image cropping.
 *
 * @package humhub.forms
 * @since 0.5
 */
class CropProfileImageForm extends CFormModel {

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
     * Declares the validation rules.
     *
     * @return Array Validation Rules
     */
    public function rules() {
        return array(
            array('cropX, cropY, cropW, cropH', 'numerical', 'integerOnly' => true),
            array('cropX, cropY, cropW, cropH', 'safe'),
        );
    }

}
