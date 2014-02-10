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
 * HContentAddonBehavior should attached to every content addon model
 *
 * Items like Comments, Likes with linked to a
 *      HContentBehavior or HContentAddonBehavior
 * Model
 *
 * An ContentAddon needs to have following DB Fields
 *      - id
 *      - object_model
 *      - object_id
 *
 * Which should point to SIContent or SIContentAddon
 *
 * @package humhub.behaviors
 * @since 0.5
 */
class HContentAddonBehavior extends HActiveRecordBehavior {

    /**
     * Cache Content Ooject and avoid multiple loading
     *
     * @var type
     */
    private $_cacheContentObject = null;

    /**
     * Returns an object which this Addon belongs to
     * Only Support Content -> ContentAddon -> ContentAddon
     *
     * This is always a object which have the behavior: HContentBehavior
     *
     * e.g. Post / Like
     *
     */
    public function getContentObject() {

        // Fastlane?
        if ($this->_cacheContentObject != null)
            return $this->_cacheContentObject;

        $target = $this->getOwner()->getUnderlyingObject();

        if ($target->asa('HContentBehavior') !== null) {
            $this->_cacheContentObject = $target;
            return $target;
        } elseif ($target->asa('HContentAddonBehavior') !== null) {
            $target2 = $target->getOwner()->getUnderlyingObject();

            if ($target2->asa('HContentBehavior') !== null) {
                $this->_cacheContentObject = $target2;
                return $target2;
            }

            # Maybe Multiple Nested?
        }
        throw new CHttpException(500, Yii::t('base', 'Invalid content object!'));
    }

    /**
     * Checks if the given / or current user can delete this content.
     * Currently only the creator can remove.
     *
     * @param type $userId
     */
    public function canDelete($userId = "") {
        if ($userId == "")
            $userId = Yii::app()->user->id;

        if ($this->getOwner()->created_by == $userId)
            return true;

        return false;
    }

    /**
     * Deligate to the underlying content object
     *
     * @param type $userId
     * @return type
     */
    public function canRead($userId = "") {
        return $this->getContentObject()->canRead($userId);
    }

    /**
     * Checks if this content can be changed
     *
     * @param type $userId
     * @return type
     */
    public function canWrite($userId = "") {
        if ($userId == "")
            $userId = Yii::app()->user->id;

        if ($this->getOwner()->created_by == $userId)
            return true;

        return false;
    }

    /**
     * Returns textual title for this content addon
     *
     * @return type
     */
    public function getContentTitle() {
        $objectModel = get_class($this->getOwner()); // e.g. Post

        return $objectModel . " (" . $this->getOwner()->id . ")";
    }

    /**
     * Before saving, check target on content addon
     */
    public function beforeSave($event) {

        if ($this->getOwner()->getUnderlyingObject() == null)
            throw new CHttpException(500, 'Could not load comment target!');

        return true;
    }

    /**
     * After saving
     */
    public function afterSave($event) {
        // Also load corresponding SIContent Object and change "updated_at & updated_by" time
        $this->getContentObject()->save();
    }

    /**
     * After delete
     */
    public function afterDelete($event) {
        // Also load corresponding SIContent Object and change "updated_at & updated_by" time
        $this->getContentObject()->save();
    }

    /**
     * Before deleting a SIContent try to delete all corresponding SIContentAddons.
     */
    public function beforeDelete($event) {

        $objectModel = get_class($this->getOwner()); // e.g. Comment
        // delete all comments (sub comments?) - not implemented yet :-)
        $comments = Comment::model()->findAllByAttributes(array('object_model' => $objectModel, 'object_id' => $this->getOwner()->id));
        foreach ($comments as $comment) {
            $comment->delete();
        }

        // delete all likes
        $likes = Like::model()->findAllByAttributes(array('object_model' => $objectModel, 'object_id' => $this->getOwner()->id));
        foreach ($likes as $like) {
            $like->delete();
        }

        // delete all activities
        $activities = Activity::model()->findAllByAttributes(array('object_model' => $objectModel, 'object_id' => $this->getOwner()->id));
        foreach ($activities as $activity) {
            $activity->delete();
        }
    }

}

?>
