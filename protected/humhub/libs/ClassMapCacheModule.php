<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

/**
 * Helper class to return structured data on cached module info
 *
 * @see ClassMapCache
 * @author Martin Rüegg
 */
class ClassMapCacheModule
{
    //  public properties
    public ?int $id = null;
    public string $class_name;
    public string $moduleId;


    public function __construct($values, string $class_name = '', string $moduleId = '')
    {
        if (is_object($values)) {
            $this->id         = $values->id;
            $this->class_name = $values->class_name;
            $this->moduleId   = $values->moduleId;

            return;
        }

        if (is_array($values)) {
            $this->id         = $values['id'];
            $this->class_name = $values['class_name'];
            $this->moduleId   = $values['moduleId'];

            return;
        }

        $this->id         = $values;
        $this->class_name = $class_name;
        $this->moduleId   = $moduleId;
    }
}
