<?php


namespace humhub\libs;

use humhub\components\Module;

/**
 * Class I18NHelper
 *
 * @since 1.4
 * @package humhub\libs
 */
class I18NHelper
{
    /**
     * Returns the default translation category for a given moduleId.
     *
     * Examples:
     *      example -> ExampleModule.
     *      long_module_name -> LongModuleNameModule.
     *
     * @return string the category id
     */
    public static function getModuleTranslationCategory($moduleId)
    {
        return implode('', array_map("ucfirst", preg_split("/(_|\-)/", $moduleId))) . 'Module.';

    }
}
