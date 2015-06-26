<?php

namespace humhub\core\activity;

/**
 * ActivityModule is responsible for all activities functions.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.modules_core.activity
 * @since 0.5
 */
class Module extends \yii\base\Module
{

    public $isCoreModule = true;

    /**
     * Inits the activity module
     */
    public function init()
    {
        $this->setImport(array(
            'activity.models.*',
            'activity.behaviors.*',
        ));
    }

    /**
     * Formatted the activity content before delivery
     *
     * @param string $text
     */
    public static function formatOutput($text)
    {
        $text = HHtml::translateMentioning($text, false);
        $text = HHtml::translateEmojis($text, false);

        return $text;
    }

}
