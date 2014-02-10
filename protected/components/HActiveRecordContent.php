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
 * HActiveRecordContent is the base activerecord for content objects.
 *
 * Each model which represents a content should derived from it.
 * (e.g. Post, Question, Task, Note, ...)
 *
 * It automatically adds the HContentBehavior which binds a 'contentMeta'
 * attribute, that always points to a Content Model Record.
 * (See SiContentBavior for more details.)
 *
 * The Content Model is responsible for
 *  - Content to User/Space Binding
 *  - Access Controlling
 *  - Wall Integration
 *  - ...
 * (See Content Model for more details.)
 *
 * Cleanup:
 * On workspace or user deletion, this objects will automatically deleted.
 *
 * Note: Comments, Likes or Files are NOT Content Objects. These objects are
 * ContentAddons (HActiveRecordContentAddon) which always belongs to one
 * Content Object.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class HActiveRecordContent extends HActiveRecord {

    /**
     * Extended Constructor which automatically attaches
     * the HContentBehavior to the objects.
     *
     * @param type $scenario
     */
    public function __construct($scenario = 'insert') {

        parent::__construct($scenario);

        $this->attachBehavior('HContentBehavior', array(
            'class' => 'application.behaviors.HContentBehavior'
        ));
    }

    /**
     * Returns a short textual title for this content.
     * Default goes to "Classname (Id)"
     *
     * It should be overwritten for a more representative text.
     *
     * @return type
     */
    public function getContentTitle() {
        $objectModel = get_class($this); // e.g. Post
        return $objectModel . " (" . $this->getOwner()->id . ")";
    }

    /**
     * If the content should also displayed on a wall, overwrite this
     * method and produce a wall output.
     *
     * e.g.
     * return Yii::app()->getController()->widget('application.modules.myModule.MyContentWidget',
     *      array('myContent' => $this),
     *      true
     * );
     *
     * @return type
     */
    public function getWallOut() {
        return "Default Wall Output for Class " . get_class($this->getOwner());
    }

}

?>
