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
 * A SIUnderlyingObjectBahavior adds the ability to models/active records
 * to link to any other model/active record.
 *
 * This is archived by the database fields object_model & object_id.
 *
 * Required database fields:
 *  - object_model
 *  - object_id
 *
 * @package humhub.behaviors
 * @since 0.5
 */
class HUnderlyingObjectBehavior extends HActiveRecordBehavior {

    /**
     * The underlying object needs to be a "instanceof" at least one
     * of this values.
     *
     * (Its also possible to specify a CBehavior name)
     *
     * @var type
     */
    public $mustBeInstanceOf = array();

    /**
     * Cache Object to avoid multiple loading
     *
     * @var type
     */
    private $_cachedObject = null;

    /**
     * Cache Object is null
     */
    private $_cached = false;

    /*
     * Returns the Underlying Object
     *
     * @return mixed
     */

    public function getUnderlyingObject() {

        
        if ($this->_cached)
            return $this->_cachedObject;


        $className = $this->getOwner()->object_model;

        if ($className == "") {
            $this->_cached = true;
            return null;
        }
        
  
        $object = $className::model()->findByPk($this->getOwner()->object_id);
        
        if (count($this->mustBeInstanceOf) == 0 || $object == null) {
            $this->_cached = true;
            $this->_cachedObject = $object;
            return $object;
        }

        // Validates object
        foreach ($this->mustBeInstanceOf as $instance) {
            if ($object instanceof $instance || $object->asa($instance) !== null) {
                $this->_cached = true;
                $this->_cachedObject = $object;
                return $object;
            }
        }

        throw new CHttpException(500, 'Underlying object of invalid type! (' . $className . ')');
    }

}

?>
