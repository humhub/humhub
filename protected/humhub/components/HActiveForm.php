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
 * Extends CActiveFrom with extra field types
 * 
 * @package humhub.components
 * @author Andi
 * @since 0.6.3
 */
class HActiveForm extends CActiveForm
{

    /**
     * Renders a datetime field for a model attribute.
     * 
     * Utilizes bootstrap-datetimepicker.js
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes.
     * @param array $fieldOptions additional picker attributes. (see HHTML::activeDateTimeField)
     * 
     * @return string the generated input field
     */
    public function dateTimeField($model, $attribute, $htmlOptions = array(), $fieldOptions = array())
    {
        return HHtml::activeDateTimeField($model, $attribute, $htmlOptions, $fieldOptions);
    }

}
