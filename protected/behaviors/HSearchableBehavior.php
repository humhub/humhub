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
 * Searchable Behavior
 * 
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.behaviors
 * @since 0.5
 */
class HSearchableBehavior extends HActiveRecordBehavior {

    public function afterSave($event) {

        if ($this->getOwner() instanceof ISearchable) {

            if (!$this->getOwner()->isNewRecord)
                HSearch::getInstance()->deleteModel($this->getOwner());

            HSearch::getInstance()->addModel($this->getOwner());
        }

        parent::afterSave($event);
    }

    public function afterDelete($event) {

        if ($this->getOwner() instanceof ISearchable) {
            HSearch::getInstance()->deleteModel($this->getOwner());
        }

        parent::afterDelete($event);
    }

}

?>