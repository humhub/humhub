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
 * HActiveRecordContentAddon is the base active record for content addons.
 *
 * Content addons are content types like Comments, Files or Likes. 
 * These are always belongs to a Content object.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class HActiveRecordContentAddon extends HActiveRecord
{

    /**
     * Content object which this addon belongs to
     * 
     * @var Content
     */
    private $_content;

    /**
     * Source object which this ContentAddon belongs to.
     * HActiveRecordContentAddon or HActiveRecordContent Object.
     * 
     * @var Mixed 
     */
    private $_source;

    /**
     * Returns the content object to which this addon belongs to.
     * 
     * @return Content Content AR which this Addon belongs to
     */
    public function getContent()
    {

        if ($this->_content != null) {
            return $this->_content;
        }

        if ($this->source instanceof HActiveRecordContent) {
            $this->_content = $this->source->content;
        } elseif ($this->source instanceof HActiveRecordContentAddon && $this->source->source instanceof HActiveRecordContent) {
            $this->_content = $this->source->source->content;
        }

        if ($this->_content == null) {
            throw new CHttpException(500, Yii::t('base', 'Could not find content of addon!'));
        }

        return $this->_content;
    }

    /**
     * Returns the source of this content addon.
     * 
     * This can be a HActiveRecordContent or HActiveRecordContentAddon object.
     * 
     * @return Mixed HActiveRecordContent or HActiveRecordContentAddon
     */
    public function getSource()
    {

        if ($this->_source != null) {
            return $this->_source;
        }

        $className = $this->object_model;
        $pk = $this->object_id;

        if ($className == "") {
            return null;
        }

        if (!class_exists($className)) {
            Yii::log("Source class of content addon not found (".$className.") not found!", CLogger::LEVEL_ERROR);
            return null;            
        }
        
        $this->_source = $className::model()->findByPk($pk);
        return $this->_source;
    }

    /**
     * Checks if the given / or current user can delete this content.
     * Currently only the creator can remove.
     *
     * @return boolean
     */
    public function canDelete()
    {
        if ($this->created_by == Yii::app()->user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if current user can read this object
     *
     * @return boolean
     */
    public function canRead()
    {
        return $this->content->canRead(Yii::app()->user->id);
    }

    /**
     * Checks if this content addon can be changed
     * 
     * @return boolean
     */
    public function canWrite()
    {
        if ($this->created_by == Yii::app()->user->id) {
            return true;
        }

        return false;
    }

    /**
     * Returns textual title for this content addon
     *
     * @return type
     */
    public function getContentTitle()
    {
        $objectModel = get_class($this); // e.g. Like
        return $objectModel . " (" . $this->getPrimaryKey() . ")";
    }

    /**
     * Validates 
     * 
     * @param type $attributes
     * @param type $clearErrors
     * @return type
     */
    public function validate($attributes = null, $clearErrors = true)
    {

        if ($this->source != null) {
            if (!$this->source instanceof HActiveRecordContentAddon && !$this->source instanceof HActiveRecordContent) {
                $this->addError('object_model', Yii::t('base', 'Content Addon source must be instance of HActiveRecordContent or HActiveRecordContentAddon!'));
            }
        }

        return parent::validate($attributes, $clearErrors);
    }

    /**
     * After saving content addon, mark underlying content as updated.
     * 
     * @return boolean
     */
    protected function afterSave()
    {
        // Auto follow the content which this addon belongs to
        $this->getContent()->getUnderlyingObject()->follow($this->created_by);
        return parent::afterSave();
    }

}

?>
