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
 * This widget is used include the comments functionality to a wall entry.
 *
 * Normally it shows a excerpt of all comments, but provides the functionality
 * to show all comments.
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
class CommentFormWidget extends HWidget
{

    /**
     * Content Object
     */
    public $object;

    /**
     * Executes the widget.
     */
    public function run()
    {

        if (Yii::app()->user->isGuest)
            return "";
        
        $modelName = $this->object->content->object_model;
        $modelId = $this->object->content->object_id;
        $id = $modelName . "_" . $modelId;

        $this->render('form', array(
            'modelName' => $modelName,
            'modelId' => $modelId,
            'id' => $modelName . "_" . $modelId,
        ));
    }

}

?>