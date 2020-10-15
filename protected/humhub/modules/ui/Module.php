<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui;

use Yii;

/**
 * This module provides general user interface components.
 *
 * @since 1.3
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $isCoreModule = true;

    /**
     * @var array contains all available icon aliases
     */
    public $iconAlias = [
        'dropdownToggle' => 'angle-down',
        'edit' => 'pencil',
        'delete' => 'trash',
        'dashboard' => 'tachometer',
        'directory' => 'book',
        'back' => 'arrow-left',
        'add' => 'plus',
        'invite' => 'paper-plane',
        'remove' => 'times',
        'controls' => 'cog',
        'about' => 'info-circle',
        'stream' => 'bars'
    ];

    /**
     * @return static
     */
    public static function getModuleInstance()
    {
        /* @var $module static*/
        $module =  Yii::$app->getModule('ui');
        return $module;
    }

    /**
     * @param $name
     * @return string|null
     */
    public function getIconAlias($name)
    {
        return $this->iconAlias[$name] ?? $name;
    }


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Yii::t('UiModule.base', 'User Interface');
    }

}
